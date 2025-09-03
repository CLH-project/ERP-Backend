<?php

use CodeIgniter\Router\RouteCollection;
/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


// Rotas para options
$routes->options('(:any)', static function () {
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
$routes->get('fornecedor','FornecedorController::paginate');
$routes->get('fornecedor/filter', 'FornecedorController::filter');
$routes->delete('fornecedor/(:num)', 'FornecedorController::delete/$1');

// Rotas Produtos
$routes->post('produtos','ProdutoController::create');
$routes->get('produtos','ProdutoController::paginate');
$routes->put('produtos/(:num)','ProdutoController::update/$1');
$routes->delete('produtos/(:num)','ProdutoController::delete/$1');

// Rotas Usuarios

$routes->post('usuarios','UsuarioController::create');