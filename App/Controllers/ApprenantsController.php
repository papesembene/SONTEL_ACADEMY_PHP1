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


// Ajouter une fonction pour récupérer les apprenants en attente
function getWaitingApprenants() {
    $model = require __DIR__ . '/../Models/ApprenantModel.php';
    return $model[Apprenant_Model_Key::GET_WAITING_LIST->value]();
}

// Modifier la fonction handleApprenantActions pour gérer les nouvelles actions
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
            $apprenants = getAllApprenants();
            $waitingApprenants = getWaitingApprenants();
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
                    // Préparer les données pour le formulaire d'édition
                    // Si pas de old_input (première visite), utiliser les données de l'apprenant en attente
                    if (empty($oldInput)) {
                        $oldInput = $waitingApprenant;
                    }
                    
                    // Si pas d'erreurs de validation (première visite), utiliser les erreurs de l'apprenant en attente
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
                    'referentiels' => $referentiels
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
    $import_report = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'waiting' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    try {
        // 1. Valider le fichier
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Aucun fichier n\'a été téléchargé');
        }
        
        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 2. Charger les données du fichier
        if ($ext === 'xlsx' || $ext === 'xls') {
            require_once __DIR__ . '/../../vendor/autoload.php';
            $data = parse_excel_file($file['tmp_name']);
        }
      
        $import_report['total'] = count($data);
      
        // 3. Valider les données
        require_once __DIR__ . '/../Services/Validation_importation.php';
        $validation_results = \App\Services\Validation_importation\validate_import_data($data);
      
        // Séparer les données valides et invalides
        $valid_data = [];
        $invalid_data = [];
        
        if (!empty($validation_results['rows'])) {
            // Parcourir chaque ligne de données
            foreach ($data as $index => $row) {
                $row_number = $index + 2; // Correspond à la numérotation dans le validateur
                
                if (isset($validation_results['rows'][$row_number])) {
                    // La ligne a des erreurs
                    $invalid_data[] = [
                        'row' => $row,
                        'errors' => $validation_results['rows'][$row_number]
                    ];
                } else {
                    // La ligne est valide
                    $valid_data[] = $row;
                }
            }
        } else {
            // Toutes les données sont valides
            $valid_data = $data;
        }

        $import_report['failed'] = count($invalid_data);
        $import_report['errors'] = $invalid_data;
       
        // S'il n'y a aucune donnée valide, rediriger avec rapport d'erreur
        if (empty($valid_data) && empty($invalid_data)) {
            session_set('warning_message', ['content' => 'Aucune donnée valide à importer.']);
            App\redirect('/apprenants?action=import');
            return;
        }
        
        // 4. Récupérer la liste d'attente existante pour vérifier les doublons
        $model = require __DIR__ . '/../Models/ApprenantModel.php';
        $waitingList = $model[Apprenant_Model_Key::GET_WAITING_LIST->value]();
        
        // Créer des tableaux pour stocker les emails et matricules existants
        $existingEmails = [];
        $existingMatricules = [];
        
        // Remplir les tableaux avec les données existantes
        foreach ($waitingList as $waiting) {
            if (isset($waiting['email']) && !empty($waiting['email'])) {
                $existingEmails[] = strtolower($waiting['email']);
            }
            if (isset($waiting['matricule']) && !empty($waiting['matricule'])) {
                $existingMatricules[] = $waiting['matricule'];
            }
        }
        
        // 5. Ajouter les données invalides à la liste d'attente en évitant les doublons
        foreach ($invalid_data as $item) {
            $apprenantData = $item['row'];
            $errors = $item['errors'];
            
            // Vérifier si l'email ou le matricule existe déjà dans la liste d'attente
            $isDuplicate = false;
            
            if (isset($apprenantData['email']) && !empty($apprenantData['email'])) {
                $email = strtolower($apprenantData['email']);
                if (in_array($email, $existingEmails)) {
                    $isDuplicate = true;
                    $import_report['duplicates']++;
                } else {
                    $existingEmails[] = $email; // Ajouter à la liste pour les prochaines vérifications
                }
            }
            
            if (isset($apprenantData['matricule']) && !empty($apprenantData['matricule'])) {
                if (in_array($apprenantData['matricule'], $existingMatricules)) {
                    $isDuplicate = true;
                    $import_report['duplicates']++;
                } else {
                    $existingMatricules[] = $apprenantData['matricule']; // Ajouter à la liste pour les prochaines vérifications
                }
            }
            
            // Si ce n'est pas un doublon, ajouter à la liste d'attente
            if (!$isDuplicate) {
                $model[Apprenant_Model_Key::ADD_TO_WAITING_LIST->value]($apprenantData, $errors);
                $import_report['waiting']++;
            }
        }
       
        // 6. Importer les données valides
        $userModel = require __DIR__ . '/../Models/User.model.php';
        $activePromoInfo = getActivePromotionAndReferentiels(); 
        
        // Récupérer tous les apprenants existants pour vérifier les doublons
        $allApprenants = $model[Apprenant_Model_Key::GET_ALL->value]();
        $allUsers = $userModel[UserModelKey::GET_ALL_EMAILS->value]();
        
        foreach ($valid_data as $row) {
            try {
                // Vérifier si le matricule ou l'email existe déjà dans la base de données
                $existingApprenant = null;
                if (isset($row['matricule']) && !empty($row['matricule'])) {
                    $existingApprenant = $model[Apprenant_Model_Key::FIND_BY_MATRICULE->value]($row['matricule'] ?? '');
                }

                $existingUser = null;
                if (isset($row['email']) && !empty($row['email'])) {
                    $existingUser = $userModel[UserModelKey::FIND_BY_EMAIL->value]($row['email']);
                }

                // Si un doublon est détecté, vérifier s'il existe déjà dans la liste d'attente
                if ($existingApprenant || $existingUser) {
                    $import_report['duplicates']++;
                    $errorMessage = [];
                    
                    if ($existingApprenant) {
                        $errorMessage[] = "Matricule déjà existant : {$row['matricule']}";
                    }
                    
                    if ($existingUser) {
                        $errorMessage[] = "Email déjà utilisé : {$row['email']}";
                    }
                    
                    // Vérifier si cet email ou matricule existe déjà dans la liste d'attente
                    $isDuplicate = false;
                    
                    if (isset($row['email']) && !empty($row['email'])) {
                        $email = strtolower($row['email']);
                        if (in_array($email, $existingEmails)) {
                            $isDuplicate = true;
                        } else {
                            $existingEmails[] = $email;
                        }
                    }
                    
                    if (isset($row['matricule']) && !empty($row['matricule'])) {
                        if (in_array($row['matricule'], $existingMatricules)) {
                            $isDuplicate = true;
                        } else {
                            $existingMatricules[] = $row['matricule'];
                        }
                    }
                    
                    // Si ce n'est pas un doublon dans la liste d'attente, l'ajouter
                    if (!$isDuplicate) {
                        $model[Apprenant_Model_Key::ADD_TO_WAITING_LIST->value]($row, ['doublon' => implode(', ', $errorMessage)]);
                        $import_report['waiting']++;
                    }
                    
                    continue;
                }

                // Générer le matricule
                $referentiels = $activePromoInfo['referentiels'];
                // Convertir $referentiels en un tableau indexé par l'ID
                $referentiels_by_id = [];
                foreach ($referentiels as $referentiel) {
                    $referentiels_by_id[$referentiel['id']] = $referentiel;
                }

                // Accéder au nom du référentiel en utilisant $row['referentiel_id']
                $referentiel_id = $row['referentiel_id'];
                if (isset($referentiels_by_id[$referentiel_id])) {
                    $referentiel_nom = $referentiels_by_id[$referentiel_id]['nom'];
                } else {
                    // Si le référentiel n'est pas trouvé, vous pouvez gérer l'erreur
                    error_log("Référentiel non trouvé pour ID: $referentiel_id");
                    $referentiel_nom = "Inconnu";
                }

                $matricule = generer_matricule($row['nom'], $referentiel_nom);
             
                // Gérer la photo si présente
                $photo_path = null;
                if (isset($row['photo_tmp']) && $row['photo_tmp']) {
                    $photo_path = handlePhotoUpload($row);
                }  
                       
                // Créer l'apprenant
                $newApprenant = [
                    'prenom' => $row['prenom'],
                    'nom' => $row['nom'],
                    'date_naissance' => $row['date_naissance'],
                    'lieu_naissance' => $row['lieu_naissance'] ?? '',
                    'matricule' => $matricule,
                    'adresse' => $row['adresse'],
                    'telephone' => $row['telephone'],
                    'photo' => $photo_path,
                    'status' => 'actif',
                    'email' => $row['email'],
                    'promotion_id' => $activePromoInfo['promotion']['id'],
                    'referentiel_id' => $row['referentiel_id'],
                    'tuteur_prenom_nom' => $row['tuteur_prenom_nom'] ?? '',
                    'tuteur_adresse' => $row['tuteur_adresse'] ?? '',
                    'tuteur_telephone' => $row['tuteur_telephone'] ?? '',
                    'tuteur_lien_parente' => $row['tuteur_lien_parente'] ?? ''
                ]; 
               
                // Ajouter l'apprenant à la base de données
                $model[Apprenant_Model_Key::ADD->value]($newApprenant);  
                // Créer un compte utilisateur
                $userModel[UserModelKey::ADD->value]([
                    'matricule' => $matricule,
                    'email' => $row['email'],
                    'password' => 'SON@TEL2025',
                    'role' => 'apprenant',
                    'password_change_required' => true
                ]);
                
                $import_report['success']++;
            } catch (\Exception $e) {
                // Vérifier si cet email ou matricule existe déjà dans la liste d'attente
                $isDuplicate = false;
                
                if (isset($row['email']) && !empty($row['email'])) {
                    $email = strtolower($row['email']);
                    if (in_array($email, $existingEmails)) {
                        $isDuplicate = true;
                    } else {
                        $existingEmails[] = $email;
                    }
                }
                
                if (isset($row['matricule']) && !empty($row['matricule'])) {
                    if (in_array($row['matricule'], $existingMatricules)) {
                        $isDuplicate = true;
                    } else {
                        $existingMatricules[] = $row['matricule'];
                    }
                }
                
                // Si ce n'est pas un doublon dans la liste d'attente, l'ajouter
                if (!$isDuplicate) {
                    $model[Apprenant_Model_Key::ADD_TO_WAITING_LIST->value]($row, ['exception' => $e->getMessage()]);
                    $import_report['waiting']++;
                }
                
                $import_report['failed']++;
            }
        }
        
        // 7. Mettre à jour le nombre d'étudiants dans la promotion
        if ($import_report['success'] > 0) {
            $promotion = $activePromoInfo['promotion'];
            $promoModel = require __DIR__ . '/../Models/Promo.model.php';
            $promoModel['update']($promotion['id'], [
                'nbr_etudiants' => ($promotion['nbr_etudiants'] ?? 0) + $import_report['success']
            ]);
        }
        
        // 8. Définir un message simple pour l'importation
        if ($import_report['success'] > 0 && $import_report['waiting'] > 0) {
            // Cas où certains apprenants ont été importés et d'autres sont en attente
            $message = "Importation terminée : {$import_report['success']} apprenants importés avec succès, {$import_report['waiting']} placés en liste d'attente.";
            session_set('warning_message', ['content' => $message]);
        } else if ($import_report['success'] > 0) {
            // Cas où tous les apprenants ont été importés avec succès
            $message = "Importation réussie : {$import_report['success']} apprenants importés.";
            session_set('success_message', ['content' => $message]);
        } else if ($import_report['waiting'] > 0) {
            // Cas où aucun apprenant n'a été importé, tous sont en attente
            $message = "Importation terminée : Aucun apprenant importé, {$import_report['waiting']} placés en liste d'attente.";
            session_set('warning_message', ['content' => $message]);
        } else {
            // Cas où aucun apprenant n'a été importé et aucun n'est en attente
            session_set('error_message', ['content' => "Échec de l'importation : Aucun apprenant importé."]);
        }
        
        // Stocker le rapport d'importation pour l'affichage détaillé dans la page d'importation
        session_set('import_report', $import_report);
        
        App\redirect('/apprenants?action=import');
        
    } catch (\Exception $e) {
        error_log("Import error: " . $e->getMessage());
        session_set('error_message', ['content' => "Échec de l'importation : Une erreur est survenue."]);
        App\redirect('/apprenants?action=import');
    }
}

/**
 * Parse un fichier Excel et retourne les données sous forme de tableau
 */
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

// Fonction pour valider un apprenant en attente
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

// Fonction pour afficher les détails d'un apprenant
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
