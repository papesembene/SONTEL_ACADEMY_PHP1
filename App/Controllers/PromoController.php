<?php
// PromotionController.php

namespace App\Controllers\Promotions;
use App\Controllers as App;
use App\Models;
use App\Enums\PromotionAttribute;
use App\Enums\Promotion_Model_Key;
use App\Enums\SuccessCode;
use App\Enums\ModelFunction;
use App\Enums\DataKey;
use App\Services\Session as Session;
use App\Services\Validate_promotion as validation;
use App\Enums\Referentiel_Model_Key;
use DateTime;

$model = require __DIR__.'/../Models/Promo.model.php';
require_once __DIR__.'/../Services/validator.service.php';

function handlePromotionActions() 
{
    session_init();
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'create':
                    createPromotion();
                    return App\redirect('/promotions');
                
                case 'update':
                    updatePromotion();
                    return App\redirect('/promotions');
                
                case 'delete':
                    deletePromotion();
                    return App\redirect('/promotions');
                
                default:
                    throw new \Exception('Action POST non valide');
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
                togglePromotionStatus();
                return;
            }
            
            // Préparer les données communes
            $action = $_GET['action'] ?? 'list';
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $itemsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 5;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            $viewMode = isset($_GET['view']) ? $_GET['view'] : 'grid';
            
            $result = get_promotions_data($status, $search, $currentPage, $itemsPerPage);
            $promos = $result['promotions'];
            $pagination = $result['pagination'];
            
            $nbPromos = $pagination['total_results'] ?? count($promos); 
            $totalItems = $pagination['total_results'] ?? 0;
            $totalPages = $pagination['total_pages'] ?? 1; 
            $startIndex = $pagination['from'] ?? 0; 
            $endIndex = $pagination['to'] ?? 0; 
            
            $referentiels = \App\Controllers\Referentiels\get_all_referentiels();
            $promotionActive = get_active_promotion();
            
            $referentielMap = array_column($referentiels, 'nom', 'id');
            $errors = session_get('validation_errors', []);
            $oldInput = session_get('old_input', []);
            
            if (isset($_GET['action']) && $_GET['action'] === 'add') {
                $oldInput = session_get('old_input', []);
                return App\render_with_layout(
                    'promotions/index', 
                    'Ajouter une promotion', 
                    'promotions',
                    [
                        'action' => $_GET['action'],
                        'currentPage' => $currentPage,
                        'itemsPerPage' => $itemsPerPage,
                        'status' => $status,
                        'search' => $search,
                        'viewMode' => $viewMode,
                        'promos' => $promos,
                        'pagination' => $pagination,
                        'nbPromos' => $nbPromos,
                        'totalItems' => $totalItems,
                        'totalPages' => $totalPages,
                        'startIndex' => $startIndex,
                        'endIndex' => $endIndex,
                        'referentiels' => \App\Controllers\Referentiels\get_all_referentiels(),
                        'promotionActive' => $promotionActive,
                        'referentielMap' => $referentielMap,
                        'errors' => session_get('validation_errors', []),
                        'oldInput' => $oldInput
                    ]
                );
            }
            
            return App\render_with_layout(
                'promotions/index', 
                'Gestion des Promotions', 
                'promotions',
                [
                    'action' => $action,
                    'currentPage' => $currentPage,
                    'itemsPerPage' => $itemsPerPage,
                    'status' => $status,
                    'search' => $search,
                    'viewMode' => $viewMode,
                    'promos' => $promos,
                    'pagination' => $pagination,
                    'nbPromos' => $nbPromos,
                    'totalItems' => $totalItems,
                    'totalPages' => $totalPages,
                    'startIndex' => $startIndex,
                    'endIndex' => $endIndex,
                    'referentiels' => $referentiels,
                    'promotionActive' => $promotionActive,
                    'referentielMap' => $referentielMap,
                    'errors' => $errors,
                    'oldInput' => $oldInput
                ]
            );
        }
    }
    catch (\Exception $e) {
        session_set('error_message', ['content' => $e->getMessage()]);
        App\redirect('/promotions');
    }
}

    function createPromotion() 
    {
        try {
            $data = $_POST;
            $files = $_FILES;
            
            // Assurez-vous que les référentiels sont toujours un tableau
            if (isset($data['referentiels']) && !is_array($data['referentiels'])) {
                $data['referentiels'] = [$data['referentiels']];
            }
            
            $errors = validation\validate_promotion($data, $files, null);  
            
            if (!empty($errors)) {
                session_set('validation_errors', $errors);
                $old_input = [
                    'nom' => $data['nom'] ?? '',
                    'datedebut' => $data['datedebut'] ?? '',
                    'datefin' => $data['datefin'] ?? '',
                    'referentiels' => $data['referentiels'] ?? []
                ];
                session_set('old_input', $old_input);
                App\redirect('/promotions?action=add');
                exit;
            }
            
            $photo_path = handle_file_upload($files['photo']);
            $model = require __DIR__.'/../Models/Promo.model.php';
            
            $model[Promotion_Model_Key::ADD->value]([
                PromotionAttribute::NAME->value => $data['nom'],
                PromotionAttribute::START_DATE->value => $data['datedebut'],
                PromotionAttribute::END_DATE->value => $data['datefin'],
                PromotionAttribute::PHOTO->value => $photo_path,
                PromotionAttribute::STATUS->value => 'inactive',
                PromotionAttribute::STUDENTS_NB->value => 0,
                PromotionAttribute::REFERENTIELS->value => $data['referentiels'] ?? [],
            ]);
            
            session_remove('validation_errors');
            session_remove('old_input');
            
            session_set('success_message', ['content' => SuccessCode::PROMOTION_CREATED->value]);
            App\redirect('/promotions');
            
        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/promotions?action=add');
        }
    }

    function get_all_promotions($status = null, $search = null, $page = 1, $per_page = 5)
    {
        // Récupérer les paramètres de l'URL si non spécifiés
        if ($status === null && isset($_GET['status'])) {
            $status = $_GET['status'];
        }
        
        if ($search === null && isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        
        if ($page === 1 && isset($_GET['page'])) {
            $page = (int)$_GET['page'];
        }
        
        if ($per_page === 5 && isset($_GET['per_page'])) {
            $per_page = (int)$_GET['per_page'];
        }
        
        $model = require __DIR__.'/../Models/Promo.model.php';
        $promotions = $model[Promotion_Model_Key::GET_ALL->value]();
        
        $promotions = array_map(function($promo) {
            return array_merge([
                'nom' => $promo['nom'] ?? 'Promotion #'.($promo['id'] ?? 'N/A'),
                'statut' => $promo['statut'] ?? 'inactive'
            ], $promo);
        }, $promotions);
        
        if (!empty($status)) {
            $promotions = array_filter($promotions, function($promo) use ($status) {
                return $promo['statut'] === $status;
            });
        }
        
        if (!empty($search)) {
            $search = strtolower(trim($search));
            $promotions = array_filter($promotions, function($promo) use ($search) {
                return isset($promo['nom']) && strpos(strtolower($promo['nom']), $search) !== false;
            });
        }
        
        $total_results = count($promotions);
        $total_pages = ceil($total_results / $per_page);
        $page = max(1, min($page, $total_pages > 0 ? $total_pages : 1));
        $offset = ($page - 1) * $per_page;
        $paginated_promotions = array_slice(array_values($promotions), $offset, $per_page);
        
        return [
            'promotions' => $paginated_promotions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'total_results' => $total_results,
                'from' => $total_results > 0 ? $offset + 1 : 0,
                'to' => min($offset + $per_page, $total_results)
            ]
        ];
    }
    function get_promotions_data($status = null, $search = null, $page = 1, $per_page = 5) {
        $model = require __DIR__.'/../Models/Promo.model.php';
        $all_promotions = $model[Promotion_Model_Key::GET_ALL->value]();
        $active_promo = array_values(array_filter($all_promotions, fn($p) => $p['statut'] === 'active'))[0] ?? null;
        $filtered = array_filter($all_promotions, function($p) use ($status, $search, $active_promo) {
            $status_match = !$status || $p['statut'] === $status;
            if ($status === 'inactive' && $p === $active_promo) {
                return false;
            }
            $search_match = !$search || (isset($p['nom']) && stripos($p['nom'], trim($search)) !== false);
            return $status_match && $search_match;
        });
        if (empty($filtered) && $search) {
            $active_promo = null;
        }
        $filtered = array_filter($filtered, fn($p) => $p !== $active_promo);
        $total = count($filtered);
        $total_pages = max(1, ceil(($total + ($active_promo && $status !== 'inactive' ? 1 : 0)) / $per_page));
        $page = max(1, min($page, $total_pages));
        $offset = ($page - 1) * ($per_page - 1); 
        if ($status !== 'inactive' && $active_promo) {
            $paginated = array_merge([$active_promo], array_slice(array_values($filtered), $offset, $per_page - 1));
        } else {
            $paginated = array_slice(array_values($filtered), $offset, $per_page);
        }
        return [
            'promotions' => $paginated,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'total_results' => $total + ($active_promo && $status !== 'inactive' ? 1 : 0), // Inclure la promotion active dans le total si applicable
                'from' => $total ? $offset + 1 : 0,
                'to' => min($offset + $per_page, $total + ($active_promo && $status !== 'inactive' ? 1 : 0))
            ]
        ];
    }
    function get_active_promotion_referentiel_count() {
        $model = require __DIR__ . '/../Models/Promo.model.php';

        $active_promo = $model[Promotion_Model_Key::GET_ACTIVE_PROMOTION->value]();
        if (!$active_promo || !isset($active_promo['referentiels']) || !is_array($active_promo['referentiels'])) {
            return 0;
        }

        return count(array_filter($active_promo['referentiels'], fn($ref) => !empty($ref)));
    }
    function get_active_promotion_apprenant_count() {
        $model = require __DIR__ . '/../Models/Promo.model.php';
        $active_promo = $model[Promotion_Model_Key::GET_ACTIVE_PROMOTION->value]();
        
        if (!$active_promo) {
            return 0;
        }
        
        return $active_promo['nbr_etudiants'] ?? 0;
    }
    function get_nbr_promotions() {
        global $fonctions_models;
        return $fonctions_models[ModelFunction::GET_NBR->value](DataKey::PROMOTIONS);
    }

    function get_active_promotion() {
        $model = require __DIR__.'/../Models/Promo.model.php';

        $getByStatus = $model[Promotion_Model_Key::GET_BY_STATUS->value] ?? null;

        if (is_callable($getByStatus)) {
            $activePromos = $getByStatus('active');
            if (!empty($activePromos)) {
                // Convertir les dates au format YYYY-MM-DD
                foreach ($activePromos as &$promo) {
                    if (isset($promo['date_debut'])) {
                        $promo['date_debut'] = DateTime::createFromFormat('d/m/Y', $promo['date_debut'])->format('Y-m-d');
                    }
                    if (isset($promo['date_fin'])) {
                        $promo['date_fin'] = DateTime::createFromFormat('d/m/Y', $promo['date_fin'])->format('Y-m-d');
                    }
                }
                return reset($activePromos);
            }
        }

        return null;
    }

    function updatePromotion() {
        try {
            $id = $_POST[PromotionAttribute::ID->value] ?? null;
            $data = [
                PromotionAttribute::ID->value => $id,
                PromotionAttribute::NAME->value => $_POST['nom'] ?? '',
                PromotionAttribute::START_DATE->value => $_POST['datedebut'] ?? '',
                PromotionAttribute::END_DATE->value => $_POST['datefin'] ?? '',
                PromotionAttribute::STATUS->value => $_POST['statut'] ?? '',
                PromotionAttribute::REFERENTIELS->value => $_POST['referentiels'] ?? [],
            ];
            
            $model = require __DIR__.'/../Models/Promo.model.php';
            $promotion = $model[Promotion_Model_Key::GET_BY_ID->value]($id);
            
            if (!$promotion) {
                throw new \Exception('Promotion non trouvée');
            }

            $errors = validation\validate_promotion($data, $_FILES, $id);

            if (!empty($errors)) {
                session_set('validation_errors', $errors);
                session_set('old_input', $data);
                App\redirect('/promotions/edit/' . $id);
                exit;
            }
            
            // Gestion de l'upload de photo si présent
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $data[PromotionAttribute::PHOTO->value] = handle_file_upload($_FILES['photo']);
            }
            
            $model[Promotion_Model_Key::UPDATE->value]($id, $data);
            
            session_set('success_message', ['content' => SuccessCode::PROMOTION_UPDATED->value]);
            App\redirect('/promotions');
        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/promotions');
        }
    }


    function togglePromotionStatus() {
        try {
            if (!isset($_GET['promotion_id'])) 
            {
                throw new \Exception('Paramètre promotion_id manquant');
            }
            $id = $_GET['promotion_id'];
            $model = require __DIR__.'/../Models/Promo.model.php';
            $promotion = $model[Promotion_Model_Key::GET_BY_ID->value]($id);
            if (!$promotion) {
                throw new \Exception("Promotion introuvable");
            }
            
            if ($promotion['statut'] === 'active') {
                throw new \Exception("Impossible de désactiver une promotion active sans en activer une autre.");
            }
            if ($promotion['statut'] === 'inactive') {
                $model[Promotion_Model_Key::DESACTIVATE_ALL->value]();
                $model[Promotion_Model_Key::UPDATE->value]($id, ['statut' => 'active']);
                session_set('success_message', [
                    'content' => 'Promotion activée avec succès'
                ]);
            }

            App\redirect('/promotions?' . http_build_query($_GET));
        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/promotions');
        }
    }
    function deletePromotion() {
        try {
            if (!isset($_POST['promotion_id'])) {
                throw new \Exception('ID de promotion manquant');
            }
            
            $id = $_POST['promotion_id'];
            $model = require __DIR__.'/../Models/Promo.model.php';
            
            if (!$model[Promotion_Model_Key::DELETE->value]($id)) {
                throw new \Exception('Échec de la suppression de la promotion');
            }
            
            session_set('success_message', ['content' => 'Promotion supprimée avec succès']);
            App\redirect('/promotions');
        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/promotions');
        }
    }

    function get_promotions_by_status($status) {
        if (!in_array($status, ['active', 'inactive'])) {
            throw new \Exception("Statut non valide !");
        }
        $model = require __DIR__.'/../Models/Promo.model.php';
        return $model[Promotion_Model_Key::GET_BY_STATUS->value]($status);
    }

    function handle_file_upload($file) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; 
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erreur lors du téléchargement du fichier: ' . $file['error']);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'image/bmp'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new \RuntimeException('Type de fichier non autorisé. Types acceptés: JPEG, PNG, GIF, WEBP, BMP');
        }
        
        $max_size = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $max_size) {
            throw new \RuntimeException('Le fichier est trop volumineux. Taille maximale: 2MB.');
        }
        
        $upload_dir = __DIR__.'/../../public/uploads/promotions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('promo_').'.'.$extension;
        $target_path = $upload_dir.$filename;
        
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new \RuntimeException('Erreur lors du déplacement du fichier uploadé.');
        }
        
        return $filename;
    }

    function handleReferentielSearch() {
        try {
            $search_term = $_POST['search_term'] ?? '';
            $referentiel_model = require __DIR__.'/../Models/Ref.Model.php';
            $search_results = $referentiel_model[Referentiel_Model_Key::SEARCH->value]($search_term);
            
            $old_input = [
                'nom' => $_POST['nom'] ?? '',
                'datedebut' => $_POST['datedebut'] ?? '',
                'datefin' => $_POST['datefin'] ?? '',
                'search_term' => $search_term,
                'referentiels' => $_POST['referentiels'] ?? []
            ];
            
            session_set('old_input', $old_input);
            session_set('search_results', $search_results);
            
            if (session_has('validation_errors')) {
                $validation_errors = session_get('validation_errors');
                session_set('validation_errors', $validation_errors);
            }
            
            App\redirect('/promotions?action=add');
        } catch (\Exception $e) {
            session_set('error_message', ['content' => 'Erreur lors de la recherche: '.$e->getMessage()]);
            App\redirect('/promotions?action=add');
        }
    }

    function get_promotion_by_id($id) {
        $model = require __DIR__ . '/../Models/Promo.model.php';
        $getById = $model[\App\Enums\Promotion_Model_Key::GET_BY_ID->value] ?? null;
    
        if (is_callable($getById)) {
            return $getById($id);
        }
    
        return null;
    }

    function buildUrl($params = []) {
        $viewMode = $_GET['view'] ?? 'grid';
        $itemsPerPage = $_GET['per_page'] ?? 5;
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        
        return '?' . http_build_query(array_filter(array_merge([
            'view' => $viewMode,
            'per_page' => $itemsPerPage,
            'status' => $status,
            'search' => $search,
            'page' => 1
        ], $params)));
    }