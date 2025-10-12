<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class UsuarioController extends BaseController
{
    protected $usuarioModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->usuarioModel = new UsuarioModel();
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dados não enviados ou inválidos.'
            ])->setStatusCode(400);
        }

        if (! $this->usuarioModel->insert($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao cadastrar usuário.',
                'errors' => $this->usuarioModel->errors()
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'sucesso',
            'message' => 'Usuário cadastrado com sucesso!'
        ])->setStatusCode(201);
    }

    public function update($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID do usuário não informado.'
            ])->setStatusCode(400);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Usuário não encontrado.'
            ])->setStatusCode(404);
        }

        $json = $this->request->getJSON(true);
        $data = array_filter($json, function ($key) {
            return in_array($key, ['nome', 'cargo', 'senha']);
        }, ARRAY_FILTER_USE_KEY);

        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dados não enviados ou inválidos.'
            ])->setStatusCode(400);
        }

        if (! $this->usuarioModel->update($id, $data)) {
            return $this->response->setJSON([
                'status' => 'erro',
                'message' => 'Erro ao atualizar usuário.',
                'errors' => $this->usuarioModel->errors()
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'sucesso',
            'message' => 'Usuário atualizado com sucesso!'
        ]);
    }

    public function paginate()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $usuarios = $this->usuarioModel->paginate($perPage, 'default', $page);
        $pager = $this->usuarioModel->pager;

        $usuariosFiltrados = array_map(function ($usuario) {
            return [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'cpf' => $usuario['cpf'],
                'cargo' => $usuario['cargo']
            ];
        }, $usuarios);

        return $this->response->setJSON([
            'status' => 'sucesso',
            'usuarios' => $usuariosFiltrados,
            'pager' => [
                'currentPage' => $pager->getCurrentPage(),
                'totalPages' => $pager->getPageCount(),
                'total' => $pager->getTotal(),
                'perPage' => $perPage
            ]
        ]);
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return $this->response->setJSON([
                'status' => 'erro',
                'mensagem' => 'ID do usuário não fornecido.'
            ])->setStatusCode(400);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON([
                'status' => 'erro',
                'mensagem' => 'Usuário não encontrado.'
            ])->setStatusCode(404);
        }

        if (! $this->usuarioModel->delete($id)) {
            return $this->response->setJSON([
                'status' => 'erro',
                'mensagem' => 'Erro ao deletar o usuário.'
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status' => 'sucesso',
            'mensagem' => 'Usuário deletado com sucesso.'
        ]);
    }
}
