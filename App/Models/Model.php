<?php
// App/Models/Model.php

namespace App\Models;

require_once __DIR__.'/../enums.php';

use App\Enums\DataKey;
use App\Enums\ModelFunction;

global $fonctions_models;

$fonctions_models = [
    ModelFunction::GET_ALL->value => function(DataKey $key) {
        $file = __DIR__.'/../data/global.json';
        
        // Créer le fichier s'il n'existe pas
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([
                DataKey::PROMOTIONS->value => [],
                DataKey::USERS->value => [],
                DataKey::STUDENTS->value => [],
                DataKey::REFERENTIELS->value => [],
                DataKey::APPRENANTS->value => [],
                DataKey::WAITING_STUDENTS->value => []
            ]));
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Initialiser la clé si elle n'existe pas
        if (!isset($data[$key->value])) {
            $data[$key->value] = [];
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        }
        
        return $data[$key->value] ?? [];
    },

    ModelFunction::SAVE->value => function(DataKey $key, array $newData) {
        $file = __DIR__.'/../data/global.json';
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $data[$key->value] = $newData;
        $result =file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        if ($result === false) {
            error_log("Échec écriture fichier");
            throw new RuntimeException("Erreur d'écriture");
        }
        
        error_log("Sauvegarde réussie");
        return true;
    },
    ModelFunction::GET_NBR->value => function(DataKey $key) {
        $file = __DIR__.'/../data/global.json';
        
        
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([
                DataKey::PROMOTIONS->value => [],
                DataKey::USERS->value => [],
                DataKey::STUDENTS->value => []
            ]));
        }
        
        $data = json_decode(file_get_contents($file), true);
        return count($data[$key->value] ?? []);
    },
    ModelFunction::GET_BY_ID->value => function(DataKey $key, $id) {
        $file = __DIR__.'/../data/global.json';
        
        // Créer le fichier s'il n'existe pas
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([
                DataKey::PROMOTIONS->value => [],
                DataKey::USERS->value => [],
                DataKey::STUDENTS->value => []
            ]));
        }
        
        $data = json_decode(file_get_contents($file), true);
        return array_filter($data[$key->value] ?? [], function($item) use ($id) {
            return $item['id'] == $id;
        });
    },
    ModelFunction::DELETE->value => function(DataKey $key, $id) {
        $file = __DIR__.'/../data/global.json';
        
        // Créer le fichier s'il n'existe pas
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([
                DataKey::PROMOTIONS->value => [],
                DataKey::USERS->value => [],
                DataKey::STUDENTS->value => []
            ]));
        }
        
        $data = json_decode(file_get_contents($file), true);
        $data[$key->value] = array_filter($data[$key->value] ?? [], function($item) use ($id) {
            return $item['id'] != $id;
        });
        
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
];

return $fonctions_models;