<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\FornecedorModel;
use App\Models\FornecedorContatoModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class FornecedorController extends ResourceController
{
    public function create()
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $modelFornecedor = new FornecedorModel();
        $modelContato = new FornecedorContatoModel();

        $data = $this->request->getJSON(true);

        $fornecedorData = [
            'nome' => $data['nome'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
        ];

        $contatoData = [
            'email'    => $data['contato']['email'] ?? null,
            'telefone' => $data['contato']['telefone'] ?? null,
        ];

        if (!$modelFornecedor->insert($fornecedorData)) {
            $db->transRollback();
            return $this->response->setStatusCode(400)->setJSON([
                 $modelFornecedor->errors()]);
        }

        $contatoData['fornecedor_id'] = $modelFornecedor->getInsertID();

        if (!$modelContato->insert($contatoData)) {
            $db->transRollback();
            return $this->response->setStatusCode(400)->setJSON([
                 $modelContato->errors()
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Erro na transação. Nenhum dado foi salvo.'
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'message' => 'Fornecedor e contato salvos com sucesso!'
        ]);
    }

    public function paginate()
    {
    $model = new FornecedorModel();

    $fornecedores = $model
        ->select('fornecedores.id, fornecedores.nome, fornecedores.cnpj, contato_fornecedores.email, contato_fornecedores.telefone')
        ->join('contato_fornecedores', 'contato_fornecedores.fornecedor_id = fornecedores.id')
        ->paginate(10, 'default');

    $pager = $model->pager;
    return $this->respond([
        'data' => $fornecedores,
        'pager' => [
            'currentPage' => $pager->getCurrentPage(),
            'totalPages' => $pager->getPageCount(),
            'perPage' => $pager->getPerPage(),
            'total' => $pager->getTotal(),
        ],
    ], ResponseInterface::HTTP_OK);
    }
    public function filter()
    {
    $nome = $this->request->getGet('nome');
    $cnpj = $this->request->getGet('cnpj');

    $model = new FornecedorModel();

    $builder = $model
        ->select('fornecedores.id, fornecedores.nome, fornecedores.cnpj, contato_fornecedores.email, contato_fornecedores.telefone')
        ->join('contato_fornecedores', 'contato_fornecedores.fornecedor_id = fornecedores.id');

    if ($nome) {
        $builder->like('fornecedores.nome', $nome);
    }

    if ($cnpj) {
        $builder->like('fornecedores.cnpj', $cnpj);
    }

    $fornecedores = $builder->findAll();

    return $this->respond([
        'data' => $fornecedores
    ], ResponseInterface::HTTP_OK);
}

    public function delete($id = null)
    {
    $model = new FornecedorModel();

    if (!$id || !$model->find($id)) {
        return $this->failNotFound("Fornecedor não encontrado");
    }

    $model->delete($id);

    return $this->respondDeleted([
        'message' => 'Fornecedor deletado com sucesso'
    ]);
    }
}