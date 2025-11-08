<?php

namespace App\Model;

class ValidacionSelect
{
    private static $mensajeError = 'Los valores del select fueron modificados, por lo tanto no se puede seguir con la operacion';

    private static $enums = [
        'tipo_anio' => ['regular', 'intensivo'],
        'turno_nombre' => ['Mañana', 'Tarde', 'Noche'],
        'titulo_prefijo' => ['Ing.', 'Msc.', 'Dr.', 'TSU.', 'Lic.', 'Esp.', 'Prof.'],
        'edificio' => ['Hilandera', 'Giraluna', 'Rio 7 Estrellas', 'Orinoco'],
        'tipo_espacio' => ['Aula', 'Laboratorio'],
        'trayecto' => ['0', '1', '2', '3', '4'],
        'periodo' => ['Anual', 'Fase I', 'Fase II'],
        'prefijo_cedula' => ['V', 'E'],
        'dedicacion' => ['Exclusiva', 'Tiempo Completo', 'Medio Tiempo', 'Tiempo Convencional'],
        'condicion' => ['Ordinario', 'Contratado por Credenciales', 'Suplente'],
        'tipo_prosecusion' => ['automatico', 'manual'],
    ];


    public static function validarEnum(string $campo, $valor, bool $permitirVacio = false): bool
    {
        if ($permitirVacio && ($valor === null || $valor === '')) {
            return true;
        }

        if (!isset(self::$enums[$campo])) {
            throw new \Exception(self::$mensajeError);
        }

        if (!in_array($valor, self::$enums[$campo], true)) {
            throw new \Exception(self::$mensajeError);
        }

        return true;
    }


    public static function obtenerValoresPermitidos(string $campo): array
    {
        if (!isset(self::$enums[$campo])) {
            return [];
        }
        return self::$enums[$campo];
    }


    public static function validarMultiple(array $validaciones, array $permitirVacios = []): bool
    {
        foreach ($validaciones as $campo => $valor) {
            $permitirVacio = in_array($campo, $permitirVacios);
            self::validarEnum($campo, $valor, $permitirVacio);
        }
        return true;
    }


    public static function validarExisteEnBD(\PDO $pdo, string $tabla, string $columna, $valor, string $columnaEstado = null): bool
    {
        if ($valor === null || $valor === '') {
            throw new \Exception(self::$mensajeError);
        }

        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE $columna = :valor";

        if ($columnaEstado !== null) {
            $sql .= " AND $columnaEstado = 1";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':valor', $valor);
        $stmt->execute();
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($resultado['total'] == 0) {
            throw new \Exception(self::$mensajeError);
        }

        return true;
    }


    public static function validarMultipleEnBD(\PDO $pdo, array $validaciones): bool
    {
        foreach ($validaciones as $config) {
            self::validarExisteEnBD(
                $pdo,
                $config['tabla'],
                $config['columna'],
                $config['valor'],
                $config['columna_estado'] ?? null
            );
        }
        return true;
    }
}
