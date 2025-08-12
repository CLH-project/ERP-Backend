<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/clientes', 'ClienteController::index',);
$routes->post('/clientes', 'ClienteController::create');