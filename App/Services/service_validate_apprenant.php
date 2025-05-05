<?php
namespace App\Services\Validation_apprenant;

require_once __DIR__.'/../enums.php';
use App\Enums\ErrorCode;
use App\Enums\ApprenantAttribute;

function validate_apprenant($data, $files, $id = null) {
    $errors = [];
    
    // Champs obligatoires
    $required_fields = [
        'prenom', 'nom', 'date_naissance', 'lieu_naissance', 
        'adresse', 'email', 'telephone', 'referentiel_id'
    ];
    
    // Champs optionnels
    $optional_fields = [
        ApprenantAttribute::TUTEUR_NAME->value, 
        ApprenantAttribute::TUTEUR_PHONE->value,
        ApprenantAttribute::TUTEUR_ADDRESS->value,
        ApprenantAttribute::TUTEUR_RELATION->value
    ];
    
    $valid_phone_pattern = '/^(76|77|78|75|70)[0-9]{7}$/'; // Numéro de téléphone valide
    $valid_date_pattern = '/^\d{4}-\d{2}-\d{2}$/';
    $valid_file_types = ['image/jpeg', 'image/png', 'image/gif'];
    $valid_file_size = 2 * 1024 * 1024; // 2MB
    $max_name_length = 50;
    $max_address_length = 100;

    // Validation des champs obligatoires
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[$field] = ErrorCode::REQUIRED_FIELD->value;
        }
    }

    // Validation des champs optionnels
    foreach ($optional_fields as $field) {
        if (isset($data[$field]) && !empty(trim($data[$field]))) {
            $max_length = match ($field) {
                ApprenantAttribute::TUTEUR_NAME->value => 50,
                ApprenantAttribute::TUTEUR_PHONE->value => 15,
                ApprenantAttribute::TUTEUR_ADDRESS->value => 100,
                ApprenantAttribute::TUTEUR_RELATION->value => 30,
                default => 100
            };
            
            if (strlen($data[$field]) > $max_length) {
                $errors[$field] = "Ce champ ne doit pas dépasser $max_length caractères";
            }
        }
    }

    // Validation du format de téléphone
    if (isset($data['telephone']) && !empty($data['telephone'])) {
        if (!preg_match($valid_phone_pattern, $data['telephone'])) {
            $errors['telephone'] = "Format de téléphone invalide (doit commencer par 76, 77, 78, 75 ou 70 et avoir 9 chiffres)";
        }
    }

    // Validation de l'email
    if (isset($data['email']) && !empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format d'email invalide";
        }
    }

    // Validation de la photo
    if (($id === null || (isset($files['photo']) && $files['photo']['size'] > 0)) && isset($files['photo'])) {
        if ($files['photo']['error'] !== 0 && $id === null) {
            $errors['photo'] = ErrorCode::PHOTO_REQUIRED->value;
        } elseif ($files['photo']['error'] === 0) {
            if (!in_array($files['photo']['type'], $valid_file_types)) {
                $errors['photo'] = ErrorCode::PHOTO_FORMAT->value;
            }
            if ($files['photo']['size'] > $valid_file_size) {
                $errors['photo'] = ErrorCode::PHOTO_SIZE->value;
            }
        }
    }

    return $errors;
}
