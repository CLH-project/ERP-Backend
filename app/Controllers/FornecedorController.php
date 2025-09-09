<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\FornecedorModel;
use App\Models\FornecedorContatoModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class FornecedorController extends ResourceController
{
    protected $fornecedorModel;
    protected $contatoModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->fornecedorModel = new FornecedorModel();
        $this->contatoModel = new FornecedorContatoModel();
    }

    public function create()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $data = $this->request->getJSON(true);

        $fornecedorData = [
            'nome' => $data['nome'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
        ];

        $contatoData = [
            'email'    => $data['contato']['email'] ?? null,
            'telefone' => $data['contato']['telefone'] ?? null,
        ];

        if (!$this->fornecedorModel->insert($fornecedorData)) {
            $db->transRollback();
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $this->fornecedorModel->errors()
            ]);
        }

        $contatoData['fornecedor_id'] = $this->fornecedorModel->getInsertID();

        if (!$this->contatoModel->insert($contatoData)) {
            $db->transRollback();
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $this->contatoModel->errors()
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Erro na transação. Nenhum dado foi salvo.'
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'status' => 'sucesso',
            'message' => 'Fornecedor e contato salvos com sucesso!',
            'id' => $contatoData['fornecedor_id']
        ]);
    }

    public function paginate()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $fornecedores = $this->fornecedorModel
            ->select('fornecedores.id, fornecedores.nome, fornecedores.cnpj, contato_fornecedores.email, contato_fornecedores.telefone')
            ->join('contato_fornecedores', 'contato_fornecedores.fornecedor_id = fornecedores.id')
            ->paginate($perPage, 'default', $page);

        $pager = $this->fornecedorModel->pager;

        return $this->response->setJSON([
            'status' => 'sucesso',
            'fornecedores' => $fornecedores,
            'pager' => [
                'currentPage' => $pager->getCurrentPage(),
                'totalPages' => $pager->getPageCount(),
                'perPage' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
            ],
        ]);
    }

    public function filter()
    {
        $nome = $this->request->getGet('nome');
        $cnpj = $this->request->getGet('cnpj');

        $builder = $this->fornecedorModel
            ->select('fornecedores.id, fornecedores.nome, fornecedores.cnpj, contato_fornecedores.email, contato_fornecedores.telefone')
            ->join('contato_fornecedores', 'contato_fornecedores.fornecedor_id = fornecedores.id');

        if ($nome) {
            $builder->like('fornecedores.nome', $nome);
        }

        if ($cnpj) {
            $builder->like('fornecedores.cnpj', $cnpj);
        }

        $fornecedores = $builder->findAll();

        return $this->response->setJSON([
            'status' => 'sucesso',
            'fornecedores' => $fornecedores
        ]);
    }

    public function delete($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID do fornecedor não informado.'
            ])->setStatusCode(400);
        }

        $fornecedor = $this->fornecedorModel->find($id);

        if (!$fornecedor) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Fornecedor não encontrado.'
            ])->setStatusCode(404);
        }

        $this->fornecedorModel->delete($id);

        return $this->response->setJSON([
            'status' => 'sucesso',
            'message' => 'Fornecedor deletado com sucesso'
        ]);
    }
}