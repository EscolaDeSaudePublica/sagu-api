<?php


namespace App\DAO\ResidenciaMultiprofissional;


use App\DAO\Traits\ArrayMapToModel;
use App\Model\ResidenciaMultiprofissional\OfertaModulo;
use Illuminate\Support\Facades\DB;

class OfertaModuloDAO
{
    use ArrayMapToModel;

    /**
     * @var OfertaModulo
     */
    public $model;


     /**
     * @var OfertaModuloTiposCargaHorariaDAO
     */
    private $ofertaModuloTiposCargaHorariaDAO;

    /**
     * @var AtividadeModuloDAO
     */
    private $atividadeModuloDAO;

    /**
     * OfertaModuloDAO constructor.
     * @param OfertaModulo $model
     */
    public function __construct()
    {
        $this->model = new OfertaModulo();
        $this->ofertaModuloTiposCargaHorariaDAO = new OfertaModuloTiposCargaHorariaDAO();
        $this->atividadeModuloDAO = new AtividadeModuloDAO();
    }

    public function buscarOfertasModuloSupervisor($supervisorId, $turmaId, $page = null)
    {
        $query = DB::table('res.ofertadeunidadetematica')
            ->distinct()
            ->select(
                'res.ofertadeunidadetematica.ofertadeunidadetematicaid as ofertadeunidadetematicaid',
                'res.ofertadeunidadetematica.inicio as inicio',
                'res.ofertadeunidadetematica.fim as fim',
                'res.ofertadeunidadetematica.encerramento as encerramento',
                'res.ofertadeunidadetematica.nome as nome',
                'res.ofertadeunidadetematica.semestre as semestre',
                DB::raw("
                    CASE WHEN semestre IN (1, 2) THEN 'Primeiro Ano' 
                        WHEN semestre in (3, 4) THEN 'Segundo Ano' 
                        WHEN semestre in (5, 6) THEN 'Terceiro Ano' 
                        ELSE 'Terceiro Ano' 
                        END AS semestre_descricao
                "),
                'res.ofertadeunidadetematica.cargahoraria as cargahoraria',
                'res.ofertadeunidadetematica.unidadetematicaid as unidadetematicaid',
                'res.turma.descricao as turma.descricao',
                'res.turma.codigoturma as turma.codigoturma',
                'res.modulo.nome as modulo.nome',
                'res.modulo.moduloid as modulo.moduloid'
            )
            ->join('res.turma', 'res.ofertadeunidadetematica.turmaid', 'res.turma.turmaid')
            ->join('res.unidadetematica', 'res.unidadetematica.unidadetematicaid', 'res.ofertadeunidadetematica.unidadetematicaid')
            ->join('res.modulo', 'res.modulo.moduloid', 'res.unidadetematica.moduloid')
            ->join(
                'res.ofertadeunidadetematicasupervisoresinstituicoes',
                'res.ofertadeunidadetematicasupervisoresinstituicoes.ofertadeunidadetematicaid',
                'res.ofertadeunidadetematica.ofertadeunidadetematicaid'
            )
            ->join(
                'res.supervisores',
                'res.supervisores.supervisorid',
                'res.ofertadeunidadetematicasupervisoresinstituicoes.supervisorid'
            )
            ->where('res.supervisores.supervisorid', $supervisorId)
            ->where('res.turma.turmaid', $turmaId)
            ->limit(25);

        if ($page) {
            $query->offset(25 * ($page - 1));
        }

        $ofertasArray = [];
        foreach ($query->get()->toArray() as $oferta) {
            $tiposCargaHoraria = $this->ofertaModuloTiposCargaHorariaDAO->tiposCargaHorariaPorOferta($oferta->ofertadeunidadetematicaid);
            $oferta->tipoCargaHoraria = $tiposCargaHoraria;
            $oferta->atividadeModulo = $this->atividadeModuloDAO->get($oferta->unidadetematicaid);

            $ofertasArray[] = $oferta;
        }
        return $this->mapToModel($ofertasArray);
    }

    public function get($ofertaId)
    {

        $select = DB::select(
            "SELECT * FROM {$this->model->getTable()} 
            WHERE ofertadeunidadetematicaid = :ofertadeunidadetematicaid",
            [
                'ofertadeunidadetematicaid' => $ofertaId
            ]
        );

        $ofertaModulo = new OfertaModulo();
        if (count($select)) {
            $select = $select[0];

            $ofertaModulo->id = $select->ofertadeunidadetematicaid;
            $ofertaModulo->moduloId = $select->unidadetematicaid;
            $ofertaModulo->dataInicio = new \DateTime($select->inicio);
            $ofertaModulo->dataFim = new \DateTime($select->fim);
            $ofertaModulo->nome = $select->nome;
            $ofertaModulo->semestre = $select->semestre;
            $ofertaModulo->cargaHoraria = $select->cargahoraria;
        }

        return $ofertaModulo;
    }
}