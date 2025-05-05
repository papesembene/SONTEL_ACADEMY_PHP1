<?php
// App/Models/User.model.php

namespace App\Models\UserModel;

require_once __DIR__.'/../enums.php';
require_once __DIR__.'/Model.php';
use App\Models;
use App\Enums\DataKey;
use App\Enums\ModelFunction;
use App\Enums\UserModelKey;

return [
    UserModelKey::AUTHENTICATE->value => function(string $login, string $password) {
        $model = require __DIR__.'/Model.php';
        
        if (!isset($model[ModelFunction::GET_ALL->value])) {
            throw new \RuntimeException('Fonction GET_ALL non trouvée');
        }
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
        
        
        $filtered = array_filter($users, function($u) use ($login) {
            return isset($u['matricule'], $u['email'], $u['password']) 
                && ($u['matricule'] === $login || $u['email'] === $login);
        });

        if ($user = reset($filtered)) {
            return ($password === $user['password']) ? $user : null;
            // return password_verify($password, $user['password']) ? $user : null;
        }
        
        return null;
    },

    UserModelKey::GET_BY_ID->value => function(string $id) {
        $model = require __DIR__.'/Model.php';
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
        
        return array_reduce($users, function($found, $user) use ($id) {
            return $found ?? ((isset($user['id']) && $user['id'] === $id) ? $user : null);
        });
    },
   

    UserModelKey::UPDATE_PASSWORD->value => function($userId, $newPassword) {
        $model = require __DIR__.'/Model.php';
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
    
    
        $user = array_reduce($users, function ($found, $u) use ($userId) {
            return $found ?? (($u['id'] === $userId) ? $u : null);
        });
    
        if (!$user) {
            error_log("Utilisateur non trouvé: $userId");
            return false;
        }
    
        $userIndex = array_search($userId, array_column($users, 'id'));
    
        if ($userIndex === false) {
            error_log("Index utilisateur non trouvé: $userId");
            return false;
        }
        $users[$userIndex]['password'] = $newPassword;
        $users[$userIndex]['password_change_required'] = false;
    
        
    
        return $model[ModelFunction::SAVE->value](DataKey::USERS, $users);
    },
    
    UserModelKey::ADD->value =>function($user){
        $model = require __DIR__.'/Model.php';
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
        
        if (!isset($user['id'])) {
            $user['id'] = uniqid();
        }
        if (!isset($userData['password_change_required'])) {
            $userData['password_change_required'] = true;
        }
        $users[] = $user;
        $model[ModelFunction::SAVE->value](DataKey::USERS, $users);
        return $user;
    },

    UserModelKey::UPDATE_PASSWORD->value => function($userId, $newPassword) {
        $model = require __DIR__.'/Model.php';
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
        
        $user = array_reduce($users, function ($found, $u) use ($userId) {
            return $found ?? (($u['id'] === $userId) ? $u : null);
        });
        
        if (!$user) {
            error_log("Utilisateur non trouvé: $userId");
            return false;
        }
        if ($newPassword === "SON@TEL2025") {
            error_log("Tentative d'utiliser le mot de passe par défaut");
            return false;
        }
        
        if (isset($user['password']) && $user['password'] === $newPassword) {
            error_log("Tentative d'utiliser le même mot de passe");
            return false;
        }
        
        $userIndex = array_search($userId, array_column($users, 'id'));
        if ($userIndex === false) {
            error_log("Index utilisateur non trouvé: $userId");
            return false;
        }
        $users[$userIndex]['password'] = $newPassword;
        
        if (isset($users[$userIndex]['role']) && $users[$userIndex]['role'] === 'apprenant') {
            $users[$userIndex]['password_change_required'] = false;
        }
        
        return $model[ModelFunction::SAVE->value](DataKey::USERS, $users);
    },
    UserModelKey::GET_ALL_EMAILS->value => function() {
        $model = require __DIR__.'/Model.php';
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
        
        return array_filter(array_column($users, 'email'));
    },
    // Ajoutez cette méthode à la fin de votre tableau de fonctions dans UserModel.php

    UserModelKey::FIND_BY_EMAIL->value => function(string $email) {
        $model = require __DIR__.'/Model.php';
        $users = $model[ModelFunction::GET_ALL->value](DataKey::USERS);
        
        // Recherche d'un utilisateur avec l'email donné
        return array_reduce($users, function($found, $user) use ($email) {
            return $found ?? ((isset($user['email']) && $user['email'] === $email) ? $user : null);
        });
    },

 ];