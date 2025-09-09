<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\FornecedorModel;
use App\Models\ProdutoModel;

class ProdutoController extends BaseController
{
    protected $produtoModel;
    protected $fornecedorModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->produtoModel = new ProdutoModel();
        $this->fornecedorModel = new FornecedorModel();
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        $fornecedor = $this->fornecedorModel->find($data['fornecedor_id']);

        if (!$fornecedor) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Fornecedor não encontrado com o ID informado.'
            ])->setStatusCode(400);
        }
        if (!isset($data['fornecedor_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'O campo fornecedor_id é obrigatório.'
            ])->setStatusCode(400);
        }

        if ($this->produtoModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'sucesso',
                'message' => 'Produto cadastrado com sucesso!',
                'id' => $this->produtoModel->getInsertID()
            ])->setStatusCode(201);
        } 
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao cadastrar produto.',
                'errors' => $this->produtoModel->errors()
            ])->setStatusCode(400);   
    }

    public function paginate()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $produtos = $this->produtoModel->paginate($perPage, 'default', $page);
        $pager = $this->produtoModel->pager;

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

    public function update($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID do produto não informado.'
            ])->setStatusCode(400);
        }

        $produto = $this->produtoModel->find($id);

        if (!$produto) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Produto não encontrado.'
            ])->setStatusCode(404);
        }

        $data = $this->request->getJSON(true);

        if (isset($data['fornecedor_id'])) {
            $fornecedor = $this->fornecedorModel->find($data['fornecedor_id']);
            if (!$fornecedor) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Fornecedor não encontrado com o ID informado.'
                ])->setStatusCode(400);
            }
        }

        if ($this->produtoModel->update($id, $data)) {
            return $this->response->setJSON([
                'status' => 'sucesso',
                'message' => 'Produto atualizado com sucesso!'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao atualizar produto.',
                'errors' => $this->produtoModel->errors()
            ])->setStatusCode(400);
        }
    }

    public function delete($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID do produto não informado.'
            ])->setStatusCode(400);
        }

        $produto = $this->produtoModel->find($id);

        if (!$produto) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Produto não encontrado.'
            ])->setStatusCode(404);
        }

        if ($this->produtoModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'sucesso',
                'message' => 'Produto excluído com sucesso!'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao excluir produto.'
            ])->setStatusCode(500);
        }
    }
    public function filterByNome($nome = null)
{
    $nome = $this->request->getGet('nome');

    if (!$nome) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Informe o nome do produto.'
        ])->setStatusCode(400);
    }

    $produtos = $this->produtoModel
        ->select('produtos.id, produtos.nome, produtos.valor_unico, fornecedores.nome as fornecedor')
        ->join('fornecedores', 'fornecedores.id = produtos.fornecedor_id')
        ->like('produtos.nome', $nome)
        ->findAll();

    return $this->response->setJSON([
        'status' => 'sucesso',
        'produtos' => $produtos
    ]);
}
}
