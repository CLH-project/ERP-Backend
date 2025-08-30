<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\FornecedorModel;
use App\Models\ProdutoModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProdutoController extends BaseController
{
    public function create()
{
    $model = new ProdutoModel();
    $fornecedorModel = new FornecedorModel();

    $data = $this->request->getJSON(true); // <-- Isso precisa vir antes de usar $data

    if (!isset($data['fornecedor_id'])) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'O campo id_fornecedor é obrigatório.'
        ])->setStatusCode(400);
    }

    $fornecedor = $fornecedorModel->find($data['fornecedor_id']);

    if (!$fornecedor) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Fornecedor não encontrado com o ID informado.'
        ])->setStatusCode(400);
    }

    if ($model->insert($data)) {
        return $this->response->setJSON([
            'status' => 'sucesso',
            'message' => 'Produto cadastrado com sucesso!',
            'id' => $model->getInsertID()
        ]);
    } else {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Erro ao cadastrar produto.',
            'errors' => $model->errors()
        ])->setStatusCode(400);
    }   
    }

    public function paginate(){
        $model = new ProdutoModel();

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $produtos = $model->paginate($perPage,'default',$page);
        $pager = $model->pager;

         return $this->response->setJSON([
        'status' => 'sucesso',
        'produtos' => $produtos,
        'pager' => [
            'currentPage' => $pager->getCurrentPage(),
            'totalPages' => $pager->getPageCount(),
            'total' => $pager->getTotal(),
            'perPage' => $perPage
        ]
    ]);
    }

}
