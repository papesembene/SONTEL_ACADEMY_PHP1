<?php
// ReferentielController.php

namespace App\Controllers\Referentiels;

use App\Controllers as App;
use App\Models;
use App\Enums\Referentiel_Model_Key;
use App\Enums\ReferentielAttribute;
use App\Enums\SuccessCode;
use App\Enums\ModelFunction;
use App\Enums\DataKey;
use App\Services\Session as Session;
use App\Services\Validation_referentiel as valide;
use Exception;
use RuntimeException;
use App\Enums\ErrorCode;
use App\Controllers\Promotions;
use App\Enums\Promotion_Model_Key;
use App\Enums\Apprenant_Model_Key;
require_once __DIR__.'/../Services/service.validate_ref.php';
    function handleReferentielActions() {
        session_init();
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') 
            {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'create':
                        createReferentiel();
                        break;
                    default:
                        throw new Exception('Action POST non valide');
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) 
            {
                $action = $_GET['action'];
                
                switch ($action) 
                {
                    default:
                    get_all_referentiels();
                }
            }
        } catch (Exception $e) 
        {
            session_set('error_message', $e->getMessage());
        }
    }

    function createReferentiel() 
    {
        try {
        
            $chemin_model = __DIR__.'/../Models/Ref.Model.php';
            if (!file_exists($chemin_model)) {
                die("Fichier modèle introuvable : $chemin_model");
            }
            $model = require $chemin_model;
            $data = $_POST;
            $files = $_FILES;
            $errors = valide\validate_referentiel($data, $files);
            if (!empty($errors)) 
            {
                session_set('validation_errors', $errors);
                session_set('old_input', $data);
                App\redirect('/referentiels?action=create');
                exit;
            }
            
            $photo_path = '';
            if (isset($files['photo']) && $files['photo']['size'] > 0) 
            {
                // echo "photo bi: " . $files['photo']['name'];
                $photo_path = handle_file_upload($files['photo']);
                // echo "<br>file  " . $photo_path;
            }  
            $new_referentiel = [
                'nom' => $data['nom'],
                'description' => $data['description'] ?? '',
                'photo' => $photo_path,
                'capacite' => $data['capacite'],
                'sessions' => $data['sessions'],
                'nbr_modules' => $data['nbr_modules'] ?? 0,
                'nbr_apprenants' => $data['nbr_apprenants'] ?? 0,
            ];
            $resultat = $model[Referentiel_Model_Key::ADD->value]($new_referentiel); 
            session_remove('validation_errors');
            session_remove('old_input');
            if (!session_has('success_message')) {
            session_set('success_message', ['content' => SuccessCode::REFERENTIEL_CREATED->value]);
                App\redirect('/referentiels');
            } else {
                error_log('déjà enregistre');
            }    
            // echo "<p> c bon .</p>";   
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage();
            session_set('error_message', $e->getMessage());
            App\redirect('/referentiels?action=create');
        }
    }

    function get_all_referentiels($search = null) 
    {
        $model = require __DIR__.'/../Models/Ref.Model.php';
        $referentiels = $model[Referentiel_Model_Key::GET_ALL->value]();
    
        
        if (!empty($search)) 
        {
            $search = strtolower(trim($search));
            $referentiels = array_filter($referentiels, function($ref) use ($search) {
                return (isset($ref['nom']) && strpos(strtolower($ref['nom']), $search) !== false) ||
                       (isset($ref['description']) && strpos(strtolower($ref['description']), $search) !== false);
            });
        }
    
        return array_values($referentiels); 
    }
    
    function get_nbr_referentiels() {
        global $fonctions_models;
        return $fonctions_models[ModelFunction::GET_NBR->value](DataKey::REFERENTIELS);
    }

    function get_referentiel_by_id($id) {
        $model = require __DIR__.'/../Models/Ref.Model.php';
        $referentiels = $model[Referentiel_Model_Key::GET_BY_ID->value]($id);
        
        return !empty($referentiels) ? reset($referentiels) : null;
    }

    function get_referentiels_by_active_promotion() {
        require_once __DIR__.'/../Controllers/PromoController.php';
        $activePromotion = \App\Controllers\Promotions\get_active_promotion(); 
        if (!$activePromotion) {
            return [];
        }
        $allReferentiels = get_all_referentiels();
        $filteredReferentiels = array_filter($allReferentiels, function($referentiel) use ($activePromotion) {
            if (isset($activePromotion['referentiels']) && is_array($activePromotion['referentiels'])) {
                return in_array($referentiel['id'], $activePromotion['referentiels']);
            }
            return false;
        }); 
        return array_values($filteredReferentiels);
    }
    function assigner_referentiels() {
        $promotion_id = $_POST['promotion_id'] ?? null;
        $referentiels_selectionnes = $_POST['referentiels'] ?? [];

        if (!$promotion_id) {
            session_set('error_message', ErrorCode::PROMOTION_NON_SPECIFIEE->value);
            App\redirect('/referentiels?action=assign');
            return;
        }

        try {
            $promotion = get_promo_by_id($promotion_id);
            verifier_promotion_encours($promotion);

            $referentiels_actuels = $promotion['referentiels'] ?? [];
            $referentiels_a_retirer = obtenir_referentiels_a_retirer($referentiels_actuels, $referentiels_selectionnes);

            verifier_referentiels_vides($referentiels_a_retirer);

            $referentiels_mis_a_jour = mettre_a_jour_referentiels($referentiels_selectionnes);
            update_promotion($promotion_id, $referentiels_mis_a_jour);

            session_set('success_message_assignation', SuccessCode::REFERENTIEL_ASSIGNE->value);
        } catch (Exception $e) {
            session_set('error_message', $e->getMessage());
        }

        App\redirect('/referentiels?action=assign');
    }
    function get_promo_by_id($promotion_id) {
        $model = require __DIR__ . '/../Models/Promo.model.php';
        $promotion = $model[Promotion_Model_Key::GET_BY_ID->value]($promotion_id);
    
        if (!$promotion) {
            throw new Exception(ErrorCode::PROMOTION_INTRROUVABLE->value);
        }
    
        return $promotion;
    }
    function verifier_promotion_encours($promotion) {
        if (strtotime($promotion['date_fin']) < time()) {
            throw new Exception(ErrorCode::PROMOTION_TERMINEE->value);
        }
    }
    function verifier_referentiels_vides($referentiels_a_retirer) {
        $apprenant_model = require __DIR__ . '/../Models/ApprenantModel.php';
    
        foreach ($referentiels_a_retirer as $referentiel_id) {
            $apprenants = $apprenant_model[Apprenant_Model_Key::GET_BY_REFERENTIEL->value]($referentiel_id);
            if (!empty($apprenants)) {
                throw new Exception(ErrorCode::REFERENTIEL_AVEC_APPRENANTS->value);
            }
        }
    }
    function mettre_a_jour_referentiels($referentiels_selectionnes) {
        return array_values($referentiels_selectionnes);
    }

    function obtenir_referentiels_a_retirer($referentiels_actuels, $referentiels_selectionnes) {
        return array_diff($referentiels_actuels, $referentiels_selectionnes);
    }
        
    function getPromotionWithValidation(string $promotionId): array {
        $promoModel = require __DIR__.'/../Models/Promo.model.php';
        $promotion = $promoModel[Promotion_Model_Key::GET_BY_ID->value]($promotionId);
        
        if (!$promotion) {
            throw new Exception("La promotion sélectionnée n'existe pas");
        }
        
        return $promotion;
    }
    function update_promotion($promotion_id, $referentiels_mis_a_jour) {
        $model = require __DIR__ . '/../Models/Promo.model.php';
    
        $promotion = ['referentiels' => $referentiels_mis_a_jour];
        $resultat = $model[Promotion_Model_Key::UPDATE->value]($promotion_id, $promotion);
    
        if (!$resultat) {
            throw new Exception(ErrorCode::ERREUR_MISE_A_JOUR_REFERENTIELS->value);
        }
    }
    
    function filterValidReferentiels(array $selectedRefs, array $existingRefs): array {
        $referentielModel = require __DIR__.'/../Models/Ref.Model.php';
        $allReferentiels = $referentielModel[Referentiel_Model_Key::GET_ALL->value]();
        $validRefs = [];
        
        foreach ($selectedRefs as $refId) {
            if (isReferentielValid($refId, $allReferentiels) && !isReferentielAlreadyAssigned($refId, $existingRefs)) {
                $validRefs[] = $refId;
            }
        }
        
        if (empty($validRefs)) {
            throw new Exception("Aucun nouveau référentiel valide à ajouter");
        }
        
        return $validRefs;
    }
    
    function isReferentielValid(string $refId, array $allReferentiels): bool {
        foreach ($allReferentiels as $ref) {
            if ($ref['id'] === $refId) {
                return true;
            }
        }
        return false;
    }
    
    
    function isReferentielAlreadyAssigned(string $refId, array $existingRefs): bool {
        return in_array($refId, $existingRefs);
    }
    
    function updatePromotionReferentiels(string $promotionId, array $existingRefs, array $newRefs): void {
        $promoModel = require __DIR__.'/../Models/Promo.model.php';
        $updatedRefs = array_merge($existingRefs, $newRefs);
        
        $success = $promoModel[Promotion_Model_Key::UPDATE->value](
            $promotionId,
            ['referentiels' => $updatedRefs]
        );
        
        if (!$success) {
            throw new Exception("Erreur lors de la mise à jour de la promotion");
        }
    }
    
    function handle_file_upload($file) {
        $upload_dir = __DIR__.'/../../public/uploads/referentiels/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = uniqid('ref_').'.'.pathinfo($file['name'], PATHINFO_EXTENSION);
        $target_path = $upload_dir.$filename;
        
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new RuntimeException('Erreur lors du téléchargement du fichier');
        }
        
        return $filename;
    }

   
    function get_non_assigned_referentiels($promotion_id = null) 
    {
        if ($promotion_id === null) {
            require_once __DIR__.'/../Controllers/PromoController.php';
            $activePromotion = \App\Controllers\Promotions\get_active_promotion();
            $promotion_id = $activePromotion ? $activePromotion['id'] : null;
        }
        if (!$promotion_id) {
            return get_all_referentiels();
        }
        $allReferentiels = get_all_referentiels();
        $promoModel = require __DIR__.'/../Models/Promo.model.php';
        $promotion = $promoModel[\App\Enums\Promotion_Model_Key::GET_BY_ID->value]($promotion_id);

        if (!$promotion || !isset($promotion['referentiels']) || !is_array($promotion['referentiels'])) {
            return $allReferentiels;
        }
        $nonAssignedReferentiels = array_filter($allReferentiels, function($referentiel) use ($promotion) {
            return !in_array($referentiel['id'], $promotion['referentiels']);
        });
        
        return array_values($nonAssignedReferentiels);
    }

    function get_active_promotion_referentiel_count() 
    {
        $model = require __DIR__ . '/../Models/Promo.model.php';
        $activePromotion = $model[Promotion_Model_Key::GET_ACTIVE_PROMOTION->value]();
        if (!$activePromotion || !isset($activePromotion['referentiels'])) {
            return 0; 
        }
        var_dump(count($activePromotion['referentiels']));
        return count($activePromotion['referentiels']);
    }

  
    function get_referentiels_by_ids(array $ids) {
        $model = require __DIR__.'/../Models/Ref.Model.php';
        $allReferentiels = $model[\App\Enums\Referentiel_Model_Key::GET_ALL->value]();
        return array_filter($allReferentiels, function($referentiel) use ($ids) {
            return in_array($referentiel['id'], $ids);
        });
    }
