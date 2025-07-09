<?php
// model/reportes/rmalla.php

require_once('model/dbconnection.php');

class MallaReport extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene una lista de todas las mallas curriculares activas.
     */
    public function getMallasActivas()
    {
        $co = $this->con();
        try {
            $sql = "SELECT mal_codigo, mal_nombre, mal_cohorte FROM tbl_malla WHERE mal_estado = 1 ORDER BY mal_nombre";
            $stmt = $co->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en MallaReport::getMallasActivas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los detalles y unidades curriculares de una malla específica.
     */
    public function getMallaConUnidades($mallaCodigo)
    {
        $co = $this->con();
        try {
            // Consulta ajustada para traer las horas como las necesita el controlador
            $sql = "SELECT 
                        m.mal_codigo, m.mal_nombre, m.mal_cohorte,
                        u.uc_codigo, u.uc_nombre, u.uc_trayecto,
                        um.mal_hora_independiente AS hti,
                        um.mal_hora_asistida AS hta,
                        (um.mal_hora_independiente + um.mal_hora_asistida) AS hte,
                        um.mal_hora_academica,
                        u.uc_creditos,
                        e.eje_nombre,
                        u.uc_periodo
                    FROM 
                        tbl_malla m
                    LEFT JOIN 
                        uc_malla um ON m.mal_codigo = um.mal_codigo
                    LEFT JOIN 
                        tbl_uc u ON um.uc_codigo = u.uc_codigo
                    LEFT JOIN
                        tbl_eje e ON u.eje_nombre = e.eje_nombre
                    WHERE 
                        m.mal_estado = 1 AND u.uc_estado = 1 AND m.mal_codigo = :mal_codigo
                    ORDER BY 
                        m.mal_nombre, u.uc_trayecto, u.uc_nombre";
            
            $stmt = $co->prepare($sql);
            // --- ¡AQUÍ ESTABA EL ERROR! ---
            // Se cambió PDO::PARAM_INT por PDO::PARAM_STR para que coincida con el tipo de dato.
            $stmt->bindParam(':mal_codigo', $mallaCodigo, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // La lógica para agrupar los datos está bien, se mantiene.
            $mallasAgrupadas = [];
            if ($results) {
                $mallaKey = $results[0]['mal_codigo'];
                $mallasAgrupadas[$mallaKey] = [
                    'mal_codigo' => $results[0]['mal_codigo'],
                    'mal_nombre' => $results[0]['mal_nombre'],
                    'mal_cohorte' => $results[0]['mal_cohorte'],
                    'unidades_por_trayecto' => []
                ];

                foreach ($results as $row) {
                    $trayectoKey = 'Trayecto ' . $row['uc_trayecto'];
                    if($row['uc_trayecto'] == 0) {
                        $trayectoKey = 'Trayecto Inicial';
                    }

                    if (!isset($mallasAgrupadas[$mallaKey]['unidades_por_trayecto'][$trayectoKey])) {
                        $mallasAgrupadas[$mallaKey]['unidades_por_trayecto'][$trayectoKey] = [];
                    }
                    $mallasAgrupadas[$mallaKey]['unidades_por_trayecto'][$trayectoKey][] = $row;
                }
            }
            return $mallasAgrupadas;

        } catch (PDOException $e) {
            error_log("Error en MallaReport::getMallaConUnidades: " . $e->getMessage());
            return false;
        }
    }
}
?>