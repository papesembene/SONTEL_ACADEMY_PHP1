<?php
namespace App\Models;
require_once __DIR__.'/../enums.php';

use App\Enums\DataKey;
use App\Enums\ModelFunction;
use App\Enums\Apprenant_Model_Key;
use App\Enums\ApprenantAttribute;

return [
    // Récupérer tous les apprenants
    Apprenant_Model_Key::GET_ALL->value => function() {
        global $fonctions_models;
        return $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
    },

    // Récupérer un apprenant par ID
    Apprenant_Model_Key::GET_BY_ID->value => function($matricule) {
        if (empty($matricule)) {
            return null;
        }
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        $resultats = array_filter($apprenants, function($apprenant) use ($matricule) {
            return $apprenant['matricule'] === $matricule;
        });
    
        $apprenant = reset($resultats);
        return $apprenant !== false ? $apprenant : null;
    },

   
    // Ajouter un nouvel apprenant
    Apprenant_Model_Key::ADD->value => function($newApprenant) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        $existingApprenant = array_filter($apprenants, function($apprenant) use ($newApprenant) {
            return $apprenant[ApprenantAttribute::EMAIL->value] === $newApprenant[ApprenantAttribute::EMAIL->value];
        });
        if (!empty($existingApprenant)) {
            return ['error' => 'L\'email existe déjà'];
        }
        $newApprenant[ApprenantAttribute::ID->value] = uniqid('app_');
        $apprenants[] = $newApprenant;
        $fonctions_models[ModelFunction::SAVE->value](DataKey::APPRENANTS, $apprenants);
        return $newApprenant;
    },


    // Mettre à jour un apprenant
    Apprenant_Model_Key::UPDATE->value => function($id, $data) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        $apprenants = array_map(function($apprenant) use ($id, $data) {
            if ($apprenant[ApprenantAttribute::ID->value] === $id) {
                $apprenant = array_merge($apprenant, $data);
            }
            return $apprenant;
        }, $apprenants);
        $fonctions_models[ModelFunction::SAVE->value](DataKey::APPRENANTS, $apprenants);
        return true;
    },

    // Supprimer un apprenant
    Apprenant_Model_Key::DELETE->value => function($id) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        $apprenants = array_filter($apprenants, function($apprenant) use ($id) {
            return $apprenant[ApprenantAttribute::ID->value] !== $id;
        });
        $fonctions_models[ModelFunction::SAVE->value](DataKey::APPRENANTS, $apprenants);
        return true;
    },

    // Récupérer les apprenants par promotion
    Apprenant_Model_Key::GET_BY_PROMOTION->value => function($promotionId) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        return array_filter($apprenants, function($apprenant) use ($promotionId) {
            return isset($apprenant[ApprenantAttribute::PROMOTION_ID->value]) &&
                   $apprenant[ApprenantAttribute::PROMOTION_ID->value] === $promotionId;
        });
    },

    // Récupérer les apprenants par référentiel
    Apprenant_Model_Key::GET_BY_REFERENTIEL->value => function($referentielId) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        return array_filter($apprenants, function($apprenant) use ($referentielId) {
            return isset($apprenant[ApprenantAttribute::REFERENTIEL_ID->value]) &&
                   $apprenant[ApprenantAttribute::REFERENTIEL_ID->value] === $referentielId;
        });
    },

    // Récupérer le nombre total d'apprenants
    Apprenant_Model_Key::GET_NBR->value => function() {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        return count($apprenants);
    },

    // Fonction pour récupérer tous les apprenants en attente
    Apprenant_Model_Key::GET_ALL_WAITING->value => function() {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS_WAITING);
        return $apprenants ?: [];
    },
    
    // Ajouter un apprenant à la liste d'attente
    Apprenant_Model_Key::ADD_TO_WAITING->value => function($data) {
        global $fonctions_models;
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        
        // Générer un ID unique pour l'apprenant en attente
        $data['waiting_id'] = uniqid('wait_');
        $data['import_date'] = date('Y-m-d H:i:s');
        
        $waitingStudents[] = $data;
        $fonctions_models[ModelFunction::SAVE->value](DataKey::WAITING_STUDENTS, $waitingStudents);
        return true;
    },
    
    // Fonction pour récupérer un apprenant en attente par son ID
    Apprenant_Model_Key::GET_WAITING_BY_ID->value => function($id) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS_WAITING);
        
        if (!$apprenants) {
            return null;
        }
        
        foreach ($apprenants as $apprenant) {
            if ($apprenant['id'] === $id) {
                return $apprenant;
            }
        }
        
        return null;
    },
    
    // Mettre à jour un apprenant en attente
    Apprenant_Model_Key::UPDATE_WAITING->value => function($id, $data) {
        global $fonctions_models;

        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS_WAITING);
        






        if (!$apprenants) {
            return false;
        }
        
        $updated = false;
        foreach ($apprenants as &$apprenant) {
            if ($apprenant['id'] === $id) {
                // Conserver les erreurs existantes
                $errors = $apprenant['errors'] ?? [];
                
                // Mettre à jour les données
                $apprenant = array_merge($apprenant, $data);
                
                // Restaurer les erreurs
                $apprenant['errors'] = $errors;
                
                $updated = true;
                break;
            }
        }
        


        if ($updated) {
            $fonctions_models[ModelFunction::SAVE->value](DataKey::APPRENANTS_WAITING, $apprenants);
            return true;
        }
        
        return false;
    },
    
    // Supprimer un apprenant de la liste d'attente
    Apprenant_Model_Key::REMOVE_FROM_WAITING->value => function($id) {
        global $fonctions_models;
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        
        $waitingStudents = array_filter($waitingStudents, function($student) use ($id) {
            return $student['waiting_id'] !== $id;
        });
        
        $fonctions_models[ModelFunction::SAVE->value](DataKey::WAITING_STUDENTS, $waitingStudents);
        return true;
    },
    
    // Compter le nombre d'apprenants en attente
    Apprenant_Model_Key::COUNT_WAITING->value => function() {
        global $fonctions_models;
        return count($fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS));
    },

    // Fonction pour ajouter un apprenant à la liste d'attente
    Apprenant_Model_Key::ADD_TO_WAITING_LIST->value => function($apprenantData, $errors) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS_WAITING);
        
        if (!$apprenants) {
            $apprenants = [];
        }
        
        $apprenantData['id'] = uniqid('wait_');
        $apprenantData['errors'] = $errors;
        $apprenantData['status'] = 'waiting';
        
        $apprenants[] = $apprenantData;
        $fonctions_models[ModelFunction::SAVE->value](DataKey::APPRENANTS_WAITING, $apprenants);
        return $apprenantData;
    },

    // Fonction pour récupérer les apprenants en attente
    Apprenant_Model_Key::GET_WAITING_LIST->value => function() {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS_WAITING);
        return $apprenants ?: [];
    },

    // Fonction pour supprimer un apprenant de la liste d'attente
    Apprenant_Model_Key::REMOVE_FROM_WAITING_LIST->value => function($id) {
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS_WAITING);
        
        if (!$apprenants) {
            return false;
        }
        
        $apprenants = array_filter($apprenants, function($apprenant) use ($id) {
            return $apprenant['id'] !== $id;
        });
        
        $fonctions_models[ModelFunction::SAVE->value](DataKey::APPRENANTS_WAITING, $apprenants);
        return true;
    },

    // Ajouter cette fonction au tableau de retour du modèle
    Apprenant_Model_Key::GET_BY_MATRICULE->value => function($matricule) {
        if (empty($matricule)) {
            return null;
        }
        global $fonctions_models;
        $apprenants = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::APPRENANTS);
        $resultats = array_filter($apprenants, function($apprenant) use ($matricule) {
            return isset($apprenant['matricule']) && $apprenant['matricule'] === $matricule;
        });

        $apprenant = reset($resultats);
        return $apprenant !== false ? $apprenant : null;
    }
];
