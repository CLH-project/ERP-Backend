<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;

class AuthController extends BaseController
{
    public function login()
    {
        $usuarioModel = new UsuarioModel();

        $input = $this->request->getJSON(true);
        $login = $input['login'] ?? null;
        $senha = $input['senha'] ?? null;

        if (!$login || !$senha) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Login e senha são obrigatórios.']);
        }

        $usuario = $usuarioModel->where('login', $login)->first();

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return $this->response->setStatusCode(401)
                ->setJSON(['error' => 'Credenciais inválidas.']);
        }

        // 🔑 Inclui o cargo do usuário no token
        $key = getenv('JWT_SECRET') ?: 'sua_chave_secreta_aqui';
        $payload = [
            'sub' => $usuario['id'],
            'login' => $usuario['login'],
            'cargo' => $usuario['cargo'], // << aqui entra o campo cargo
            'iat' => time(),
            'exp' => time() + 3600 // 1 hora de validade
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->response->setJSON([
            'token' => $token,
            'usuario' => [
                'id' => $usuario['id'],
                'login' => $usuario['login'],
                'nome' => $usuario['nome'] ?? null,
                'cargo' => $usuario['cargo'] ?? null
            ]
        ]);
    }
}

