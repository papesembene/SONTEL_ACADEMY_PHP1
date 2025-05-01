<?php
declare(strict_types=1);

// namespace App\Services\Session;

require_once __DIR__.'/../enums.php';


function session_init(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || $_SERVER['SERVER_PORT'] == 443;

        ini_set('session.cookie_secure', $is_https ? '1' : '0');

        session_start([
            'cookie_lifetime' => 86400,
            'cookie_secure' => $is_https,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax'
        ]);
    }
}


function session_set(string $key, $value): void {
    session_init();
    $_SESSION[$key] = $value;
}
function session_remove(string $key): void {
    session_init();
    unset($_SESSION[$key]);
}



function session_has($key) {
    session_init();
    return isset($_SESSION[$key]);
}

function session_get($key, $default = []) {
    session_init();
    return $_SESSION[$key] ?? $default;
}

function get_old_input($key, $default = '') {
    $old = session_get('old_input', []);
    return htmlspecialchars($old[$key] ?? $default);
}

function clear_session_messages() {
    session_remove('validation_errors');
    session_remove('old_input');
    session_remove('success_message');
    session_remove('success_message_assignation');
    session_remove('success_message_unassign');
}


function session_destroy_all(): void {
    session_init();
    
    // Vide les données de session
    $_SESSION = [];

    // Supprime le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Détruit la session
    session_destroy();
}