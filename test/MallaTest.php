<?php

use PHPUnit\Framework\TestCase;

/**
 * --- PREPARACIÓN DEL ENTORNO DE MOCK ---
 *
 * Mockeamos la clase padre 'Connection' para evitar que intente
 * una conexión real a la base de datos.
 *
 * Esto permite que 'require_once' cargue 'mallacurricular.php' de forma segura,
 * ya que su 'extends Connection' encontrará esta clase "falsa".
 */ 


// Incluimos la clase que vamos a probar
// Ajusta la ruta si es necesario (ej. '../model/mallacurricular.php')
require_once 'model/mallacurricular.php';

/**
 * --- SUITE DE PRUEBAS PARA LA CLASE Malla ---
 *
 * Aquí probamos toda la lógica de negocio de la clase Malla,
 * aislando completamente la base de datos.
 */
class MallaTest extends TestCase
{
    /** @var Malla|\PHPUnit\Framework\MockObject\MockObject */
    private $malla;

    /** @var PDO|\PHPUnit\Framework\MockObject\MockObject */
    private $pdoMock;

    /** @var PDOStatement|\PHPUnit\Framework\MockObject\MockObject */
    private $stmtMock;

    /**
     * Configura el entorno antes de CADA prueba.
     */
    protected function setUp(): void
    {
        // 1. Crear mocks para PDO y PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // 2. Crear un "Mock Parcial" de la clase Malla.
        // Queremos probar todos sus métodos, EXCEPTO Con().
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor() // No llamar al constructor real (que llama a parent::__construct)
            ->onlyMethods(['Con']) // Solo vamos a mockear (reemplazar) el método Con()
            ->getMock();

        // 3. Configurar el método Con() mockeado para que SIEMPRE devuelva nuestro mock de PDO.
        $this->malla->method('Con')->willReturn($this->pdoMock);

        // 4. Configuración común: simular que prepare() y query() devuelven un statement
        // Esto se puede sobreescribir en cada test si es necesario
        
        // Simular setAttribute (llamado en casi todos los métodos)
        $this->pdoMock->method('setAttribute');
    }

    //--------------------------------------------------------------------------
    // Pruebas para: verificarCondicionesParaRegistrar()
    //--------------------------------------------------------------------------

    public function testVerificarCondicionesParaRegistrar_Exito()
    {
        // Configurar Mocks:
        $stmtCountMock = $this->createMock(PDOStatement::class);
        $stmtTrayectosMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
    ->method('query')
    ->withConsecutive(
        ["SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1"],
        ["SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_estado = 1"]
    )
    ->willReturnOnConsecutiveCalls(
        $stmtCountMock,
        $stmtTrayectosMock
    );

        $stmtCountMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10'); // Hay UCs

        $stmtTrayectosMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN, 0)
            ->willReturn(['0', '1', '2', '3', '4']); // Todos los trayectos existen

        // Ejecutar y Validar
        $resultado = $this->malla->verificarCondicionesParaRegistrar();

        $this->assertTrue($resultado['puede_registrar']);
        $this->assertEquals('', $resultado['mensaje']);
    }

    public function testVerificarCondicionesParaRegistrar_Falla_NoHayUCs()
    {
        // Configurar Mocks:
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with("SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1")
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('0'); // No hay UCs

        // Ejecutar y Validar
        $resultado = $this->malla->verificarCondicionesParaRegistrar();

        $this->assertFalse($resultado['puede_registrar']);
        $this->assertStringContainsString('No hay unidades curriculares', $resultado['mensaje']);
    }

    public function testVerificarCondicionesParaRegistrar_Falla_FaltanTrayectos()
    {
        // Configurar Mocks:
        $stmtCountMock = $this->createMock(PDOStatement::class);
        $stmtTrayectosMock = $this->createMock(PDOStatement::class);

         $this->pdoMock->expects($this->exactly(2))
    ->method('query')
    ->withConsecutive(
        ["SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1"],
        ["SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_estado = 1"]
    )
    ->willReturnOnConsecutiveCalls(
        $stmtCountMock,
        $stmtTrayectosMock
    );

        $stmtCountMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('10'); // Hay UCs

        $stmtTrayectosMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN, 0)
            ->willReturn(['0', '1', '3']); // Faltan el 2 y 4

        // Ejecutar y Validar
        $resultado = $this->malla->verificarCondicionesParaRegistrar();

        $this->assertFalse($resultado['puede_registrar']);
        $this->assertStringContainsString('Faltan unidades curriculares en los trayectos: 2, 4', $resultado['mensaje']);
    }

    public function testVerificarCondicionesParaRegistrar_Falla_DBException()
    {
        // Configurar Mocks:
       $this->pdoMock->expects($this->once())
    ->method('query')
    // Sé 100% explícito con el SQL que va a fallar
    ->with("SELECT COUNT(*) FROM tbl_uc WHERE uc_estado = 1")
    ->willThrowException(new PDOException('Error de BD'));

        // Ejecutar y Validar
        $resultado = $this->malla->verificarCondicionesParaRegistrar();

        
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de BD', $resultado['mensaje']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: Registrar()
    //--------------------------------------------------------------------------
    public function testRegistrar_UnidadesConHorasCero(){
        // OBJETIVO: Probar que el registro falla si CUALQUIER UC tiene todas las horas en 0.

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10], // Válida
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 0, 'hora_asistida' => 0, 'hora_academica' => 0]  // Inválida (en 0)
        ];

        // 1. Mockear mocks internos (NO deberían llamarse)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);

        // 2. Asegurar que la validación de trayectos, código y cohorte NUNCA se llama
        $this->malla->expects($this->never())->method('Existecodigo');
        $this->malla->expects($this->never())->method('ExisteCohorte');
        $this->pdoMock->expects($this->never())->method('prepare');
        
        // 3. Asegurar que la transacción NUNCA inicia
        $this->pdoMock->expects($this->never())->method('beginTransaction');

        // Ejecutar
        $resultado = $this->malla->Registrar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('horas en 0', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_UnidadConValoresNulos(){
        // OBJETIVO: Probar que el registro falla si CUALQUIER UC tiene valores nulos o faltantes.

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10], // Válida
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 10, 'hora_asistida' => 10] // Inválida (falta hora_academica)
        ];

        // 1. Mockear mocks internos (NO deberían llamarse)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con']) // Solo mockeamos Con()
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);

        // 2. Asegurar que la transacción NUNCA inicia
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('prepare');

        // Ejecutar
        $resultado = $this->malla->Registrar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('nulos o faltantes', $resultado['mensaje']);
    }

    public function testRegistrar_Exito()
    {
        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
            ['uc_codigo' => 'UC-T2', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
            ['uc_codigo' => 'UC-T3', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
            ['uc_codigo' => 'UC-T4', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
        ];

        // Configurar Mocks:
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla = $this->createMock(PDOStatement::class);
        $stmtInsertUCMalla = $this->createMock(PDOStatement::class);

        // 1. Mockear llamadas INTERNAS a Existecodigo y ExisteCohorte
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 2. Mockear la secuencia de llamadas a prepare()
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: Validación de trayectos
                [$this->stringContains('SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN')],
                // 2da llamada: Insertar en la malla principal
                [$this->stringContains('INSERT INTO tbl_malla')],
                // 3ra a 7ma llamada: Insertar en uc_malla (una por cada unidad)
                [$this->stringContains('INSERT INTO uc_malla')],
                [$this->stringContains('INSERT INTO uc_malla')],
                [$this->stringContains('INSERT INTO uc_malla')],
                [$this->stringContains('INSERT INTO uc_malla')],
                [$this->stringContains('INSERT INTO uc_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtTrayectos,
                $stmtInsertMalla,
                $stmtInsertUCMalla,
                $stmtInsertUCMalla,
                $stmtInsertUCMalla,
                $stmtInsertUCMalla,
                $stmtInsertUCMalla
            );
        
        // 3. Configurar el mock de validación de trayectos para que PASE
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);

        // 4. Mockear la transacción
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        // 5. Mockear los INSERTS
        $stmtInsertMalla->expects($this->once())->method('execute');
        $stmtInsertUCMalla->expects($this->exactly(5))->method('execute');

        // Ejecutar
        $this->malla->setMalCodigo('NUEVA-MALLA');
        $this->malla->setMalNombre('Nueva Malla Curricular');
        $this->malla->setMalCohorte(5);
        $this->malla->setMalDescripcion('Descripción');
        $resultado = $this->malla->Registrar($unidades);

        // Validar
        $this->assertEquals('registrar', $resultado['resultado']);
        $this->assertStringContainsString('Registro Incluido', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_UnidadesVacias()
    {
        $resultado = $this->malla->Registrar([]);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('No se han proporcionado', $resultado['mensaje']);
    }



    public function testRegistrar_Falla_TrayectosFaltantes()
    {
        // Configurar Mocks:
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn(['0', '1', '2']); // Faltan 3 y 4

        // Ejecutar
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error de validación: Debe incluir al menos una UC de los trayectos: Trayecto 3, Trayecto 4', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_CodigoYaExiste()
    {
        // Mockear validación de trayectos (pasa)
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);

        // Mockear Existecodigo (falla)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'existe', 'mensaje' => 'Código duplicado']);

        // Ejecutar
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);

        // Validar
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('Código duplicado', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_CohorteYaExiste()
    {
        // Mockear validación de trayectos (pasa)
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);

        // Mockear Existecodigo (pasa) y ExisteCohorte (falla)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'existe', 'mensaje' => 'Cohorte duplicada']);

        // Ejecutar
        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);

        // Validar
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('Cohorte duplicada', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_DBExceptionEnValidacionTrayecto()
    {
        $this->pdoMock->method('prepare')
            ->willThrowException(new PDOException('Error validando trayectos'));

        $resultado = $this->malla->Registrar([['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10]]);

        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('Error al validar los trayectos', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_DBExceptionEnInsert()
    {
        $unidades = [ ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10] ];

        // Configurar Mocks:
        $stmtTrayectos = $this->createMock(PDOStatement::class);

        // 1. Mockear Existecodigo y ExisteCohorte (pasan)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 2. Mockear la secuencia de prepare() para que la 1ra (validación) pase
        //    y la 2da (INSERT) falle.
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: Validación de trayectos (debe pasar)
                [$this->stringContains('SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN')],
                // 2da llamada: Insertar en la malla principal (debe fallar)
                [$this->stringContains('INSERT INTO tbl_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtTrayectos, // Devuelve el mock de trayectos
                $this->throwException(new PDOException('Error de INSERT')) // ¡Lanza la excepción!
            );
        
        // 3. Configurar el mock de validación para que PASE
        //    (Devolvemos todos los trayectos)
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']);

        // 4. Mockear Transacción (falla)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Ejecutar
        // (Aunque pasamos solo 1 UC, el mock de fetchAll() devuelve todos los trayectos
        // para que la validación interna del modelo pase)
        $resultado = $this->malla->Registrar($unidades); 

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de INSERT', $resultado['mensaje']);
    }

    public function testRegistrar_Falla_DBExceptionEnInsertDeUCMalla()
    {
        // OBJETIVO: Probar que un fallo al insertar en 'uc_malla' (el bucle)
        // revierte la transacción (ROLLBACK).

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
        ];

        // 1. Mockear mocks internos (Existecodigo, ExisteCohorte) para que pasen
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 2. Mocks para los statements
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla = $this->createMock(PDOStatement::class);
        // Preparamos un mock que lanzará la excepción
        $stmtInsertUCMalla_Falla = $this->createMock(PDOStatement::class);

        // 3. Secuencia de llamadas a prepare()
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: Validación de trayectos (pasa)
                [$this->stringContains('SELECT DISTINCT uc_trayecto FROM tbl_uc WHERE uc_codigo IN')],
                // 2da llamada: Insertar en la malla principal (pasa)
                [$this->stringContains('INSERT INTO tbl_malla')],
                // 3ra llamada: Insertar en uc_malla (¡falla!)
                [$this->stringContains('INSERT INTO uc_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtTrayectos,
                $stmtInsertMalla,
                $stmtInsertUCMalla_Falla // Devuelve el mock que fallará
            );

        // 4. Configurar mocks
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); // Pasa validación
        $stmtInsertMalla->method('execute')->willReturn(true); // Pasa INSERT principal
        // Configura el mock del bucle para que lance la excepción
        $stmtInsertUCMalla_Falla->method('execute')
             ->willThrowException(new PDOException('Error en bucle UC'));

        // 5. Mockear Transacción (falla)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Ejecutar
        $resultado = $this->malla->Registrar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en bucle UC', $resultado['mensaje']);
    }

    public function testRegistrar_Exito_OmiteUnidadesConHorasCero()
    {
        // OBJETIVO: Probar la lógica de negocio que omite insertar UCs
        // si todas sus horas son 0.

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 0, 'hora_academica' => 0], // INSERTA (1)
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 0, 'hora_asistida' => 0, 'hora_academica' => 0], // OMITE
            ['uc_codigo' => 'UC-T2', 'hora_independiente' => 0, 'hora_asistida' => 5, 'hora_academica' => 0], // INSERTA (2)
            ['uc_codigo' => 'UC-T3', 'hora_independiente' => 0, 'hora_asistida' => 0, 'hora_academica' => 1], // INSERTA (3)
        ];
        $numero_de_inserts_esperados = 3; // <-- Solo 3 UCs se insertarán
        $numero_de_prepare_esperados = 3; // <-- 1 (trayecto) + 1 (malla) + 1 (uc_malla)

        // 1. Mockear mocks internos (pasan)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'Existecodigo', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('Existecodigo')->willReturn(['resultado' => 'ok']);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 2. Mocks para los statements
        $stmtTrayectos = $this->createMock(PDOStatement::class);
        $stmtInsertMalla = $this->createMock(PDOStatement::class);
        $stmtInsertUCMalla = $this->createMock(PDOStatement::class); // Este se reutilizará

        // 3. **LA CORRECCIÓN**
        // Definir la secuencia exacta de llamadas a prepare()
        $this->pdoMock->expects($this->exactly($numero_de_prepare_esperados))
            ->method('prepare')
            ->withConsecutive(
                [$this->stringContains('SELECT DISTINCT uc_trayecto')], // 1. Validación de trayecto
                [$this->stringContains('INSERT INTO tbl_malla')],      // 2. Insertar malla
                [$this->stringContains('INSERT INTO uc_malla')]        // 3. Preparar el bucle (SOLO 1 VEZ)
            )
            ->willReturnOnConsecutiveCalls(
                $stmtTrayectos,
                $stmtInsertMalla,
                $stmtInsertUCMalla // Devuelve el mock reutilizable
            );
        // ------------------------------------------

        // 4. Configurar mocks de statements
        $stmtTrayectos->method('fetchAll')->willReturn(['0', '1', '2', '3', '4']); // Pasa validación

        // 5. Mockear Transacción (éxito)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack');

        // 6. Validar ejecuciones
        $stmtInsertMalla->expects($this->once())->method('execute');
        // Esperamos que 'execute' se llame solo 3 veces (omitiendo la UC-T1)
        $stmtInsertUCMalla->expects($this->exactly($numero_de_inserts_esperados))->method('execute');

        // Ejecutar
        $resultado = $this->malla->Registrar($unidades);

        // Validar
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    //--------------------------------------------------------------------------
    // Pruebas para: Modificar()
    //--------------------------------------------------------------------------
public function testModificar_Falla_UnidadConHorasCero()
    {
        // OBJETIVO: Probar que la modificación falla si CUALQUIER UC tiene todas las horas en 0.

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10], // Válida
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 0, 'hora_asistida' => 0, 'hora_academica' => 0]  // Inválida (en 0)
        ];

        // 1. Mockear mocks internos (NO deberían llamarse)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);

        // 2. Asegurar que la validación de cohorte NUNCA se llama
        $this->malla->expects($this->never())->method('ExisteCohorte');
        
        // 3. Asegurar que la transacción NUNCA inicia
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('prepare');

        // Ejecutar
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('horas en 0', $resultado['mensaje']);
    }

    public function testModificar_Falla_UnidadConValoresNulos()
    {
        // OBJETIVO: Probar que la modificación falla si CUALQUIER UC tiene valores nulos o faltantes.

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10] // Inválida (falta hora_academica)
        ];

        // 1. Mockear mocks internos (NO deberían llamarse)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        
        // 2. Asegurar que la validación de cohorte NUNCA se llama
        $this->malla->expects($this->never())->method('ExisteCohorte');

        // 3. Asegurar que la transacción NUNCA inicia
        $this->pdoMock->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('prepare');

        // Ejecutar
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('nulos o faltantes', $resultado['mensaje']);
    }

    public function testModificar_Exito_SinCambioDeCodigo()
    {
        // 1. Array de unidades VÁLIDO
        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
            ['uc_codigo' => 'UC-T2', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
            ['uc_codigo' => 'UC-T3', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
            ['uc_codigo' => 'UC-T4', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
        ];
        $num_unidades = count($unidades);

        // 2. Mockear ExisteCohorte (pasa)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 3. Mockear Transacción (Esperamos éxito)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack'); // Esperamos que NUNCA falle

        // 4. Mocks para los statements
        // No necesitamos $stmtTrayectos aquí
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);

        // 5. Secuencia de llamadas a prepare() - SIN validación de trayectos
        $consecutive_calls = [
            // 1. UPDATE tbl_malla (Es la PRIMERA llamada real)
            [$this->stringStartsWith('UPDATE tbl_malla SET mal_codigo = :nuevo_codigo')],
            // 2. DELETE FROM uc_malla
            [$this->stringStartsWith('DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo_original')],
        ];
        // Los mocks devueltos empiezan con Update y Delete
        $return_calls = [ $stmtUpdate, $stmtDelete ];

        // 3. Bucle de INSERTs
        for ($i = 0; $i < $num_unidades; $i++) {
            $consecutive_calls[] = [$this->stringStartsWith('INSERT INTO uc_malla')];
            $return_calls[] = $stmtInsert;
        }

        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(...$consecutive_calls)
            ->willReturnOnConsecutiveCalls(...$return_calls);

        // 6. NO necesitamos configurar $stmtTrayectos

        // 7. Definir expectativas de ejecución
        $stmtUpdate->expects($this->once())->method('execute');
        $stmtDelete->expects($this->once())->method('execute');
        $stmtInsert->expects($this->exactly($num_unidades))->method('execute');

        // Ejecutar
        $this->malla->setMalCodigo('MALLA-1');
        $this->malla->setMalCodigoOriginal('MALLA-1'); // Mismo código
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('modificar', $resultado['resultado']);
    }
  public function testModificar_Exito_ConCambioDeCodigo()
    {
        // 1. Array de unidades VÁLIDO
        $unidades = [
             ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T2', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T3', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
             ['uc_codigo' => 'UC-T4', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1],
        ];
        $num_unidades = count($unidades);

        // 2. Mockear ExisteCohorte (pasa)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 3. Mockear Transacción (Esperamos éxito)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        $this->pdoMock->expects($this->never())->method('rollBack'); // Esperamos que NUNCA falle

        // 4. Mocks para los statements
        // No necesitamos $stmtTrayectos aquí
        $stmtCheck = $this->createMock(PDOStatement::class); // Mock para el chequeo de código
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class);

        // 5. Secuencia de llamadas a prepare() - SIN validación de trayectos
        $consecutive_calls = [
            // 1. Chequeo de código nuevo (Es la PRIMERA llamada real en este caso)
            [$this->stringStartsWith('SELECT mal_codigo FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
            // 2. UPDATE tbl_malla
            [$this->stringStartsWith('UPDATE tbl_malla SET mal_codigo = :nuevo_codigo')],
            // 3. DELETE FROM uc_malla
            [$this->stringStartsWith('DELETE FROM uc_malla WHERE mal_codigo = :mal_codigo_original')],
        ];
        // Los mocks devueltos empiezan con Check, Update, Delete
        $return_calls = [ $stmtCheck, $stmtUpdate, $stmtDelete ];

        // 4. Bucle de INSERTs
        for ($i = 0; $i < $num_unidades; $i++) {
            $consecutive_calls[] = [$this->stringStartsWith('INSERT INTO uc_malla')];
            $return_calls[] = $stmtInsert;
        }

        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(...$consecutive_calls)
            ->willReturnOnConsecutiveCalls(...$return_calls);

        // 6. Configurar mock de chequeo de código para que PASE
        $stmtCheck->method('fetch')->willReturn(false); // El nuevo código NO existe
        // Esperar que se ejecute la consulta de chequeo
        $stmtCheck->expects($this->once())->method('execute');
        // NO necesitamos configurar $stmtTrayectos

        // 7. Definir expectativas de ejecución para los otros statements
        $stmtUpdate->expects($this->once())->method('execute');
        $stmtDelete->expects($this->once())->method('execute');
        $stmtInsert->expects($this->exactly($num_unidades))->method('execute');

        // Ejecutar
        $this->malla->setMalCodigo('MALLA-NUEVA');
        $this->malla->setMalCodigoOriginal('MALLA-VIEJA'); // Código diferente
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('modificar', $resultado['resultado']);
    }


    public function testModificar_Falla_NuevoCodigoYaExiste()
    {
        $unidades = [['uc_codigo' => 'UC1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1]];

        // Mockear ExisteCohorte (pasa)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // Mockear Transacción (falla)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Mockear Chequeo de código nuevo (SÍ existe)
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(true); // El nuevo código SÍ existe

        // Ejecutar
        $this->malla->setMalCodigo('MALLA-NUEVA');
        $this->malla->setMalCodigoOriginal('MALLA-VIEJA'); // Código diferente
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringContainsString('El nuevo código ya está en uso', $resultado['mensaje']);
    }

     public function testModificar_Falla_CohorteDuplicada()
    {
        $unidades = [['uc_codigo' => 'UC1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1]];

        // Mockear ExisteCohorte (falla)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'existe', 'mensaje' => 'Cohorte duplicada']);

        // Ejecutar
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertEquals('Cohorte duplicada', $resultado['mensaje']);
        // Asegurar que no se inició la transacción
        $this->pdoMock->expects($this->never())->method('beginTransaction');
    }

    public function testModificar_Falla_DBException()
    {
         $unidades = [['uc_codigo' => 'UC1', 'hora_independiente' => 1, 'hora_asistida' => 1, 'hora_academica' => 1]];

        // Mockear ExisteCohorte (pasa)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // Mockear Transacción (falla)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Simular que el UPDATE falla
         $this->pdoMock->method('prepare')
            ->with($this->stringContains('UPDATE tbl_malla'))
            ->willThrowException(new PDOException('Error de UPDATE'));

        // Ejecutar
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de UPDATE', $resultado['mensaje']);
    }
public function testModificar_Falla_DBExceptionEnInsertDeUCMalla()
    {
        // OBJETIVO: Probar que un fallo al RE-INSERTAR en 'uc_malla'
        // revierte la transacción (ROLLBACK).

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 10, 'hora_asistida' => 10, 'hora_academica' => 10],
        ];

        // 1. Mockear ExisteCohorte (pasa)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);

        // 2. Mocks para los statements
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert_Falla = $this->createMock(PDOStatement::class);

        // 3. Secuencia de llamadas a prepare()
        $this->pdoMock->expects($this->any())
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: UPDATE (pasa)
                [$this->stringContains('UPDATE tbl_malla')],
                // 2da llamada: DELETE (pasa)
                [$this->stringContains('DELETE FROM uc_malla')],
                // 3ra llamada: INSERT (¡falla!)
                [$this->stringContains('INSERT INTO uc_malla')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtUpdate,
                $stmtDelete,
                $stmtInsert_Falla
            );

        // 4. Configurar mocks
        $stmtUpdate->method('execute')->willReturn(true); // Pasa UPDATE
        $stmtDelete->method('execute')->willReturn(true); // Pasa DELETE
        $stmtInsert_Falla->method('execute')
             ->willThrowException(new PDOException('Error en bucle INSERT de modificar')); // Falla INSERT

        // 5. Mockear Transacción (falla)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Ejecutar
        $this->malla->setMalCodigo('MALLA-1');
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en bucle INSERT de modificar', $resultado['mensaje']);
    }

   public function testModificar_Exito_OmiteUnidadesConHorasCero()
    {
        // OBJETIVO: Probar que la lógica de omitir horas 0 también
        // funciona en el método Modificar.

        $unidades = [
            ['uc_codigo' => 'UC-T0', 'hora_independiente' => 1, 'hora_asistida' => 0, 'hora_academica' => 0], // INSERTA (1)
            ['uc_codigo' => 'UC-T1', 'hora_independiente' => 0, 'hora_asistida' => 0, 'hora_academica' => 0], // OMITE
            ['uc_codigo' => 'UC-T2', 'hora_independiente' => 0, 'hora_asistida' => 5, 'hora_academica' => 0], // INSERTA (2)
        ];
        $numero_de_inserts_esperados = 2; // <-- Solo 2 UCs se insertarán
        $numero_de_prepare_esperados = 4; // <-- 1 (check) + 1 (update) + 1 (delete) + 1 (uc_malla)

        // 1. Mockear ExisteCohorte (pasa)
        $this->malla = $this->getMockBuilder(Malla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['Con', 'ExisteCohorte'])
            ->getMock();
        $this->malla->method('Con')->willReturn($this->pdoMock);
        $this->malla->method('ExisteCohorte')->willReturn(['resultado' => 'ok']);
        
        // Asignamos códigos para que el chequeo de código (línea 167) se salte
        // Esto hace la prueba más limpia y se enfoca en el bucle
        $this->malla->setMalCodigo('MALLA-1');
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $numero_de_prepare_esperados = 3; // <-- 1 (update) + 1 (delete) + 1 (uc_malla)


        // 2. Mocks para los statements
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtInsert = $this->createMock(PDOStatement::class); // Se reutilizará

        // 3. **LA CORRECCIÓN**
        $this->pdoMock->expects($this->exactly($numero_de_prepare_esperados))
            ->method('prepare')
            ->withConsecutive(
                // No hay 'SELECT mal_codigo' porque mal_codigo === mal_codigo_original
                [$this->stringContains('UPDATE tbl_malla')],      // 1. Update malla
                [$this->stringContains('DELETE FROM uc_malla')],  // 2. Delete UCs
                [$this->stringContains('INSERT INTO uc_malla')]    // 3. Preparar el bucle (SOLO 1 VEZ)
            )
            ->willReturnOnConsecutiveCalls(
                $stmtUpdate,
                $stmtDelete,
                $stmtInsert // Devuelve el mock reutilizable
            );
        // ------------------------------------------

        // 4. Configurar mocks de statements (no es necesario fetchAll)

        // 5. Mockear Transacción (éxito)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');
        // El error decía que rollBack() FUE llamado, ahora esperamos que NUNCA lo sea
        $this->pdoMock->expects($this->never())->method('rollBack'); 

        // 6. Validar ejecuciones
        $stmtUpdate->expects($this->once())->method('execute');
        $stmtDelete->expects($this->once())->method('execute');
        // Esperamos que 'execute' se llame solo 2 veces (omitiendo la UC-T1)
        $stmtInsert->expects($this->exactly($numero_de_inserts_esperados))->method('execute');

        // Ejecutar
        // Los códigos ya fueron seteados en el paso 1
        $resultado = $this->malla->Modificar($unidades);

        // Validar
        $this->assertEquals('modificar', $resultado['resultado']);
    }
    // Pruebas para: Consultar()
    //--------------------------------------------------------------------------

    public function testConsultar_Exito()
    {
        $datosEsperados = [
            ['mal_codigo' => 'M-01', 'mal_nombre' => 'Malla 1', 'mal_activa' => 1],
            ['mal_codigo' => 'M-02', 'mal_nombre' => 'Malla 2', 'mal_activa' => 0]
        ];

        // Configurar Mocks
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT * FROM tbl_malla'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($datosEsperados);

        // Ejecutar
        $resultado = $this->malla->Consultar();

        // Validar
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }

    public function testConsultar_Falla_DBException()
    {
        // Configurar Mocks
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willThrowException(new PDOException('Error de Consulta'));

        // Ejecutar
        $resultado = $this->malla->Consultar();

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de Consulta', $resultado['mensaje']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: cambiarEstadoActivo()
    //--------------------------------------------------------------------------
public function testCambiarEstadoActivo_Falla_DBExceptionEnUpdate()
    {
        // OBJETIVO: Probar que un fallo en el UPDATE (después de un
        // SELECT exitoso) revierte la transacción.

        // 1. Mocks para los statements
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate_Falla = $this->createMock(PDOStatement::class);

        // 2. Secuencia de llamadas a prepare()
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: SELECT (pasa)
                [$this->stringContains('SELECT mal_activa FROM tbl_malla')],
                // 2da llamada: UPDATE (¡falla!)
                [$this->stringContains('UPDATE tbl_malla SET mal_activa')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtSelect,
                $stmtUpdate_Falla
            );

        // 3. Configurar mocks
        $stmtSelect->method('fetchColumn')->willReturn('1'); // Pasa SELECT
        $stmtUpdate_Falla->method('execute')
             ->willThrowException(new PDOException('Error en UPDATE de estado')); // Falla UPDATE

        // 4. Mockear Transacción (falla)
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Ejecutar
        $resultado = $this->malla->cambiarEstadoActivo();

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en UPDATE de estado', $resultado['mensaje']);
    }
    public function testCambiarEstadoActivo_Desactivar()
    {
        // 1. Configurar Mocks
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');

        // 2. Mocks para los statements
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        // 3. Secuencia de llamadas a prepare()
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: SELECT
                [$this->stringContains('SELECT mal_activa FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
                // 2da llamada: UPDATE
                [$this->stringContains('UPDATE tbl_malla SET mal_activa = :nuevo_estado')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtSelect,
                $stmtUpdate
            );

        // 4. Configurar el mock de SELECT para que devuelva "1" (Activa)
        $stmtSelect->method('fetchColumn')->willReturn('1'); 
        $stmtSelect->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');

        // Ejecutar
        $this->malla->setMalCodigo('M-01');
        $resultado = $this->malla->cambiarEstadoActivo();

        // Validar
        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals('desactivar', $resultado['accion_bitacora']);
        $this->assertStringContainsString('desactivada', $resultado['mensaje']);
    }
    public function testCambiarEstadoActivo_Activar()
    {
        // 1. Configurar Mocks
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->once())->method('commit');

        // 2. Mocks para los statements
        $stmtSelect = $this->createMock(PDOStatement::class);
        $stmtUpdate = $this->createMock(PDOStatement::class);

        // 3. Secuencia de llamadas a prepare()
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                // 1ra llamada: SELECT
                [$this->stringContains('SELECT mal_activa FROM tbl_malla WHERE mal_codigo = :mal_codigo')],
                // 2da llamada: UPDATE
                [$this->stringContains('UPDATE tbl_malla SET mal_activa = :nuevo_estado')]
            )
            ->willReturnOnConsecutiveCalls(
                $stmtSelect,
                $stmtUpdate
            );
            
        // 4. Configurar el mock de SELECT para que devuelva "0" (Inactiva)
        $stmtSelect->method('fetchColumn')->willReturn('0'); 
        $stmtSelect->expects($this->once())->method('execute');
        $stmtUpdate->expects($this->once())->method('execute');

        // Ejecutar
        $resultado = $this->malla->cambiarEstadoActivo();

        // Validar
        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals('activar', $resultado['accion_bitacora']);
        $this->assertStringContainsString('activada', $resultado['mensaje']);
    }

    public function testCambiarEstadoActivo_Falla_DBException()
    {
        // Configurar Mocks
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->once())->method('rollBack');

        // Simular que el SELECT falla
        $this->pdoMock->method('prepare')
             ->with($this->stringContains('SELECT mal_activa'))
             ->willThrowException(new PDOException('Error de SELECT estado'));

        // Ejecutar
        $resultado = $this->malla->cambiarEstadoActivo();

        // Validar
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de SELECT estado', $resultado['mensaje']);
    }


    //--------------------------------------------------------------------------
    // Pruebas para: Existecodigo()
    //--------------------------------------------------------------------------

    public function testExistecodigo_OK_CodigoEsIgualAlOriginal()
    {
        $this->pdoMock->expects($this->never())->method('prepare');
        $this->malla->setMalCodigo('CODIGO-1');
        $this->malla->setMalCodigoOriginal('CODIGO-1');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('ok', $resultado['resultado']);
    }

    public function testExistecodigo_OK_CodigoEsNuevoYNoExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->malla->setMalCodigo('CODIGO-NUEVO');
        $this->malla->setMalCodigoOriginal('CODIGO-VIEJO');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('ok', $resultado['resultado']);
    }

    public function testExistecodigo_Existe_CodigoEsNuevoPeroExiste()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'CODIGO-DUPLICADO']); // Simula encontrar una fila
        $this->malla->setMalCodigo('CODIGO-DUPLICADO');
        $this->malla->setMalCodigoOriginal('CODIGO-VIEJO');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('existe', $resultado['resultado']);
        $this->assertStringContainsString('codigo ya existe', $resultado['mensaje']);
    }

    public function testExistecodigo_Existe_CreandoNuevo()
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'CODIGO-DUPLICADO']);
        $this->malla->setMalCodigo('CODIGO-DUPLICADO');
        $this->malla->setMalCodigoOriginal(null);
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('existe', $resultado['resultado']);
    }

    public function testExistecodigo_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error chequeando código'));
        $this->malla->setMalCodigo('CODIGO-NUEVO');
        $this->malla->setMalCodigoOriginal('CODIGO-VIEJO');
        $resultado = $this->malla->Existecodigo();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error chequeando código', $resultado['mensaje']);
    }


    //--------------------------------------------------------------------------
    // Pruebas para: ExisteCohorte()
    //--------------------------------------------------------------------------

    public function testExisteCohorte_NoExiste_ModoRegistrar()
    {
        $this->pdoMock->method('prepare')->with("SELECT * FROM tbl_malla WHERE mal_cohorte = :mal_cohorte")->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->malla->setMalCohorte(5);
        $resultado = $this->malla->ExisteCohorte(false);
        $this->assertEquals('ok', $resultado['resultado']);
    }

    public function testExisteCohorte_Existe_ModoRegistrar()
    {
        $this->pdoMock->method('prepare')->with("SELECT * FROM tbl_malla WHERE mal_cohorte = :mal_cohorte")->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'OTRA-MALLA']); // Simula encontrar una fila
        $this->malla->setMalCohorte(5);
        $resultado = $this->malla->ExisteCohorte(false);
        $this->assertEquals('existe', $resultado['resultado']);
    }

    public function testExisteCohorte_NoExiste_ModoModificar()
    {
        $this->pdoMock->method('prepare')
             ->with($this->stringContains("AND mal_codigo != :mal_codigo_excluir"))
             ->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->malla->setMalCohorte(5);
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $resultado = $this->malla->ExisteCohorte(true);
        $this->assertEquals('ok', $resultado['resultado']);
    }

     public function testExisteCohorte_Existe_ModoModificar()
    {
        $this->pdoMock->method('prepare')
             ->with($this->stringContains("AND mal_codigo != :mal_codigo_excluir"))
             ->willReturn($this->stmtMock);
        $this->stmtMock->method('fetch')->willReturn(['mal_codigo' => 'OTRA-MALLA']); // Simula encontrar una fila
        $this->malla->setMalCohorte(5);
        $this->malla->setMalCodigoOriginal('MALLA-1');
        $resultado = $this->malla->ExisteCohorte(true);
        $this->assertEquals('existe', $resultado['resultado']);
    }

    public function testExisteCohorte_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error chequeando cohorte'));
        $this->malla->setMalCohorte(5);
        $resultado = $this->malla->ExisteCohorte(false);
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error chequeando cohorte', $resultado['mensaje']);
    }

    //--------------------------------------------------------------------------
    // Pruebas para: obtenerUnidadesCurriculares()
    //--------------------------------------------------------------------------

    public function testObtenerUnidadesCurriculares_Exito()
    {
        $datosEsperados = [
            ['uc_codigo' => 'UC-01', 'uc_nombre' => 'Unidad 1', 'uc_trayecto' => '0'],
            ['uc_codigo' => 'UC-02', 'uc_nombre' => 'Unidad 2', 'uc_trayecto' => '1']
        ];

        $this->pdoMock->method('query')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($datosEsperados);

        $resultado = $this->malla->obtenerUnidadesCurriculares();

        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }

    public function testObtenerUnidadesCurriculares_Falla_DBException()
    {
        $this->pdoMock->method('query')->willThrowException(new PDOException('Error obteniendo UCs'));
        $resultado = $this->malla->obtenerUnidadesCurriculares();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error obteniendo UCs', $resultado['mensaje']);
    }


    //--------------------------------------------------------------------------
    // Pruebas para: obtenerUnidadesPorMalla()
    //--------------------------------------------------------------------------

    public function testObtenerUnidadesPorMalla_Exito()
    {
        $datosEsperados = [
            ['uc_codigo' => 'UC-01', 'uc_nombre' => 'Unidad 1', 'uc_trayecto' => '0', 'mal_hora_independiente' => 10, 'mal_hora_asistida' => 5, 'mal_hora_academica' => 3],
        ];

        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($datosEsperados);

        $this->malla->setMalCodigo('MALLA-1');
        $resultado = $this->malla->obtenerUnidadesPorMalla();

        $this->assertEquals('ok', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }

    public function testObtenerUnidadesPorMalla_Falla_DBException()
    {
        $this->pdoMock->method('prepare')->willThrowException(new PDOException('Error obteniendo UCs por malla'));
        $this->malla->setMalCodigo('MALLA-1');
        $resultado = $this->malla->obtenerUnidadesPorMalla();
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error obteniendo UCs por malla', $resultado['mensaje']);
    }

}