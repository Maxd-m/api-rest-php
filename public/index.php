<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../core/Router.php';
require_once '../config/Database.php';
require_once '../resources/v1/UserResource.php';
require_once '../resources/v1/ProductResource.php';
require_once '../resources/v1/LoginResource.php';
require_once '../middlewares/AuthMiddleware.php';
require_once '../models/ApiToken.php';

$database = new Database();
$db = $database->getConnection();

$apiToken = new ApiToken($db);
$authMiddleware = new AuthMiddleware($apiToken);


$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName;

$router = new Router('v1', $basePath);
$userResource = new UserResource();
$productResource = new ProductResource();
$loginResource = new LoginResource();

// rutas
$router->addRoute('GET', '/users', [$userResource, 'index'], [$authMiddleware, 'handle']);
$router->addRoute('GET', '/users/{id}', [$userResource, 'show'], [$authMiddleware, 'handle']);
$router->addRoute('POST', '/users', [$userResource, 'store'], [$authMiddleware, 'handle']);
$router->addRoute('PUT', '/users/{id}', [$userResource, 'update'], [$authMiddleware, 'handle']);
$router->addRoute('DELETE', '/users/{id}', [$userResource, 'destroy'], [$authMiddleware, 'handle']);

$router->addRoute('GET', '/products', [$productResource, 'index'], [$authMiddleware, 'handle']);
$router->addRoute('GET', '/products/{id}', [$productResource, 'show'], [$authMiddleware, 'handle']);
$router->addRoute('POST', '/products', [$productResource, 'store'], [$authMiddleware, 'handle']);
$router->addRoute('PUT', '/products/{id}', [$productResource, 'update'], [$authMiddleware, 'handle']);
$router->addRoute('DELETE', '/products/{id}', [$productResource, 'destroy'], [$authMiddleware, 'handle']);

$router->addRoute('POST', '/login', [$loginResource, 'login']);

$router->dispatch();
?>