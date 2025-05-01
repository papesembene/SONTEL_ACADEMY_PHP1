<?php
// error.fr.php content
namespace App\Translate\Fr;
use App\Enums\ErrorCode;


return [
    ErrorCode::REQUIRED_FIELD->value => 'Le champ %s est obligatoire',
    ErrorCode::UNIQUE_NAME->value => 'Le nom "%s" existe déjà',
    ErrorCode::INVALID_DATE->value => 'Format de date invalide (jj/mm/aaaa requis)',
    ErrorCode::PHOTO_REQUIRED->value => 'La photo est obligatoire',
    ErrorCode::PHOTO_FORMAT->value => 'Format d\'image invalide (JPG ou PNG uniquement)',
    ErrorCode::PHOTO_SIZE->value => 'La taille de l\'image ne doit pas dépasser 2Mo',
    ErrorCode::NOM_TROP_LONG->value => 'Le nom ne doit pas dépasser 100 caractères',
    ErrorCode::SESSIONS_OBLIGATOIRE->value => 'Le nombre de sessions est obligatoire',
    ErrorCode::REFERENTIEL_REQUIRED->value => 'Referentiel  obligatoire',
    ErrorCode::DATE_FIN_BEFORE_DEBUT->value => 'La date de fin doit être postérieure à la date de début',
    ErrorCode::MIN_REFERENTIELS->value => 'Au moins un référentiel est requis',
    ErrorCode::EMAIL_REQUIRED->value => 'L\'email est requis',
    ErrorCode::NEW_PASSWORD_REQUIRED->value  =>  'Le nouveau mot de passe est requis',
    ErrorCode::OLD_PASSWORD_REQUIRED->value =>  'L\'ancien mot de passe est requis',
    ErrorCode::PASSWORD_TRUE->value => 'Les mots de passe ne correspondent pas',
];




