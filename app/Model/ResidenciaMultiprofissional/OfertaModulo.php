<?php


namespace App\Model\ResidenciaMultiprofissional;


use App\Model\BaseModel\BaseModelSagu;

/**
 * Class OfertaModulo
 * @package App\Model\ResidenciaMultiprofissional
 *
 * CONFIGURAÇÕES:
 * protected $camposComposicao variável com os campos internos que são composições de outras classes modelo
 * protected $mapFieldModel variável que faz referência à uma coluna da tabela que está escrita diferente do modelo.
 *
 */
class OfertaModulo extends BaseModelSagu
{
    protected $schema = 'res';
    protected $table = 'ofertadeunidadetematica';

    /**
     * @var int
     */
    public $id;

    public $moduloId;

    /**
     * @var date
     */
    public $dataInicio;

    /**
     * @var \DateTime
     */
    public $dataFim;

    /**
     * @var string
     */
    public $encerramento;

    /**
     * @var string
     */
    public $nome;

    /**
     * @var int
     */
    public $semestre;

    /**
     * @var String
     */
    public $semestre_descricao;

    /**
     * @var Turma
     */
    public $turma;

    /**
     * @var Modulo
     */
    public $modulo;

    public $cargaHoraria;

    /**
     * Configuração para as variáveis de composição do objeto
     * OBRIGATÓRIO implementar método set para cada variável da composição
     * @var array [ 'nome da variável interna da composição' => [ valores do objeto ] ]
     */
    protected $camposComposicao = [
        'turma' => [],
        'modulo' => []
    ];

    /**
     * Mapemento entre os cmapos da tabela e as variáveis do objeto.
     * @var string[]
     */
    protected $mapFieldModel = [
        'ofertadeunidadetematicaid' => 'id',
        'inicio' => 'dataInicio',
        'fim' => 'dataFim'
    ];

    public function setTurma($dados)
    {
        $this->setModeloComposto(Turma::class, 'turma', $dados);
    }

    public function setModulo($dados)
    {
        $this->setModeloComposto(Modulo::class, 'modulo', $dados);
    }
}