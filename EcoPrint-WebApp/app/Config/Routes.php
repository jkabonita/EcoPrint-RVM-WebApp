<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

 // Main
$routes->get('/', 'Home::index');
$routes->get('/php-info', 'Home::phpInfo');

//Users
$routes->group('dashboard', ['filter' => ['auth:user,admin,print']], function($routes) {
    $routes->get('/', 'User::index');
    $routes->post('update-profile', 'User::updateProfile');
    $routes->get('recycling-logs', 'User::recyclingLogs'); 
    $routes->get('files', 'User::files'); 
    $routes->get('files/upload-file', 'User::uploadFilePage'); 
    $routes->post('files/upload-file', 'User::uploadFile');
    $routes->get('files/view-file/(:segment)', 'User::viewFilePage/$1');
    $routes->get('files/delete-file/(:segment)', 'User::deleteFile/$1'); 
    $routes->get('credit-transfers', 'User::creditTransfers');
    $routes->post('credit-transfers', 'User::creditTransfers');
});

//Login
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
$routes->get('register/(:segment)', 'Auth::registerForm/$1');

// Terms and Privacy Policy
$routes->get('terms-of-service', 'Home::termsOfService');
$routes->get('privacy-policy', 'Home::privacyPolicy');

//Google Login
$routes->get('auth/google-login', 'Auth::googleLogin');
$routes->get('auth/google-callback', 'Auth::googleCallback');

//Admin Functions
$routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('users', 'Admin::listOfUsers');
    $routes->get('recyclable-bottles', 'Admin::recyclableBottles');
    $routes->post('recyclable-bottles/edit-credit/(:segment)', 'Admin::editBottleCredit/$1');
    $routes->get('reverse-vending-machines', 'Admin::reverseVendingMachines');
    $routes->get('reverse-vending-machine-details/(:segment)', 'Admin::reverseVendingMachineDetails/$1');
    $routes->get('printing-service', 'Admin::printingService');
    $routes->get('admin-and-office', 'Admin::accounts');
    $routes->get('admin-and-office/add-account', 'Admin::addAccountPage');
    $routes->get('admin-and-office/suggest-account', 'Admin::suggestAccount');
    $routes->post('admin-and-office/add-account', 'Admin::addAccount');
    $routes->get('admin-and-office/edit-account/(:segment)', 'Admin::editAccountPage/$1');
    $routes->post('admin-and-office/edit-account/(:segment)', 'Admin::editAccount/$1');
    $routes->get('admin-and-office/remove-privilege/(:segment)', 'Admin::removePrivilege/$1');
    $routes->get('get-bottle-stats', 'Admin::getBottleRecycleStats');

});

//Printing Functions
$routes->group('print', ['filter' => 'auth:admin,print'], function($routes) {
    $routes->get('/', 'PrintController::printLogs');
    $routes->get('print-logs', 'PrintController::printLogs');
    $routes->get('print-auth', 'PrintController::printAuth');
    $routes->post('print-auth', 'PrintController::printAuth');
    $routes->get('user-files', 'PrintController::userFiles');
    $routes->get('user-files/view/(:segment)', 'PrintController::initiatePrint/$1');
    $routes->post('user-files/print/(:segment)', 'PrintController::printView/$1');
    $routes->get('user-files/confirm-printing/(:segment)', 'PrintController::confirmPrint/$1');
    $routes->get('user-files/cancel-printing/(:segment)', 'PrintController::cancelPrint/$1');
});

//Printing API
$routes->group('api/print', function($routes) {
    $routes->post('process-print/(:segment)', 'PrintController::processPrint/$1');
    $routes->get('get-print-job/(:segment)/(:segment)', 'PrintController::getPrintJob/$1/$2');
    $routes->post('update-print-job/(:segment)', 'PrintController::updatePrintJobStatus/$1');
    $routes->get('job-status/(:num)', 'PrintController::getJobStatus/$1');
});

//Machine API Routes
$routes->group('api/machine', function($routes) {
    $routes->get('/', 'MachineApi::index');
    $routes->get('get-status', 'MachineApi::getMachineData');
    $routes->get('session-init/(:segment)', 'MachineApi::sessionInit/$1');
    $routes->post('finalize-session', 'MachineApi::finalizeSession');     
    $routes->post('heartbeat/(:segment)', 'MachineApi::heartbeat/$1'); 
    $routes->post('report-error/(:segment)', 'MachineApi::reportError/$1');
    $routes->post('report-full-storage/(:segment)', 'MachineApi::reportFullStorage/$1');
});

// User UX API
$routes->group('api/user', function($routes) {
    $routes->post('get-user', 'User::getUserToTransfer');
});

