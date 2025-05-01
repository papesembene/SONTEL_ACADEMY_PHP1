<?php
namespace App\Services\Auth;

require_once __DIR__.'/../enums.php';
use App\Enums\ErrorCode;
$translations = require __DIR__.'/../translate/fr/error.fr.php';

function validateForgotPassword($login, $newPassword, $confirmPassword)
{
    $errors = [];
    
    if (empty($login)) {
        $errors[] = ErrorCode::EMAIL_REQUIRED->value;
    }
  
    if (empty($newPassword)) {
        $errors[] = ErrorCode::OLD_PASSWORD_REQUIRED->value;
    }
    
    if (empty($confirmPassword)) { 
        $errors[] = ErrorCode::NEW_PASSWORD_REQUIRED->value;
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = ErrorCode::PASSWORD_TRUE->value;
    }
    
    return $errors;
}