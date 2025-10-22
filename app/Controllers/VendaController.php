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

        // Normaliza $data para array caso venha como stdClass (evita "stdClass as array")
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        if (!$data || !is_array($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dados não enviados ou inválidos.'
            ])->setStatusCode(400);
        }

        // Verifica se o cliente existe (usa coalescing para evitar notice)
        $clienteId = $data['cliente_id'] ?? null;
        $cliente = $this->clienteModel->find($clienteId);
        // defesa: normaliza resultado do model caso seja stdClass (apesar do returnType)
        if (is_object($cliente)) {
            $cliente = json_decode(json_encode($cliente), true);
        }

        if (!$cliente) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cliente não encontrado.'
            ])->setStatusCode(404);
        }

        // Captura ID do usuário logado (tenta várias fontes)
        $usuarioId = null;

        // 1) Session (se estiver usando sessão)
        if (function_exists('session') && session()->has('usuario_id')) {
            $usuarioId = session()->get('usuario_id');
        }
        if (empty($usuarioId) && function_exists('session') && session()->has('id')) {
            $usuarioId = session()->get('id');
        }

        // 2) helper auth() (se existir)
        if (empty($usuarioId) && function_exists('auth')) {
            $authUser = auth()->user();
            if (!empty($authUser)) {
                if (is_array($authUser)) {
                    $usuarioId = $authUser['id'] ?? $authUser['usuario_id'] ?? null;
                } elseif (is_object($authUser)) {
                    $usuarioId = $authUser->id ?? $authUser->usuario_id ?? null;
                }
            }
        }

        // 3) Request attribute (alguns filtros PSR usam getAttribute)
        if (empty($usuarioId) && is_callable([$this->request, 'getAttribute'])) {
            $reqUser = $this->request->getAttribute('user') ?? $this->request->getAttribute('usuario') ?? null;
            if (!empty($reqUser)) {
                if (is_array($reqUser)) {
                    $usuarioId = $reqUser['id'] ?? $reqUser['usuario_id'] ?? null;
                } elseif (is_object($reqUser)) {
                    $usuarioId = $reqUser->id ?? $reqUser->usuario_id ?? null;
                }
            }
        }

        // 4) Fallback: tenta extrair id direto do Authorization: Bearer <token>
        if (empty($usuarioId)) {
            $authHeader = $this->request->getHeaderLine('Authorization') ?: $this->request->getServer('HTTP_AUTHORIZATION');
            if (!empty($authHeader) && stripos($authHeader, 'Bearer ') === 0) {
                $token = trim(substr($authHeader, 7));
                // tenta decodificar payload JWT (ATENÇÃO: sem verificar assinatura)
                $parts = explode('.', $token);
                if (count($parts) >= 2) {
                    $payload = json_decode(self::base64url_decode($parts[1]), true);
                    if (is_array($payload)) {
                        $usuarioId = $payload['id'] ?? $payload['usuario_id'] ?? $payload['sub'] ?? null;
                    }
                }
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

            foreach ($data['itens'] as $rawItem) {
                // garante que cada item seja um array
                $item = is_object($rawItem) ? json_decode(json_encode($rawItem), true) : (array) $rawItem;

                // Valida campos mínimos do item
                if (empty($item['produto_id']) || empty($item['quantidade'])) {
                    throw new \Exception('Item inválido: produto_id e quantidade são obrigatórios.');
                }

                $produto = $this->produtoModel->find($item['produto_id']);
                // defesa extra: normaliza produto caso venha como stdClass
                if (is_object($produto)) {
                    $produto = json_decode(json_encode($produto), true);
                }

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

    public function paginate()
    {
        $perPage = (int) ($this->request->getVar('per_page') ?? 10);
        // o método paginate do Model usa a query param "page" automaticamente,
        // então não precisamos setar $page manualmente aqui
        $this->vendaModel->select(
            'vendas.id AS venda_id,' .
            'vendas.usuario_id, usuarios.nome AS usuario_nome,' .
            'vendas.cliente_id, clientes.nome AS cliente_nome,' .
            'vendas.total_venda, vendas.created_at, vendas.updated_at'
        )
        ->join('usuarios', 'usuarios.id = vendas.usuario_id', 'left')
        ->join('clientes', 'clientes.id = vendas.cliente_id', 'left')
        ->orderBy('vendas.created_at', 'DESC');

        $rows = $this->vendaModel->paginate($perPage);
        $pager = $this->vendaModel->pager;

        // Normaliza possíveis objetos para array (segurança)
        $items = array_map(function ($r) {
            return is_object($r) ? json_decode(json_encode($r), true) : $r;
        }, $rows);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => array_map(function ($row) {
                return [
                    'venda_id'     => $row['venda_id'] ?? null,
                    'usuario_id'   => $row['usuario_id'] ?? null,
                    'usuario_nome' => $row['usuario_nome'] ?? null,
                    'cliente_id'   => $row['cliente_id'] ?? null,
                    'cliente_nome' => $row['cliente_nome'] ?? null,
                    'total_venda'  => $row['total_venda'] ?? 0,
                    'created_at'   => $row['created_at'] ?? null,
                    'updated_at'   => $row['updated_at'] ?? null,
                ];
            }, $items),
            'pagination' => [
                'currentPage' => $pager ? $pager->getCurrentPage() : 1,
                'perPage'     => $pager ? $pager->getPerPage() : $perPage,
                'total'       => $pager ? $pager->getTotal() : count($items),
                'pageCount'   => $pager ? $pager->getPageCount() : 1,
            ],
        ])->setStatusCode(200);
    }

    // adicionar método utilitário na classe (abaixo do método cadastrar ou na classe):
    private static function base64url_decode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        $data = strtr($data, '-_', '+/');
        return base64_decode($data);
    }
}
