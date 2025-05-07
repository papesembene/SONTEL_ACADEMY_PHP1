<?php
// validator.service.php - Fonction validate_referentiel
namespace App\Services\Validation_referentiel;
require_once __DIR__.'/../enums.php';
use App\Enums\ErrorCode;
use App\Enums\Referentiel_Model_Key;

function validate_referentiel($data, $files, $id = null) {
    $errors = [];
   
    $translations = require __DIR__.'/../translate/fr/error.fr.php';
   
    if (empty($data['nom'])) {
        $errors['nom'][] = ErrorCode::REFERENTIEL_REQUIRED->value;
    } 
    elseif (strlen($data['nom']) > 100) {

        $errors['nom'][] = ErrorCode:: NOM_TROP_LONG->value ;
    }
    elseif (check_unique_name($data['nom'],$id)===false)
    {   
        $errors['nom'][] = ErrorCode:: NOM_EXISTE->value ;
        
    }
    
    if (empty($data['capacite'])) {
        $errors['capacite'] = ErrorCode::REQUIRED_FIELD->value;
    } elseif (!is_numeric($data['capacite']) || $data['capacite'] <= 0) {
        $errors['capacite'][] = 'La capacité doit être un nombre positif';
    }
    
    
    if (empty($data['sessions'])) {
        $errors['sessions'][]= ErrorCode::SESSIONS_OBLIGATOIRE->value;
    }
    
    if (($id === null || (isset($files['photo']) && $files['photo']['size'] > 0)) && isset($files['photo'])) {
        if ($files['photo']['error'] !== 0 && $id === null) {
            $errors['photo'][] = ErrorCode::PHOTO_REQUIRED->value;
        } elseif ($files['photo']['error'] === 0) {
                      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($files['photo']['type'], $allowed_types)) {
                $errors['photo'][] = ErrorCode::PHOTO_FORMAT->value;
            }
            if ($files['photo']['size'] > 2 * 1024 * 1024) {
                $errors['photo'][] = ErrorCode::PHOTO_SIZE->value;
            }
        }
    }
    
    return $errors;
}

function validateAssignRequest(array $postData): array {
    if (empty($postData['referentiels'])) {
        throw new \Exception("Veuillez sélectionner au moins un référentiel");
    }
    
    if (empty($postData['promotion_id'])) {
        throw new \Exception("Veuillez sélectionner une promotion");
    }
    
    return $postData;
}

function check_unique_name($nom, $id = null) {
    $model = require __DIR__ . '/../Models/Ref.Model.php';
    
    $existing = $model[\App\Enums\Referentiel_Model_Key::GET_BY_NAME->value]($nom);
   
    if (!$existing) return true;

    // Si ajout ($id est null), alors doublon interdit
    if ($id === null) return false;

    // Si modification, autorisé uniquement si c'est le même enregistrement
    return $existing['id'] === $id;
}