<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/registrasi', 'RegistrasiController::registrasi');
$routes->post('/login', 'LoginController::login');
$routes->options('registrasi', static fn () => service('response')->setStatusCode(200));
$routes->options('login', static fn () => service('response')->setStatusCode(200));

$routes->group('produk', function($routes) {
    $routes->post('/', 'ProdukController::create');
    $routes->get('/', 'ProdukController::list');
    $routes->get('(:segment)', 'ProdukController::detail/$1');
    $routes->put('(:segment)', 'ProdukController::ubah/$1');
    $routes->delete('(:segment)', 'ProdukController::hapus/$1');
    $routes->options('/', static fn () => service('response')->setStatusCode(200));
    $routes->options('(:segment)', static fn () => service('response')->setStatusCode(200));
});
