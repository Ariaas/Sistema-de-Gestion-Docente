<?php

require_once('model/dbconnection.php');

class ProsecucionReport extends Connection
{

    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerAniosAcademicos()
    {
        $co = $this->con();
        try {
            $stmt = $co->query("SELECT DISTINCT tra_anio FROM tbl_trayecto ORDER BY tra_anio DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerAniosAcademicos: " . $e->getMessage());
            return [];
        } finally {
            $co = null;
        }
    }

    private function obtenerTrayectosOrdenados($anioAcademico, $co)
    {
        $sql = "SELECT tra_id, tra_numero, tra_anio 
                FROM tbl_trayecto 
                WHERE tra_anio = :anio 
                ORDER BY FIELD(tra_numero, 'INICIAL', 'I', 'II', 'III', 'IV'), tra_numero ASC";
        $stmt = $co->prepare($sql);
        $stmt->bindParam(':anio', $anioAcademico, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerSeccionesConGrupoPorTrayecto($tra_id, $co, $activas = true)
    {
        $estadoCondicion = $activas ? "s.sec_estado = 1" : "1=1"; 
        $sql = "
            SELECT s.sec_id, s.sec_codigo, s.sec_cantidad, s.sec_estado, s.tra_id, t.tra_numero AS trayecto_numero_seccion, g.gro_id
            FROM tbl_seccion s
            JOIN tbl_trayecto t ON s.tra_id = t.tra_id
            LEFT JOIN seccion_grupo sg ON s.sec_id = sg.sec_id
            LEFT JOIN tbl_grupo g ON sg.gro_id = g.gro_id AND g.grupo_estado = 1
            WHERE s.tra_id = :tra_id AND $estadoCondicion 
            ORDER BY s.sec_codigo ASC
        ";
        $stmt = $co->prepare($sql);
        $stmt->bindParam(':tra_id', $tra_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function agruparSeccionesVisualmente($seccionesCrudas)
    {
        $gruposDetectados = [];
        $seccionesIndividuales = [];
        $idsProcesados = [];

        foreach ($seccionesCrudas as $sec) {
            if (in_array($sec['sec_id'], $idsProcesados)) continue;

            if ($sec['gro_id']) {
                if (!isset($gruposDetectados[$sec['gro_id']])) {
                    $gruposDetectados[$sec['gro_id']] = [
                        'codigos' => [],
                        'cantidad_total' => 0,
                        'es_union' => true,
                        'ids_componentes' => [],
                        'trayecto_original_numero' => $sec['trayecto_numero_seccion'] ?? null,
                        'sec_ids_concatenados' => ''
                    ];
                }
                
                foreach ($seccionesCrudas as $sInner) {
                    if ($sInner['gro_id'] == $sec['gro_id'] && !in_array($sInner['sec_id'], $gruposDetectados[$sec['gro_id']]['ids_componentes'])) {
                        $gruposDetectados[$sec['gro_id']]['codigos'][] = $sInner['sec_codigo'];
                        $gruposDetectados[$sec['gro_id']]['cantidad_total'] += (int)$sInner['sec_cantidad'];
                        $gruposDetectados[$sec['gro_id']]['ids_componentes'][] = $sInner['sec_id'];
                        $idsProcesados[] = $sInner['sec_id'];
                    }
                }
                sort($gruposDetectados[$sec['gro_id']]['codigos']);
                $gruposDetectados[$sec['gro_id']]['sec_ids_concatenados'] = implode('-', $gruposDetectados[$sec['gro_id']]['ids_componentes']);
            } else {
                $seccionData = [
                    'codigos' => [$sec['sec_codigo']],
                    'cantidad_total' => (int)$sec['sec_cantidad'],
                    'es_union' => false,
                    'ids_componentes' => [$sec['sec_id']],
                    'trayecto_original_numero' => $sec['trayecto_numero_seccion'] ?? null,
                    'sec_ids_concatenados' => (string)$sec['sec_id']
                ];
                $seccionesIndividuales[] = $seccionData;
                $idsProcesados[] = $sec['sec_id'];
            }
        }

        $resultadoFinal = array_values($gruposDetectados);
        foreach ($seccionesIndividuales as $ind) {
            $resultadoFinal[] = $ind;
        }

        usort($resultadoFinal, function ($a, $b) {
            return strcmp($a['codigos'][0], $b['codigos'][0]);
        });

        return $resultadoFinal;
    }


    public function obtenerDatosProsecucion($anioAcademico)
    {
        $co = $this->con();
        $datosFormateados = [];
        $trayectosDelAnio = $this->obtenerTrayectosOrdenados($anioAcademico, $co);
        $mapaNombresTrayectos = array_column($trayectosDelAnio, 'tra_numero', 'tra_id');
        $ordenNombresTrayectos = array_column($trayectosDelAnio, 'tra_numero');


        foreach ($trayectosDelAnio as $trayectoActualInfo) {
            $idTrayectoActual = $trayectoActualInfo['tra_id'];
            $nombreTrayectoActual = $trayectoActualInfo['tra_numero'];

            $seccionesActivasDelTrayecto = $this->obtenerSeccionesConGrupoPorTrayecto($idTrayectoActual, $co, true);
            $gruposVisualesActuales = $this->agruparSeccionesVisualmente($seccionesActivasDelTrayecto);

            $filasParaEsteTrayecto = [];

            foreach ($gruposVisualesActuales as $grupoActual) {
                $fila = [
                    'trayecto_actual_nombre' => $nombreTrayectoActual,
                    'secciones_actuales_codigos' => $grupoActual['codigos'],
                    'secciones_actuales_cantidad' => $grupoActual['cantidad_total'],
                    'promociones_info' => []
                ];

                $idsSeccionesOrigenParaPromocion = $grupoActual['ids_componentes'];
                $seccionesPromovidasDestino = [];

                if (!empty($idsSeccionesOrigenParaPromocion)) {
                    $placeholders = implode(',', array_fill(0, count($idsSeccionesOrigenParaPromocion), '?'));
                    $sqlPromocion = "
                        SELECT p.sec_id_promocion, s.sec_id, s.sec_codigo, s.sec_cantidad, s.tra_id, t.tra_numero AS trayecto_numero_seccion, g.gro_id
                        FROM tbl_promocion p
                        JOIN tbl_seccion s ON p.sec_id_promocion = s.sec_id
                        JOIN tbl_trayecto t ON s.tra_id = t.tra_id
                        LEFT JOIN seccion_grupo sg ON s.sec_id = sg.sec_id
                        LEFT JOIN tbl_grupo g ON sg.gro_id = g.gro_id AND g.grupo_estado = 1
                        WHERE p.sec_id_origen IN ($placeholders) AND s.sec_estado = 1
                        ORDER BY t.tra_numero ASC, s.sec_codigo ASC
                    ";
                    $stmtPromocion = $co->prepare($sqlPromocion);
                    $stmtPromocion->execute($idsSeccionesOrigenParaPromocion);
                    $seccionesPromovidasCrudas = $stmtPromocion->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($seccionesPromovidasCrudas)) {
                        $agrupadasPorTrayectoDestino = [];
                        foreach ($seccionesPromovidasCrudas as $spc) {
                            $agrupadasPorTrayectoDestino[$spc['trayecto_numero_seccion']][] = $spc;
                        }

                        foreach ($agrupadasPorTrayectoDestino as $traNumDest => $seccionesEnTraDest) {
                            $gruposVisualesDestino = $this->agruparSeccionesVisualmente($seccionesEnTraDest);
                            foreach ($gruposVisualesDestino as $gvd) {
                                $fila['promociones_info'][] = [
                                    'trayecto_destino_nombre' => $traNumDest,
                                    'secciones_destino_codigos' => $gvd['codigos'],
                                    'secciones_destino_cantidad' => $gvd['cantidad_total'],
                                ];
                            }
                        }
                    }
                }
                if (empty($fila['promociones_info'])) {
                    $indiceActual = array_search($nombreTrayectoActual, $ordenNombresTrayectos);
                    $nombreSiguienteTrayecto = ($indiceActual !== false && isset($ordenNombresTrayectos[$indiceActual + 1])) ? $ordenNombresTrayectos[$indiceActual + 1] : 'FIN';
                    $fila['promociones_info'][] = [
                        'trayecto_destino_nombre' => $nombreSiguienteTrayecto,
                        'secciones_destino_codigos' => ['-'],
                        'secciones_destino_cantidad' => '-',
                    ];
                }
                $filasParaEsteTrayecto[] = $fila;
            }
            $datosFormateados[$nombreTrayectoActual] = $filasParaEsteTrayecto;
        }
        return $datosFormateados;
    }
}
