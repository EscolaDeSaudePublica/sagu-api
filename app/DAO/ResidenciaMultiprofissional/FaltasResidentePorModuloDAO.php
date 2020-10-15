<?php


namespace App\DAO\ResidenciaMultiprofissional;


use App\DAO\ResidenciaMultiprofissional\Abstracoes\InterfaceAvaliacaoDAO;
use App\Services\ResidenciaMultiprofissional\AvaliacaoResidenciaMultiprofissional;

class FaltasResidentePorModuloDAO implements InterfaceAvaliacaoDAO
{
    public $model;

    /**
     * FaltasResidentePorModuloDAO constructor.
     */
    public function __construct()
    {
        $this->model;
    }

    public function atualizar($residenteId, $ofertaId, $faltas)
    {
        return \DB::table('aps.faltadoresidentenaofertadeunidadetematica')
            ->where('residenteid', $residenteId)
            ->where('ofertadeunidadetematicaid', $ofertaId)
            ->update(['falta' => $faltas]);
    }

    public function inserir($residenteId, $ofertaId, $faltas, $username)
    {
        return \DB::table('aps.faltadoresidentenaofertadeunidadetematica')
            ->insert(
                [
                    'username' => $username,
                    'residenteid' => $residenteId,
                    'ofertadeunidadetematicaid' => $ofertaId,
                    'falta' => $faltas
                ]
            );
    }
}