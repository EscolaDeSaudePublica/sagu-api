<?php

namespace Tests\ResidenciaMultiprofissional;

use App\DAO\ResidenciaMultiprofissional\CargaHorariaComplementarDAO;
use App\DAO\ResidenciaMultiprofissional\OfertaModuloDAO;
use App\DAO\ResidenciaMultiprofissional\TurmaDAO;
use TestCase;
use Symfony\Component\HttpFoundation\Response;

class OfertaModuloCargaHorariaComplementarTest extends TestCase
{
    private $ofertasDoSupervisor;
    private $turmasSupervisor;
    private $cargaHorariaComplementarDAO;

    public function setUp()
    {
        parent::setUp();
        $this->authenticated();

        $turmaDAO = new TurmaDAO();
        $this->turmasSupervisor = $turmaDAO->buscarTurmasSupervisor($this->supervisor->supervisorid);
        $turmaId = $this->turmasSupervisor[0]['id'];

        $ofertaModuloTurmasDAO = new OfertaModuloDAO();
        $this->ofertasDoSupervisor = $ofertaModuloTurmasDAO->buscarOfertasModuloSupervisor($this->supervisor->supervisorid, $turmaId);

        $this->cargaHorariaComplementarDAO = new CargaHorariaComplementarDAO();
    }


    public function testLancamentoDeCargaHorariaComplementarNaoAutorizado()
    {
        $this->post(
            '/residencia-multiprofissional/supervisores/turma/13/oferta/314/cargahoraria-complementar',
            []
        )
            ->seeStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    public function testLancamentoDeCargaHorariaComplementarParametrosNumeroFloat()
    {
        $this->post(
            '/residencia-multiprofissional/supervisores/turma/13.18/oferta/3.14/cargahoraria-complementar',
            [],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_BAD_REQUEST)
            ->seeJsonEquals(
                [
                    'error' => true,
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Parâmetro { turma } não é um valor número aceitável.'
                ]
            );
    }

    public function testLancamentoDeCargaHorariaComplementarSemDados()
    {
        $this->post(
            '/residencia-multiprofissional/supervisores/turma/13/oferta/314/cargahoraria-complementar',
            [],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_BAD_REQUEST)
            ->seeJsonEquals(
                [
                    'sucesso' => false,
                    'mensagem' => 'Carga horária complementar é obrigatório'
                ]
            );
    }


    public function testLancamentoDeCargaHorariaOK()
    {
        $turmaId = $this->turmasSupervisor[0]['id'];
        $ofertaId = $this->ofertasDoSupervisor[0]->id;
        $residenteId = 751;

        $this->json(
            'POST',
            "/residencia-multiprofissional/supervisores/turma/{$turmaId}/oferta/{$ofertaId}/cargahoraria-complementar",
            [
                'cargaHoraria' => [
                    'residenteId' => $residenteId,
                    'tipoCargaHorariaComplementar' => 2,
                    'cargaHoraria' => 30,
                    'justificativa' => 'Plantão',
                    'tipoCargaHoraria' => 'C',
                ]
            ],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJsonStructure(
                [
                    'sucesso',
                    'cargaHorariaComplementar' => [
                        'id',
                        'tipoCargaHorariaComplementar' => [
                            'id',
                            'descricao'
                        ],
                        'residente',
                        'oferta',
                        'cargaHoraria',
                        'justificativa',
                        'tipoCargaHoraria',
                    ]
                ]
            );
    }

    public function testLancamentoDeCargaHorariaCamposInvalidos()
    {
        $turmaId = $this->turmasSupervisor[0]['id'];
        $ofertaId = $this->ofertasDoSupervisor[0]->id;

        $this->json(
            'POST',
            "/residencia-multiprofissional/supervisores/turma/{$turmaId}/oferta/{$ofertaId}/cargahoraria-complementar",
            [
                'cargaHoraria' => [
                    [
                        'campo1' => 1,
                        'campo2' => 2,
                        'campo3' => 3,
                        'campo4' => 4,
                        'campo5' => 5,
                    ]
                ]
            ],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_BAD_REQUEST)
            ->seeJsonEquals(
                [
                    'sucesso' => false,
                    'mensagem' => 'Campo(s) inválido(s)'
                ]
            );
    }

    public function testLancamentoDeCargaHorariaUpdateCamposInvalidos()
    {
        $turmaId = $this->turmasSupervisor[0]['id'];
        $ofertaId = $this->ofertasDoSupervisor[0]->id;

        $this->json(
            'PUT',
            "/residencia-multiprofissional/supervisores/turma/{$turmaId}/oferta/{$ofertaId}/cargahoraria-complementar/56",
            [
                'cargaHoraria' => [
                    [
                        'campo1' => 1,
                        'campo2' => 2,
                        'campo3' => 3,
                        'campo4' => 4,
                        'campo5' => 5,
                    ]
                ]
            ],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_BAD_REQUEST)
            ->seeJsonEquals(
                [
                    'sucesso' => false,
                    'mensagem' => 'Campo(s) inválido(s)'
                ]
            );
    }


    public function testUpdateLancamentoDeCargaHorariaOK()
    {
        $turmaId = $this->turmasSupervisor[0]['id'];
        $ofertaId = $this->ofertasDoSupervisor[0]->id;
        $residenteId = 751;

        $chComplementar = $this->cargaHorariaComplementarDAO->getCargaHorariaComplementarDoResidenteNaOferta($residenteId, $ofertaId);

        $this->json(
            'PUT',
            "/residencia-multiprofissional/supervisores/turma/{$turmaId}/oferta/{$ofertaId}/cargahoraria-complementar/{$chComplementar['0']->id}",
            [
                'cargaHoraria' => [
                    'residenteId' => $residenteId,
                    'tipoCargaHorariaComplementar' => 2,
                    'cargaHoraria' => 30,
                    'justificativa' => 'Plantão',
                    'tipoCargaHoraria' => 'C',
                ]
            ],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJsonStructure(
                [
                    'sucesso',
                    'cargaHorariaComplementar' => [
                        'id',
                        'tipoCargaHorariaComplementar' => [
                            'id',
                            'descricao'
                        ],
                        'residente',
                        'oferta',
                        'cargaHoraria',
                        'justificativa',
                        'tipoCargaHoraria',
                    ]
                ]
            );
    }

    public function testDeleteLancamentoDeCargaHorariaSemId()
    {
        $turmaId = $this->turmasSupervisor[0]['id'];
        $ofertaId = $this->ofertasDoSupervisor[0]->id;
        $residenteId = 751;

        $chComplementar = $this->cargaHorariaComplementarDAO->getCargaHorariaComplementarDoResidenteNaOferta($residenteId, $ofertaId);

        $this->delete(
            "/residencia-multiprofissional/supervisores/turma/{$turmaId}/oferta/{$ofertaId}/cargahoraria-complementar/{$chComplementar['0']->id}",
            [],
            [
                'Authorization' => 'Bearer ' . $this->currentToken
            ]
        )
            ->seeStatusCode(Response::HTTP_OK)
            ->seeJsonStructure(
                [
                    'sucesso'
                ]
            );
    }

    public function testLancamentoDeCargaHorariaComHorasMenorQueZero()
    {
        $turmaId = $this->turmasSupervisor[0]['id'];
        $ofertaId = $this->ofertasDoSupervisor[0]->id;
        $residenteId = 751;

        $this->json(
            'POST',
            "/residencia-multiprofissional/supervisores/turma/{$turmaId}/oferta/{$ofertaId}/cargahoraria-complementar",
            [
                'cargaHoraria' => [
                    'residenteId' => $residenteId,
                    'tipoCargaHorariaComplementar' => 2,
                    'cargaHoraria' => -5,
                    'justificativa' => 'Plantão',
                    'tipoCargaHoraria' => 'C',
                ]
            ],
            [
                'Authorization' => 'Bearer ' . $this->currentToken,
                'Content-Type' => 'application/json'
            ]
        )
            ->seeStatusCode(Response::HTTP_BAD_REQUEST)
            ->seeJsonEquals(
                [
                    'sucesso' => false,
                    'mensagem' => 'Carga horária complementar não pode ser menor ou igual a 0'
                ]
            );
    }
}