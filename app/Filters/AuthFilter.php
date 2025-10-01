<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return \config\Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token não fornecido.']);
        }

        $jwt = $matches[1];
        $secretKey = getenv('JWT_SECRET') ?: 'sua_chave_secreta_aqui';

        try {
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
            // Você pode adicionar o usuário decodificado ao request, se quiser
            $request->user = $decoded;
        } catch (\Exception $e) {
            return \config\Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token inválido ou expirado.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada aqui
    }
}