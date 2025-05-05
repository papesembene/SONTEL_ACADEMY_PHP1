<?php
namespace App\Enums;
// app/enums.php
enum DataKey: string{
    case PROMOTIONS = 'promotions';
    case USERS = 'users';
    case STUDENTS = 'students';
    case REFERENTIELS = 'referentiels';
    case APPRENANTS = 'apprenants';
    case APPRENANTS_WAITING = 'apprenants_waiting'; 
}
enum ModelFunction: string{
    case GET_ALL = 'getAll';
    case SAVE = 'save';
    case GET_BY_ID = 'getById';
    case GET_NBR = "get_nbr";
    case DELETE = 'delete';
    case UPDATE = 'update';

}
enum UserModelKey:string{
    case AUTHENTICATE = 'authenticate';
    case UPDATE_PASSWORD = 'update_password';
    case GET_BY_ID = 'get_by_id';
    case GET_USER_INDEX = 'get_user_index';
    case ADD = 'add';
    case GET_ALL_EMAILS = 'get_all_emails';
    case FIND_BY_EMAIL = 'find_by_email';
}
enum JsonOperation: string{
    case DECODE = 'decode';
    case ENCODE = 'encode';
}

enum PromotionStatus: string{
    case ACTIVE = 'actif';
    case INACTIVE = 'inactif';
}

enum ApprenantAttribute: string {
    case ID = "id";
    case MATRICULE = "matricule";
    case NAME = "nom";
    case FIRST_NAME = "prenom";
    case EMAIL = "email";
    case ADDRESS = "adresse";
    case PHONE = "telephone";
    case BIRTH_DATE = "date_naissance";
    case BIRTH_PLACE = "lieu_naissance";
    case PROMOTION_ID = "promotion_id";
    case REFERENTIEL_ID = "referentiel_id";
    case STATUS = "statut";
    case TUTEUR_NAME = "tuteur_nom";
    case TUTEUR_ADDRESS = "tuteur_adresse";
    case TUTEUR_PHONE = "tuteur_telephone";
    case TUTEUR_RELATION = "lien_parente";
    case PHOTO = "photo";
}

enum Apprenant_Model_Key: string {
    case GET_ALL = 'get_all';
    case GET_BY_ID = 'get_by_id';
    case GET_BY_MATRICULE= 'get_by_matricule';
    case GET_BY_PROMOTION = 'get_by_promotion';
    case GET_BY_REFERENTIEL = 'get_by_referentiel';
    case ADD = 'add';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case FIND_BY_MATRICULE = 'find_by_matricule';
    case FIND_BY_EMAIL = 'find_by_email';
    case GET_NBR = 'get_nbr';
    case ADD_TO_WAITING = 'add_to_waiting';
    case ADD_TO_WAITING_LIST = 'add_to_waiting_list';
    case GET_WAITING_LIST = 'get_waiting_list';
    case GET_ALL_WAITING = 'get_all_waiting';
    case GET_WAITING_BY_ID = 'get_waiting_by_id';
    case UPDATE_WAITING = 'update_waiting';
    case REMOVE_FROM_WAITING = 'remove_from_waiting';
    case REMOVE_FROM_WAITING_LIST = 'remove_from_waiting_list';
    case COUNT_WAITING = 'count_waiting'; 
}
enum PromotionAttribute: string {
    case ID = "id";
    case NAME = "nom";
    case STATUS = "statut";
    case START_DATE = "date_debut";
    case END_DATE = "date_fin";
    case REFERENTIELS = "referentiels";
    case PHOTO = "photo";
    case STUDENTS_NB = "nbr_etudiants";
}
enum Promotion_Model_Key:string {
    case DELETE = "delete";
    case GET_ALL = "get_all";
    case GET_BY_ID = "get_by_id";
    case UPDATE = "update";
    case ADD = "add";
    case GET_NBR = "get_nbr";
    case DESACTIVATE_ALL = "desactivate_all";
    case GET_BY_STATUS = "get_by_status";
    case GET_BY_NAME = "get_by_name";
    case GET_ACTIVE_PROMOTION = "get_active_promotion";
   
}



enum UtilisateurAttribute: string {
    case ID = "id";
    case NAME = "nom";
    case EMAIL = "email";
    case PASSWORD = "mot_de_passe";
    case ROLE = "role";
}
enum ErrorCode: string {
    case REQUIRED_FIELD = 'champ obligatoire manquant';
    case UNIQUE_NAME = 'unique_name';
    case PHOTO_FORMAT = 'photo_format';
    case PHOTO_SIZE = 'photo_size';
    case REFERENTIEL_REQUIRED = 'Referentiel  obligatoire';
    case DATE_FIN_BEFORE_DEBUT = 'DATE_FIN_BEFORE_DEBUT';
    case INVALID_STATUS = 'invalid_status';
    case INVALID_DATE = 'date invalide';
   case PHOTO_REQUIRED = 'photo requis';
   case NOM_TROP_LONG = 'nom trop long';
   case NOM_EXISTE = 'le nom existe deja';
    case MIN_REFERENTIELS = 'min_referentiels';
   case SESSIONS_OBLIGATOIRE = 'sessions obligatoire';
   case EMAIL_REQUIRED = 'L\'email est requis';
   case NEW_PASSWORD_REQUIRED = 'Le nouveau mot de passe est requis';
   case OLD_PASSWORD_REQUIRED = 'L\'ancien mot de passe est requis';
   case PASSWORD_TRUE = 'Les mots de passe ne correspondent pas';
   case PROMOTION_NON_SPECIFIEE = 'Promotion non spécifiée';
   case PROMOTION_INTRROUVABLE = 'Promotion introuvable'; 
   case PROMOTION_TERMINEE = 'Cette promotion est terminée. Vous ne pouvez plus modifier ses référentiels.'; 
   case REFERENTIEL_AVEC_APPRENANTS = 'Impossible de désaffecter ce référentiel car il contient des apprenants.'; 
   case ERREUR_MISE_A_JOUR_REFERENTIELS = 'Une erreur est survenue lors de la mise à jour des référentiels.'; 
    case INVALID_EMAIL = 'L\'adresse email est invalide.';
    case INVALID_PHONE = 'Le numéro de téléphone est invalide. Il doit contenir 9 chiffres et commencer par 76, 77, 78, 75 ou 70.';
    case INVALID_FILE_TYPE = 'Le type de fichier est invalide. Seuls les fichiers JPEG, PNG et GIF sont acceptés.';
    case INVALID_FILE_TYPE_IMPORT = 'Le type de fichier est invalide. Seuls les fichiers CSV, XLSX et XLS sont acceptés.';
    case FILE_TOO_LARGE = 'La taille du fichier dépasse la limite autorisée de 2 Mo.';
    case UPLOAD_ERROR = 'Une erreur est survenue lors du téléchargement du fichier.';
    case MAX_LENGTH_EXCEEDED = 'La longueur maximale autorisée pour ce champ a été dépassée.';
    case PATHINFO_EXTENSION = 'L\'extension du fichier est invalide.';
    case FILE_TOO_LARGE_IMPORT = 'La taille du fichier dépasse la limite autorisée de 5 Mo.';
    case INVALID_FILE_EXTENSION = 'L\'extension du fichier est invalide. Seules les extensions CSV, XLSX et XLS sont acceptées.';

}

enum SuccessCode: string {
    case PROMOTION_CREATED = 'promotion cree avec success';
    case PASSWORD_OK = 'Votre mot de passe a été mis à jour avec succès';
    case PROMOTION_UPDATED = 'promotion modifier avec succees';
    case REFERENTIEL_CREATED = 'Référentiel créé avec succès';
    case REFERENTIEL_UPDATED = 'Référentiel mis à jour avec succès';
    case REFERENTIEL_DELETED = 'Référentiel supprimé avec succès';
    case REFERENTIEL_ASSIGNE = 'référentiel(s) ajouté(s) avec succès';
}

enum ReferentielAttribute: string {
    case ID = 'id';
    case NAME = 'nom';
    case DESCRIPTION = 'description';
    case PHOTO = 'photo';
    case CAPACITY = 'capacite';
    case SESSIONS = 'sessions';
    case MODULES_NB = 'nbr_modules';
}


enum Referentiel_Model_Key: string {
    case GET_ALL = 'get_all';
    case GET_BY_ID = 'get_by_id';
    case ADD = 'add';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case GET_NBR = 'get_nbr';
    case SEARCH = 'search';
    case GET_BY_NAME = "get_by_name";
    case EXISTS = 'exists';

}


enum ApprenantAttente_Model_Key: string {
    case GET_ALL = 'get_all';
    case CREATE = 'create';
    case GET_BY_ID = 'get_by_id';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case COUNT = 'count';
    case VALIDATE = 'validate';
}
