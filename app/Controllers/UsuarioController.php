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
                'message' => 'Usuário cadastrado com sucesso!',
                'id' => $model->getInsertID()
            ])->setStatusCode(201);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Erro ao cadastrar usuário.',
                'errors' => $model->errors()
            ])->setStatusCode(400);
        }
    }
}
