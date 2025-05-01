<?php
namespace App\Services\Validation_importation;

require_once __DIR__.'/../enums.php';
use App\Enums\ErrorCode;
use App\Enums\UserModelKey;

function validate_import_file($file) {
    $errors = [];
    
    // Vérifier si un fichier a été téléchargé
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors['file'] = ErrorCode::REQUIRED_FILE->value;
        return $errors;
    }
    // Vérifier le type de fichier (CSV ou XLSX uniquement)
    $file_type = $file['type'];
    $valid_types = [
        'text/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ]; 
    if (!in_array($file_type, $valid_types)) {
        $errors['file_type'] = ErrorCode::INVALID_FILE_TYPE_IMPORT->value;
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        $errors['file_size'] = ErrorCode::FILE_TOO_LARGE_IMPORT->value;
    }
    
    // Vérifier l'extension du fichier
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
        $errors['file_extension'] = ErrorCode::INVALID_FILE_EXTENSION->value;
    }
    
    return $errors;
}

function validate_import_data($data) {
    $errors = [];
    $row_errors = [];
    $existing_emails = [];
    $emails_in_file = [];
    // Récupérer les emails existants
    $userModel = require __DIR__ . '/../Models/User.model.php';
    $existing_emails = $userModel[UserModelKey::GET_ALL_EMAILS->value]();

    
    foreach ($data as $index => $row) {
        $row_number = $index + 2; 
        $row_error = [];
        
       
        $required_fields = [
            'nom', 'prenom', 'email', 'date_naissance', 
            'adresse', 'telephone', 'referentiel_id'
        ];
        
        foreach ($required_fields as $field) {
            if (!isset($row[$field]) || empty(trim($row[$field]))) {
                $row_error[$field] = ErrorCode::REQUIRED_FIELD->value;
            }
        }
        
        // Validation du format email
        if (isset($row['email']) && !empty($row['email'])) {
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $row_error['email'] = ErrorCode::INVALID_EMAIL->value;
            }
            elseif (in_array($row['email'], $emails_in_file)) {
                $row_error['email'] = "Email dupliqué dans le fichier";
            }
            // Vérifier unicité dans la base
            elseif (in_array($row['email'], $existing_emails)) {
                $row_error['email'] = "Email existe déjà en base de données";
            }
            else {
                $emails_in_file[] = $row['email'];
            }
        }
        
        // Validation du format téléphone
        if (isset($row['telephone']) && !empty($row['telephone'])) {
            if (!preg_match('/^(76|77|78|75|70)[0-9]{7}$/', $row['telephone'])) {
                $row_error['telephone'] = ErrorCode::INVALID_PHONE->value;
            }
        }

        if (isset($row['date_naissance']) && !empty($row['date_naissance'])) {
            $date_format = '/^\d{4}-\d{2}-\d{2}$/';
            if (!preg_match($date_format, $row['date_naissance'])) {
                $row_error['date_naissance'] = "Format de date invalide (YYYY-MM-DD requis)";
            }
        }

        if (isset($row['referentiel_id']) && !empty($row['referentiel_id'])) {
            $referentielModel = require __DIR__ . '/../Models/Ref.Model.php';
            if (!$referentielModel['exists']($row['referentiel_id'])) {
                $row_error['referentiel_id'] = "Référentiel inexistant";
            }
        }
        
        if (!empty($row_error)) {
            $row_errors[$row_number] = $row_error;
        }
    }
    
    if (!empty($row_errors)) {
        $errors['rows'] = $row_errors;
    }
    
    return $errors;
}
?>