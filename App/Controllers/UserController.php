<?php
// UserController.php
namespace App\Controllers\UserController;
use Exception;
use App\Models as Model;
use App\Models\UserModel;
use App\Controllers as Controller;
use App\Enums\ModelFunction;
use App\Enums\UserModelKey;
use App\Enums\DataKey;
use  App\Services\Auth as Auth;
use App\Enums\ErrorCode;
use App\Enums\SuccessCode;
require_once __DIR__.'/controller.php';
require_once __DIR__.'/../Services/auth.service.php';
require_once __DIR__.'/../Services/session.service.php';
require_once __DIR__.'/../enums.php'; 

function login() 
{
    session_init();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userModel = require __DIR__.'/../Models/User.model.php';
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);
        try {   
            if (empty($login) || empty($password)) {
                throw new Exception('required_field');
            }
            $user = $userModel[UserModelKey::AUTHENTICATE->value]($login, $password);
            error_log("Auth result: " . json_encode($user));
            if (empty($user) || !is_array($user)) {
                throw new Exception('invalid_credentials');
            }
            if (!isset($user['id'], $user['role'], $user['matricule'])) {
                throw new Exception('invalid_user_data');
            }
            session_set('user', [
                'id' => $user['id'],
                'role' => (string)$user['role'],
                'nom' => (string)($user['nom'] ?? ''),
                'matricule' => (string)$user['matricule']
            ]);  
            if(isset($user['password_change_required']) && $user['password_change_required'] === true) {
                session_set('password_change_required', true);
                Controller\redirect('/forgot-password');
                exit;
            }

             ob_start(); 
            Controller\redirect(match($user['role']) {
                'admin' => '/dashboard',
                'apprenant' => '/profile_apprenant',
                'vigile' => '/vigile/scan',
            });
            exit;
        } catch (Exception $e) {     
            $errorKey = $e->getMessage();
            $errors = require __DIR__.'/error.controller.php'; 
            session_set('login_errors', [$errors[$errorKey] ?? 'Une erreur est survenue']);
            session_set('old_input', [
                'login' => $login
            ]);
        }
    }
    require __DIR__.'/../Views/auth/login.php';
    
    if (session_has('login_errors')) {
        session_remove('login_errors');
    }
}

function logout() {
    session_destroy_all();
    Controller\redirect('/');
}


function forgotPassword()
{
    session_init();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleForgotPassword();
    }
    
    
    require __DIR__.'/../Views/auth/forgot_password.php';
    
    if (session_has('forgot_password_errors')) {
        session_remove('forgot_password_errors');
    }
}


// function handleForgotPassword()
// {
//     $login = trim($_POST['login'] ?? '');
//     $newPassword = trim($_POST['new_password'] ?? '');
//     $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
//     $errors = Auth\validateForgotPassword($login, $newPassword, $confirmPassword);
    
//     if (empty($errors)) {
//         $user = findUserByLogin($login);
        
//         if ($user) {
//             if (updateUserPassword($user['id'], $newPassword)) {
//                 session_set('success_message', ['content' => SuccessCode::PASSWORD_OK->value]);
//                 session_destroy_all();
//                 Controller\redirect('/');
//                 exit;
//             } else {
//                 $errors[] = 'Impossible de mettre à jour le mot de passe';
//             }
//         } else {
//             $errors[] = 'Aucun utilisateur trouvé avec ce matricule ou email';
//         }
//     }
    
//     if (!empty($errors)) {
//         storeForgotPasswordErrors($errors, $login);
//     }
// }

function handleForgotPassword() {
    $login = trim($_POST['login'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    $errors = Auth\validateForgotPassword($login, $newPassword, $confirmPassword);
    
    if (empty($errors)) {
        $user = findUserByLogin($login);
        
        if ($user) {
            if (updateUserPassword($user['id'], $newPassword)) {
                // Si l'utilisateur est un apprenant et était forcé de changer son mot de passe
                if ($user['role'] === 'apprenant' && 
                    (isset($user['password_change_required']) && $user['password_change_required'] === true || 
                     session_has('password_change_required'))) {
                    updateUserProperty($user['id'], 'password_change_required', false);
                    
                    if (session_has('password_change_required')) {
                        session_remove('password_change_required');
                    }
                }
                
                session_set('success_message', ['content' => SuccessCode::PASSWORD_OK->value]);
                session_destroy_all();
                Controller\redirect('/');
                exit;
            } else {
                $errors[] = 'Impossible de mettre à jour le mot de passe';
            }
        } else {
            $errors[] = 'Aucun utilisateur trouvé avec ce matricule ou email';
        }
    }
    
    if (!empty($errors)) {
        storeForgotPasswordErrors($errors, $login);
    }
}

function updateUserProperty($userId, $property, $value) {
    global $data; // Assurez-vous que $data contient vos données JSON chargées
    
    $updated = false;
    foreach ($data['users'] as &$user) {
        if ($user['id'] === $userId) {
            $user[$property] = $value;
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        // Sauvegarder les modifications
        $jsonFile = __DIR__ . '/../data/data.json'; // Ajustez selon votre structure
        return file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    return false;
}
function findUserByLogin($login)
{
    try {
        global $fonctions_models;
        $users = $fonctions_models[ModelFunction::GET_ALL->value](\App\Enums\DataKey::USERS);
        $filteredUsers = array_filter($users, function($u) use ($login) {
            return isset($u['matricule'], $u['email']) 
                && ($u['matricule'] === $login || $u['email'] === $login);
        });
           
        return reset($filteredUsers);
    } catch (Exception $e) {
        error_log("Erreur lors de la recherche d'utilisateur: " . $e->getMessage());
        return null;
    }
}

function updateUserPassword($userId, $newPassword)
{
    try {
        $userModel = require __DIR__.'/../Models/User.model.php';
        return $userModel[UserModelKey::UPDATE_PASSWORD->value]($userId, $newPassword);
    } catch (Exception $e) {
        error_log("Erreur de mise à jour du mot de passe: " . $e->getMessage());
        return false;
    }
}


function storeForgotPasswordErrors($errors, $login)
{
    session_set('forgot_password_errors', $errors);
    session_set('old_input', [
        'login' => $login
    ]);
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function render_view($template, $data = []) {
    extract($data);
    ob_start();
    require __DIR__."/../Views/{$template}.php";
    $content = ob_get_clean();
    require __DIR__."/../Views/layout/base.layout.php";
    exit;
}