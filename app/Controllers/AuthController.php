<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function login()
    {
         $data = $this->request->getJSON(true);

        if (!$data || empty($data['login']) || empty($data['senha'])) {
            return $this->response->setJSON([
                'status' => 'erro',
                'mensagem' => 'login e senha são obrigatórios.'
            ])->setStatusCode(400);
        }
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->where('login', $data['login'])->first();
        if( !$usuario || !password_verify($data['senha'], $usuario['senha'])) {
            return $this->response->setJSON([
                'status' => 'erro',
                'mensagem' => 'Credenciais inválidas.'
            ])->setStatusCode(401);
        }
        $session = session();
        $session->set([
            'usuario_id'     => $usuario['id'],
            'usuario_nome'   => $usuario['nome'],
            'usuario_cargo'  => $usuario['cargo'],
            'usuario_logado' => true
        ]);
        return $this->response->setJSON([
            'status' => 'sucesso',
            'mensagem' => 'Login realizado com sucesso.',
            'usuario' => [
                'id'    => $usuario['id'],
                'nome'  => $usuario['nome'],
                'cargo' => $usuario['cargo']
            ]
        ])->setStatusCode(200);
    }
}
