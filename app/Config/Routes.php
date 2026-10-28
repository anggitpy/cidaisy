<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', static fn () => redirect()->to(site_url('admin')));
$routes->get('denied', static fn () => service('response')->setStatusCode(403)->setBody(view('errors/denied')));
service('auth')->routes($routes, ['except' => ['register', 'logout']]);
$routes->post('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction', ['as' => 'logout']);

// DEVELOPER NOTE: Menu visibility is presentation; permissions must be checked server-side.
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'session'], static function ($routes) {
    $routes->get('/', 'Dashboard::index', ['filter' => 'admin-access:dashboard.access']);
    $routes->get('users', 'Users::index', ['filter' => 'admin-access:users.view']);
    $routes->get('users/new', 'Users::create', ['filter' => 'admin-access:users.create']);
    $routes->post('users', 'Users::store', ['filter' => 'admin-access:users.create']);
    $routes->get('users/(:num)', 'Users::show/$1', ['filter' => 'admin-access:users.view']);
    $routes->get('users/(:num)/edit', 'Users::edit/$1', ['filter' => 'admin-access:users.edit']);
    $routes->post('users/(:num)', 'Users::update/$1', ['filter' => 'admin-access:users.edit']);
    $routes->post('users/(:num)/status', 'Users::status/$1', ['filter' => 'admin-access:users.edit']);
    $routes->post('users/(:num)/reset-password', 'Users::resetPassword/$1', ['filter' => 'admin-access:users.edit']);
    $routes->post('users/(:num)/delete', 'Users::delete/$1', ['filter' => 'admin-access:users.delete']);
    $routes->get('profile', 'Profile::index', ['filter' => 'admin-access:profile.edit']);
    $routes->post('profile', 'Profile::update', ['filter' => 'admin-access:profile.edit']);
    $routes->post('profile/password', 'Profile::password', ['filter' => 'admin-access:profile.edit']);
    $routes->get('settings', 'Settings::index', ['filter' => 'admin-access:settings.view']);
    $routes->post('settings', 'Settings::update', ['filter' => 'admin-access:settings.edit']);
});
