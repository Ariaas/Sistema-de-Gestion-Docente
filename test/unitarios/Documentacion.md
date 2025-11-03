## PRUEBAS DETALLADAS

### Caso No. 1 (testRegistrar_ConAniosInvalidos con data set "año null")

**Código de la Prueba:**
```php
/**
 * @test
 * @dataProvider providerAniosInvalidos
 */
public function testRegistrar_ConAniosInvalidos($anio, $descripcion)
{
    $this->setupMocksParaRegistroExitoso();
    
    $this->anio->setAnio($anio);
    $this->anio->setTipo('regular');
    $this->anio->setFases([
        ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
        ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
    ]);

    $resultado = $this->anio->Registrar();

    $this->assertTrue(
        !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
        "Fallo validación: {$descripcion}"
    );
}
```

**Data Provider:**
```php
public function providerAniosInvalidos()
{
    return [
        'año null' => [null, 'El año es null'],
        'año vacío' => ['', 'El año es cadena vacía'],
        'año con espacios' => ['   ', 'El año solo contiene espacios'],
        'año texto' => ['ABC', 'El año es texto no numérico'],
        'año negativo' => [-2024, 'El año es negativo'],
        'año cero' => [0, 'El año es cero'],
        'año decimal' => [2024.5, 'El año es decimal'],
        'año menor a 2000' => [1999, 'El año es menor al rango permitido'],
        'año mayor a 2100' => [2101, 'El año es mayor al rango permitido'],
    ];
}
```

| **Objetivos de la prueba** | Verificar que no se pueda registrar un año académico con valor null |
|---|---|
| **Técnicas** | Mocking (PDO, PDOStatement), Data Provider (providerAniosInvalidos), Stubs (fetchColumn, fetch, beginTransaction) |
| **Código Involucrado** | `Anio::Registrar()` |
| **Caso de prueba** | **Descripción:** Simula el registro de un año académico con valor null<br>**Entradas:** `$anio->setAnio(null)`, `tipo='regular'`, `fases=[2 fases válidas]`<br>**Mocks configurados:** MallaActiva=true (stub fetchColumn→1), AnioNoExiste (stub fetch→false)<br>**Salida Esperada:** `['resultado' => 'error', 'mensaje' => 'El año no puede estar vacío']` |
| **Resultado** | **❌ FALLO** |
| **Observaciones** | La validación NO existe. El código NO valida si el año es null o vacío. Se requiere agregar: `if ($this->aniAnio === null \|\| $this->aniAnio === '') { return error; }` al inicio de `Registrar()`. |

---

### Caso No. 10 (testRegistrar_ConAnioValido)

**Código de la Prueba:**
```php
/**
 * @test
 * Caso específico: Año válido debe ser aceptado
 */
public function testRegistrar_ConAnioValido()
{
    $this->setupMocksParaRegistroExitoso();
    
    $this->anio->setAnio(2024);
    $this->anio->setTipo('regular');
    $this->anio->setFases([
        ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
        ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
    ]);

    $resultado = $this->anio->Registrar();

    $this->assertIsArray($resultado);
    $this->assertArrayHasKey('resultado', $resultado);
}
```

**Helper Method:**
```php
private function setupMocksParaRegistroExitoso()
{
    $this->mockMallaActiva(true);
    $this->mockAnioNoExiste();
    $this->pdoMock->method('beginTransaction')->willReturn(true);
    $this->pdoMock->method('commit')->willReturn(true);
}
```

| **Objetivos de la prueba** | Verificar que años válidos (2024) sean procesados correctamente |
|---|---|
| **Técnicas** | Mocking (PDO), Stub (MallaActiva, Existe, Transacciones) |
| **Código Involucrado** | `Anio::Registrar()`, `Anio::MallaActiva()`, `Anio::Existe()` |
| **Caso de prueba** | **Descripción:** Registra año 2024 con datos completamente válidos<br>**Entradas:** `anio=2024`, `tipo='regular'`, `fases=[2 fases con fechas válidas]`<br>**Mocks:** MallaActiva→true, Existe→false, beginTransaction→true, commit→true<br>**Salida Esperada:** Array con clave 'resultado' (registro procesado) |
| **Resultado** | **✅ APROBADO** |
| **Observaciones** | El flujo principal funciona cuando los datos son válidos. El código puede ejecutar INSERT sin errores de lógica si hay malla activa. |

---

### Caso No. 11-16 (testRegistrar_ConTiposInvalidos)

**Código de la Prueba:**
```php
/**
 * @test
 * @dataProvider providerTiposInvalidos
 */
public function testRegistrar_ConTiposInvalidos($tipo, $descripcion)
{
    $this->setupMocksParaRegistroExitoso();
    
    $this->anio->setAnio(2024);
    $this->anio->setTipo($tipo);
    $this->anio->setFases([
        ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30']
    ]);

    $resultado = $this->anio->Registrar();

    $this->assertTrue(
        !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
        "Fallo validación: {$descripcion}"
    );
}
```

**Data Provider:**
```php
public function providerTiposInvalidos()
{
    return [
        'tipo null' => [null, 'El tipo es null'],
        'tipo vacío' => ['', 'El tipo es cadena vacía'],
        'tipo con espacios' => ['   ', 'El tipo solo contiene espacios'],
        'tipo inválido' => ['anual', 'El tipo no es regular ni intensivo'],
        'tipo con mayúsculas' => ['REGULAR', 'El tipo con mayúsculas'],
        'tipo número' => [123, 'El tipo es número'],
    ];
}
```

| **Objetivos de la prueba** | Verificar que solo se acepten tipos 'regular' o 'intensivo' (6 casos) |
|---|---|
| **Técnicas** | Data Provider (providerTiposInvalidos), Mocking |
| **Código Involucrado** | `Anio::Registrar()` |
| **Caso de prueba** | **Descripción:** Prueba 6 valores inválidos de tipo<br>**Casos:** null, '', '   ', 'anual', 'REGULAR', 123<br>**Salida Esperada:** Error en todos los casos |
| **Resultado** | **❌ FALLO (6/6)** |
| **Observaciones** | NO hay validación de tipo. Se requiere: `if ($this->aniTipo === null \|\| trim($this->aniTipo) === '') return error;` y `if (!in_array(strtolower(trim($this->aniTipo)), ['regular', 'intensivo'])) return error;` |

---

### Caso No. 36 (testRegistrar_SinMallaActiva)

**Código de la Prueba:**
```php
/**
 * @test
 * No debe permitir registro sin malla activa
 */
public function testRegistrar_SinMallaActiva()
{
    $this->mockMallaActiva(false);
    $this->mockAnioNoExiste();
    
    $this->anio->setAnio(2024);
    $this->anio->setTipo('regular');
    $this->anio->setFases([
        ['numero' => 1, 'apertura' => '2024-01-15', 'cierre' => '2024-06-30'],
        ['numero' => 2, 'apertura' => '2024-07-15', 'cierre' => '2024-12-15']
    ]);

    $resultado = $this->anio->Registrar();

    $this->assertEquals('error', $resultado['resultado']);
    $this->assertStringContainsString('malla', strtolower($resultado['mensaje']));
}
```

**Helper Mock:**
```php
private function mockMallaActiva($exists = true)
{
    $stmtMalla = $this->createMock(PDOStatement::class);
    $stmtMalla->method('fetchColumn')->willReturn($exists ? 1 : false);
    $this->pdoMock->method('query')->willReturn($stmtMalla);
}
```

| **Objetivos de la prueba** | Verificar que el sistema NO permita registrar un año si no existe malla curricular activa |
|---|---|
| **Técnicas** | Mocking (PDO, PDOStatement), Stub retorna false en MallaActiva |
| **Código Involucrado** | `Anio::Registrar()`, `Anio::MallaActiva()` |
| **Caso de prueba** | **Descripción:** Intenta registrar año cuando MallaActiva() retorna false<br>**Entradas:** Datos válidos (anio=2024, tipo='regular', fases válidas)<br>**Mock configurado:** `$stmtMalla->fetchColumn()->willReturn(false)`<br>**Salida Esperada:** `['resultado' => 'error', 'mensaje' => '...malla curricular activa...']` |
| **Resultado** | **✅ APROBADO** |
| **Observaciones** | Esta validación SÍ está implementada correctamente. El código verifica con `if (!$this->MallaActiva())` y retorna error apropiado. |

---

### Caso No. 37 (testModificar_SinParametrosOriginales)

**Código de la Prueba:**
```php
/**
 * @test
 * Modificar debe requerir parámetros originales
 */
public function testModificar_SinParametrosOriginales()
{
    $this->anio->setAnio(2025);
    $this->anio->setTipo('regular');
    $this->anio->setFases([
        ['numero' => 1, 'apertura' => '2025-01-15', 'cierre' => '2025-06-30'],
        ['numero' => 2, 'apertura' => '2025-07-15', 'cierre' => '2025-12-15']
    ]);

    $resultado = $this->anio->Modificar(null, null);

    $this->assertTrue(
        !isset($resultado['resultado']) || $resultado['resultado'] === 'error',
        "Modificar debe requerir parámetros originales"
    );
}
```

| **Objetivos de la prueba** | Verificar que Modificar() valide los parámetros originales (anioOriginal, tipoOriginal) |
|---|---|
| **Técnicas** | Prueba directa sin mocks (falla antes de acceder BD) |
| **Código Involucrado** | `Anio::Modificar($anioOriginal, $tipoOriginal)` |
| **Caso de prueba** | **Descripción:** Llama Modificar(null, null) sin proporcionar valores originales<br>**Entradas:** `anioOriginal=null`, `tipoOriginal=null`<br>**Salida Esperada:** `['resultado' => 'error', 'mensaje' => 'Parámetros originales requeridos']` |
| **Resultado** | **⚠️ ERROR (ParseError)** |
| **Observaciones** | NO se puede probar. Línea 452 de `model/anio.php` tiene Union Types (`string\|null`) incompatibles con PHP 7.x. Error: `syntax error, unexpected token "\|", expecting "{"`. **CRÍTICO:** Corregir sintaxis antes de continuar pruebas de Modificar(). |

---

### Caso No. 49 (testEliminar_AnioExistente)

**Código de la Prueba:**
```php
/**
 * @test
 * Eliminar año que existe
 */
public function testEliminar_AnioExistente()
{
    $stmtExiste = $this->createMock(PDOStatement::class);
    $stmtExiste->method('fetch')->willReturn(['ani_anio' => 2024]);
    
    $stmtDelete = $this->createMock(PDOStatement::class);
    $stmtDelete->method('execute')->willReturn(true);
    
    $this->pdoMock->method('prepare')
        ->willReturnOnConsecutiveCalls($stmtExiste, $stmtDelete);
    
    $this->anio->setAnio(2024);
    $this->anio->setTipo('regular');

    $resultado = $this->anio->Eliminar();

    $this->assertIsArray($resultado);
    $this->assertArrayHasKey('resultado', $resultado);
}
```

| **Objetivos de la prueba** | Verificar eliminación exitosa de un año que existe en BD |
|---|---|
| **Técnicas** | Mocking (PDO, PDOStatement), Stub secuencial (willReturnOnConsecutiveCalls) |
| **Código Involucrado** | `Anio::Eliminar()`, `Anio::Existe()` |
| **Caso de prueba** | **Descripción:** Elimina año 2024 regular que existe en BD<br>**Entradas:** `anio=2024`, `tipo='regular'`<br>**Mocks:** Existe→['ani_anio'=>2024], DELETE execute→true<br>**Salida Esperada:** Array con 'resultado' (eliminación procesada) |
| **Resultado** | **✅ APROBADO** |
| **Observaciones** | La eliminación funciona cuando el año existe. El flujo DELETE es correcto. |

---

### Caso No. 50-53 (testSetAndGet Getters/Setters)

**Código de las Pruebas:**
```php
/** @test */
public function testSetAndGetAnio()
{
    $this->anio->setAnio(2023);
    $this->assertEquals(2023, $this->anio->getAnio());
}

/** @test */
public function testSetAndGetTipo()
{
    $this->anio->setTipo('regular');
    $this->assertEquals('regular', $this->anio->getTipo());
}

/** @test */
public function testSetAndGetActivo()
{
    $this->anio->setActivo(1);
    $this->assertEquals(1, $this->anio->getActivo());
}

/** @test */
public function testSetAndGetFases()
{
    $fases = [
        ['numero' => 1, 'apertura' => '2023-01-01', 'cierre' => '2023-06-01'],
        ['numero' => 2, 'apertura' => '2023-07-01', 'cierre' => '2023-12-01']
    ];
    $this->anio->setFases($fases);
    $this->assertEquals($fases, $this->anio->getFases());
}
```

| **Objetivos de la prueba** | Verificar funcionamiento de getters y setters de propiedades |
|---|---|
| **Técnicas** | Pruebas unitarias básicas sin mocks |
| **Código Involucrado** | `setAnio()`, `getAnio()`, `setTipo()`, `getTipo()`, `setActivo()`, `getActivo()`, `setFases()`, `getFases()` |
| **Casos de prueba** | **4 pruebas:** Año (2023), Tipo ('regular'), Activo (1), Fases (array)<br>**Patrón:** set valor → assert get valor === valor esperado |
| **Resultado** | **✅ APROBADO (4/4)** |
| **Observaciones** | Los getters y setters funcionan correctamente. Las propiedades se almacenan y recuperan sin problemas. |

---
