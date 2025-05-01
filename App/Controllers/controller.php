<?php
namespace App\Controllers;
require_once __DIR__.'/../Services/session.service.php';

define('BASE_URL', 'http://pape.birame.sa.edu.sn:8031');

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}


function render_with_layout($viewPath, $pageTitle, $currentPage = '', $data = []) {
    $content = render_view($viewPath, $data);
    extract([
        'content' => $content,
        'page_title' => $pageTitle,
        'current_page' => $currentPage
    ]);
    
    ob_start();
    require __DIR__."/../Views/layout/base.layout.php";
    return ob_get_clean();
}

function render_view($viewPath, $data = []) {
    extract($data);
    ob_start();
    include __DIR__."/../Views/$viewPath.php";
    return ob_get_clean();
}




