<?php

use CodeIgniter\Commands\Utilities\Routes;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('login', 'AuthController::login');
$routes->options('login', static function () {
        return response()
            ->setStatusCode(204)
            ->setHeader('Allow', 'OPTIONS, POST');
    });

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/clientes', 'ClienteController::paginados',); // URL http://localhost:8000/clientes
    $routes->post('/clientes', 'ClienteController::create'); // URL http://localhost:8000/clientes
    $routes->get('/clientes/(:num)', 'ClienteController::show/$1'); // URL http://localhost:8000/clientes/1
    $routes->delete('/clientes/(:num)', 'ClienteController::delete/$1'); // URL http://localhost:8000/clientes/1
    $routes->get('/clientes/(:any)', 'ClienteController::show/$1'); // URL http://localhost:8000/clientes/12345678901 (CPF) ou http://localhost/clientes/JoÃ£o (Nome)


    // Rotas Fornecedores
    $routes->post('fornecedores', 'FornecedorController::create');
    $routes->get('fornecedores', 'FornecedorController::paginate');
    $routes->get('fornecedores/filter/(:any)', 'FornecedorController::filter/$1');
    $routes->delete('fornecedores/(:num)', 'FornecedorController::delete/$1');

    // Rotas Produtos
    $routes->post('produtos', 'ProdutoController::create');
    $routes->get('produtos', 'ProdutoController::paginate');
    $routes->put('produtos/(:num)', 'ProdutoController::update/$1');
    $routes->delete('produtos/(:num)', 'ProdutoController::delete/$1');
    $routes->get('produtos/filter', 'ProdutoController::filterByNome');

    $routes->options('produtos', static function () {
        return response()
            ->setStatusCode(204)
            ->setHeader('Allow', 'OPTIONS, POST');
    });

    $routes->options('fornecedores', static function () {
        return response()
            ->setStatusCode(204)
            ->setHeader('Allow', 'OPTIONS, POST');
    });

    $routes->options('usuarios', static function () {
        return response()
            ->setStatusCode(204)
            ->setHeader('Allow', 'OPTIONS, POST');
    });
});

$routes->group('', ['filter' => 'gerente'], function ($routes) {
    // Rotas Usuarios
    $routes->get('usuarios', 'UsuarioController::paginate');
    $routes->post('usuarios', 'UsuarioController::create');
    $routes->put('usuarios/(:num)', 'UsuarioController::update/$1');
    $routes->delete('usuarios/(:num)', 'UsuarioController::delete/$1');

    $routes->options('usuarios', static function () {
        return response()
            ->setStatusCode(204)
            ->setHeader('Allow', 'OPTIONS, POST');
    });
});