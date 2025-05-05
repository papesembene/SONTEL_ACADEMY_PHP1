<?php
// Fichier: public/index.php

// require_once __DIR__.'/../App/Services/session.service.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// session_init();

// // Chargement des routes
// $routes = require __DIR__ . '/../App/route/route.web.php';
// $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// $method = $_SERVER['REQUEST_METHOD'];
// $routeFound = false;

// foreach ($routes as $route => $handler) {
//     list($routeMethod, $routePath) = explode(' ', $route, 2);  
//     error_log("Route trouvée : $route"); // Ajoutez ce log
//     if ($method === $routeMethod && $uri === $routePath) {
//         try {
//             $routeFound = true;
            
//             // Exécution du handler
//             $data = call_user_func($handler);
            
            
//             if (isset($data['content'])) {
//                 extract($data);
//                 require __DIR__ . '/../App/Views/layout/base.layout.php';
//             }
            
//         } catch (Exception $e) {
//             error_log("Route error: " . $e->getMessage());
//             session_set('error_message', 'Une erreur technique est survenue');
//             die("Erreur : " . $e->getMessage());
//         }
//         break;
//     }
// }

// if (!$routeFound) {
//     http_response_code(404);
//     die("Erreur 404 : Page non trouvée");
// }



require_once __DIR__.'/../App/Services/session.service.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_init();

$routes = require __DIR__ . '/../App/route/route.web.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$routeFound = false;

foreach ($routes as $route => $handler) {
    list($routeMethod, $routePath) = explode(' ', $route, 2);
   
    if ($method === $routeMethod && $uri === $routePath) {
        try {
            $routeFound = true;
            $response = call_user_func($handler);
            if (is_string($response)) {
                echo $response;
            } else {
                throw new Exception('Type de réponse invalide: ' . gettype($response));
            }
            
            break;
        } catch (Exception $e) {
            error_log("Route error: " . $e->getMessage());
            session_set('error_message', ['content' => 'Une erreur technique est survenue']);
            die("Erreur : " . $e->getMessage());
        }
    }
}

if (!$routeFound) {
    http_response_code(404);
    die("Erreur 404 : Page non trouvée");
}