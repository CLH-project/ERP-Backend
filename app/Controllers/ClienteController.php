<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\ClienteModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class ClienteController extends ResourceController
{
    use ResponseTrait;
    protected $clienteModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->clienteModel = new ClienteModel();
    }

    public function paginados()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $clientes = $this->clienteModel
            ->select('id, nome, cpf, telefone')
            ->paginate($perPage, 'default', $page);

        $pager = $this->clienteModel->pager;

        return $this->respond([
            'data' => $clientes,
            'pager' => [
                'currentPage' => $pager->getCurrentPage(),
                'totalPages' => $pager->getPageCount(),
                'perPage' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
            ],
        ], ResponseInterface::HTTP_OK);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->failValidationErrors(['message' => 'Dados não enviados ou inválidos.']);
        }

        if ($this->clienteModel->save($data)) {
            return $this->respondCreated(['message' => 'Cadastro realizado com sucesso']);
        } else {
            return $this->failValidationErrors($this->clienteModel->errors());
        }
    }

    public function show($param = null)
    {
        if (!$param) {
            return $this->fail('Informe um ID, nome ou CPF para consulta');
        }

        $cliente = $this->clienteModel->find($param);

        if (!$cliente) {
            $cliente = $this->clienteModel
                ->select('id, nome, cpf, telefone')
                ->groupStart()
                    ->where('cpf', $param)
                    ->orLike('nome', $param)
                ->groupEnd()
                ->first();
        }

        if ($cliente) {
            return $this->respond($cliente, ResponseInterface::HTTP_OK);
        }

        return $this->failNotFound('Cliente não encontrado');
    }

    public function delete($id = null)
    {
        if (!$id) {
            return $this->failValidationErrors(['message' => 'ID não informado.']);
        }

        $clienteEncontrado = $this->clienteModel->find($id);

        if (!$clienteEncontrado) {
            return $this->failNotFound('Cliente não encontrado');
        }

        if ($this->clienteModel->delete($id)) {
            return $this->respondDeleted(['message' => 'Cliente deletado com sucesso']);
        }
        return $this->failServerError('Falha ao deletar cliente');
        
    }
}
