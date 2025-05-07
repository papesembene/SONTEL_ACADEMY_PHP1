<?php
namespace App\Models;
require_once __DIR__.'/../enums.php';

use App\Enums\DataKey;
use App\Enums\ModelFunction;
use App\Enums\ApprenantAttente_Model_Key;
use App\Enums\Apprenant_Model_Key;

// Assurez-vous que cette variable est accessible
global $fonctions_models;

// Créez un tableau de fonctions à retourner
$attenteModel = [
    // Récupérer tous les apprenants en attente
    ApprenantAttente_Model_Key::GET_ALL->value => function() {
        global $fonctions_models;
        $data = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        return is_array($data) ? $data : [];
    },
    
    // Ajouter un apprenant à la liste d'attente
    ApprenantAttente_Model_Key::CREATE->value => function($data) {
        global $fonctions_models;
        
        // Vérifier que $fonctions_models est bien défini
        if (!isset($fonctions_models) || !is_array($fonctions_models)) {
            error_log('Erreur: $fonctions_models n\'est pas défini ou n\'est pas un tableau');
            return false;
        }
        
        // Vérifier que la fonction GET_ALL existe
        if (!isset($fonctions_models[ModelFunction::GET_ALL->value]) || !is_callable($fonctions_models[ModelFunction::GET_ALL->value])) {
            error_log('Erreur: La fonction GET_ALL n\'existe pas dans $fonctions_models');
            return false;
        }
        
        // Récupérer les apprenants en attente
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        
        // Vérifier que $waitingStudents est bien un tableau
        if (!is_array($waitingStudents)) {
            $waitingStudents = [];
        }
        
        // Générer un ID unique pour l'apprenant en attente
        $data['waiting_id'] = uniqid('wait_');
        $data['import_date'] = date('Y-m-d H:i:s');
        
        // Ajouter l'apprenant en attente
        $waitingStudents[] = $data;
        
        // Vérifier que la fonction SAVE existe
        if (!isset($fonctions_models[ModelFunction::SAVE->value]) || !is_callable($fonctions_models[ModelFunction::SAVE->value])) {
            error_log('Erreur: La fonction SAVE n\'existe pas dans $fonctions_models');
            return false;
        }
        
        // Sauvegarder les apprenants en attente
        $fonctions_models[ModelFunction::SAVE->value](DataKey::WAITING_STUDENTS, $waitingStudents);
        return true;
    },
    
    // Récupérer un apprenant en attente par ID
    ApprenantAttente_Model_Key::GET_BY_ID->value => function($id) {
        global $fonctions_models;
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        
        if (!is_array($waitingStudents)) {
            return null;
        }
        
        foreach ($waitingStudents as $student) {
            if (isset($student['waiting_id']) && $student['waiting_id'] === $id) {
                return $student;
            }
        }
        
        return null;
    },
    
    // Mettre à jour un apprenant en attente
    ApprenantAttente_Model_Key::UPDATE->value => function($id, $data) {
        global $fonctions_models;
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        
        if (!is_array($waitingStudents)) {
            return false;
        }
        
        $updated = false;
        foreach ($waitingStudents as $key => $student) {
            if (isset($student['waiting_id']) && $student['waiting_id'] === $id) {
                // Préserver l'ID et la date d'importation
                $data['waiting_id'] = $id;
                $data['import_date'] = $student['import_date'] ?? date('Y-m-d H:i:s');
                $waitingStudents[$key] = $data;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $fonctions_models[ModelFunction::SAVE->value](DataKey::WAITING_STUDENTS, $waitingStudents);
            return true;
        }
        
        return false;
    },
    
    // Supprimer un apprenant de la liste d'attente
    ApprenantAttente_Model_Key::DELETE->value => function($id) {
        global $fonctions_models;
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        
        if (!is_array($waitingStudents)) {
            return false;
        }
        
        $waitingStudents = array_filter($waitingStudents, function($student) use ($id) {
            return !isset($student['waiting_id']) || $student['waiting_id'] !== $id;
        });
        
        $fonctions_models[ModelFunction::SAVE->value](DataKey::WAITING_STUDENTS, $waitingStudents);
        return true;
    },
    
    // Compter le nombre d'apprenants en attente
    ApprenantAttente_Model_Key::COUNT->value => function() {
        global $fonctions_models;
        $waitingStudents = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::WAITING_STUDENTS);
        return is_array($waitingStudents) ? count($waitingStudents) : 0;
    },
    
    // Valider un apprenant en attente et le déplacer vers la liste principale
    ApprenantAttente_Model_Key::VALIDATE->value => function($id, $data) {
        global $fonctions_models;
        
        // Charger le modèle des apprenants
        $apprenantModel = require_once __DIR__.'/ApprenantModel.php';
        
        // Créer l'apprenant dans la liste principale
        $result = $apprenantModel[Apprenant_Model_Key::CREATE->value]($data);
        
        if ($result) {
            // Supprimer de la liste d'attente
            $deleteResult = $fonctions_models[ApprenantAttente_Model_Key::DELETE->value]($id);
            return $deleteResult;
        }
        
        return false;
    }
];

// Retourner le tableau de fonctions
return $attenteModel;