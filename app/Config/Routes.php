<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Controller de Clientes
$routes->get('/clientes', 'ClienteController::index',);
$routes->post('/clientes', 'ClienteController::create');
$routes->get('/clientes/(:num)', 'ClienteController::show/$1');
$routes->delete('/clientes/(:num)', 'ClienteController::delete/$1');