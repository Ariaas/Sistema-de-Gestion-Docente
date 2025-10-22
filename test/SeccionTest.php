<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\StringStartsWith;

// Incluir la clase del modelo que vamos a probar
require_once 'model/seccion.php'; // Asegúrate que la ruta sea correcta

/**
 * --- SUITE DE PRUEBAS PARA LA CLASE Seccion ---
 *
 * Esta suite prueba la lógica de negocio del modelo Seccion,
 * aislando la base de datos mediante Mocks de PDO.
 *
 * @coversDefaultClass Seccion
 */
class SeccionTest extends TestCase
{
    /** @var Seccion|\PHPUnit\Framework\MockObject\MockObject */
    private $seccion;

    /** @var PDO|\PHPUnit\Framework\MockObject\MockObject */
    private $pdoMock;

    /** @var PDOStatement|\PHPUnit\Framework\MockObject\MockObject */
    private $stmtMock;

    /**
     * Configura el entorno antes de CADA prueba.
     * Se crea un mock parcial de 'Seccion', reemplazando solo el método Con()
     * para inyectar nuestro mock de PDO.
     */
    protected function setUp(): void
    {
        // 1. Crear mocks para PDO y PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // 2. Crear un "Mock Parcial" de la clase Seccion.
        $this->seccion = $this->getMockBuilder(Seccion::class)
            ->disableOriginalConstructor() // No llamar al constructor real
            ->onlyMethods(['Con']) // Solo vamos a mockear (reemplazar) el método Con()
            ->getMock();

        // 3. Configurar el método Con() mockeado para que SIEMPRE devuelva nuestro mock de PDO.
        $this->seccion->method('Con')->willReturn($this->pdoMock);

        // 4. Configuración común: simular que setAttribute se llama sin problemas
        $this->pdoMock->method('setAttribute');
    }

    /**
     * Limpia el entorno después de cada prueba.
     */
    protected function tearDown(): void
    {
        $this->seccion = null;
        $this->pdoMock = null;
        $this->stmtMock = null;
    }

    //--------------------------------------------------------------------------
    // PRUEBAS PARA: RegistrarSeccion (Sin cambios respecto a tu versión original)
    //--------------------------------------------------------------------------

    /**
     * @covers ::RegistrarSeccion
     * Prueba el "camino feliz" al registrar una sección completamente nueva.
     * Espera que se inicie una transacción, se verifique que no existe,
     * se inserte y se haga commit.
     */
    public function testRegistrarSeccion_Exito_NuevaSeccion()
    {
        // 1. Arrange: Datos de entrada
        $codigo = 'IN1101';
        $cantidad = 30;
        $anio = 2025;
        $tipo = 'regular';

        // 2. Arrange: Mocks de Transacción (esperamos éxito)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        // 3. Arrange: Mocks de Consultas
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT sec_estado FROM tbl_seccion WHERE sec_codigo =')],
                [$this->stringContains('INSERT INTO tbl_seccion')]
            )
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtInsert);

        // 4. Arrange: Comportamiento de Mocks
        // No existe:
        $stmtCheck->expects($this->once())->method('fetch')->willReturn(false);
        // Inserta correctamente:
        $stmtInsert->expects($this->once())->method('execute')->willReturn(true);

        // 5. Act
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);

        // 6. Assert
        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
        $this->assertStringContainsString('Se registró la sección', $resultado['mensaje']);
        $this->assertEquals($codigo, $resultado['nuevo_codigo']);
    }

    /**
     * @covers ::RegistrarSeccion
     * Prueba que el sistema falla si la sección ya existe y está activa.
     * Espera que haga rollback y devuelva error.
     */
    public function testRegistrarSeccion_Falla_CodigoYaExisteYActivo()
    {
        // 1. Arrange: Datos
        $codigo = 'IN1101';
        $cantidad = 30;
        $anio = 2025;
        $tipo = 'regular';

        // 2. Arrange: Mocks de Transacción (esperamos fallo)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack'); // Falla y revierte

        // 3. Arrange: Mocks de Consultas
        $stmtCheck = $this->createMock(PDOStatement::class);
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sec_estado FROM tbl_seccion'))
            ->willReturn($stmtCheck);

        // 4. Arrange: Comportamiento
        // SÍ existe y está activa (estado 1):
        $stmtCheck->expects($this->once())->method('fetch')->willReturn(['sec_estado' => 1]);
        // Nunca debe intentar insertar
        $this->pdoMock->expects($this->never())->method($this->stringContains('INSERT INTO'));

        // 5. Act
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);

        // 6. Assert
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('ya existe', $resultado['mensaje']);
    }

    /**
     * @covers ::RegistrarSeccion
     * @covers ::EliminarDependenciasDeSeccion
     * Prueba que el sistema sobrescribe una sección inactiva (estado 0).
     * Espera que elimine dependencias y luego haga UPDATE en lugar de INSERT.
     */
    public function testRegistrarSeccion_Exito_SobrescribeInactiva()
    {
        // 1. Arrange
        $codigo = 'IN1101';
        $cantidad = 30;
        $anio = 2025;
        $tipo = 'regular';

        // 2. Arrange: Mocks de Transacción (esperamos éxito)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        // 3. Arrange: Mocks de Consultas
        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtDeleteUC = $this->createMock(PDOStatement::class);
        $stmtDeleteDoc = $this->createMock(PDOStatement::class);
        $stmtDeleteHor = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(5))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT sec_estado FROM tbl_seccion')],
                // Llamadas de EliminarDependenciasDeSeccion
                [$this->stringContains('DELETE FROM uc_horario')],
                [$this->stringContains('DELETE FROM docente_horario')],
                [$this->stringContains('DELETE FROM tbl_horario')],
                // Llamada de UPDATE
                [$this->stringContains('UPDATE tbl_seccion SET')]
            )
            ->willReturnOnConsecutiveCalls($stmtCheck, $stmtDeleteUC, $stmtDeleteDoc, $stmtDeleteHor, $stmtUpdate);

        // 4. Arrange: Comportamiento
        // SÍ existe y está inactiva (estado 0):
        $stmtCheck->expects($this->once())->method('fetch')->willReturn(['sec_estado' => 0]);
        // Espera que se ejecuten las eliminaciones
        $stmtDeleteUC->expects($this->once())->method('execute');
        $stmtDeleteDoc->expects($this->once())->method('execute');
        $stmtDeleteHor->expects($this->once())->method('execute');
        // Espera que se ejecute el UPDATE
        $stmtUpdate->expects($this->once())->method('execute');

        // 5. Act
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);

        // 6. Assert
        $this->assertEquals('registrar_seccion_ok', $resultado['resultado']);
    }

    /**
     * @covers ::RegistrarSeccion
     * @dataProvider providerValidacionRegistrarSeccion
     * Prueba las validaciones de entrada (formato, rangos) ANTES de tocar la BD.
     */
    public function testRegistrarSeccion_Falla_ValidacionTemprana($codigo, $cantidad, $anio, $tipo, $mensaje_esperado)
    {
        // 1. Arrange: Nunca debe tocar la BD
        $this->pdoMock->expects($this->never())->method('prepare');
        $this->pdoMock->expects($this->never())->method('beginTransaction');

        // 2. Act
        $resultado = $this->seccion->RegistrarSeccion($codigo, $cantidad, $anio, $tipo);

        // 3. Assert
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString($mensaje_esperado, $resultado['mensaje']);
    }

    /**
     * Proveedor de datos para las pruebas de validación de `RegistrarSeccion`.
     */
    public static function providerValidacionRegistrarSeccion(): array
    {
        return [
            'Codigo nulo' => [null, 30, 2025, 'regular', 'Todos los campos'],
            'Cantidad nula' => ['IN1101', null, 2025, 'regular', 'Todos los campos'],
            'Año nulo' => ['IN1101', 30, null, 'regular', 'Todos los campos'],
            'Tipo nulo' => ['IN1101', 30, 2025, null, 'Todos los campos'],
            'Formato codigo invalido' => ['1101', 30, 2025, 'regular', 'Formato de código inválido'],
            'Cantidad fuera de rango (alta)' => ['IN1101', 100, 2025, 'regular', 'cantidad de estudiantes debe ser un número'],
            'Cantidad fuera de rango (baja)' => ['IN1101', -1, 2025, 'regular', 'cantidad de estudiantes debe ser un número'],
            'Cantidad no numerica' => ['IN1101', 'abc', 2025, 'regular', 'cantidad de estudiantes debe ser un número'],
        ];
    }


    //--------------------------------------------------------------------------
    // PRUEBAS PARA: Modificar (Sin cambios respecto a tu versión original)
    //--------------------------------------------------------------------------

    /**
     * @covers ::Modificar
     * Prueba la FASE 1 de Modificar: Detección de conflictos.
     * Simula que el docente excede sus horas académicas.
     * Espera que devuelva 'confirmar_conflicto' y NO inicie la transacción.
     */
    public function testModificar_RetornaConfirmarConflicto_PorExcesoHorasDocente()
    {
        // 1. Arrange: Datos de entrada
        $sec_codigo = 'IN1101';
        $ani_anio = 2025;
        // Horario con 1 clase que cuesta 6 horas
        $items_horario = [
            ['uc_codigo' => 'UC-001', 'doc_cedula' => 123456, 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:20', 'espacio' => null]
        ];
        $items_json = json_encode($items_horario);
        $cantidad = 30;
        $forzar = false;

        // 2. Arrange: Mocks de Validación (Fase 1)
        $stmtCheckUC = $this->createMock(PDOStatement::class);
        $stmtMaxHoras = $this->createMock(PDOStatement::class);
        $stmtHorasActuales = $this->createMock(PDOStatement::class);
        $stmtHorasUC = $this->createMock(PDOStatement::class);
        $stmtDocNombre = $this->createMock(PDOStatement::class);

        // Simular que ValidarClaseEnVivo no da conflictos
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class);

        // Usamos willReturnCallback para manejar múltiples llamadas a prepare con diferentes SQLs
        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use (
                $stmtCheckUC, $stmtMaxHoras, $stmtHorasActuales, $stmtHorasUC, $stmtDocNombre,
                $stmtGrupo, $stmtVLV_Doc, $stmtVLV_Esp
            ) {
                if (str_contains($sql, 'SELECT uc_nombre')) return $stmtCheckUC;
                if (str_contains($sql, 'SELECT act_academicas')) return $stmtMaxHoras;
                if (str_contains($sql, 'SELECT SUM(um.mal_hora_academica)')) return $stmtHorasActuales;
                if (str_contains($sql, 'SELECT mal_hora_academica')) return $stmtHorasUC;
                if (str_contains($sql, 'SELECT doc_nombre, doc_apellido')) return $stmtDocNombre;
                if (str_contains($sql, 'SELECT grupo_union_id')) return $stmtGrupo;
                // Ajusta estas condiciones si las SQL exactas son diferentes
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.doc_cedula = ?')) return $stmtVLV_Doc;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.esp_numero = ?')) return $stmtVLV_Esp;

                // Devolver un mock genérico si no coincide ninguna SQL esperada
                return $this->createMock(PDOStatement::class);
            });

        // 3. Arrange: Comportamiento de Mocks
        // Malla activa
        $stmtMallaActiva = $this->createMock(PDOStatement::class);
        $this->pdoMock->method('query')->with($this->stringContains('SELECT mal_codigo FROM tbl_malla'))->willReturn($stmtMallaActiva);
        $stmtMallaActiva->method('fetchColumn')->willReturn('MALLA-ACTIVA');

        // No hay UCs duplicadas
        $stmtCheckUC->method('fetchColumn')->willReturn('UC Test');

        // Lógica de Horas (EL CONFLICTO)
        $stmtMaxHoras->method('fetchColumn')->willReturn(10); // Límite de 10 horas
        $stmtHorasActuales->method('fetchColumn')->willReturn(5); // Ya tiene 5 horas en OTRAS secciones
        $stmtHorasUC->method('fetchColumn')->willReturn(6); // La nueva clase cuesta 6 horas
        // Total: 5 + 6 = 11. Excede las 10.

        // Info del docente para el mensaje
        $stmtDocNombre->method('fetch')->willReturn(['doc_nombre' => 'Juan', 'doc_apellido' => 'Perez']);

        // ValidarClaseEnVivo (Sin conflictos)
        $stmtGrupo->method('fetchColumn')->willReturn(null);
        $stmtVLV_Doc->method('fetchAll')->willReturn([]); // No hay conflictos de docente
        $stmtVLV_Esp->method('fetch')->willReturn(false); // No hay conflictos de espacio

        // 4. Arrange: NUNCA debe iniciar la transacción de guardado
        $this->pdoMock->expects($this->never())->method('beginTransaction');

        // 5. Act
        $resultado = $this->seccion->Modificar($sec_codigo, $ani_anio, $items_json, $cantidad, $forzar);

        // 6. Assert
        $this->assertEquals('confirmar_conflicto', $resultado['resultado']);
        $this->assertStringContainsString('excedería sus horas académicas', $resultado['mensaje']);
        $this->assertStringContainsString('Juan Perez', $resultado['mensaje']);
    }

    /**
     * @covers ::Modificar
     * @covers ::EliminarDependenciasDeSeccion
     * Prueba la FASE 2 de Modificar: Transacción de guardado.
     * Simula un fallo (PDOException) durante el INSERT en el bucle.
     * Espera que la transacción haga ROLLBACK, revirtiendo los DELETEs.
     * ---
     * **NOTA:** Esta prueba falló indicando que rollBack() no fue llamado.
     * Esto sugiere un problema en el bloque catch del *modelo* `Seccion.php`,
     * no en la prueba. La prueba se mantiene como está para verificar la corrección del modelo.
     * ---
     */
    public function testModificar_Falla_RollbackEnErrorDeInsert()
    {
        // 1. Arrange: Datos (Forzar = true para saltar validaciones)
        $sec_codigo = 'IN1101';
        $ani_anio = 2025;
        $items_horario = [
            ['uc_codigo' => 'UC-001', 'doc_cedula' => 123, 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:20']
        ];
        $items_json = json_encode($items_horario);
        $cantidad = 30;
        $forzar = true; // Saltamos la Fase 1

        // 2. Arrange: Mocks de Transacción (esperamos fallo)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack'); // La expectativa clave

        // *** NUEVA CONFIGURACIÓN EXPLÍCITA para inTransaction() ***
        // Usamos un estado interno en la prueba para simular si la transacción está activa
        $isTransactionActive = false;
        $this->pdoMock->method('beginTransaction')
            ->willReturnCallback(function () use (&$isTransactionActive) {
                $isTransactionActive = true; // Marcar como activa al llamar beginTransaction
                return true; // Valor de retorno de beginTransaction (generalmente true)
            });

        $this->pdoMock->method('inTransaction')
            ->willReturnCallback(function () use (&$isTransactionActive) {
                // Devolver el estado simulado actual
                return $isTransactionActive;
            });

        // Opcional pero bueno: Simular que commit y rollBack desactivan la transacción
        $this->pdoMock->method('commit')
             ->willReturnCallback(function () use (&$isTransactionActive) {
                $isTransactionActive = false;
                return true;
             });
        $this->pdoMock->method('rollBack')
             ->willReturnCallback(function () use (&$isTransactionActive) {
                $isTransactionActive = false; // Marcar como inactiva al llamar rollBack
                return true; // Valor de retorno de rollBack (generalmente true)
             });
        // *** FIN DE LA NUEVA CONFIGURACIÓN ***


        // 3. Arrange: Mocks de Consultas (Fase 2) - Sin cambios aquí
        $stmtUpdateSec = $this->createMock(PDOStatement::class);
        $stmtDeleteUC = $this->createMock(PDOStatement::class);
        $stmtDeleteDoc = $this->createMock(PDOStatement::class);
        $stmtDeleteHor = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class); // El que fallará

        $this->pdoMock->expects($this->exactly(5)) // Ajustado si el número de prepares cambia
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('UPDATE tbl_seccion SET sec_cantidad')],
                [$this->stringContains('DELETE FROM uc_horario')],
                [$this->stringContains('DELETE FROM docente_horario')],
                [$this->stringContains('DELETE FROM tbl_horario')],
                [$this->stringContains('INSERT INTO uc_horario')]
            )
            ->willReturnOnConsecutiveCalls($stmtUpdateSec, $stmtDeleteUC, $stmtDeleteDoc, $stmtDeleteHor, $stmtInsert);

        // 4. Arrange: Comportamiento - Sin cambios aquí
        $stmtUpdateSec->expects($this->once())->method('execute')->willReturn(true);
        $stmtDeleteUC->expects($this->once())->method('execute')->willReturn(true);
        $stmtDeleteDoc->expects($this->once())->method('execute')->willReturn(true);
        $stmtDeleteHor->expects($this->once())->method('execute')->willReturn(true);

        $exceptionMessage = "Error de FK: doc_cedula no existe";
        $stmtInsert->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException($exceptionMessage));

        // 5. Act
        $resultado = $this->seccion->Modificar($sec_codigo, $ani_anio, $items_json, $cantidad, $forzar);

        // 6. Assert - Sin cambios aquí
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString($exceptionMessage, $resultado['mensaje']);
    }
    //--------------------------------------------------------------------------
    // PRUEBAS PARA: ValidarClaseEnVivo (testValidarClaseEnVivo_DetectaConflictoDocente sin cambios)
    //--------------------------------------------------------------------------

    /**
     * @covers ::ValidarClaseEnVivo
     * Prueba que la validación detecta un conflicto de docente en otra sección.
     */
    public function testValidarClaseEnVivo_DetectaConflictoDocente()
    {
        // 1. Arrange: Mocks
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use ($stmtGrupo, $stmtVLV_Doc, $stmtVLV_Esp) {
                if (str_contains($sql, 'SELECT grupo_union_id')) return $stmtGrupo;
                // Ajusta estas condiciones si las SQL exactas son diferentes
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.doc_cedula = ?')) return $stmtVLV_Doc; // Conflicto Docente
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.esp_numero = ?')) return $stmtVLV_Esp; // Conflicto Espacio
                return $this->createMock(PDOStatement::class);
            });

        // 2. Arrange: Comportamiento
        // No pertenece a un grupo
        $stmtGrupo->method('fetchColumn')->willReturn(null);

        // SÍ hay conflicto de docente
        $conflicto_doc = [['sec_codigo' => 'OTRA-SEC', 'doc_nombre' => 'Juan', 'doc_apellido' => 'Perez']];
        $stmtVLV_Doc->method('fetchAll')->willReturn($conflicto_doc);

        // NO hay conflicto de espacio
        $stmtVLV_Esp->method('fetch')->willReturn(false);

        // 3. Act
        $resultado = $this->seccion->ValidarClaseEnVivo(123, 'UC-001', ['numero' => '1', 'tipo' => 'Aula', 'edificio' => 'A'], 'Lunes', '08:00', '10:00', 'MI-SEC');

        // 4. Assert
        $this->assertTrue($resultado['conflicto']);
        $this->assertCount(1, $resultado['mensajes']);
        $this->assertEquals('docente', $resultado['mensajes'][0]['tipo']);
        $this->assertStringContainsString('Juan Perez', $resultado['mensajes'][0]['mensaje']);
    }

    /**
     * @covers ::ValidarClaseEnVivo
     * Prueba que la validación ignora conflictos si ocurren en secciones
     * que pertenecen al mismo grupo de unión.
     * ---
     * **NOTA:** Esta prueba falló con "Array to string conversion".
     * Esto indica un problema en el *modelo* `Seccion.php` al pasar los parámetros
     * al `execute()` para la cláusula `NOT IN`. La prueba se mantiene así
     * para verificar la corrección del modelo.
     * ---
     */
    public function testValidarClaseEnVivo_IgnoraConflictoEnMismoGrupoUnion()
    {
        // 1. Arrange: Mocks
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtSeccionesGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use ($stmtGrupo, $stmtSeccionesGrupo, $stmtVLV_Doc, $stmtVLV_Esp) {
                if (str_contains($sql, 'SELECT grupo_union_id FROM tbl_seccion WHERE sec_codigo = ?')) return $stmtGrupo;
                if (str_contains($sql, 'SELECT sec_codigo FROM tbl_seccion WHERE grupo_union_id = ?')) return $stmtSeccionesGrupo;
                 // Ajusta estas condiciones si las SQL exactas son diferentes
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.doc_cedula = ?') && str_contains($sql, 'NOT IN')) return $stmtVLV_Doc;
                if (str_contains($sql, 'FROM uc_horario uh') && str_contains($sql, 'uh.esp_numero = ?') && str_contains($sql, 'NOT IN')) return $stmtVLV_Esp;
                return $this->createMock(PDOStatement::class); // Mock genérico
            });

        // 2. Arrange: Comportamiento
        // La sección 'MI-SEC' pertenece al 'grupo-A'
        $stmtGrupo->expects($this->once())->method('execute')->with(['MI-SEC']);
        $stmtGrupo->method('fetchColumn')->willReturn('grupo-A');

        // El 'grupo-A' contiene 'MI-SEC' y 'OTRA-SEC'
        $stmtSeccionesGrupo->expects($this->once())->method('execute')->with(['grupo-A']);
        // Devolvemos los códigos como un array plano de strings
        $stmtSeccionesGrupo->method('fetchAll')->with(PDO::FETCH_COLUMN)->willReturn(['MI-SEC', 'OTRA-SEC']);

        // Se espera que la consulta de conflicto de docente se ejecute con los parámetros correctos
        // incluyendo la lista de exclusión APLANADA (aquí es donde fallaba el modelo).
        // El modelo debe usar array_values() o similar antes de pasar a execute.
        $parametrosEsperadosDoc = array_merge([123], ['MI-SEC', 'OTRA-SEC'], ['Lunes', '10:00', '08:00']);
        $stmtVLV_Doc->expects($this->once())
            ->method('execute')
            ->with($parametrosEsperadosDoc); // Verificamos los parámetros exactos

        // Simulamos que la consulta (con NOT IN) no devuelve conflictos
        $stmtVLV_Doc->method('fetchAll')->willReturn([]);

        // Hacemos lo mismo para la validación de espacio
        $parametrosEsperadosEsp = array_merge(['1', 'Aula', 'A'], ['MI-SEC', 'OTRA-SEC'], ['Lunes', '10:00', '08:00']);
         $stmtVLV_Esp->expects($this->once())
            ->method('execute')
            ->with($parametrosEsperadosEsp);
        $stmtVLV_Esp->method('fetch')->willReturn(false);

        // 3. Act
        $resultado = $this->seccion->ValidarClaseEnVivo(123, 'UC-001', ['numero' => '1', 'tipo' => 'Aula', 'edificio' => 'A'], 'Lunes', '08:00', '10:00', 'MI-SEC');

        // 4. Assert: La expectativa correcta es que NO haya conflicto
        $this->assertFalse($resultado['conflicto']);
    }

    //--------------------------------------------------------------------------
    // PRUEBAS PARA: ConsultarDetalles (Sin cambios)
    //--------------------------------------------------------------------------

    /**
     * @covers ::ConsultarDetalles
     * Prueba que ConsultarDetalles formatea correctamente la hora (de 5 a 8 chars)
     * y el objeto 'espacio'.
     */
    public function testConsultarDetalles_FormateaDatosCorrectamente()
    {
        // 1. Arrange: Datos crudos de la BD
        $datosBD = [
            [
                'uc_codigo' => 'UC-001', 'doc_cedula' => 123, 'subgrupo' => null,
                'esp_numero' => 'L1', 'esp_tipo' => 'Laboratorio', 'esp_edificio' => 'Hilandera',
                'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '09:20' // <-- 5 chars
            ]
        ];

        $this->pdoMock->expects($this->once())->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn($datosBD);

        // 2. Act
        $resultado = $this->seccion->ConsultarDetalles('IN1101', 2025);

        // 3. Assert
        $this->assertEquals('ok', $resultado['resultado']);
        $item = $resultado['mensaje'][0];

        // Verifica formato de hora (de '08:00' a '08:00:00')
        $this->assertEquals('08:00:00', $item['hora_inicio']);
        $this->assertEquals('09:20:00', $item['hora_fin']);

        // Verifica formato de espacio
        $this->assertArrayHasKey('espacio', $item);
        $this->assertEquals('L1', $item['espacio']['numero']);
        $this->assertEquals('Laboratorio', $item['espacio']['tipo']);
        $this->assertArrayNotHasKey('esp_numero', $item); // Verifica que se limpió
    }

    //--------------------------------------------------------------------------
    // PRUEBAS PARA: Lógica de Fases (CON CORRECCIONES)
    //--------------------------------------------------------------------------

    /**
     * @covers ::obtenerUcPorDocente
     * @covers ::determinarFaseActual
     * Prueba que, si la fecha actual cae en Fase 1, la consulta SQL
     * filtra correctamente por UCs de 'Fase I', 'Anual' y '0'.
     * ---
     * CORREGIDO: Se ajustó el willReturnCallback y la expectativa de 'prepare'.
     * ---
     */
    public function testObtenerUcPorDocente_FiltraParaFase1()
    {
        // 1. Arrange: Mocks para determinarFaseActual() y obtenerUcPorDocente()
        $stmtAnio = $this->createMock(PDOStatement::class);
        $stmtFase = $this->createMock(PDOStatement::class);
        $stmtUCs = $this->createMock(PDOStatement::class); // El objetivo

        // Mapeo SQL -> Mock Statement
        $prepareMap = [
            ['SELECT ani_anio, ani_tipo FROM tbl_anio', $stmtAnio],
            ['SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase', $stmtFase],
            // La consulta principal de UCs (ajusta si la SQL es ligeramente diferente)
            ['SELECT u.uc_codigo, u.uc_nombre, u.uc_trayecto, u.uc_periodo FROM tbl_uc u WHERE', $stmtUCs]
        ];

        $this->pdoMock->method('prepare')
             ->willReturnCallback(function ($sql) use ($prepareMap) {
                 foreach ($prepareMap as [$sqlPattern, $stmtMock]) {
                     // Usamos str_starts_with para más flexibilidad
                     if (str_starts_with($sql, trim(substr($sqlPattern, 0, 60)))) {
                         return $stmtMock;
                     }
                 }
                 throw new Exception("SQL no esperado en prepare: " . $sql); // Falla si la SQL no coincide
             });


        // 3. Arrange: Simular que estamos en Fase 1
        $hoy = new DateTime();
        $inicioFase1 = (clone $hoy)->modify('-1 month')->format('Y-m-d');
        $finFase1 = (clone $hoy)->modify('+1 month')->format('Y-m-d');

        // Comportamiento de determinarFaseActual
        $stmtAnio->method('fetch')->willReturn(['ani_anio' => 2025, 'ani_tipo' => 'regular']);
        $stmtFase->method('fetchAll')->willReturn([
            ['fase_numero' => 1, 'fase_apertura' => $inicioFase1, 'fase_cierre' => $finFase1],
             // Añadimos otra fase fuera de rango para asegurar que elige la correcta
            ['fase_numero' => 2, 'fase_apertura' => '2026-01-01', 'fase_cierre' => '2026-06-01']
        ]);

        // Comportamiento de la consulta de UCs
        // Verificamos que se ejecutó la consulta correcta (implícito por el willReturnCallback)
        // y simulamos que devuelve datos
        $datosUCFase1 = [['uc_codigo' => 'UC-FASE1', 'uc_nombre' => 'Test Fase 1', 'uc_trayecto' => '1', 'uc_periodo' => 'Fase I']];
        $stmtUCs->expects($this->once())->method('execute')->with([':trayecto_seccion' => 1]); // Verifica parámetro
        $stmtUCs->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($datosUCFase1);


        // 4. Act
        $resultado = $this->seccion->obtenerUcPorDocente(null, '1'); // Docente null, Trayecto 1

        // 5. Assert: Ahora sí esperamos 'ok' y los datos filtrados
        $this->assertEquals('ok', $resultado['mensaje']);
        $this->assertNotEmpty($resultado['data']);
        $this->assertEquals('UC-FASE1', $resultado['data'][0]['uc_codigo']);
    }

    /**
     * @covers ::obtenerUcPorDocente
     * @covers ::determinarFaseActual
     * Prueba que, si no estamos en ninguna fase, la función devuelve un mensaje de error.
     * ---
     * CORREGIDO: Se ajustó el willReturnCallback y la expectativa sobre 'prepare'.
     * ---
     */
    public function testObtenerUcPorDocente_Falla_SiNoHayFaseActiva()
    {
        // 1. Arrange: Mocks para determinarFaseActual()
        $stmtAnio = $this->createMock(PDOStatement::class);
        $stmtFase = $this->createMock(PDOStatement::class);

        // Mapeo SQL -> Mock Statement
        $prepareMap = [
            ['SELECT ani_anio, ani_tipo FROM tbl_anio', $stmtAnio],
            ['SELECT fase_numero, fase_apertura, fase_cierre FROM tbl_fase', $stmtFase]
            // NO debe incluir la consulta de UCs
        ];

         $this->pdoMock->method('prepare')
             ->willReturnCallback(function ($sql) use ($prepareMap) {
                 foreach ($prepareMap as [$sqlPattern, $stmtMock]) {
                     if (str_starts_with($sql, trim(substr($sqlPattern, 0, 60)))) {
                         return $stmtMock;
                     }
                 }
                 // Si llega aquí, significa que intentó preparar la consulta de UCs, lo cual es un error
                 throw new Exception("SQL no esperado en prepare (posiblemente la consulta de UCs): " . $sql);
             });


        // 2. Arrange: Simular que estamos FUERA de fase (fechas pasadas)
        $stmtAnio->method('fetch')->willReturn(['ani_anio' => 2025, 'ani_tipo' => 'regular']);
        $stmtFase->method('fetchAll')->willReturn([
            ['fase_numero' => 1, 'fase_apertura' => '2024-01-01', 'fase_cierre' => '2024-06-01']
        ]);

        // 3. Assert: NUNCA debe intentar preparar la consulta de UCs
        // (Esto está implícito en el willReturnCallback que lanzará una excepción si se intenta)


        // 4. Act
        $resultado = $this->seccion->obtenerUcPorDocente(123456, '1');

        // 5. Assert
        $this->assertEmpty($resultado['data']);
        $this->assertStringContainsString('Fuera de período', $resultado['mensaje']);
    }

 
    /**
     * @covers ::UnirHorarios
     * Prueba que UnirHorarios falla si una de las secciones a unir no existe.
     */
    public function testUnirHorarios_Falla_SeccionInexistente()
    {
        // 1. Arrange: Datos
        $sec_origen = 'IN1101';
        $secs_a_unir = ['IN1101', 'IN1102', 'INEXISTENTE']; // Una sección no existe

        // 2. Arrange: Mock de validación
        // Simular que la consulta devuelve solo 2 de las 3 secciones
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT sec_codigo, ani_anio, ani_tipo FROM tbl_seccion WHERE sec_codigo IN'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->method('execute')->with($secs_a_unir);
        $this->stmtMock->method('fetchAll')->willReturn([
            ['sec_codigo' => 'IN1101', 'ani_anio' => 2025, 'ani_tipo' => 'regular'],
            ['sec_codigo' => 'IN1102', 'ani_anio' => 2025, 'ani_tipo' => 'regular'],
            // Falta 'INEXISTENTE'
        ]);

        // 3. Arrange: No debe iniciar transacción
        $this->pdoMock->expects($this->never())->method('beginTransaction');

        // 4. Act
        $resultado = $this->seccion->UnirHorarios($sec_origen, $secs_a_unir);

        // 5. Assert
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('no son válidas', $resultado['mensaje']);
    }

 
     /**
     * @covers ::ValidarClaseEnVivo
     * Prueba cómo maneja ValidarClaseEnVivo un array $espacio incompleto (sin tipo o edificio).
     * Debería funcionar si la consulta usa `?? null`.
     */
    public function testValidarClaseEnVivo_ManejaEspacioIncompleto()
    {
        // 1. Arrange: Espacio incompleto
        $espacio_incompleto = ['numero' => '1']; // Falta tipo y edificio

        // 2. Arrange: Mocks
        $stmtGrupo = $this->createMock(PDOStatement::class);
        $stmtVLV_Doc = $this->createMock(PDOStatement::class);
        $stmtVLV_Esp = $this->createMock(PDOStatement::class); // Mock para la consulta de espacio

        $this->pdoMock->method('prepare')
            ->willReturnCallback(function ($sql) use ($stmtGrupo, $stmtVLV_Doc, $stmtVLV_Esp) {
                if (str_contains($sql, 'SELECT grupo_union_id')) return $stmtGrupo;
                if (str_contains($sql, 'uh.doc_cedula = ?')) return $stmtVLV_Doc;
                // Identificar la consulta de espacio
                if (str_contains($sql, 'uh.esp_numero = ?')) return $stmtVLV_Esp;
                return $this->createMock(PDOStatement::class);
            });

        // 3. Arrange: Comportamiento
        $stmtGrupo->method('fetchColumn')->willReturn(null); // Sin grupo
        $stmtVLV_Doc->method('fetchAll')->willReturn([]); // Sin conflicto docente

        // Verificar que execute() para espacio se llama con NULLs donde faltan datos
        $parametrosEsperadosEsp = [
            $espacio_incompleto['numero'], // numero
            null,                          // tipo (espera null)
            null,                          // edificio (espera null)
            'MI-SEC',                      // sección a excluir
            'Martes',                      // dia
            '12:00',                       // hora_fin
            '10:00'                        // hora_inicio
        ];
        $stmtVLV_Esp->expects($this->once())
            ->method('execute')
            ->with($parametrosEsperadosEsp);
        $stmtVLV_Esp->method('fetch')->willReturn(false); // Simular que no hay conflicto

        // 4. Act
        $resultado = $this->seccion->ValidarClaseEnVivo(456, 'UC-002', $espacio_incompleto, 'Martes', '10:00', '12:00', 'MI-SEC');

        // 5. Assert
        $this->assertFalse($resultado['conflicto']); // Espera que no haya conflicto
    }
} // Fin de la clase SeccionTest