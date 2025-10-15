<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\ProdutoModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
class VendaController extends BaseController
{
    protected $produtoModel;
    protected $vendaModel;
    protected $itemVendaModel;
    protected $clienteModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->produtoModel = new ProdutoModel();
        $this->vendaModel = new \App\Models\VendaModel();
        $this->itemVendaModel = new \App\Models\ItemVendaModel();
        $this->clienteModel = new \App\Models\ClienteModel();
    }
    public function cadastrar()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dados não enviados ou inválidos.'
            ])->setStatusCode(400);
        }

        // Verifica se o cliente existe
        $cliente = $this->clienteModel->find($data['cliente_id']);
        if (!$cliente) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cliente não encontrado.'
            ])->setStatusCode(404);
        }

        // Captura ID do usuário logado (session, auth() ou atributo da request)
        $usuarioId = null;

        if (function_exists('session') && session()->has('usuario_id')) {
            $usuarioId = session()->get('usuario_id');
        }
        if (empty($usuarioId) && function_exists('session') && session()->has('id')) {
            $usuarioId = session()->get('id');
        }
        if (empty($usuarioId) && function_exists('auth')) {
            $authUser = auth()->user();
            if (!empty($authUser)) {
                $usuarioId = $authUser->id ?? $authUser['id'] ?? null;
            }
        }
        if (empty($usuarioId)) {
            $reqUser = $this->request->getAttribute('user') ?? $this->request->getAttribute('usuario');
            if (!empty($reqUser)) {
                $usuarioId = $reqUser->id ?? $reqUser['id'] ?? null;
            }
        }

        if (empty($usuarioId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Usuário não autenticado.'
            ])->setStatusCode(401);
        }

        // Inicia a transação
        $this->vendaModel->transBegin();

        // Cria a venda (campo usuario_id e total_venda conforme VendaModel)
        $vendaData = [
            'usuario_id' => $usuarioId,
            'cliente_id' => $data['cliente_id'],
            'data_venda' => date('Y-m-d H:i:s'),
            'total_venda' => 0 // Será atualizado depois
        ];

        if (!$this->vendaModel->insert($vendaData)) {
            $this->vendaModel->transRollback();
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao cadastrar venda.',
                'errors' => $this->vendaModel->errors()
            ])->setStatusCode(400);
        }

        $vendaId = $this->vendaModel->getInsertID();
        $totalVenda = 0;

        // Processa os itens da venda
        try {
            if (empty($data['itens']) || !is_array($data['itens'])) {
                throw new \Exception('Itens da venda não informados.');
            }

            foreach ($data['itens'] as $item) {
                // Valida campos mínimos do item
                if (empty($item['produto_id']) || empty($item['quantidade'])) {
                    throw new \Exception('Item inválido: produto_id e quantidade são obrigatórios.');
                }

                $produto = $this->produtoModel->find($item['produto_id']);
                if (!$produto) {
                    throw new \Exception("Produto com ID {$item['produto_id']} não encontrado.", 404);
                }

                if ($produto['estoque'] < $item['quantidade']) {
                    throw new \Exception("Estoque insuficiente para o produto {$produto['nome']}.", 400);
                }

                // Determina o preço unitário: usa preço do item se enviado, senão do produto
                $precoUnitario = $item['preco_unitario'] ?? ($produto['preco'] ?? ($produto['preco_venda'] ?? 0));
                $quantidade = (int) $item['quantidade'];
                $subtotal = round($precoUnitario * $quantidade, 2);

                // Insere item de venda
                $itemData = [
                    'venda_id' => $vendaId,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoUnitario,
                    'subtotal' => $subtotal
                ];

                if (!$this->itemVendaModel->insert($itemData)) {
                    $errors = $this->itemVendaModel->errors();
                    throw new \Exception('Erro ao inserir item da venda: ' . json_encode($errors));
                }

                // Atualiza estoque do produto
                $novoEstoque = $produto['estoque'] - $quantidade;
                if (!$this->produtoModel->update($produto['id'], ['estoque' => $novoEstoque])) {
                    $errors = $this->produtoModel->errors();
                    throw new \Exception('Erro ao atualizar estoque do produto: ' . json_encode($errors));
                }

                $totalVenda += $subtotal;
            }

            // Atualiza o total da venda (campo total_venda)
            if (!$this->vendaModel->update($vendaId, ['total_venda' => $totalVenda])) {
                $errors = $this->vendaModel->errors();
                throw new \Exception('Erro ao atualizar total da venda: ' . json_encode($errors));
            }

            // Commit da transação
            $this->vendaModel->transCommit();

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Venda cadastrada com sucesso.',
                'venda_id' => $vendaId,
                'total' => $totalVenda
            ])->setStatusCode(201);

        } catch (\Exception $e) {
            // Rollback e resposta de erro
            $this->vendaModel->transRollback();

            $code = $e->getCode();
            $statusCode = ($code === 404) ? 404 : (($code === 400) ? 400 : 500);

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ])->setStatusCode($statusCode);
        }
    }
}
