    <?php
    require_once __DIR__.'/../Models/Model.php';
    require_once __DIR__.'/../Controllers/controller.php';
    require_once __DIR__.'/../Services/session.service.php';
    require_once __DIR__.'/../Controllers/PromoController.php';
    require_once __DIR__.'/../Controllers/RefController.php';
    require_once __DIR__.'/../Controllers/UserController.php';
    require_once __DIR__.'/../Controllers/ApprenantsController.php';

    use App\Controllers\Apprenants as Apprenants;
    use App\Controllers\UserController as User;
    use App\Controllers\Promotions as Promo;
    use App\Controllers\Referentiels as Referentiels;
    use App\Controllers as Controller;

    function checkAuth() 
    {
    session_init();
        if (!session_has('user')) {
            error_log('Utilisateur non authentifié');
            Controller\redirect('/');
            exit;
        }
    }

    function protectedView(string $currentPage, string $viewPath, string $headerTitle) {
        checkAuth();
        return App\Controllers\render_with_layout(
            $viewPath, 
            $headerTitle,
            $currentPage,
            [
                'currentPage' => $currentPage,
                'contentHeader' => '<h2>'.$headerTitle.'</h2>'
            ]
        );
    }

    return [

        'GET /' => function() 
        {
            session_init();
            if (session_has('user')) 
            {
                Controller\redirect(match(session_get('user.role')) 
                {
                    'admin' => '/dashboard',
                    'apprenant' => '/profile_apprenant',
                    default => '/'
                });
            }
            require_once __DIR__.'/../Views/auth/login.php';
            exit;
        },

        'POST /' => fn() => User\login(),

        'GET /dashboard' => fn() => protectedView(
            'dashboard',
            'dashboard', 
            'Bienvenue sur votre tableau de bord'
        ),

    'GET /promotions' => function () {
            session_init();
            checkAuth();
            return App\Controllers\Promotions\handlePromotionActions();
        },

        'GET /promotions/toggle' => fn() => Promo\togglePromotionStatus(),

        'POST /promotions/create' => fn() => Promo\handlePromotionActions(),
        'GET /referentiels' => function() {
            session_init();
            checkAuth();
            global $data;
            $data['referentiels'] = Referentiels\get_referentiels_by_active_promotion();
            return protectedView('referentiels', 'referentiels/index', 'Gestion des Référentiels');
        },
        
        // 'GET /referentiels' => fn() => protectedView('referentiels', 'referentiels/index.php', 'Gestion des Référentiels'),

        'POST /referentiels/create' => fn() => Referentiels\handleReferentielActions(),
        
        'GET /referentiels/{id}' => fn($id) => Referentiels\get_referentiel_by_id($id),

        'GET /referentiels/count' => fn() => Referentiels\get_nbr_referentiels(),
        
        'POST /referentiels/assign' => fn() => Referentiels\assigner_referentiels(),

        'GET /apprenants' => function() {
        session_init();
        if (!session_has('user')) {
            Controller\redirect('/');
        }
        return Apprenants\handleApprenantActions();
         },

        'POST /referentiels/unassign' => fn() => App\Controllers\Referentiels\unassignReferentiel(),
        'GET /logout' => function() {
            session_destroy_all();
            Controller\redirect('/');
            exit;
        },
        
        'POST /apprenants/create' => fn() => Apprenants\handleApprenantActions(),
        'POST /apprenants/import' => fn() => Apprenants\handleApprenantActions(),
        
        'GET /apprenants/attente' => function() {
            session_init();
            checkAuth();
            return protectedView('apprenants', 'apprenants/index', 'Liste d\'attente des Apprenants', ['action' => 'liste_attente']);
        },

        'GET /apprenants/attente/{id}' => function($id) {
            session_init();
            checkAuth();
            return protectedView('apprenants', 'apprenants/index', 'Édition d\'un Apprenant en attente', ['action' => 'edit_attente', 'id' => $id]);
        },

        'POST /apprenants/attente/{id}' => function($id) {
            session_init();
            checkAuth();
            return App\Controllers\Apprenants\updateApprenantEnAttente($id);
        },
        
        'GET /profile_apprenant' => function() {
            session_init();
            checkAuth(); 
            return protectedView('profile', 'profile_apprenant', 'Mon Profil');
        },
        'GET /forgot-password' => fn() => User\forgotPassword(),
        'POST /forgot-password' => fn() => User\forgotPassword(),
        
        // Routes pour la gestion des apprenants en attente
        'GET /apprenants/edit-waiting/{id}' => fn($id) => Apprenants\editWaitingApprenant($id),
        'POST /apprenants/update-waiting' => fn() => Apprenants\updateWaitingApprenant(),
        'POST /apprenants/remove-waiting' => fn() => Apprenants\removeWaitingApprenant(),
        'POST /apprenants/validate-waiting' => function() {
            session_init();
            if (!session_has('user')) {
                Controller\redirect('/');
            }
            return Apprenants\validateWaitingApprenant();
        },

        'POST /apprenants' => function() {
            session_init();
            if (!session_has('user')) {
                Controller\redirect('/');
            }
            return Apprenants\handleApprenantActions();
        },

      

        'GET /apprenants/show/{matricule}' => function($matricule) {
            session_init();
            checkAuth();
            return Apprenants\showApprenant($matricule);
        },

    ];