<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class UsuarioController extends BaseController
{
    public function create()
    {
        $model = new UsuarioModel();

        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dados não enviados ou inválidos.'
            ])->setStatusCode(400);
        }

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status' => 'sucesso',
                'message' => 'Usuário cadastrado com sucesso!'
            ])->setStatusCode(201);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao cadastrar usuário.',
                'errors' => $model->errors()
            ])->setStatusCode(400);
        }
    }

    public function update ($id = null){
        $model = new UsuarioModel();

        if(!$id){
            return $this->response->setJSON([
                'status' => 'error',
                'message'=> 'ID do usuário não informado.'
            ])->setStatusCode(400);
        }

        $usuario = $model->find($id);
        if (!$usuario) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Usuário não encontrado.'
        ])->setStatusCode(404);
    }
        $json = $this->request->getJSON(true);
        $data = array_filter($json,function ($key){
            return in_array($key,['nome','cargo','senha']);
        },ARRAY_FILTER_USE_KEY);

        if (!$data) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Dados não enviados ou inválidos.'
        ])->setStatusCode(400);
    }
    if ($model->update($id, $data)) {
        return $this->response->setJSON([
            'status' => 'sucesso',
            'message' => 'Usuário atualizado com sucesso!'
        ]);
    } else {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Erro ao atualizar usuário.',
            'errors' => $model->errors()
        ])->setStatusCode(400);
    }
}
    public function paginate()
    {
        $model = new UsuarioModel();

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $usuarios = $model->paginate($perPage, 'default', $page);
        $pager = $model->pager;

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

    $model = new UsuarioModel();

    $usuario = $model->find($id);

    if (!$usuario) {
        return $this->response->setJSON([
            'status' => 'erro',
            'mensagem' => 'Usuário não encontrado.'
        ])->setStatusCode(404);
    }

    if ($model->delete($id)) {
        return $this->response->setJSON([
            'status' => 'sucesso',
            'mensagem' => 'Usuário deletado com sucesso.'
        ]);
    } else {
        return $this->response->setJSON([
            'status' => 'erro',
            'mensagem' => 'Erro ao deletar o usuário.'
        ])->setStatusCode(500);
    }
}
}
