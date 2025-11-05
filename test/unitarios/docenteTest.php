<?php

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once 'model/docente.php';

class DocenteTest extends TestCase
{
    /** @var \PDO&MockObject */
    private $pdoMock;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
    }

    /**
     * @return Docente&MockObject
     */
    private function createDocenteMock(array $extraMethods = [])
    {
        $builder = $this->getMockBuilder(Docente::class)
            ->disableOriginalConstructor();
        $builder->onlyMethods(array_merge(['Con'], $extraMethods));
        $docente = $builder->getMock();
        $docente->method('Con')->willReturn($this->pdoMock);
        return $docente;
    }

    private function seedDocenteDatos(Docente $docente): void
    {
        $docente->setCedula(12345678);
        $docente->setPrefijo('V');
        $docente->setNombre('John');
        $docente->setApellido('Doe');
        $docente->setCorreo('john@example.com');
        $docente->setCategoriaNombre('Titular');
        $docente->setDedicacion('TC');
        $docente->setCondicion('Ordinario');
        $docente->setIngreso('2020-01-01');
        $docente->setAnioConcurso(2021);
        $docente->setTipoConcurso('Abierto');
        $docente->setObservacion('Obs');
        $docente->setTitulos(['MSC::Docencia']);
        $docente->setCoordinaciones(['Coordinación 1']);
        $docente->setHorasAcademicas(10);
        $docente->setCreacionIntelectual(5);
        $docente->setIntegracionComunidad(3);
        $docente->setGestionAcademica(2);
        $docente->setOtras(1);
    }

    /** @return PDOStatement&MockObject */
    private function makeStatement()
    {
        /** @var \PDOStatement&MockObject $stmt */
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        return $stmt;
    }

    public function testRegistrar_Falla_DocenteActivo(): void
    {
        $docente = $this->createDocenteMock();
        $docente->setCedula(12345678);

        $stmtEstado = $this->makeStatement();
        $stmtEstado->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 12345678])
            ->willReturn(true);
        $stmtEstado->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['doc_estado' => '1']);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT doc_estado FROM tbl_docente'))
            ->willReturn($stmtEstado);

        $this->pdoMock->expects($this->never())->method('beginTransaction');

        $resultado = $docente->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya se encuentra registrada', $resultado['mensaje']);
    }

    public function testRegistrar_ReactivacionDocenteInactivo(): void
    {
        $docente = $this->createDocenteMock();
        $this->seedDocenteDatos($docente);

        $stmtEstado = $this->makeStatement();
        $stmtEstado->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 12345678])
            ->willReturn(true);
        $stmtEstado->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['doc_estado' => '0']);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())->method('execute')->willReturn(true);

        $stmtDeleteTitulo = $this->makeStatement();
        $stmtDeleteTitulo->expects($this->once())->method('execute')->willReturn(true);

        $stmtInsertTitulo = $this->makeStatement();
        $stmtInsertTitulo->expects($this->once())
            ->method('execute')
            ->with([
                ':doc_cedula' => 12345678,
                ':tit_prefijo' => 'MSC',
                ':tit_nombre' => 'Docencia',
            ])
            ->willReturn(true);

        $stmtDeleteCoord = $this->makeStatement();
        $stmtDeleteCoord->expects($this->once())->method('execute')->willReturn(true);

        $stmtInsertCoord = $this->makeStatement();
        $stmtInsertCoord->expects($this->once())
            ->method('execute')
            ->with([
                ':doc_cedula' => 12345678,
                ':cor_nombre' => 'Coordinación 1',
            ])
            ->willReturn(true);

        $stmtSelectActividad = $this->makeStatement();
        $stmtSelectActividad->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmtSelectActividad->method('fetch')->willReturn(false);

        $stmtInsertActividad = $this->makeStatement();
        $stmtInsertActividad->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdoMock->expects($this->exactly(8))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT doc_estado FROM tbl_docente')],
                [$this->stringStartsWith('UPDATE tbl_docente SET')],
                [$this->stringContains('DELETE FROM titulo_docente')],
                [$this->stringContains('INSERT INTO titulo_docente')],
                [$this->stringContains('DELETE FROM coordinacion_docente')],
                [$this->stringContains('INSERT INTO coordinacion_docente')],
                [$this->stringContains('SELECT doc_cedula FROM tbl_actividad')],
                [$this->stringContains('INSERT INTO tbl_actividad')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtEstado,
                $stmtUpdate,
                $stmtDeleteTitulo,
                $stmtInsertTitulo,
                $stmtDeleteCoord,
                $stmtInsertCoord,
                $stmtSelectActividad,
                $stmtInsertActividad
            );

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $resultado = $docente->Registrar();

        $this->assertEquals('incluir', $resultado['resultado']);
        $this->assertStringContainsString('reactivado y actualizado', $resultado['mensaje']);
    }

    public function testRegistrar_FallaEnInsertPrincipal(): void
    {
        $docente = $this->createDocenteMock();
        $this->seedDocenteDatos($docente);

        $stmtEstado = $this->makeStatement();
        $stmtEstado->method('execute')->willReturn(true);
        $stmtEstado->method('fetch')->with(PDO::FETCH_ASSOC)->willReturn(false);

        $stmtInsert = $this->makeStatement();
        $stmtInsert->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception('Fallo insert'));

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT doc_estado FROM tbl_docente')],
                [$this->stringStartsWith('INSERT INTO tbl_docente')]
            )
            ->willReturnOnConsecutiveCalls($stmtEstado, $stmtInsert);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        $resultado = $docente->Registrar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo insert', $resultado['mensaje']);
    }

    public function testRegistrar_NuevoDocente_Exito(): void
    {
        $docente = $this->createDocenteMock();
        $this->seedDocenteDatos($docente);

        $stmtEstado = $this->makeStatement();
        $stmtEstado->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 12345678])
            ->willReturn(true);
        $stmtEstado->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        $stmtInsertDoc = $this->makeStatement();
        $stmtInsertDoc->expects($this->once())->method('execute')->willReturn(true);

        $stmtInsertTitulo = $this->makeStatement();
        $stmtInsertTitulo->expects($this->once())->method('execute')->willReturn(true);

        $stmtInsertCoord = $this->makeStatement();
        $stmtInsertCoord->expects($this->once())->method('execute')->willReturn(true);

        $stmtActividadCheck = $this->makeStatement();
        $stmtActividadCheck->expects($this->once())->method('execute')->willReturn(true);
        $stmtActividadCheck->method('fetch')->willReturn(false);

        $stmtActividadInsert = $this->makeStatement();
        $stmtActividadInsert->expects($this->once())->method('execute')->willReturn(true);

        $this->pdoMock->expects($this->exactly(6))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT doc_estado FROM tbl_docente')],
                [$this->stringStartsWith('INSERT INTO tbl_docente')],
                [$this->stringContains('INSERT INTO titulo_docente')],
                [$this->stringContains('INSERT INTO coordinacion_docente')],
                [$this->stringContains('SELECT doc_cedula FROM tbl_actividad')],
                [$this->stringStartsWith('INSERT INTO tbl_actividad')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtEstado,
                $stmtInsertDoc,
                $stmtInsertTitulo,
                $stmtInsertCoord,
                $stmtActividadCheck,
                $stmtActividadInsert
            );

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $resultado = $docente->Registrar();

        $this->assertEquals('incluir', $resultado['resultado']);
        $this->assertStringContainsString('Se registró el docente correctamente', $resultado['mensaje']);
    }

    public function testModificar_DocenteNoExiste_RetornaError(): void
    {
        $docente = $this->createDocenteMock(['existe']);
        $docente->method('existe')->with(12345678)->willReturn(false);
        $docente->setCedula(12345678);

        $resultado = $docente->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe o está inactivo', $resultado['mensaje']);
    }

    public function testModificar_ActualizaCorrectamente(): void
    {
        $docente = $this->createDocenteMock(['existe']);
        $docente->method('existe')->with(12345678)->willReturn(true);
        $this->seedDocenteDatos($docente);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())->method('execute')->willReturn(true);

        $stmtDeleteTitulo = $this->makeStatement();
        $stmtDeleteTitulo->expects($this->once())->method('execute')->willReturn(true);

        $stmtInsertTitulo = $this->makeStatement();
        $stmtInsertTitulo->expects($this->once())->method('execute')->willReturn(true);

        $stmtDeleteCoord = $this->makeStatement();
        $stmtDeleteCoord->expects($this->once())->method('execute')->willReturn(true);

        $stmtInsertCoord = $this->makeStatement();
        $stmtInsertCoord->expects($this->once())->method('execute')->willReturn(true);

        $stmtSelectActividad = $this->makeStatement();
        $stmtSelectActividad->expects($this->once())->method('execute')->willReturn(true);
        $stmtSelectActividad->method('fetch')->willReturn(['doc_cedula' => 12345678]);

        $stmtUpdateActividad = $this->makeStatement();
        $stmtUpdateActividad->expects($this->once())->method('execute')->willReturn(true);

        $this->pdoMock->expects($this->exactly(7))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringStartsWith('UPDATE tbl_docente SET')],
                [$this->stringContains('DELETE FROM titulo_docente')],
                [$this->stringContains('INSERT INTO titulo_docente')],
                [$this->stringContains('DELETE FROM coordinacion_docente')],
                [$this->stringContains('INSERT INTO coordinacion_docente')],
                [$this->stringContains('SELECT doc_cedula FROM tbl_actividad')],
                [$this->stringStartsWith('UPDATE tbl_actividad SET')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtUpdate,
                $stmtDeleteTitulo,
                $stmtInsertTitulo,
                $stmtDeleteCoord,
                $stmtInsertCoord,
                $stmtSelectActividad,
                $stmtUpdateActividad
            );

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        $resultado = $docente->Modificar();

        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('modificó el docente correctamente', $resultado['mensaje']);
    }

    public function testModificar_ErrorDuranteActualizacion(): void
    {
        $docente = $this->createDocenteMock(['existe']);
        $docente->method('existe')->with(12345678)->willReturn(true);
        $this->seedDocenteDatos($docente);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception('Fallo update docente'));

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringStartsWith('UPDATE tbl_docente SET'))
            ->willReturn($stmtUpdate);

        $resultado = $docente->Modificar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo update docente', $resultado['mensaje']);
    }

    public function testEliminar_DocenteNoExiste(): void
    {
        $docente = $this->createDocenteMock(['existe']);
        $docente->method('existe')->with(555)->willReturn(false);
        $docente->setCedula(555);

        $resultado = $docente->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no existe o ya está inactivo', $resultado['mensaje']);
    }

    public function testEliminar_Exito(): void
    {
        $docente = $this->createDocenteMock(['existe']);
        $docente->method('existe')->with(555)->willReturn(true);
        $docente->setCedula(555);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 555])
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE tbl_docente SET doc_estado = 0'))
            ->willReturn($stmtUpdate);

        $resultado = $docente->Eliminar();

        $this->assertEquals('eliminar', $resultado['resultado']);
        $this->assertStringContainsString('Se cambio el estado del docente correctamente', $resultado['mensaje']);
    }

    public function testEliminar_ErrorEnUpdate(): void
    {
        $docente = $this->createDocenteMock(['existe']);
        $docente->method('existe')->with(555)->willReturn(true);
        $docente->setCedula(555);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception('Fallo eliminar docente'));

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE tbl_docente SET doc_estado = 0'))
            ->willReturn($stmtUpdate);

        $resultado = $docente->Eliminar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo eliminar docente', $resultado['mensaje']);
    }

    public function testActivar_Exito(): void
    {
        $docente = $this->createDocenteMock();
        $docente->setCedula(321);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 321])
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE tbl_docente SET doc_estado = 1'))
            ->willReturn($stmtUpdate);

        $resultado = $docente->Activar();

        $this->assertEquals('activar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Activo', $resultado['mensaje']);
    }

    public function testActivar_Error(): void
    {
        $docente = $this->createDocenteMock();
        $docente->setCedula(321);

        $stmtUpdate = $this->makeStatement();
        $stmtUpdate->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception('Fallo activar docente'));

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE tbl_docente SET doc_estado = 1'))
            ->willReturn($stmtUpdate);

        $resultado = $docente->Activar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo activar docente', $resultado['mensaje']);
    }

    public function testListar_Exito(): void
    {
        $docente = $this->createDocenteMock();

        $stmtPrincipal = $this->makeStatement();
        $stmtPrincipal->expects($this->once())->method('execute')->willReturn(true);
        $stmtPrincipal->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn([
            [
                'doc_prefijo' => 'V',
                'doc_cedula' => '111',
                'doc_nombre' => 'Ana',
                'doc_apellido' => 'Pérez',
                'doc_correo' => 'ana@example.com',
                'doc_dedicacion' => 'TC',
                'doc_condicion' => 'Ordinario',
                'doc_ingreso' => '2020-01-01',
                'doc_anio_concurso' => '2021',
                'doc_tipo_concurso' => 'Abierto',
                'doc_observacion' => 'Obs',
                'cat_nombre' => 'Titular',
                'doc_estado' => 1,
            ],
        ]);

        $stmtTitulos = $this->makeStatement();
        $stmtTitulos->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => '111'])
            ->willReturn(true);
        $stmtTitulos->method('fetch')->with(PDO::FETCH_ASSOC)->willReturn([
            'titulos' => 'Doctor',
            'titulos_ids' => 'DR::Doctor',
        ]);

        $stmtCoordinaciones = $this->makeStatement();
        $stmtCoordinaciones->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => '111'])
            ->willReturn(true);
        $stmtCoordinaciones->method('fetch')->with(PDO::FETCH_ASSOC)->willReturn([
            'coordinaciones' => 'Coord 1',
            'coordinaciones_ids' => 'Coord 1',
        ]);

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT d.doc_prefijo')],
                [$this->stringContains('SELECT GROUP_CONCAT')],
                [$this->stringContains('FROM coordinacion_docente')]
            )
            ->willReturnOnConsecutiveCalls($stmtPrincipal, $stmtTitulos, $stmtCoordinaciones);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $resultado = $docente->Listar();

        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertSame('Doctor', $resultado['mensaje'][0]['titulos']);
        $this->assertSame('Coord 1', $resultado['mensaje'][0]['coordinaciones']);
    }

    public function testListar_ErrorEnConsulta(): void
    {
        $docente = $this->createDocenteMock();

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT d.doc_prefijo'))
            ->willThrowException(new Exception('Fallo listar'));

        $resultado = $docente->Listar();

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo listar', $resultado['mensaje']);
    }

    public function testExiste_RetornaTrue(): void
    {
        $docente = $this->createDocenteMock();
        $docente->setCedula(1);

        $stmt = $this->makeStatement();
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 1])
            ->willReturn(true);
        $stmt->method('rowCount')->willReturn(1);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT * FROM tbl_docente'))
            ->willReturn($stmt);

        $this->assertTrue($docente->Existe(1));
    }

    public function testExiste_RetornaFalseCuandoNoHayRegistros(): void
    {
        $docente = $this->createDocenteMock();

        $stmt = $this->makeStatement();
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(0);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT * FROM tbl_docente'))
            ->willReturn($stmt);

        $this->assertFalse($docente->Existe(99));
    }

    public function testExiste_RetornaFalseAnteExcepcion(): void
    {
        $docente = $this->createDocenteMock();

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT * FROM tbl_docente'))
            ->willThrowException(new Exception('Fallo existe'));

        $this->assertFalse($docente->Existe(10));
    }

    public function testObtenerHorasActividad_Encontradas(): void
    {
        $docente = $this->createDocenteMock();

        $stmt = $this->makeStatement();
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 200])
            ->willReturn(true);
        $stmt->method('fetch')->willReturn([
            'act_academicas' => '5',
            'act_creacion_intelectual' => '3',
            'act_integracion_comunidad' => '2',
            'act_gestion_academica' => '1',
            'act_otras' => '0',
        ]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT act_academicas'))
            ->willReturn($stmt);

        $resultado = $docente->ObtenerHorasActividad(200);

        $this->assertEquals('consultar_horas', $resultado['resultado']);
        $this->assertEquals('5', $resultado['mensaje']['act_academicas']);
    }

    public function testObtenerHorasActividad_NoEncontradas(): void
    {
        $docente = $this->createDocenteMock();

        $stmt = $this->makeStatement();
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT act_academicas'))
            ->willReturn($stmt);

        $resultado = $docente->ObtenerHorasActividad(200);

        $this->assertEquals('horas_no_encontradas', $resultado['resultado']);
        $this->assertEquals('0', $resultado['mensaje']['act_academicas']);
    }

    public function testObtenerHorasActividad_Error(): void
    {
        $docente = $this->createDocenteMock();

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT act_academicas'))
            ->willThrowException(new Exception('Fallo horas'));

        $resultado = $docente->ObtenerHorasActividad(200);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo horas', $resultado['mensaje']);
    }

    public function testObtenerPreferenciasHorario_ConDatos(): void
    {
        $docente = $this->createDocenteMock();

        $stmt = $this->makeStatement();
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':doc_cedula' => 300])
            ->willReturn(true);
        $stmt->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn([
            ['dia_semana' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '10:00'],
            ['dia_semana' => 'Martes', 'hora_inicio' => '09:00', 'hora_fin' => '11:00'],
        ]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT dia_semana, hora_inicio, hora_fin'))
            ->willReturn($stmt);

        $resultado = $docente->ObtenerPreferenciasHorario(300);

        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals('08:00', $resultado['mensaje']['Lunes']['inicio']);
    }

    public function testObtenerPreferenciasHorario_Error(): void
    {
        $docente = $this->createDocenteMock();

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT dia_semana, hora_inicio, hora_fin'))
            ->willThrowException(new Exception('Fallo preferencias'));

        $resultado = $docente->ObtenerPreferenciasHorario(300);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo preferencias', $resultado['mensaje']);
    }

    public function testVerificarEnHorario_ConAsignaciones(): void
    {
        $docente = $this->createDocenteMock();

        $stmt = $this->makeStatement();
        $stmt->expects($this->once())
            ->method('execute')
            ->with([':docCedula' => 87654321])
            ->willReturn(true);
        $stmt->method('fetchColumn')->willReturn(2);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM docente_horario'))
            ->willReturn($stmt);

        $resultado = $docente->verificarEnHorario(87654321);

        $this->assertEquals('en_horario', $resultado['resultado']);
        $this->assertStringContainsString('está en un horario', $resultado['mensaje']);
    }

    public function testVerificarEnHorario_SinAsignaciones(): void
    {
        $docente = $this->createDocenteMock();

        $stmt = $this->makeStatement();
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchColumn')->willReturn(0);

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM docente_horario'))
            ->willReturn($stmt);

        $resultado = $docente->verificarEnHorario(1000);

        $this->assertEquals('no_en_horario', $resultado['resultado']);
        $this->assertStringContainsString('no está en un horario', $resultado['mensaje']);
    }

    public function testVerificarEnHorario_Error(): void
    {
        $docente = $this->createDocenteMock();

        $this->pdoMock->expects($this->once())
            ->method('setAttribute')
            ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM docente_horario'))
            ->willThrowException(new Exception('Fallo horario'));

        $resultado = $docente->verificarEnHorario(999);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Fallo horario', $resultado['mensaje']);
    }
}
