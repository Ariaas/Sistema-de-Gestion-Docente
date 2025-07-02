<?php

require_once('model/dbconnection.php');

class MallaReport extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene una lista de todas las mallas curriculares activas.
     * @return array Lista de mallas con id, nombre y cohorte.
     */
    public function getMallasActivas()
    {
        $co = $this->con();
        try {
            $sql = "SELECT mal_id, mal_nombre, mal_cohorte FROM tbl_malla WHERE mal_estado = 1 ORDER BY mal_nombre";
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
     * @param int $mallaId El ID de la malla a buscar.
     * @return array Datos agrupados de la malla y sus unidades.
     */
    public function getMallaConUnidades($mallaId)
    {
        $co = $this->con();
        try {
            $sql = "SELECT 
                        m.mal_id, m.mal_codigo, m.mal_nombre, m.mal_cohorte,
                        u.uc_codigo, u.uc_nombre, u.uc_trayecto,
                        um.mal_hora_independiente AS hti,
                        um.mal_hora_asistida AS hta,
                        (um.mal_hora_independiente + um.mal_hora_asistida) AS hte,
                        u.uc_creditos,
                        e.eje_nombre,
                        u.uc_periodo
                    FROM 
                        tbl_malla m
                    LEFT JOIN 
                        uc_malla um ON m.mal_id = um.mal_id
                    LEFT JOIN 
                        tbl_uc u ON um.uc_id = u.uc_id
                    LEFT JOIN
                        tbl_eje e ON u.eje_id = e.eje_id
                    WHERE 
                        m.mal_estado = 1 AND u.uc_estado = 1 AND m.mal_id = :malla_id
                    ORDER BY 
                        m.mal_nombre, u.uc_trayecto, u.uc_nombre";
            
            $stmt = $co->prepare($sql);
            $stmt->bindParam(':malla_id', $mallaId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $mallasAgrupadas = [];
            foreach ($results as $row) {
                $mallaKey = $row['mal_id'];
                if (!isset($mallasAgrupadas[$mallaKey])) {
                    $mallasAgrupadas[$mallaKey] = [
                        'mal_codigo' => $row['mal_codigo'],
                        'mal_nombre' => $row['mal_nombre'],
                        'mal_cohorte' => $row['mal_cohorte'],
                        'unidades_por_trayecto' => []
                    ];
                }

                $trayectoKey = 'Trayecto ' . $row['uc_trayecto'];
                if($row['uc_trayecto'] == 0) {
                    $trayectoKey = 'Trayecto Inicial';
                }

                if (!isset($mallasAgrupadas[$mallaKey]['unidades_por_trayecto'][$trayectoKey])) {
                    $mallasAgrupadas[$mallaKey]['unidades_por_trayecto'][$trayectoKey] = [];
                }

                $mallasAgrupadas[$mallaKey]['unidades_por_trayecto'][$trayectoKey][] = $row;
            }
            return $mallasAgrupadas;

        } catch (PDOException $e) {
            error_log("Error en MallaReport::getMallaConUnidades: " . $e->getMessage());
            return false;
        }
    }
}
?>