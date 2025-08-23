<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->options('clientes', static function () {
    return response()->setStatusCode(204);
});

// Controller de Clientes
$routes->get('/clientes', 'ClienteController::paginados',); // URL http://localhost:8000/clientes
$routes->post('/clientes', 'ClienteController::create'); // URL http://localhost:8000/clientes
$routes->get('/clientes/(:num)', 'ClienteController::show/$1'); // URL http://localhost:8000/clientes/1
$routes->delete('/clientes/(:num)', 'ClienteController::delete/$1'); // URL http://localhost:8000/clientes/1
$routes->get('/clientes/(:any)', 'ClienteController::show/$1'); // URL http://localhost:8000/clientes/12345678901 (CPF) ou http://localhost/clientes/João (Nome)


// Rotas Fornecedores

$routes->post('fornecedor' ,'FornecedorController::create');