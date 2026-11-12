<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public
$routes->addRedirect('/', 'dashboard');
$routes->get('login', 'Auth::showLogin');
$routes->post('login', 'Auth::attempt');

// Everything else requires a signed-in user (alias "auth" -> App\Filters\AuthFilter, see README).
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('logout', 'Auth::logout');

    $routes->get('dashboard', 'Dashboard::index');

    // Customers
    $routes->get('customers', 'Customers::index');
    $routes->get('customers/create', 'Customers::create');
    $routes->post('customers/store', 'Customers::store');
    $routes->get('customers/edit/(:num)', 'Customers::edit/$1');
    $routes->post('customers/update/(:num)', 'Customers::update/$1');
    $routes->post('customers/delete/(:num)', 'Customers::delete/$1');

    // Products
    $routes->get('products', 'Products::index');
    $routes->get('products/create', 'Products::create');
    $routes->post('products/store', 'Products::store');
    $routes->get('products/edit/(:num)', 'Products::edit/$1');
    $routes->post('products/update/(:num)', 'Products::update/$1');
    $routes->post('products/delete/(:num)', 'Products::delete/$1');

    // Invoices
    $routes->get('invoices', 'Invoices::index');
    $routes->get('invoices/create', 'Invoices::create');
    $routes->post('invoices/store', 'Invoices::store');
    $routes->get('invoices/view/(:num)', 'Invoices::show/$1');
    $routes->get('invoices/edit/(:num)', 'Invoices::edit/$1');
    $routes->post('invoices/update/(:num)', 'Invoices::update/$1');
    $routes->post('invoices/delete/(:num)', 'Invoices::delete/$1');
    $routes->post('invoices/status/(:num)', 'Invoices::status/$1');
    $routes->get('invoices/pdf/(:num)', 'Invoices::pdf/$1');
    $routes->get('invoices/print/(:num)', 'Invoices::printView/$1');

    // Settings
    $routes->get('settings', 'Settings::index');
    $routes->post('settings', 'Settings::save');
    $routes->get('settings/logo', 'Settings::logo');
});
