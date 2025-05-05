<?php
namespace App\Controllers\Apprenants;

use App\Controllers as App;
use App\Models;
use App\Enums\ModelFunction;
use App\Enums\Apprenant_Model_Key;
use App\Enums\Promotion_Model_Key;
USE App\Enums\Referentiel_Model_Key;
use App\Enums\UserModelKey;
use App\Enums\ApprenantAttribute;
use App\Enums\DataKey;
use App\Services\Session as Session;
use App\Services\Validation_apprenant as Validator;
use App\Services\Validation_importation as ImportValidator;


require_once __DIR__ . '/../Services/service_validate_apprenant.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;



function getWaitingApprenants() {
    $model = require __DIR__ . '/../Models/ApprenantModel.php';
    return $model[Apprenant_Model_Key::GET_WAITING_LIST->value]();
}

function handleApprenantActions() {
    session_init();
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
           
            // Débogage
            error_log("POST action reçue: " . $action);
            error_log("POST data: " . print_r($_POST, true));
            
            switch ($action) {
                case 'create':
                    createApprenant();
                    return App\Controllers\redirect('/apprenants'); 
                
                case 'import':
                    importApprenant();
                    return App\Controllers\redirect('/apprenants'); 
                
                case 'validate_waiting':
                    validateWaitingApprenant();
                    return App\Controllers\redirect('/apprenants');
                
                case 'remove_waiting':
                    removeWaitingApprenant();
                    return App\Controllers\redirect('/apprenants?tab=waiting');
                
                default:
                    throw new \Exception('Action POST non valide: ' . $action);
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = $_GET['action'] ?? 'list';
            
            // Paramètres de pagination
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            $tab = $_GET['tab'] ?? 'retained';
            
            // Paramètre de filtrage par référentiel
            $referentielFilter = isset($_GET['referentiel']) ? $_GET['referentiel'] : null;
            $promoactive = isset($_GET['promotion']) ? $_GET['promotion'] : null ;
           
            
            // Récupérer tous les apprenants
            $allApprenants = getAllApprenants();
            $allWaitingApprenants = getWaitingApprenants();
            
            // Filtrer par référentiel si nécessaire
            if ($referentielFilter) {
                $allApprenants = array_filter($allApprenants, function($apprenant) use ($referentielFilter,$promoactive) {
                    
                    return $apprenant['referentiel_id'] == $referentielFilter && $apprenant['promotion_id'] == $promoactive;
                  
                });
             
                $allWaitingApprenants = array_filter($allWaitingApprenants, function($apprenant) use ($referentielFilter) {
                    return isset($apprenant['referentiel_id']) && $apprenant['referentiel_id'] === $referentielFilter;
                });
                
                // Réindexer les tableaux après filtrage
                $allApprenants = array_values($allApprenants);
                $allWaitingApprenants = array_values($allWaitingApprenants);
            }
            
            // Pagination pour les apprenants retenus
            $totalRetained = count($allApprenants);
            $totalPagesRetained = ceil($totalRetained / $perPage);

            $currentPage = max(1, min($currentPage, $totalPagesRetained > 0 ? $totalPagesRetained : 1));
            $startIndexRetained = ($currentPage - 1) * $perPage;
            $apprenants = array_slice($allApprenants, $startIndexRetained, $perPage);
            
            // Pagination pour les apprenants en attente
            $totalWaiting = count($allWaitingApprenants);
            $totalPagesWaiting = ceil($totalWaiting / $perPage);
            $currentPageWaiting = $tab === 'waiting' ? $currentPage : 1;

            $currentPageWaiting = max(1, min($currentPageWaiting, $totalPagesWaiting > 0 ? $totalPagesWaiting : 1));
            $startIndexWaiting = ($currentPageWaiting - 1) * $perPage;
            $waitingApprenants = array_slice($allWaitingApprenants, $startIndexWaiting, $perPage);
            
            // Calcul des indices pour l'affichage
            $startIndex = $tab === 'retained' ? ($totalRetained > 0 ? $startIndexRetained + 1 : 0) : ($totalWaiting > 0 ? $startIndexWaiting + 1 : 0);
            $endIndex = $tab === 'retained' 
                ? min($startIndexRetained + $perPage, $totalRetained) 
                : min($startIndexWaiting + $perPage, $totalWaiting);
            $totalItems = $tab === 'retained' ? $totalRetained : $totalWaiting;
            $totalPages = $tab === 'retained' ? $totalPagesRetained : $totalPagesWaiting;
            
            $allreferentiels = \App\Controllers\Referentiels\get_all_referentiels();
            $validation_errors = session_get('validation_errors', []);
            $oldInput = session_get('old_input', []);
            $activeData = getActivePromotionAndReferentiels();
            $activePromotion = $activeData['promotion'];
            $referentiels = $activeData['referentiels'];
            if ($action === 'show' && isset($_GET['matricule'])) {
                $mat = $_GET['matricule'];
                return showApprenant($mat);
            }
            
            // Si on édite un apprenant en attente
            if ($action === 'edit_waiting' && isset($_GET['id'])) {
                $waitingId = $_GET['id'];
                $waitingApprenant = null;
                
                // Trouver l'apprenant en attente
                foreach ($waitingApprenants as $waiting) {
                    if ($waiting['id'] === $waitingId) {
                        $waitingApprenant = $waiting;
                        break;
                    }
                }
                
                if ($waitingApprenant) {
                    if (empty($oldInput)) {
                        $oldInput = $waitingApprenant;
                    }
                
                    if (empty($validation_errors) && isset($waitingApprenant['errors'])) {
                        $validation_errors = $waitingApprenant['errors'];
                    }
                }
                
                return App\render_with_layout(
                    'apprenants/index',
                    'Modifier un apprenant en attente',
                    'apprenants',
                    [
                        'action' => 'edit_waiting',
                        'apprenants' => $apprenants,
                        'waitingApprenants' => $waitingApprenants,
                        'allreferentiels' => $allreferentiels,
                        'validation_errors' => $validation_errors,
                        'oldInput' => $oldInput,
                        'activeData' => $activeData,
                        'activePromotion' => $activePromotion,
                        'referentiels' => $referentiels,
                        'waitingId' => $waitingId
                    ]
                );
            }
            
            if (in_array($action, ['add', 'import'])) {
                return App\render_with_layout(
                    'apprenants/index',
                    $action === 'add' ? 'Ajouter un apprenant' : 'Importer des apprenants',
                    'apprenants',
                    [
                        'action' => $action,
                        'apprenants' => $apprenants,
                        'waitingApprenants' => $waitingApprenants,
                        'allreferentiels' => $allreferentiels,
                        'validation_errors' => $validation_errors,
                        'oldInput' => $oldInput,
                        'activeData' => $activeData,
                        'activePromotion' => $activePromotion,
                        'referentiels' => $referentiels
                    ]
                );
            }
            
            return App\render_with_layout(
                'apprenants/index', 
                'Gestion des Apprenants', 
                'apprenants', 
                [
                    'action' => $action,
                    'apprenants' => $apprenants,
                    'waitingApprenants' => $waitingApprenants,
                    'allreferentiels' => $allreferentiels,
                    'validation_errors' => $validation_errors,
                    'oldInput' => $oldInput,
                    'activeData' => $activeData,
                    'activePromotion' => $activePromotion,
                    'referentiels' => $referentiels,
                    // Add this line:
                    'current_page' => 'apprenants',
                    // Rest of your data...
                    'currentPage' => $currentPage,
                    'perPage' => $perPage,
                    'totalItems' => $totalItems,
                    'totalPages' => $totalPages,
                    'startIndex' => $startIndex,
                    'endIndex' => $endIndex,
                    'tab' => $tab,
                    'referentielFilter' => $referentielFilter
                ]
            );
        }
    }
    catch (\Exception $e) {
        session_set('error_message', ['content' => $e->getMessage()]);
        App\redirect('/apprenants');
    }
}

function getActivePromotionAndReferentiels() {
    $promotion_model = require __DIR__ . '/../Models/Promo.model.php';
    $activePromotion = $promotion_model[Promotion_Model_Key::GET_ACTIVE_PROMOTION->value]();

    if (!$activePromotion) {
        throw new \Exception("Aucune promotion active n'est disponible.");
    }
    $today = new \DateTime();
    $endDate = \DateTime::createFromFormat('d/m/Y', $activePromotion['date_fin']);
    
    // if ($endDate < $today) {
    //     throw new \Exception("La promotion active est déjà terminée.");
    // }
    global $fonctions_models;
    $allReferentiels = $fonctions_models[ModelFunction::GET_ALL->value](DataKey::REFERENTIELS);
    
    $referentielIds = $activePromotion['referentiels'] ?? [];
    $associatedReferentiels = array_filter($allReferentiels, function($ref) use ($referentielIds) {
        return in_array($ref['id'], $referentielIds);
    });

    return [
        'promotion' => $activePromotion,
        'referentiels' => array_values($associatedReferentiels)
    ];
}

function generer_matricule($nom, $referentiel_nom) {
    if (empty($nom) || empty($referentiel_nom)) {
        throw new \Exception("Nom et référentiel ne peuvent pas être vides");
    }
    $nom_part = strlen($nom) <= 3 ? strtoupper($nom) : strtoupper(substr($nom, 0, 3));
    $referentiel_part = strtoupper(substr($referentiel_nom, 0, 3));
    $unique_id = date('Ymd');
    return $nom_part.$referentiel_part.$unique_id;
}

function createApprenant() {
    session_init();
    
    try {
        $data = $_POST;
        $files = $_FILES;
        $activePromoInfo = getActivePromotionAndReferentiels();
        $activePromotion = $activePromoInfo['promotion'];
        $referentiels = $activePromoInfo['referentiels'];
        
        require_once __DIR__ . '/../Services/service_validate_apprenant.php';
        $errors = Validator\validate_apprenant($data, $_FILES);
       
        if (!empty($errors)) {
            session_set('validation_errors', $errors);
            session_set('old_input', $data);
            App\redirect('/apprenants?action=add');
            exit;
        }
        $referentiel_nom = array_column($referentiels, 'nom', 'id')[$data['referentiel_id']];
        $matricule = generer_matricule($data['nom'], $referentiel_nom);
        $photo_path = '';
            if (isset($files['photo']) && $files['photo']['size'] > 0) 
            { 
                $photo_path = handle_file_upload($files['photo']);
            } 
        $newApprenant = [
            ApprenantAttribute::FIRST_NAME->value => $data['prenom'],
            ApprenantAttribute::NAME->value => $data['nom'],
            ApprenantAttribute::BIRTH_DATE->value => $data['date_naissance'],
            ApprenantAttribute::BIRTH_PLACE->value => $data['lieu_naissance'],
            ApprenantAttribute::MATRICULE->value => $matricule,
            ApprenantAttribute::ADDRESS->value => $data['adresse'],
            ApprenantAttribute::PHONE->value => $data['telephone'],
            ApprenantAttribute::PHOTO->value => $photo_path,
            ApprenantAttribute::STATUS->value => 'actif',
            ApprenantAttribute::EMAIL->value => $data['email'],
            ApprenantAttribute::PROMOTION_ID->value => $activePromotion['id'], 
            ApprenantAttribute::REFERENTIEL_ID->value => $data['referentiel_id'],
            ApprenantAttribute::TUTEUR_NAME->value => $data['tuteur_prenom_nom'],
            ApprenantAttribute::TUTEUR_ADDRESS->value => $data['tuteur_adresse'],
            ApprenantAttribute::TUTEUR_PHONE->value => $data['tuteur_telephone'],
            ApprenantAttribute::TUTEUR_RELATION->value => $data['tuteur_lien_parente'],
        ];

        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        $model[Apprenant_Model_Key::ADD->value]($newApprenant);
        $promoModel = require __DIR__ . '/../Models/Promo.model.php';
        $promoModel[Promotion_Model_Key::UPDATE->value]($activePromotion['id'], [
            'nbr_etudiants' => ($activePromotion['nbr_etudiants'] ?? 0) + 1
        ]);
        $userModel = require __DIR__ . '/../Models/User.model.php';
        $default_password = 'SON@TEL2025';
        $userModel[UserModelKey::ADD->value]([
            'matricule' => $matricule,
            'email' => $data['email'],
            'password' => $default_password,
            'role' => 'apprenant',
            'password_change_required' => true
        ]);
        
        require_once __DIR__ . '/../Services/service_send_email.php';
        $email_sent = envoyer_email(
            $data['email'],
            $data['prenom'] . ' ' . $data['nom'],
            $matricule,
            $default_password,
            $activePromotion['nom'],
            $referentiel_nom,
            $activePromotion['date_debut']
        );

        if (!$email_sent) {
            session_set('warning_message', ['content' => 'Apprenant ajouté mais l\'email n\'a pas pu être envoyé']);
        } else {
            session_set('success_message', ['content' => 'Apprenant ajouté avec succès et email envoyé']);
        }

        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/apprenants?action=add');
            exit;
        }

        session_remove('validation_errors');
        session_remove('old_input');
        App\redirect('/apprenants');
}

    function updateApprenant() {
        try {
            $id = $_POST[ApprenantAttribute::ID->value] ?? null;
            $data = $_POST;
            // Validation des données
            $errors = Validator\validate_apprenant($data, $id);
            if (!empty($errors)) {
                session_set('validation_errors', $errors);
                session_set('old_input', $data);
                App\redirect('/apprenants/edit/' . $id);
                exit;
            }
            $model = require __DIR__ . '/../Models/ApprenantModel.php';
            $model[Apprenant_Model_Key::UPDATE->value]($id, $data);
            session_set('success_message', ['content' => 'Apprenant mis à jour avec succès']);
            App\redirect('/apprenants');
        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/apprenants');
        }
    }

    function deleteApprenant() {
        try {
            $id = $_POST[ApprenantAttribute::ID->value] ?? null;

            if (!$id) {
                throw new \Exception('ID de l\'apprenant manquant');
            }

            $model = require __DIR__ . '/../Models/ApprenantModel.php';
            $model[Apprenant_Model_Key::DELETE->value]($id);

            session_set('success_message', ['content' => 'Apprenant supprimé avec succès']);
            App\redirect('/apprenants');
        } catch (\Exception $e) {
            session_set('error_message', ['content' => $e->getMessage()]);
            App\redirect('/apprenants');
        }
    }

    function getAllApprenants() {
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        return $model[Apprenant_Model_Key::GET_ALL->value]();
    }

    function getApprenantsByPromotion($promotionId) {
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        return $model[Apprenant_Model_Key::GET_BY_PROMOTION->value]($promotionId);
    }

    function getApprenantsByReferentiel($referentielId) {
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        return $model[Apprenant_Model_Key::GET_BY_REFERENTIEL->value]($referentielId);
    }

    function getApprenantById($id) {
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        return $model[Apprenant_Model_Key::GET_BY_ID->value]($id);
    }

function handle_file_upload($file) {
    $upload_dir = __DIR__.'/../../public/uploads/apprenants/';
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


function importApprenant() {
    session_init();
   
    // Initialiser le rapport d'importation
    $rapport_import = [
        'total' => 0,
        'succes' => 0,
        'echec' => 0,
        'attente' => 0,
        'doublons' => 0,
        'erreurs' => []
    ];

    try {
        // 1. Valider et charger le fichier
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Aucun fichier n\'a été téléchargé');
        }
        
        $fichier = $_FILES['import_file'];
        $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        
        // 2. Charger les données du fichier
        $donnees = [];
        if ($ext === 'xlsx' || $ext === 'xls') {
            require_once __DIR__ . '/../../vendor/autoload.php';
            $donnees = parse_excel_file($fichier['tmp_name']);
        }
      
        $rapport_import['total'] = count($donnees);
      
        // 3. Valider les données
        require_once __DIR__ . '/../Services/Validation_importation.php';
        $resultats_validation = \App\Services\Validation_importation\validate_import_data($donnees);
        
        // Traiter les résultats de validation
        list($donnees_valides, $donnees_invalides) = traiterResultatsValidation($donnees, $resultats_validation);
        $rapport_import['echec'] = count($donnees_invalides);
        $rapport_import['erreurs'] = $donnees_invalides;
       
        // S'il n'y a aucune donnée valide, rediriger avec rapport d'erreur
        if (empty($donnees_valides) && empty($donnees_invalides)) {
            session_set('warning_message', ['content' => 'Aucune donnée valide à importer.']);
            App\redirect('/apprenants?action=import');
            return;
        }
        
        // 4. Gérer les données invalides et valides
        $rapport_import = traiterDonneesImportation($donnees_valides, $donnees_invalides, $rapport_import);
        
        // 5. Définir le message d'importation
        definirMessageImportation($rapport_import);
        
        // Stocker le rapport d'importation pour l'affichage
        session_set('import_report', $rapport_import);
        
        App\redirect('/apprenants?action=import');
        
    } catch (\Exception $e) {
        error_log("Erreur d'importation: " . $e->getMessage());
        session_set('error_message', ['content' => "Échec de l'importation : Une erreur est survenue."]);
        App\redirect('/apprenants?action=import');
    }
}

/**
 * Traite les résultats de validation et sépare les données valides et invalides
 */
function traiterResultatsValidation($donnees, $resultats_validation) {
    $donnees_valides = [];
    $donnees_invalides = [];
    
    if (!empty($resultats_validation['rows'])) {
        foreach ($donnees as $index => $ligne) {
            $numero_ligne = $index + 2; // Correspond à la numérotation dans le validateur
            
            if (isset($resultats_validation['rows'][$numero_ligne])) {
                // La ligne a des erreurs
                $donnees_invalides[] = [
                    'row' => $ligne,
                    'errors' => $resultats_validation['rows'][$numero_ligne]
                ];
            } else {
                // La ligne est valide
                $donnees_valides[] = $ligne;
            }
        }
    } else {
        // Toutes les données sont valides
        $donnees_valides = $donnees;
    }
    
    return [$donnees_valides, $donnees_invalides];
}

/**
 * Traite les données d'importation (valides et invalides)
 */
function traiterDonneesImportation($donnees_valides, $donnees_invalides, $rapport_import) {
    $model = require __DIR__ . '/../Models/ApprenantModel.php';
    $userModel = require __DIR__ . '/../Models/User.model.php';
    
    // Récupérer les listes d'emails et matricules existants
    list($emails_existants, $matricules_existants) = recupererEmailsEtMatriculesExistants();
    
    // Traiter les données invalides
    foreach ($donnees_invalides as $item) {
        $donnees_apprenant = $item['row'];
        $erreurs = $item['errors'];
        
        // Vérifier si c'est un doublon
        $est_doublon = estDoublon($donnees_apprenant, $emails_existants, $matricules_existants);
        
        if ($est_doublon) {
            $rapport_import['doublons']++;
        } else {
            // Ajouter à la liste d'attente
            $model[Apprenant_Model_Key::ADD_TO_WAITING_LIST->value]($donnees_apprenant, $erreurs);
            $rapport_import['attente']++;
            
            // Mettre à jour les listes
            if (isset($donnees_apprenant['email']) && !empty($donnees_apprenant['email'])) {
                $emails_existants[] = strtolower($donnees_apprenant['email']);
            }
            if (isset($donnees_apprenant['matricule']) && !empty($donnees_apprenant['matricule'])) {
                $matricules_existants[] = $donnees_apprenant['matricule'];
            }
        }
    }
    
    // Traiter les données valides
    $activePromoInfo = getActivePromotionAndReferentiels();
    
    foreach ($donnees_valides as $ligne) {
        try {
            // Vérifier les doublons dans la base de données
            $apprenant_existant = null;
            if (isset($ligne['matricule']) && !empty($ligne['matricule'])) {
                $apprenant_existant = $model[Apprenant_Model_Key::FIND_BY_MATRICULE->value]($ligne['matricule'] ?? '');
            }

            $utilisateur_existant = null;
            if (isset($ligne['email']) && !empty($ligne['email'])) {
                $utilisateur_existant = $userModel[UserModelKey::FIND_BY_EMAIL->value]($ligne['email']);
            }

            // Gérer les doublons
            if ($apprenant_existant || $utilisateur_existant) {
                $rapport_import = gererDoublon($ligne, $apprenant_existant, $utilisateur_existant, $model, $rapport_import, $emails_existants, $matricules_existants);
                continue;
            }

            // Créer et ajouter l'apprenant
            $rapport_import = ajouterNouvelApprenant($ligne, $activePromoInfo, $model, $userModel, $rapport_import);
            
        } catch (\Exception $e) {
            // Gérer les erreurs
            $rapport_import = gererErreurAjout($ligne, $e, $model, $rapport_import, $emails_existants, $matricules_existants);
        }
    }
    
    // Mettre à jour le nombre d'étudiants dans la promotion
    if ($rapport_import['succes'] > 0) {
        mettreAJourNombreEtudiants($activePromoInfo, $rapport_import['succes']);
    }
    
    return $rapport_import;
}

/**
 * Récupère les emails et matricules existants
 */
function recupererEmailsEtMatriculesExistants() {
    $model = require __DIR__ . '/../Models/ApprenantModel.php';
    $liste_attente = $model[Apprenant_Model_Key::GET_WAITING_LIST->value]();
    
    $emails_existants = [];
    $matricules_existants = [];
    
    foreach ($liste_attente as $attente) {
        if (isset($attente['email']) && !empty($attente['email'])) {
            $emails_existants[] = strtolower($attente['email']);
        }
        if (isset($attente['matricule']) && !empty($attente['matricule'])) {
            $matricules_existants[] = $attente['matricule'];
        }
    }
    
    return [$emails_existants, $matricules_existants];
}

/**
 * Vérifie si un apprenant est un doublon
 */
function estDoublon($donnees_apprenant, $emails_existants, $matricules_existants) {
    if (isset($donnees_apprenant['email']) && !empty($donnees_apprenant['email'])) {
        $email = strtolower($donnees_apprenant['email']);
        if (in_array($email, $emails_existants)) {
            return true;
        }
    }
    
    if (isset($donnees_apprenant['matricule']) && !empty($donnees_apprenant['matricule'])) {
        if (in_array($donnees_apprenant['matricule'], $matricules_existants)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Gère les doublons détectés
 */
function gererDoublon($ligne, $apprenant_existant, $utilisateur_existant, $model, $rapport_import, $emails_existants, $matricules_existants) {
    $rapport_import['doublons']++;
    $messages_erreur = [];
    
    if ($apprenant_existant) {
        $messages_erreur[] = "Matricule déjà existant : {$ligne['matricule']}";
    }
    
    if ($utilisateur_existant) {
        $messages_erreur[] = "Email déjà utilisé : {$ligne['email']}";
    }
    
    // Vérifier si c'est un doublon dans la liste d'attente
    $est_doublon = estDoublon($ligne, $emails_existants, $matricules_existants);
    
    if (!$est_doublon) {
        $model[Apprenant_Model_Key::ADD_TO_WAITING_LIST->value]($ligne, ['doublon' => implode(', ', $messages_erreur)]);
        $rapport_import['attente']++;
        
        // Mettre à jour les listes
        if (isset($ligne['email']) && !empty($ligne['email'])) {
            $emails_existants[] = strtolower($ligne['email']);
        }
        if (isset($ligne['matricule']) && !empty($ligne['matricule'])) {
            $matricules_existants[] = $ligne['matricule'];
        }
    }
    
    return $rapport_import;
}

/**
 * Ajoute un nouvel apprenant
 */
function ajouterNouvelApprenant($ligne, $activePromoInfo, $model, $userModel, $rapport_import) {
    // Générer le matricule
    $matricule = genererMatriculeImport($ligne, $activePromoInfo);
    
    // Gérer la photo si présente
    $chemin_photo = null;
    if (isset($ligne['photo_tmp']) && $ligne['photo_tmp']) {
        $chemin_photo = handlePhotoUpload($ligne);
    }  
           
    // Créer l'apprenant
    $nouvel_apprenant = [
        'prenom' => $ligne['prenom'],
        'nom' => $ligne['nom'],
        'date_naissance' => $ligne['date_naissance'],
        'lieu_naissance' => $ligne['lieu_naissance'] ?? '',
        'matricule' => $matricule,
        'adresse' => $ligne['adresse'],
        'telephone' => $ligne['telephone'],
        'photo' => $chemin_photo,
        'status' => 'actif',
        'email' => $ligne['email'],
        'promotion_id' => $activePromoInfo['promotion']['id'],
        'referentiel_id' => $ligne['referentiel_id'],
        'tuteur_prenom_nom' => $ligne['tuteur_prenom_nom'] ?? '',
        'tuteur_adresse' => $ligne['tuteur_adresse'] ?? '',
        'tuteur_telephone' => $ligne['tuteur_telephone'] ?? '',
        'tuteur_lien_parente' => $ligne['tuteur_lien_parente'] ?? ''
    ]; 
   
    // Ajouter l'apprenant et créer son compte utilisateur
    $model[Apprenant_Model_Key::ADD->value]($nouvel_apprenant);  
    $userModel[UserModelKey::ADD->value]([
        'matricule' => $matricule,
        'email' => $ligne['email'],
        'password' => 'SON@TEL2025',
        'role' => 'apprenant',
        'password_change_required' => true
    ]);
    
    $rapport_import['succes']++;
    return $rapport_import;
}

/**
 * Génère un matricule pour un apprenant importé
 */
function genererMatriculeImport($ligne, $activePromoInfo) {
    $referentiels = $activePromoInfo['referentiels'];
    $referentiels_par_id = [];
    
    foreach ($referentiels as $referentiel) {
        $referentiels_par_id[$referentiel['id']] = $referentiel;
    }

    $referentiel_id = $ligne['referentiel_id'];
    if (isset($referentiels_par_id[$referentiel_id])) {
        $nom_referentiel = $referentiels_par_id[$referentiel_id]['nom'];
    } else {
        error_log("Référentiel non trouvé pour ID: $referentiel_id");
        $nom_referentiel = "Inconnu";
    }

    return generer_matricule($ligne['nom'], $nom_referentiel);
}

/**
 * Gère les erreurs lors de l'ajout d'un apprenant
 */
function gererErreurAjout($ligne, $exception, $model, $rapport_import, $emails_existants, $matricules_existants) {
    $est_doublon = estDoublon($ligne, $emails_existants, $matricules_existants);
    
    if (!$est_doublon) {
        $model[Apprenant_Model_Key::ADD_TO_WAITING_LIST->value]($ligne, ['exception' => $exception->getMessage()]);
        $rapport_import['attente']++;
        
        // Mettre à jour les listes
        if (isset($ligne['email']) && !empty($ligne['email'])) {
            $emails_existants[] = strtolower($ligne['email']);
        }
        if (isset($ligne['matricule']) && !empty($ligne['matricule'])) {
            $matricules_existants[] = $ligne['matricule'];
        }
    }
    
    $rapport_import['echec']++;
    return $rapport_import;
}

/**
 * Met à jour le nombre d'étudiants dans la promotion
 */
function mettreAJourNombreEtudiants($activePromoInfo, $nombre_nouveaux) {
    $promotion = $activePromoInfo['promotion'];
    $promoModel = require __DIR__ . '/../Models/Promo.model.php';
    $promoModel['update']($promotion['id'], [
        'nbr_etudiants' => ($promotion['nbr_etudiants'] ?? 0) + $nombre_nouveaux
    ]);
}

/**
 * Définit le message d'importation en fonction du rapport
 */
function definirMessageImportation($rapport_import) {
    if ($rapport_import['succes'] > 0 && $rapport_import['attente'] > 0) {
        // Cas où certains apprenants ont été importés et d'autres sont en attente
        $message = "Importation terminée : {$rapport_import['succes']} apprenants importés avec succès, {$rapport_import['attente']} placés en liste d'attente.";
        session_set('warning_message', ['content' => $message]);
    } else if ($rapport_import['succes'] > 0) {
        // Cas où tous les apprenants ont été importés avec succès
        $message = "Importation réussie : {$rapport_import['succes']} apprenants importés.";
        session_set('success_message', ['content' => $message]);
    } else if ($rapport_import['attente'] > 0) {
        // Cas où aucun apprenant n'a été importé, tous sont en attente
        $message = "Importation terminée : Aucun apprenant importé, {$rapport_import['attente']} placés en liste d'attente.";
        session_set('warning_message', ['content' => $message]);
    } else {
        // Cas où aucun apprenant n'a été importé et aucun n'est en attente
        session_set('error_message', ['content' => "Échec de l'importation : Aucun apprenant importé."]);
    }
}

function parse_excel_file($filepath) {
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
        $worksheet = $spreadsheet->getActiveSheet();
        $header = [];
        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                $rowData[] = $value !== null ? trim((string)$value) : '';
            }

            if ($rowIndex === 1) {
                $header = array_map('strtolower', $rowData);
                continue;
            }

            if (!empty($header) && count($header) === count($rowData)) {
                $data[] = array_combine($header, $rowData);
            }
        }
        return $data;
    } catch (\Exception $e) {
        error_log("ERREUR parse_excel_file: ".$e->getMessage());
        return [];
    }
}


function handlePhotoUpload($row) {
    if (isset($row['photo_tmp']) && $row['photo_tmp']) {
        $upload_dir = __DIR__.'/../../public/uploads/apprenants/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = uniqid('app_').'.'.pathinfo($row['photo_tmp'], PATHINFO_EXTENSION);
        $target_path = $upload_dir.$filename;
        
        if (!move_uploaded_file($row['photo_tmp'], $target_path)) {
            throw new RuntimeException('Erreur lors du téléchargement du fichier');
        }
        
        return $filename;
    }
    return null;
}

function validateWaitingApprenant() {
    session_init();
    
    try {
        $waitingId = $_POST['waiting_id'] ?? '';
        if (empty($waitingId)) {
            throw new \Exception('ID de l\'apprenant en attente manquant');
        }
        
        // Récupérer l'apprenant en attente
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        $waitingApprenants = $model[Apprenant_Model_Key::GET_WAITING_LIST->value]();
        
        $waitingApprenant = null;
        foreach ($waitingApprenants as $apprenant) {
            if ($apprenant['id'] === $waitingId) {
                $waitingApprenant = $apprenant;
                break;
            }
        }
        
        if (!$waitingApprenant) {
            throw new \Exception('Apprenant en attente non trouvé');
        }
        
        // Valider les données corrigées
        $data = $_POST;
        $files = $_FILES;
        
        require_once __DIR__ . '/../Services/service_validate_apprenant.php';
        $errors = Validator\validate_apprenant($data, $_FILES);
        
        if (!empty($errors)) {
            session_set('validation_errors', $errors);
            session_set('old_input', $data);
            App\redirect('/apprenants?action=edit_waiting&id=' . $waitingId);
            exit;
        }
        
        // Récupérer les informations de promotion active
        $activePromoInfo = getActivePromotionAndReferentiels();
        $referentiels = $activePromoInfo['referentiels'];
        
        // Trouver le nom du référentiel
        $referentiel_nom = "Inconnu";
        foreach ($referentiels as $referentiel) {
            if ($referentiel['id'] === $data['referentiel_id']) {
                $referentiel_nom = $referentiel['nom'];
                break;
            }
        }
        
        // Générer un matricule
        $matricule = generer_matricule($data['nom'], $referentiel_nom);
        
        // Gérer la photo si présente
        $photo_path = '';
        if (isset($files['photo']) && $files['photo']['size'] > 0) { 
            $photo_path = handle_file_upload($files['photo']);
        }
        
        // Préparer les données de l'apprenant
        $newApprenant = [
            ApprenantAttribute::FIRST_NAME->value => $data['prenom'],
            ApprenantAttribute::NAME->value => $data['nom'],
            ApprenantAttribute::BIRTH_DATE->value => $data['date_naissance'],
            ApprenantAttribute::BIRTH_PLACE->value => $data['lieu_naissance'],
            ApprenantAttribute::MATRICULE->value => $matricule,
            ApprenantAttribute::ADDRESS->value => $data['adresse'],
            ApprenantAttribute::PHONE->value => $data['telephone'],
            ApprenantAttribute::PHOTO->value => $photo_path,
            ApprenantAttribute::STATUS->value => 'actif',
            ApprenantAttribute::EMAIL->value => $data['email'],
            ApprenantAttribute::PROMOTION_ID->value => $activePromoInfo['promotion']['id'],
            ApprenantAttribute::REFERENTIEL_ID->value => $data['referentiel_id'],
            ApprenantAttribute::TUTEUR_NAME->value => $data['tuteur_prenom_nom'],
            ApprenantAttribute::TUTEUR_ADDRESS->value => $data['tuteur_adresse'],
            ApprenantAttribute::TUTEUR_PHONE->value => $data['tuteur_telephone'],
            ApprenantAttribute::TUTEUR_RELATION->value => $data['tuteur_lien_parente'],
        ];
        
        // Ajouter l'apprenant à la base de données
        $model[Apprenant_Model_Key::ADD->value]($newApprenant);
        
        // Créer un compte utilisateur
        $userModel = require __DIR__ . '/../Models/User.model.php';
        $userModel[UserModelKey::ADD->value]([
            'matricule' => $matricule,
            'email' => $data['email'],
            'password' => 'SON@TEL2025',
            'role' => 'apprenant',
            'password_change_required' => true
        ]);
        
        // Mettre à jour le nombre d'étudiants dans la promotion
        $promoModel = require __DIR__ . '/../Models/Promo.model.php';
        $promoModel[Promotion_Model_Key::UPDATE->value]($activePromoInfo['promotion']['id'], [
            'nbr_etudiants' => ($activePromoInfo['promotion']['nbr_etudiants'] ?? 0) + 1
        ]);
        
        // Supprimer l'apprenant de la liste d'attente
        $model[Apprenant_Model_Key::REMOVE_FROM_WAITING_LIST->value]($waitingId);
        
        // Envoyer un email à l'apprenant (optionnel)
        require_once __DIR__ . '/../Services/service_send_email.php';
        $email_sent = envoyer_email(
            $data['email'],
            $data['prenom'] . ' ' . $data['nom'],
            $matricule,
            'SON@TEL2025',
            $activePromoInfo['promotion']['nom'],
            $referentiel_nom,
            $activePromoInfo['promotion']['date_debut']
        );
        
        if (!$email_sent) {
            session_set('warning_message', ['content' => 'Apprenant validé et ajouté avec succès, mais l\'email n\'a pas pu être envoyé']);
        } else {
            session_set('success_message', ['content' => 'Apprenant validé et ajouté avec succès et email envoyé']);
        }
        
    } catch (\Exception $e) {
        session_set('error_message', ['content' => $e->getMessage()]);
    }
    
    session_remove('validation_errors');
    session_remove('old_input');
    App\redirect('/apprenants');
}

// Fonction pour supprimer un apprenant de la liste d'attente
function removeWaitingApprenant() {
    session_init();
    
    try {
        $waitingId = $_POST['id'] ?? '';
        if (empty($waitingId)) {
            throw new \Exception('ID de l\'apprenant en attente manquant');
        }
        
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        $result = $model[Apprenant_Model_Key::REMOVE_FROM_WAITING_LIST->value]($waitingId);
        
        if ($result) {
            session_set('success_message', ['content' => 'Apprenant supprimé de la liste d\'attente']);
        } else {
            session_set('error_message', ['content' => 'Impossible de supprimer l\'apprenant de la liste d\'attente']);
        }
    } catch (\Exception $e) {
        session_set('error_message', ['content' => $e->getMessage()]);
    }
    
    App\redirect('/apprenants?tab=waiting');
}


function showApprenant($matricule) {
    $apprenantModel = require __DIR__ . '/../Models/ApprenantModel.php';
    $apprenant = $apprenantModel[Apprenant_Model_Key::GET_BY_MATRICULE->value]($matricule);
    if (!$apprenant) {
        App\redirect('/apprenants');
        exit;
    }
    $referentielModel = require __DIR__ . '/../Models/Ref.Model.php';
    $referentiel = $referentielModel[Referentiel_Model_Key::GET_BY_ID->value]($apprenant['referentiel_id']);
    if (is_array($referentiel) && isset($referentiel[0])) {
        $referentielData = $referentiel[0];
    } else {
        $referentielData = $referentiel;
    }
    $referentielNom = isset($referentielData['nom']) ? $referentielData['nom'] : 'Non défini';
    $stats = [
        'presences' => 20,
        'retards' => 5,
        'absences' => 1
    ];
    
    $modules = [];
    
    global $data;
    $data['apprenant'] = $apprenant;
    $data['referentielNom'] = $referentielNom;
    $data['stats'] = $stats;
    $data['modules'] = $modules;
    
    return App\render_with_layout(
        'apprenants/show',
        'Détails de l\'apprenant',
        'apprenants',
        [
            'currentPage' => 'apprenants',
            'contentHeader' => '<h2>Détails de l\'apprenant</h2>',
            'apprenant' => $apprenant,
            'referentielNom' => $referentielNom,
            'stats' => $stats,
            'modules' => $modules
        ]
    );
}
