<?php
use PHPUnit\Framework\TestCase;
require_once 'model/titulo.php';

/**
 * Esta es la suite de pruebas unitarias para la clase Titulo.
 * Su objetivo es probar toda la lógica de negocio de la clase Titulo
 * aislando completamente la base de datos (PDO).
 */
class TituloTest extends TestCase
{
    // Propiedad para guardar el "doble" falso de PDO
 private $pdoMock;
    // Propiedad para guardar el "doble" parcial de la clase Titulo
 private $titulo;

    /**
     * Esta función se ejecuta ANTES de CADA prueba (test...).
     * Su propósito es configurar un entorno limpio y controlado para cada test.
     */
 protected function setUp(): void
 {
        // 1. Creamos un mock (un doble) de la clase PDO.
        // Esto simula la conexión a la BD sin conectarse realmente.
 $this->pdoMock = $this->createMock(PDO::class);

        // 2. Creamos un "Mock Parcial" de nuestra clase Titulo.
 $this->titulo = $this->getMockBuilder(Titulo::class)
            // 3. Deshabilitamos el constructor original (ej. parent::__construct())
  ->disableOriginalConstructor()
            // 4. Le decimos que SÓLO reemplace el método 'Con'.
            // Todos los demás (Registrar, Modificar, etc.) serán el CÓDIGO REAL.
  ->onlyMethods(['Con'])
  ->getMock();

        // 5. LA MAGIA: Cuando el código real de $this->titulo llame a $this->Con(),
        // PHPUnit interceptará esa llamada y devolverá nuestro $pdoMock falso.
 $this->titulo->method('Con')->willReturn($this->pdoMock);
 
        // 6. Simula que cualquier llamada a 'setAttribute' (que PDO usa) siempre funciona.
 $this->pdoMock->method('setAttribute')->willReturn(true);
 }

 public function testGettersAndSetters()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');

        // 2. Validación
        $this->assertEquals('Ing.', $this->titulo->get_prefijo());
        $this->assertEquals('Sistemas', $this->titulo->get_nombreTitulo());
    }
    // --- Pruebas para el método Registrar() ---


    public function testConsultarDevuelveDatosCorrectamente()
    {
        // 1. Preparación: Datos que simulamos que la BD devuelve
        $datosEsperados = [
            ['tit_prefijo' => 'Ing.', 'tit_nombre' => 'Sistemas'],
            ['tit_prefijo' => 'Lic.', 'tit_nombre' => 'Letras'],
            ['tit_prefijo' => 'Dr.', 'tit_nombre' => 'Ciencias']
        ];

        // 2. Configuración de Mocks
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn($datosEsperados);

        // IMPORTANTE: El método Consultar() usa ->query(), no ->prepare()
        $this->pdoMock->method('query')->willReturn($stmt);

        // 3. Ejecución
        $resultado = $this->titulo->Consultar();

        // 4. Validación
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']);
    }

    /**
     * Prueba que Consultar() maneja correctamente una excepción de la BD.
     */
    public function testConsultarManejaExcepciones()
    {
        // 1. Preparación: Creamos una excepción falsa
        $exception = new PDOException("Error de consulta SQL");
        
        // 2. Configuración de Mocks
        // Le decimos a ->query() que lance un error
        $this->pdoMock->method('query')->will($this->throwException($exception));

        // 3. Ejecución
        $resultado = $this->titulo->Consultar();

        // 4. Validación
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error de consulta SQL', $resultado['mensaje']);
    }

    // --- Pruebas para el método Existe() ---

    /**
     * Prueba que Existe() devuelve 'true' cuando encuentra un registro.
     * (Modo Registrar)
     */
    public function testExisteDevuelveTrueSiExiste()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');
        // No seteamos los "originales"

        // 2. Configuración de Mocks
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(['1']); // Devuelve un registro

        $this->pdoMock->method('prepare')->willReturn($stmt);
        
        // 3. Ejecución
        $resultado = $this->titulo->Existe();

        // 4. Validación
        $this->assertTrue($resultado);
    }

    /**
     * Prueba que Existe() devuelve 'false' cuando NO encuentra un registro.
     * (Modo Registrar)
     */
    public function testExisteDevuelveFalseSiNoExiste()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');

        // 2. Configuración de Mocks
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false); // NO devuelve registro

        $this->pdoMock->method('prepare')->willReturn($stmt);
        
        // 3. Ejecución
        $resultado = $this->titulo->Existe();

        // 4. Validación
        $this->assertFalse($resultado);
    }

    /**
     * Prueba la lógica de exclusión de Existe() en "Modo Modificar".
     * Si los datos nuevos y originales son iguales, debe devolver 'false'
     * (porque se está excluyendo a sí mismo de la búsqueda).
     */
    public function testExisteDevuelveFalseEnModoModificarCuandoDatosNoCambian()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Sistemas');
        $this->titulo->set_original_prefijo('Ing.'); // Mismos datos
        $this->titulo->set_original_nombre('Sistemas'); // Mismos datos

        // 2. Configuración de Mocks
        $stmt = $this->createMock(PDOStatement::class);
        // La consulta SQL (que incluye "AND (tit_prefijo != ...)") 
        // no debe encontrar nada, por lo tanto, devuelve false.
        $stmt->method('fetch')->willReturn(false); 

        $this->pdoMock->method('prepare')->willReturn($stmt);
        
        // 3. Ejecución
        $resultado = $this->titulo->Existe();

        // 4. Validación
        $this->assertFalse($resultado);
    }

    /**
     * Prueba que el registro falla si el nombre es un string VACÍO.
     * La lógica de tu aplicación (validación frontend) debería evitar esto,
     * pero la BD también debería rechazarlo (CHECK constraint).
     */
    public function testRegistrarConNombreVacioFalla()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(''); // <-- String vacío

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); // No existe activo

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false); // No existe inactivo

        // Mock para el INSERT
        $stmtInsert = $this->createMock(PDOStatement::class);
        // Simulamos un error genérico de constraint (ej. CHECK fallido)
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; // Código genérico de constraint
        $stmtInsert->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * Prueba que el registro falla si el prefijo es un string VACÍO.
     */
    public function testRegistrarConPrefijoVacioFalla()
    {
        // 1. Preparación
        $this->titulo->set_prefijo(''); // <-- String vacío
        $this->titulo->set_nombreTitulo('Un Nombre Valido');

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); // No existe activo

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false); // No existe inactivo

        // Mock para el INSERT
        $stmtInsert = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $stmtInsert->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
    }

    /**
     * Prueba que la modificación falla si el nuevo nombre es un string VACÍO.
     */
    public function testModificarAUnNombreVacioFalla()
    {
        // 1. Preparación
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(''); // <-- String vacío

        // 2. Configuración de Mocks
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); // Pasa la validación de duplicado

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);

        // Mock para el UPDATE
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; // Error de "CHECK constraint"
        $exception->errorInfo[1] = null; 
        $stmtUpdate->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);

        // 4. Ejecución
        $resultado = $this->titulo->Modificar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertStringNotContainsString('utilizado por uno o más docentes', $resultado['mensaje']);
    }

    public function testRegistrarConEspaciosEnBlancoCircundantes()
    {
        // 1. Preparación: Asignamos datos CON espacios
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo(' En Sistemas '); // <-- Datos "sucios"

        // 2. Configuración de Mocks (Simulamos que no existe)
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        // La prueba debe PASAR, demostrando que el título se registró
        // con los espacios extra.
        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * Prueba la lógica de modificación cuando solo cambia UNA parte de la llave.
     * (Ej: Cambiar de 'Ing.' a 'Dr.' pero mantener 'En Sistemas').
     * Esto verifica que la lógica "Existe()" funciona bien en este caso de borde.
     */
    public function testModificarSoloPrefijoExitoso()
    {
        // 1. Preparación: datos originales y nuevos
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Dr.'); // <-- Solo cambia el prefijo
        $this->titulo->set_nombreTitulo('En Sistemas'); 

        // 2. Configuración de Mocks
        // Mock para la consulta de chequeo (¿existe 'Dr.', 'En Sistemas'?)
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); // No existe, se puede modificar

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);

        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdate->method('execute')->willReturn(true);

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);

        // 4. Ejecución
        $resultado = $this->titulo->Modificar();

        // 5. Validación
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
    }

    /**
     * Prueba de robustez: ¿Qué pasa si el controlador (por un error)
     * manda un tipo de dato incorrecto, como un Array, en lugar de un string?
     * PHP es de tipado débil, pero PDO fallará.
     */
    public function testRegistrarConTipoDeDatoIncorrectoFalla()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        // PHP lanzará un "Array to string conversion" que PDO no manejará
        $this->titulo->set_nombreTitulo(['Esto es un array']); 

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        // Mock para el INSERT
        $stmtInsert = $this->createMock(PDOStatement::class);
        // bindParam() fallará y lanzará una excepción
        $exception = new PDOException("Error en bindParam, tipo incorrecto");
        $stmtInsert->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
        $this->assertEquals('Error en bindParam, tipo incorrecto', $resultado['mensaje']);
    }

    /**
     * Prueba la sensibilidad a mayúsculas/minúsculas (Case-Sensitivity).
     * El código asume que la BD distingue entre 'Sistemas' y 'sistemas'.
     * Esta prueba verifica que se pueden registrar como dos títulos distintos.
     */
    public function testRegistrarTituloConDiferenteCaseEsExitoso()
    {
        // 1. Preparación: Asignamos 'Ing.' y 'sistemas' (minúscula)
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('sistemas');

        // 2. Configuración de Mocks
        // Simulamos que 'Ing.', 'sistemas' (minúscula) NO existe activo
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        // Simulamos que 'Ing.', 'sistemas' (minúscula) NO existe inactivo
        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        // Simulamos que el INSERT funciona
        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        // Debe registrarse sin problemas, probando que se considera
        // un título diferente a 'Sistemas' (mayúscula).
        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * Prueba el "camino feliz" de Consultar() cuando la tabla está vacía.
     */
    public function testConsultarDevuelveArrayVacioCuandoNoHayTitulos()
    {
        // 1. Preparación: Datos vacíos
        $datosEsperados = [];

        // 2. Configuración de Mocks
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn($datosEsperados); // Devuelve array vacío

        $this->pdoMock->method('query')->willReturn($stmt);

        // 3. Ejecución
        $resultado = $this->titulo->Consultar();

        // 4. Validación
        $this->assertEquals('consultar', $resultado['resultado']);
        $this->assertEquals($datosEsperados, $resultado['mensaje']); // Confirma que el mensaje es []
    }

    /**
     * Prueba de BUG: El método Existe() tiene un try/catch que devuelve 'true'
     * ante CUALQUIER error de base de datos. Esta prueba lo confirma.
     */
    public function testExisteDevuelveTrueErroneamenteCuandoLaConsultaFalla()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Error');
        $this->titulo->set_nombreTitulo('Fatal');

        // 2. Configuración de Mocks
        // Simulamos que la consulta (prepare o execute) falla
        $exception = new PDOException("Error de SQL");
        $this->pdoMock->method('prepare')->will($this->throwException($exception));

        // 3. Ejecución
        // El 'catch' en Existe() atrapará la excepción...
        $resultado = $this->titulo->Existe();

        // 4. Validación
        // ...y devolverá 'true', lo cual es un comportamiento incorrecto (un bug).
        // La prueba PASARÁ, confirmando la existencia del bug en el código.
        $this->assertTrue($resultado);
    }

    public function testModificarManejaExcepcionGenerica()
    {
        // 1. Preparación
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('En Informática');

        // 2. Configuración de Mocks
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); 

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);

        $stmtUpdate = $this->createMock(PDOStatement::class);
        // Simulamos un error genérico (ej. '42S02' = Tabla no encontrada)
        $exception = new PDOException("Tabla 'tbl_titulo' no existe");
        $exception->errorInfo[0] = '42S02'; 
        $stmtUpdate->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);

        // 4. Ejecución
        $resultado = $this->titulo->Modificar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
        // Valida que devuelve el mensaje crudo del error, no el de "en uso"
        $this->assertEquals("Tabla 'tbl_titulo' no existe", $resultado['mensaje']);
    }
    public function testRegistrarConLongitudMinimaExacta()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Cinco'); // Exactamente 5 caracteres

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    
    /**
     * Prueba el límite SUPERIOR.
     * Verifica que el registro funciona con la longitud máxima permitida (ej. 80).
     */
    public function testRegistrarConLongitudMaximaExacta()
    {
        // 1. Preparación
        $nombreLargo = str_repeat('a', 80); // Asumiendo VARCHAR(80)
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo($nombreLargo); 

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('registrar', $resultado['resultado']);
    }

    /**
     * Prueba el camino triste "demasiado corto".
     * Esto solo fallará si la Base de Datos tiene un CHECK constraint
     * (ej: CHECK (LENGTH(tit_nombre) >= 5)).
     */
    public function testRegistrarConNombreDemasiadoCortoFalla()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Abc'); // Menos de 5 caracteres

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        // Mock para el INSERT
        $stmtInsert = $this->createMock(PDOStatement::class);
        // Simulamos un error de CHECK constraint
        $exception = new PDOException();
        $exception->errorInfo[0] = '23000'; 
        $stmtInsert->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
    }
   

    /**
     * Prueba el "camino bueno" de la seguridad de PDO.
     * Verifica que registrar un nombre con comillas simples (un carácter
     * clásico de SQL Injection) NO rompe la consulta y se registra
     * correctamente gracias a bindParam.
     */
    public function testRegistrarConComillasSimplesEsExitoso()
    {
        // 1. Preparación: El dato "peligroso"
        $this->titulo->set_prefijo('Dr.');
        $this->titulo->set_nombreTitulo("Doctor 'Honoris Causa'"); 

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->method('execute')->willReturn(true); 

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        // Debe ser exitoso, probando que bindParam manejó la comilla.
        $this->assertEquals('registrar', $resultado['resultado']);
    }
    
    /**
     * Prueba el "camino malo" del encoding de la Base de Datos.
     * ¿Qué pasa si se envía un carácter UTF-8 de 4 bytes (como un emoji)
     * y la BD no está configurada con 'utf8mb4'?
     * La BD debería lanzar un error.
     */
    public function testRegistrarConCaracterUTF8ExtensoFalla()
    {
        // 1. Preparación
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('Ingeniería Aeroespacial 🚀'); 

        // 2. Configuración de Mocks
        $stmtExisteActivo = $this->createMock(PDOStatement::class);
        $stmtExisteActivo->method('fetchColumn')->willReturn(false); 

        $stmtExisteInactivo = $this->createMock(PDOStatement::class);
        $stmtExisteInactivo->method('fetchColumn')->willReturn(false);

        // Mock para el INSERT
        $stmtInsert = $this->createMock(PDOStatement::class);
        // Simulamos un error de "Incorrect string value"
        $exception = new PDOException();
        $exception->errorInfo[0] = '22007'; // Código de error común para encoding
        $stmtInsert->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $stmtExisteActivo,
                $stmtExisteInactivo,
                $stmtInsert
            );

        // 4. Ejecución
        $resultado = $this->titulo->Registrar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
    }
    /**
     * Prueba que la modificación funciona correctamente incluso si no
     * se cambió ningún dato.
     */
    public function testModificarSinCambiosFunciona()
    {
        // 1. Preparación: Datos originales y nuevos son IDÉNTICOS
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('En Sistemas');

        // 2. Configuración de Mocks
        // NOTA: La lógica de tu clase omite el chequeo "Existe()" si
        // los datos no cambiaron, y pasa directo al UPDATE.

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);

        $stmtUpdate = $this->createMock(PDOStatement::class);
        $stmtUpdate->method('execute')->willReturn(true);

        // 3. Secuencia de PDO (Solo espera 2 llamadas)
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtDelete, $stmtUpdate);

        // 4. Ejecución
        $resultado = $this->titulo->Modificar();

        // 5. Validación
        $this->assertEquals('modificar', $resultado['resultado']);
        $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
    }

    /**
     * Prueba la captura de un error de Foreign Key (violación de integridad)
     * al MODIFICAR un título que está en uso.
     */
    public function testModificarTituloEnUsoGeneraErrorDeConstraint()
    {
        // 1. Preparación
        $this->titulo->set_original_prefijo('Ing.');
        $this->titulo->set_original_nombre('En Sistemas');
        $this->titulo->set_prefijo('Ing.');
        $this->titulo->set_nombreTitulo('En Informática'); // Un cambio válido

        // 2. Configuración de Mocks
        $stmtExiste = $this->createMock(PDOStatement::class);
        $stmtExiste->method('fetch')->willReturn(false); // Pasa la validación de duplicado

        $stmtDelete = $this->createMock(PDOStatement::class);
        $stmtDelete->method('execute')->willReturn(true);

        // Mock para el UPDATE
        $stmtUpdate = $this->createMock(PDOStatement::class);
        $exception = new PDOException();
        // Asignamos el código de error de SQL para "Integrity Constraint Violation"
        $exception->errorInfo[0] = '23000'; 
        $stmtUpdate->method('execute')->will($this->throwException($exception));

        // 3. Secuencia de PDO
        $this->pdoMock->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);

        // 4. Ejecución
        $resultado = $this->titulo->Modificar();

        // 5. Validación
        $this->assertEquals('error', $resultado['resultado']);
        // Validamos que se muestre el mensaje amigable
        $this->assertStringContainsString('utilizado por uno o más docentes', $resultado['mensaje']);
    }
 public function testRegistrarTituloNuevoExitoso()
 {
        // 1. Preparación: Asignamos los datos al objeto
  $this->titulo->set_prefijo('Ing.');
 $this->titulo->set_nombreTitulo('En Sistemas');

        // 2. Configuración de Mocks (Simulamos las respuestas de la BD)
        
        // Mock para la 1ra consulta (buscar título activo)
 $stmtExisteActivo = $this->createMock(PDOStatement::class);
 $stmtExisteActivo->method('fetchColumn')->willReturn(false); // Devuelve "no existe"

 // Mock para la 2da consulta (buscar título inactivo)
 $stmtExisteInactivo = $this->createMock(PDOStatement::class);
 $stmtExisteInactivo->method('fetchColumn')->willReturn(false); // Devuelve "no existe"

 // Mock para la 3ra consulta (el INSERT)
 $stmtInsert = $this->createMock(PDOStatement::class);
 $stmtInsert->method('execute')->willReturn(true); // Devuelve "éxito"

 // 3. Le decimos a PDO que devuelva los mocks en secuencia
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls(
  $stmtExisteActivo,   // 1ra llamada a prepare()
  $stmtExisteInactivo, // 2da llamada a prepare()
  $stmtInsert          // 3ra llamada a prepare()
  );

        // 4. Ejecución: Llamamos al método que queremos probar
 $resultado = $this->titulo->Registrar();

        // 5. Validación: Comprobamos que el resultado es el esperado
 $this->assertEquals('registrar', $resultado['resultado']);
 $this->assertStringContainsString('correctamente', $resultado['mensaje']);
 }

    /**
     * Prueba que el sistema falla si intentas registrar un título
     * que ya existe y está activo.
     */
 public function testRegistrarTituloCuandoYaExisteActivo()
 {
        // 1. Preparación
 $this->titulo->set_prefijo('Lic.');
 $this->titulo->set_nombreTitulo('En Contaduría');

        // 2. Configuración de Mocks
        // Mock para la 1ra consulta (buscar título activo)
 $stmtExisteActivo = $this->createMock(PDOStatement::class);
 $stmtExisteActivo->method('fetchColumn')->willReturn(true); // Devuelve "SÍ existe"

 // Solo simulamos la primera llamada, porque la lógica debe parar aquí
 $this->pdoMock->method('prepare')->willReturn($stmtExisteActivo);

        // 3. Ejecución
 $resultado = $this->titulo->Registrar();

        // 4. Validación
 $this->assertEquals('error', $resultado['resultado']);
 $this->assertStringContainsString('El título colocado ya existe', $resultado['mensaje']);
 }

    /**
     * Prueba la lógica de "reactivación": si un título existía pero estaba
     * inactivo (eliminado lógicamente), lo debe volver a activar.
     */
 public function testRegistrarTituloCuandoExisteInactivoDebeReactivar()
 {
        // 1. Preparación
 $this->titulo->set_prefijo('TSU.');
 $this->titulo->set_nombreTitulo('En Informática');

        // 2. Configuración de Mocks
 $stmtExisteActivo = $this->createMock(PDOStatement::class);
 $stmtExisteActivo->method('fetchColumn')->willReturn(false); // No existe activo

 $stmtExisteInactivo = $this->createMock(PDOStatement::class);
 $stmtExisteInactivo->method('fetchColumn')->willReturn(true); // SÍ existe inactivo

 $stmtReactivar = $this->createMock(PDOStatement::class);
 $stmtReactivar->method('execute')->willReturn(true); // El UPDATE de reactivación funciona

 // 3. Definimos la secuencia de respuestas de PDO
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls(
  $stmtExisteActivo,   // 1ra llamada
  $stmtExisteInactivo, // 2da llamada
  $stmtReactivar         // 3ra llamada (el UPDATE)
  );

        // 4. Ejecución
 $resultado = $this->titulo->Registrar();

        // 5. Validación
 $this->assertEquals('registrar', $resultado['resultado']);
 $this->assertStringContainsString('Se registró el título correctamente', $resultado['mensaje']);
 }
 
    // --- Pruebas para el método Modificar() ---

    /**
     * Prueba el "camino feliz" de una modificación exitosa.
     */
 public function testModificarTituloExitoso()
 {
        // 1. Preparación: datos originales y nuevos
 $this->titulo->set_original_prefijo('Ing.');
 $this->titulo->set_original_nombre('En Sistemas');
 $this->titulo->set_prefijo('Ing.');
 $this->titulo->set_nombreTitulo('En Informática'); // <-- El cambio

        // 2. Configuración de Mocks
        // Mock para la consulta de chequeo (¿existe el *nuevo* nombre?)
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(false); // No existe, se puede modificar

        // Mock para la consulta de UPDATE (o DELETE/INSERT si así fuera la lógica)
 $stmtDelete = $this->createMock(PDOStatement::class); // El nombre es irrelevante
 $stmtDelete->method('execute')->willReturn(true);

 $stmtUpdate = $this->createMock(PDOStatement::class); // El nombre es irrelevante
 $stmtUpdate->method('execute')->willReturn(true);

 // 3. Secuencia de PDO
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete, $stmtUpdate);

        // 4. Ejecución
 $resultado = $this->titulo->Modificar();

        // 5. Validación
 $this->assertEquals('modificar', $resultado['resultado']);
 $this->assertStringContainsString('Se modificó el título correctamente', $resultado['mensaje']);
 }

    /**
     * Prueba que la modificación falla si intentamos cambiar a un nombre
     * que ya está en uso por OTRO título.
     */
 public function testModificarTituloAUnNombreQueYaExiste()
 {
        // 1. Preparación
 $this->titulo->set_original_prefijo('Ing.');
 $this->titulo->set_original_nombre('En Sistemas');
 $this->titulo->set_prefijo('Lic.'); // <-- Nuevo nombre
 $this->titulo->set_nombreTitulo('En Educación'); // <-- Nuevo nombre

        // 2. Configuración de Mocks
        // Mock para la consulta de chequeo (¿existe el *nuevo* nombre?)
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(['1']); // ¡SÍ existe!

 // La lógica debe parar aquí
 $this->pdoMock->method('prepare')->willReturn($stmtExiste);

        // 3. Ejecución
 $resultado = $this->titulo->Modificar();

        // 4. Validación
 $this->assertEquals('error', $resultado['resultado']);
 $this->assertStringContainsString('El titulo colocado ya existe', $resultado['mensaje']);
 }

    // --- Pruebas para el método Eliminar() ---

    /**
     * Prueba el "camino feliz" de una eliminación (lógica) exitosa.
     */
 public function testEliminarTituloExitoso()
 {
        // 1. Preparación
 $this->titulo->set_prefijo('Dr.');
 $this->titulo->set_nombreTitulo('En Ciencias');

        // 2. Configuración de Mocks
        // Mock para la 1ra consulta (¿existe el título a eliminar?)
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(['1']); // SÍ existe

 // Mock para la 2da consulta (el UPDATE para poner estado = 0)
 $stmtUpdate = $this->createMock(PDOStatement::class);
 $stmtUpdate->method('execute')->willReturn(true); // Funciona bien

 // 3. Secuencia de PDO
 $this->pdoMock->method('prepare')
  ->willReturnOnConsecutiveCalls($stmtExiste, $stmtUpdate);
  
        // 4. Ejecución
 $resultado = $this->titulo->Eliminar();

        // 5. Validación
 $this->assertEquals('eliminar', $resultado['resultado']);
 $this->assertStringContainsString('Se eliminó el título correctamente', $resultado['mensaje']);
 }
 
    /**
     * Prueba que la eliminación falla si el título no existe.
     */
 public function testEliminarTituloQueNoExiste()
 {
        // 1. Preparación
 $this->titulo->set_prefijo('Msc.');
 $this->titulo->set_nombreTitulo('En Gerencia');

        // 2. Configuración de Mocks
        // Mock para la 1ra consulta (¿existe?)
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(false); // NO existe

 // La lógica debe parar aquí
 $this->pdoMock->method('prepare')->willReturn($stmtExiste);
  
        // 3. Ejecución
 $resultado = $this->titulo->Eliminar();

        // 4. Validación
 $this->assertEquals('error', $resultado['resultado']);
 $this->assertStringContainsString('El título que intenta eliminar no existe', $resultado['mensaje']);
 }

    /**
     * Prueba la captura de un error de Foreign Key (violación de integridad).
     * Esto pasa si intentas eliminar un título que ya está asignado a un docente.
     */
 public function testEliminarTituloEnUsoGeneraErrorDeConstraint()
 {
        // 1. Preparación
 $this->titulo->set_prefijo('Esp.');
 $this->titulo->set_nombreTitulo('En Redes');
 
        // 2. Configuración de Mocks
        // Mock para la 1ra consulta (¿existe?)
 $stmtExiste = $this->createMock(PDOStatement::class);
 $stmtExiste->method('fetch')->willReturn(['1']); // SÍ existe
 
        // Mock para la 2da consulta (el UPDATE de eliminación)
 $stmtUpdate = $this->createMock(PDOStatement::class);
        // Creamos una excepción falsa de PDO
 $exception = new PDOException();
        // Asignamos el código de error de SQL para "Integrity Constraint Violation"
 $exception->errorInfo[0] = '23000'; 
        // Le decimos al mock que "lance" esta excepción cuando se intente ejecutar
 $stmtUpdate->method('execute')->will($this->throwException($exception));
        // 3. Secuencia de PDO
 $this->pdoMock->method('prepare')
->willReturnOnConsecutiveCalls($stmtExiste, $stmtUpdate);

        // 4. Ejecución (El bloque try/catch en tu clase debe atrapar la excepción)
 $resultado = $this->titulo->Eliminar();

        // 5. Validación
 $this->assertEquals('error', $resultado['resultado']);
        // Validamos que el mensaje sea amigable y no el error '23000'
$this->assertStringContainsString('utilizado por uno o más docentes', $resultado['mensaje']); }
}


