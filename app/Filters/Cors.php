<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');

        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');

        if ($request->getMethod() === 'options') {
            return $response->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}