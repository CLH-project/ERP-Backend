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
            return $this->response->setJSON([
                 $modelFornecedor->errors()
            ]);
        }

        $contatoData['fornecedor_id'] = $modelFornecedor->getInsertID();

        if (!$modelContato->insert($contatoData)) {
            $db->transRollback();
            return $this->response->setJSON([
                 $modelContato->errors()
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'error' => 'Erro na transação. Nenhum dado foi salvo.'
            ]);
        }

        return $this->response->setJSON([
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
}