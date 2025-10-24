<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Health check / API info
$routes->get('/', 'Home::index');

// Authentication routes
$routes->group('auth', function($routes) {
    $routes->post('login', 'Auth::login');
    $routes->post('guest-register', 'Auth::guestRegister');
});

// Token management routes
$routes->group('token', function($routes) {
    $routes->post('force-login', 'Token::forceLogin');
    $routes->post('refresh', 'Token::refresh');
});

// User/Client routes (require User Access Token)
$routes->group('me', ['filter' => 'auth:user'], function($routes) {
    $routes->get('prices', 'User::prices');
    $routes->get('catalog', 'User::catalog');
    $routes->get('addresses', 'User::addresses');
    $routes->get('orders', 'User::orders');
});

// Cart routes - Model 2 (Recommended)
// Note: item/validate accepts both user tokens AND integration tokens
$routes->post('cart/item/validate', 'Cart::validateItem', ['filter' => 'auth:user,integration']);

$routes->group('cart', ['filter' => 'auth:user,guest'], function($routes) {
    $routes->post('validate', 'Cart::validateCart');
    $routes->get('shipping-options', 'Cart::shippingOptions');
    $routes->post('shipping-options', 'Cart::shippingOptions');
    $routes->post('confirm', 'Cart::confirm');
});

// Catalog sync route
$routes->get('catalog/export', 'Catalog::export', ['filter' => 'auth:integration']);
