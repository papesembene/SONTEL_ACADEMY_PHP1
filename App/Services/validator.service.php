<?php
namespace App\Services\Validate_promotion;

require_once __DIR__.'/../enums.php';
use App\Enums\ErrorCode;
use App\Enums\PromotionAttribute;
use App\Enums\Promotion_Model_Key;
use App\Enums\ModelFunction;
use App\Enums\DataKey;
use DateTime;

function validate_promotion(array $data, array $files) {
    $errors = [];
    $translations = require __DIR__.'/../translate/fr/error.fr.php';

    $validation_rules = [
        'nom' => [
            'required' => [
                'message' => ErrorCode::REQUIRED_FIELD,
                'params' => ['Nom de la promotion']
            ],
            'unique' => [
                'message' => ErrorCode::UNIQUE_NAME
            ]
        ],
        'datedebut' => [
            'required' => [
                'message' => ErrorCode::REQUIRED_FIELD,
                'params' => ['Date de début']
            ],
            'date_format' => [
                'format' => 'd/m/Y',
                'message' => ErrorCode::INVALID_DATE
            ]
        ],
        'datefin' => [
            'required' => [
                'message' => ErrorCode::REQUIRED_FIELD,
                'params' => ['Date de fin']
            ],
            'date_format' => [
                'format' => 'd/m/Y',
                'message' => ErrorCode::INVALID_DATE
            ],
            'after_start_date' => [
                'message' => ErrorCode::DATE_FIN_BEFORE_DEBUT
            ]
        ],
        'photo' => [
            'file_required' => [
                'message' => ErrorCode::PHOTO_REQUIRED
            ],
            'file_type' => [
                'allowed' => ['image/jpeg', 'image/png'],
                'message' => ErrorCode::PHOTO_FORMAT
            ],
            'file_size' => [
                'max' => 2 * 1024 * 1024,
                'message' => ErrorCode::PHOTO_SIZE
            ]
        ],
        'referentiels' => [
            'required' => [
                'message' => ErrorCode::REQUIRED_FIELD,
                'params' => ['Référentiels']
            ],
            'min_count' => [
                'min' => 1,
                'message' => ErrorCode::MIN_REFERENTIELS
            ]
        ]
    ];

    foreach ($validation_rules as $field => $rules) {
        // Récupération correcte de la valeur selon le type de champ
        $value = $field === 'photo' ? ($files[$field] ?? null) : ($data[$field] ?? null);
        
        foreach ($rules as $rule_type => $rule) {
            $valid = true;
            $message_params = $rule['params'] ?? [];
            
            switch ($rule_type) {
                case 'required':
                    if ($field === 'photo') {
                        $valid = !empty($value['tmp_name']);
                    } elseif (is_array($value)) {
                        $valid = !empty($value);
                    } else {
                        $valid = !empty(trim($value ?? ''));
                    }
                    break;
                    
                case 'unique':
                    $valid = check_unique_name($value,$id);
                    $message_params = [$value]; 
                    break;
                    
                case 'date_format':
                    $date = DateTime::createFromFormat($rule['format'], $value);
                    $valid = $date && $date->format($rule['format']) === $value;
                    break;
                    
                case 'after_start_date':
                    if (!empty($data['datedebut']) && !empty($data['datefin'])) {
                        $dateDebut = DateTime::createFromFormat('d/m/Y', $data['datedebut']);
                        $dateFin = DateTime::createFromFormat('d/m/Y', $data['datefin']);
                        $valid = $dateFin > $dateDebut;
                    }
                    break;
                    
                case 'file_type':
                    $valid = !empty($value['tmp_name']) && 
                             in_array(mime_content_type($value['tmp_name']), $rule['allowed']);
                    break;
                    
                case 'file_size':
                    $valid = !empty($value['tmp_name']) && 
                             $value['size'] <= $rule['max'];
                    break;
                    
                case 'min_count':
                    $count = is_array($value) ? count($value) : 0;
                    $valid = $count >= $rule['min'];
                    $message_params = [$rule['min']];
                    break;
            }

            if (!$valid) {
                $errors[$field][] = vsprintf(
                    $translations[$rule['message']->value],
                    $message_params
                );
                break;
            }
        }
    }
    
    return $errors;
}

function check_unique_name($nom, $id = null) {
    $model = require __DIR__ . '/../Models/Promo.model.php';
    
    $existing = $model[\App\Enums\Promotion_Model_Key::GET_BY_NAME->value]($nom);
    
    if (!$existing) return true;

    // Si ajout ($id est null), alors doublon interdit
    if ($id === null) return false;

    // Si modification, autorisé uniquement si c'est le même enregistrement
    return $existing['id'] === $id;
}

