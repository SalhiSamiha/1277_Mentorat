<?php
// Fonctions du thème enfant

// Charger les styles et scripts
function theme_enfant_scripts() {
   
    // Charger le style du thème parent
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    // Charger le style du thème enfant
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
    
    // Charger le JavaScript pour les demandes de mentorat
    wp_enqueue_script('mentor-js', get_stylesheet_directory_uri() . '/js/mentor.js', array('jquery'), '1.0', true);
    
    // Localisation pour AJAX - UNIQUEMENT pour les utilisateurs connectés
    if (is_user_logged_in()) {
        wp_localize_script('mentor-js', 'mentor_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('demande_mentor_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'theme_enfant_scripts');

// Créer le type de post personnalisé pour les mentors
function creer_type_post_mentor() {
    $labels = array(
        'name' => 'Mentors',
        'singular_name' => 'Mentor',
        'menu_name' => 'Mentors',
        'name_admin_bar' => 'Mentor',
        'add_new' => 'Ajouter un mentor',
        'add_new_item' => 'Ajouter un nouveau mentor',
        'new_item' => 'Nouveau mentor',
        'edit_item' => 'Modifier le mentor',
        'view_item' => 'Voir le mentor',
        'all_items' => 'Tous les mentors',
        'search_items' => 'Rechercher des mentors',
        'not_found' => 'Aucun mentor trouvé',
        'not_found_in_trash' => 'Aucun mentor dans la corbeille'
    );
    
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-businessperson',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
    );
    
    register_post_type('mentor', $args);
}
add_action('init', 'creer_type_post_mentor');

// Ajouter les métadonnées pour les mentors
function add_mentor_metadata() {
    if (!get_option('mentor_domaines')) {
        add_option('mentor_domaines', array('Développement', 'Design', 'Marketing', 'Finance', 'Santé'));
    }
    if (!get_option('mentor_langues')) {
        add_option('mentor_langues', array('Français', 'Anglais', 'Espagnol'));
    }
    if (!get_option('mentor_regions')) {
        add_option('mentor_regions', array('Europe', 'Amérique du Nord', 'Amérique latine', 'Asie', 'Afrique'));
    }
}
add_action('init', 'add_mentor_metadata');

// Traitement AJAX pour les demandes de jumelage
function demande_mentorat() {
    // Vérifier le nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'demande_mentor_nonce')) {
        wp_send_json_error('Erreur de sécurité');
    }
    
    // Vérifier que l'utilisateur est connecté
    if (!is_user_logged_in()) {
        wp_send_json_error('Utilisateur non connecté');
    }
    
    $mentor_id = intval($_POST['mentor_id']);
    $user_id = get_current_user_id();
    
    if ($user_id && $mentor_id) {
        // Récupérer les demandes existantes
        $demandes_existantes = get_user_meta($user_id, 'demandes_mentorat', true);
        if (empty($demandes_existantes)) {
            $demandes_existantes = array();
        }
        
        // Vérifier si une demande existe déjà pour ce mentor
        foreach ($demandes_existantes as $demande) {
            if ($demande['mentor_id'] == $mentor_id) {
                wp_send_json_error('Demande déjà existante');
            }
        }
        
        // Ajouter la nouvelle demande
        $demandes_existantes[] = array(
            'mentor_id' => $mentor_id,
            'date' => current_time('mysql'),
            'statut' => 'en_attente'
        );
        
        // Mettre à jour les métadonnées utilisateur
        update_user_meta($user_id, 'demandes_mentorat', $demandes_existantes);
        
        wp_send_json_success('Demande envoyée avec succès');
    } else {
        wp_send_json_error('Données manquantes');
    }
}
add_action('wp_ajax_demande_mentorat', 'demande_mentorat');

// Gérer les utilisateurs non connectés qui tentent de faire une demande
function mentorat_non_connecte() {
    wp_send_json_error('Veuillez vous connecter pour faire une demande');
}
add_action('wp_ajax_nopriv_demande_mentorat', 'mentorat_non_connecte');

// Ajouter un shortcode pour afficher le formulaire de recherche
function shortcode_recherche_mentor() {
    ob_start();
    include get_stylesheet_directory() . '/formulaire-recherche-mentor.php';
    return ob_get_clean();
}
add_shortcode('recherche_mentor', 'shortcode_recherche_mentor');

// Flusher les permaliens à l'activation du thème
function theme_enfant_activation() {
    creer_type_post_mentor();
    add_mentor_metadata();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'theme_enfant_activation');

// Créer les rôles personnalisés Mentor et Mentoré
function creer_roles_personnalises() {
    // Rôle Mentor
    add_role('mentor', 'Mentor', array(
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => true,
        'upload_files' => true,
        'publish_posts' => true,
        'edit_published_posts' => true,
        'delete_published_posts' => true,
    ));
    
    // Rôle Mentoré
    add_role('mentore', 'Mentoré', array(
        'read' => true,
        'edit_posts' => false,
        'upload_files' => true,
    ));
}
add_action('init', 'creer_roles_personnalises');

// Configuration ACF pour les rôles utilisateurs - VERSION SIMPLIFIÉE
function configurer_champs_acf_par_role() {
    if (!function_exists('acf_add_local_field_group')) return;
    
    // UN SEUL groupe de champs pour TOUS les utilisateurs avec tous les champs
    acf_add_local_field_group(array(
        'key' => 'group_user_profile_complet',
        'title' => 'Profil Utilisateur Complet',
        'fields' => array(
            // Image de profil pour tous les utilisateurs
            array(
                'key' => 'field_user_photo',
                'label' => 'Photo de profil',
                'name' => 'user_photo',
                'type' => 'image',
                'instructions' => 'Téléchargez votre photo de profil (format recommandé : 300x300px)',
                'required' => 0,
                'return_format' => 'url',
                'preview_size' => 'medium',
                'library' => 'all',
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
            ),
            // Champs communs à tous
            array(
                'key' => 'field_user_bio',
                'label' => 'Bio',
                'name' => 'user_bio',
                'type' => 'textarea',
                'instructions' => 'Votre biographie personnelle',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
            ),
            array(
                'key' => 'field_user_phone',
                'label' => 'Téléphone',
                'name' => 'user_phone',
                'type' => 'text',
                'instructions' => 'Votre numéro de téléphone',
                'required' => 0,
            ),
            array(
                'key' => 'field_user_location',
                'label' => 'Localisation',
                'name' => 'user_location',
                'type' => 'text',
                'instructions' => 'Ville et pays',
                'required' => 0,
            ),
            array(
                'key' => 'field_user_interests',
                'label' => 'Centres d\'intérêt',
                'name' => 'user_interests',
                'type' => 'textarea',
                'instructions' => 'Vos passions et centres d\'intérêt',
                'required' => 0,
                'rows' => 4,
            ),
            // Champs spécifiques aux MENTORS
            array(
                'key' => 'field_user_skills',
                'label' => 'Compétences (Mentor)',
                'name' => 'user_skills',
                'type' => 'checkbox',
                'instructions' => 'Sélectionnez vos compétences',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentor',
                        ),
                    ),
                ),
                'choices' => array(
                    'developpement' => 'Développement Web',
                    'design' => 'Design UX/UI',
                    'marketing' => 'Marketing Digital',
                    'data' => 'Data Science',
                    'entrepreneuriat' => 'Entrepreneuriat',
                    'gestion' => 'Gestion de projet',
                ),
                'layout' => 'vertical',
            ),
            array(
                'key' => 'field_expertise_level',
                'label' => 'Niveau d\'expertise (Mentor)',
                'name' => 'expertise_level',
                'type' => 'select',
                'instructions' => 'Votre niveau d\'expertise global',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentor',
                        ),
                    ),
                ),
                'choices' => array(
                    'debutant' => 'Débutant',
                    'intermediaire' => 'Intermédiaire',
                    'avance' => 'Avancé',
                    'expert' => 'Expert',
                ),
            ),
            array(
                'key' => 'field_availability_status',
                'label' => 'Statut de disponibilité (Mentor)',
                'name' => 'availability_status',
                'type' => 'select',
                'instructions' => 'Votre statut actuel',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentor',
                        ),
                    ),
                ),
                'choices' => array(
                    'disponible' => 'Disponible',
                    'occupe' => 'Occupé',
                    'vacances' => 'En vacances',
                ),
            ),
            // NOUVEAU : Créneaux horaires pour les mentors
            array(
                'key' => 'field_time_slots',
                'label' => 'Créneaux horaires disponibles',
                'name' => 'time_slots',
                'type' => 'repeater',
                'instructions' => 'Ajoutez vos créneaux horaires de disponibilité',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentor',
                        ),
                    ),
                ),
                'layout' => 'block',
                'button_label' => 'Ajouter un créneau',
                'sub_fields' => array(
                    array(
                        'key' => 'field_day',
                        'label' => 'Jour',
                        'name' => 'day',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 1,
                        'choices' => array(
                            'lundi' => 'Lundi',
                            'mardi' => 'Mardi',
                            'mercredi' => 'Mercredi',
                            'jeudi' => 'Jeudi',
                            'vendredi' => 'Vendredi',
                            'samedi' => 'Samedi',
                            'dimanche' => 'Dimanche',
                        ),
                        'default_value' => '',
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 0,
                        'return_format' => 'value',
                        'ajax' => 0,
                        'placeholder' => '',
                    ),
                    array(
                        'key' => 'field_start_time',
                        'label' => 'Heure de début',
                        'name' => 'start_time',
                        'type' => 'time_picker',
                        'instructions' => '',
                        'required' => 1,
                        'display_format' => 'H:i',
                        'return_format' => 'H:i',
                    ),
                    array(
                        'key' => 'field_end_time',
                        'label' => 'Heure de fin',
                        'name' => 'end_time',
                        'type' => 'time_picker',
                        'instructions' => '',
                        'required' => 1,
                        'display_format' => 'H:i',
                        'return_format' => 'H:i',
                    ),
                ),
            ),
            // Champs spécifiques aux MENTORÉS
            array(
                'key' => 'field_learning_goals',
                'label' => 'Objectifs d\'apprentissage (Mentoré)',
                'name' => 'learning_goals',
                'type' => 'textarea',
                'instructions' => 'Ce que vous souhaitez apprendre',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentore',
                        ),
                    ),
                ),
                'rows' => 4,
            ),
            array(
                'key' => 'field_desired_skills',
                'label' => 'Compétences recherchées (Mentoré)',
                'name' => 'desired_skills',
                'type' => 'checkbox',
                'instructions' => 'Compétences que vous souhaitez développer',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentore',
                        ),
                    ),
                ),
                'choices' => array(
                    'developpement' => 'Développement Web',
                    'design' => 'Design UX/UI',
                    'marketing' => 'Marketing Digital',
                    'data' => 'Data Science',
                    'entrepreneuriat' => 'Entrepreneuriat',
                    'gestion' => 'Gestion de projet',
                ),
                'layout' => 'vertical',
            ),
            array(
                'key' => 'field_current_level',
                'label' => 'Niveau actuel (Mentoré)',
                'name' => 'current_level',
                'type' => 'select',
                'instructions' => 'Votre niveau actuel',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_user_role_condition',
                            'operator' => '==',
                            'value' => 'mentore',
                        ),
                    ),
                ),
                'choices' => array(
                    'debutant' => 'Débutant complet',
                    'debutant_avance' => 'Débutant avancé',
                    'intermediaire' => 'Intermédiaire',
                    'avance' => 'Avancé',
                ),
            ),
            // Champ caché pour la logique conditionnelle
            array(
                'key' => 'field_user_role_condition',
                'label' => 'Rôle utilisateur',
                'name' => 'user_role_condition',
                'type' => 'text',
                'wrapper' => array(
                    'class' => 'acf-hidden',
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'user_role',
                    'operator' => '==',
                    'value' => 'all',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
    ));
}
add_action('acf/init', 'configurer_champs_acf_par_role');

// Définir automatiquement le rôle pour la logique conditionnelle ACF
function definir_role_utilisateur_acf($user_id) {
    $user = get_userdata($user_id);
    if ($user) {
        $roles = $user->roles;
        $primary_role = !empty($roles) ? $roles[0] : '';
        update_field('user_role_condition', $primary_role, 'user_' . $user_id);
    }
}
add_action('profile_update', 'definir_role_utilisateur_acf');
add_action('user_register', 'definir_role_utilisateur_acf');

// Shortcode pour afficher le formulaire d'inscription avec choix de rôle
function formulaire_inscription_avec_role() {
    // Permettre l'affichage même si connecté (pour test)
    $is_logged_in = is_user_logged_in();
    
    ob_start();
    ?>
    <div class="registration-form">
        <?php if ($is_logged_in): ?>
        <div class="alert alert-info">
            <strong>Mode test :</strong> Vous êtes déjà connecté. Ce formulaire créera un nouveau compte.
        </div>
        <?php endif; ?>
        
        <h3>Créer un compte</h3>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="creer_compte_utilisateur">
            
            <div class="form-group">
                <label for="user_login">Nom d'utilisateur *</label>
                <input type="text" name="user_login" id="user_login" required>
            </div>
            
            <div class="form-group">
                <label for="user_email">Email *</label>
                <input type="email" name="user_email" id="user_email" required>
            </div>
            
            <div class="form-group">
                <label for="user_role">Je souhaite être *</label>
                <select name="user_role" id="user_role" required>
                    <option value="">Choisissez un rôle</option>
                    <option value="mentor">Mentor</option>
                    <option value="mentore">Mentoré</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe *</label>
                <input type="password" name="password_confirm" id="password_confirm" required>
            </div>
            
            <button type="submit" class="submit-button">Créer mon compte</button>
        </form>
    </div>
    
    <style>
    .registration-form {
        max-width: 500px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1rem;
        border-left: 4px solid #bee5eb;
    }
    
    .registration-form .form-group {
        margin-bottom: 1rem;
    }
    
    .registration-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .registration-form input,
    .registration-form select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .submit-button {
        background: #3498db;
        color: white;
        padding: 1rem 2rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        font-size: 1rem;
    }
    
    .submit-button:hover {
        background: #2980b9;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('formulaire_inscription', 'formulaire_inscription_avec_role');

// Traitement de la création de compte
function traiter_creation_compte() {
    if (isset($_POST['user_login']) && isset($_POST['user_email']) && isset($_POST['user_role'])) {
        $user_login = sanitize_user($_POST['user_login']);
        $user_email = sanitize_email($_POST['user_email']);
        $user_role = sanitize_text_field($_POST['user_role']);
        $password = $_POST['password'];
        
        // Validation
        if ($password !== $_POST['password_confirm']) {
            wp_die('Les mots de passe ne correspondent pas.');
        }
        
        if (!in_array($user_role, array('mentor', 'mentore'))) {
            wp_die('Rôle invalide.');
        }
        
        // Créer l'utilisateur
        $user_id = wp_create_user($user_login, $password, $user_email);
        
        if (is_wp_error($user_id)) {
            wp_die($user_id->get_error_message());
        }
        
        // Assigner le rôle
        $user = new WP_User($user_id);
        $user->set_role($user_role);
        
        // Définir le rôle pour ACF
        update_field('user_role_condition', $user_role, 'user_' . $user_id);
        
        // Connecter l'utilisateur automatiquement
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        // Rediriger vers la page de profil
        wp_redirect(home_url('/mon-profil'));
        exit;
    }
}
add_action('admin_post_nopriv_creer_compte_utilisateur', 'traiter_creation_compte');
add_action('admin_post_creer_compte_utilisateur', 'traiter_creation_compte');

// AJAX pour récupérer le compteur de demandes
function get_demandes_count() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Non connecté');
    }
    
    $user_id = get_current_user_id();
    $demandes = get_user_meta($user_id, 'demandes_mentorat', true);
    $count = is_array($demandes) ? count($demandes) : 0;
    
    wp_send_json_success(array('count' => $count));
}
add_action('wp_ajax_get_demandes_count', 'get_demandes_count');

// Shortcode pour le formulaire de recherche de mentors
function mentor_search_form_shortcode() {
    ob_start();
    ?>
    <div class="form-recherche-mentor-container">
        <form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="form-recherche-mentor">
            <input type="hidden" name="recherche_mentor" value="1">
            
            <div class="form-group">
                <label for="domaine">Domaine d'expertise</label>
                <select name="domaine" id="domaine">
                    <option value="">Tous les domaines</option>
                    <option value="developpement" <?php echo selected($_GET['domaine'] ?? '', 'developpement'); ?>>Développement Web</option>
                    <option value="design" <?php echo selected($_GET['domaine'] ?? '', 'design'); ?>>Design UX/UI</option>
                    <option value="marketing" <?php echo selected($_GET['domaine'] ?? '', 'marketing'); ?>>Marketing Digital</option>
                    <option value="data" <?php echo selected($_GET['domaine'] ?? '', 'data'); ?>>Data Science</option>
                    <option value="entrepreneuriat" <?php echo selected($_GET['domaine'] ?? '', 'entrepreneuriat'); ?>>Entrepreneuriat</option>
                    <option value="gestion" <?php echo selected($_GET['domaine'] ?? '', 'gestion'); ?>>Gestion de projet</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="langue">Langue</label>
                <select name="langue" id="langue">
                    <option value="">Toutes les langues</option>
                    <option value="fr" <?php echo selected($_GET['langue'] ?? '', 'fr'); ?>>Français</option>
                    <option value="en" <?php echo selected($_GET['langue'] ?? '', 'en'); ?>>Anglais</option>
                    <option value="es" <?php echo selected($_GET['langue'] ?? '', 'es'); ?>>Espagnol</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="region">Région</label>
                <select name="region" id="region">
                    <option value="">Toutes les régions</option>
                    <option value="europe" <?php echo selected($_GET['region'] ?? '', 'europe'); ?>>Europe</option>
                    <option value="amerique-nord" <?php echo selected($_GET['region'] ?? '', 'amerique-nord'); ?>>Amérique du Nord</option>
                    <option value="amerique-sud" <?php echo selected($_GET['region'] ?? '', 'amerique-sud'); ?>>Amérique du Sud</option>
                    <option value="asie" <?php echo selected($_GET['region'] ?? '', 'asie'); ?>>Asie</option>
                    <option value="afrique" <?php echo selected($_GET['region'] ?? '', 'afrique'); ?>>Afrique</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="expertise_level">Niveau d'expertise</label>
                <select name="expertise_level" id="expertise_level">
                    <option value="">Tous les niveaux</option>
                    <option value="debutant" <?php echo selected($_GET['expertise_level'] ?? '', 'debutant'); ?>>Débutant</option>
                    <option value="intermediaire" <?php echo selected($_GET['expertise_level'] ?? '', 'intermediaire'); ?>>Intermédiaire</option>
                    <option value="avance" <?php echo selected($_GET['expertise_level'] ?? '', 'avance'); ?>>Avancé</option>
                    <option value="expert" <?php echo selected($_GET['expertise_level'] ?? '', 'expert'); ?>>Expert</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="availability_status">Disponibilité</label>
                <select name="availability_status" id="availability_status">
                    <option value="">Tous les statuts</option>
                    <option value="disponible" <?php echo selected($_GET['availability_status'] ?? '', 'disponible'); ?>>Disponible</option>
                    <option value="occupe" <?php echo selected($_GET['availability_status'] ?? '', 'occupe'); ?>>Occupé</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="jour_creneau">Jour de disponibilité</label>
                <select name="jour_creneau" id="jour_creneau">
                    <option value="">Tous les jours</option>
                    <option value="lundi" <?php echo selected($_GET['jour_creneau'] ?? '', 'lundi'); ?>>Lundi</option>
                    <option value="mardi" <?php echo selected($_GET['jour_creneau'] ?? '', 'mardi'); ?>>Mardi</option>
                    <option value="mercredi" <?php echo selected($_GET['jour_creneau'] ?? '', 'mercredi'); ?>>Mercredi</option>
                    <option value="jeudi" <?php echo selected($_GET['jour_creneau'] ?? '', 'jeudi'); ?>>Jeudi</option>
                    <option value="vendredi" <?php echo selected($_GET['jour_creneau'] ?? '', 'vendredi'); ?>>Vendredi</option>
                    <option value="samedi" <?php echo selected($_GET['jour_creneau'] ?? '', 'samedi'); ?>>Samedi</option>
                    <option value="dimanche" <?php echo selected($_GET['jour_creneau'] ?? '', 'dimanche'); ?>>Dimanche</option>
                </select>
            </div>
            
            <div class="form-group actions">
                <input type="submit" value="🔍 Rechercher" class="btn-rechercher">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="btn-reinitialiser">🔄 Réinitialiser</a>
            </div>
        </form>
    </div>

    <style>
    .form-recherche-mentor-container {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .form-recherche-mentor {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .form-recherche-mentor .form-group {
        margin-bottom: 0;
    }
    
    .form-recherche-mentor label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .form-recherche-mentor select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }
    
    .form-recherche-mentor .actions {
        grid-column: 1 / -1;
        text-align: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }
    
    .btn-rechercher {
        background: #3498db;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        margin-right: 1rem;
    }
    
    .btn-rechercher:hover {
        background: #2980b9;
    }
    
    .btn-reinitialiser {
        background: #95a5a6;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-reinitialiser:hover {
        background: #7f8c8d;
        color: white;
    }
    
    @media (max-width: 768px) {
        .form-recherche-mentor {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('mentor_search_form', 'mentor_search_form_shortcode');

// Shortcode pour les résultats de recherche de mentors
function mentor_search_results_shortcode() {
    ob_start();
    
    // Vérifier si une recherche a été effectuée
    if (isset($_GET['recherche_mentor']) && $_GET['recherche_mentor'] == '1') {
        
        // Récupérer et sécuriser les critères de recherche
        $domaine = isset($_GET['domaine']) ? sanitize_text_field($_GET['domaine']) : '';
        $langue = isset($_GET['langue']) ? sanitize_text_field($_GET['langue']) : '';
        $region = isset($_GET['region']) ? sanitize_text_field($_GET['region']) : '';
        $expertise_level = isset($_GET['expertise_level']) ? sanitize_text_field($_GET['expertise_level']) : '';
        $availability_status = isset($_GET['availability_status']) ? sanitize_text_field($_GET['availability_status']) : '';
        $jour_creneau = isset($_GET['jour_creneau']) ? sanitize_text_field($_GET['jour_creneau']) : '';
        
        // Construire la requête des utilisateurs
        $args = array(
            'role' => 'mentor',
            'meta_query' => array(
                'relation' => 'AND'
            )
        );
        
        // Ajouter les filtres de métadonnées si des valeurs sont spécifiées
        if (!empty($domaine)) {
            $args['meta_query'][] = array(
                'key' => 'user_skills',
                'value' => $domaine,
                'compare' => 'LIKE'
            );
        }
        
        if (!empty($langue)) {
            $args['meta_query'][] = array(
                'key' => 'user_langue',
                'value' => $langue,
                'compare' => '='
            );
        }
        
        if (!empty($region)) {
            $args['meta_query'][] = array(
                'key' => 'user_region',
                'value' => $region,
                'compare' => '='
            );
        }
        
        if (!empty($expertise_level)) {
            $args['meta_query'][] = array(
                'key' => 'expertise_level',
                'value' => $expertise_level,
                'compare' => '='
            );
        }
        
        if (!empty($availability_status)) {
            $args['meta_query'][] = array(
                'key' => 'availability_status',
                'value' => $availability_status,
                'compare' => '='
            );
        }
        
        // Exécuter la requête de base
        $mentors = get_users($args);
        
        // Filtrer par créneaux horaires si spécifié
        if (!empty($jour_creneau) && !empty($mentors)) {
            $mentors_filtres = array();
            
            foreach ($mentors as $mentor) {
                $user_id = $mentor->ID;
                $time_slots = get_field('time_slots', 'user_' . $user_id);
                
                // Vérifier si le mentor a des créneaux pour le jour demandé
                $has_slot = false;
                if ($time_slots && is_array($time_slots)) {
                    foreach ($time_slots as $slot) {
                        if (isset($slot['day']) && $slot['day'] === $jour_creneau) {
                            $has_slot = true;
                            break;
                        }
                    }
                }
                
                if ($has_slot) {
                    $mentors_filtres[] = $mentor;
                }
            }
            
            $mentors = $mentors_filtres;
        }
        
        // Afficher les résultats
        if (!empty($mentors)) {
            echo '<div class="liste-mentors">';
            echo '<h2>🎯 ' . count($mentors) . ' mentor(s) trouvé(s)</h2>';
            
            foreach ($mentors as $mentor) {
                $user_id = $mentor->ID;
                $profile_photo = get_field('user_photo', 'user_' . $user_id);
                $user_skills = get_field('user_skills', 'user_' . $user_id);
                $expertise_level = get_field('expertise_level', 'user_' . $user_id);
                $user_bio = get_field('user_bio', 'user_' . $user_id);
                $user_location = get_field('user_location', 'user_' . $user_id);
                $availability_status = get_field('availability_status', 'user_' . $user_id);
                $time_slots = get_field('time_slots', 'user_' . $user_id);
                ?>
                <div class="mentor-card">
                    <div class="mentor-header">
                        <div class="mentor-avatar">
                            <?php if ($profile_photo) : ?>
                                <img src="<?php echo esc_url($profile_photo); ?>" alt="<?php echo esc_attr($mentor->display_name); ?>">
                            <?php else : ?>
                                <?php echo get_avatar($user_id, 80); ?>
                            <?php endif; ?>
                        </div>
                        <div class="mentor-info">
                            <h3><?php echo esc_html($mentor->display_name); ?></h3>
                            <?php if ($expertise_level) : ?>
                                <p class="mentor-expertise">📊 Niveau <?php echo esc_html($expertise_level); ?></p>
                            <?php endif; ?>
                            <?php if ($availability_status) : ?>
                                <span class="availability-status <?php echo esc_attr($availability_status); ?>">
                                    <?php 
                                    $status_labels = array(
                                        'disponible' => '🟢 Disponible',
                                        'occupe' => '🔴 Occupé',
                                        'vacances' => '🟡 En vacances'
                                    );
                                    echo esc_html($status_labels[$availability_status] ?? $availability_status); 
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mentor-details">
                        <?php if ($user_skills && is_array($user_skills)) : ?>
                            <div class="mentor-skills">
                                <strong>💼 Compétences :</strong>
                                <div class="skills-list">
                                    <?php foreach ($user_skills as $skill) : ?>
                                        <span class="skill-tag"><?php echo esc_html($skill); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($user_location) : ?>
                            <p><strong>📍 Localisation :</strong> <?php echo esc_html($user_location); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($time_slots && is_array($time_slots)) : ?>
                            <div class="mentor-availability">
                                <strong>⏰ Disponibilités :</strong>
                                <div class="time-slots">
                                    <?php 
                                    $slots_by_day = array();
                                    foreach ($time_slots as $slot) {
                                        if (isset($slot['day']) && isset($slot['start_time']) && isset($slot['end_time'])) {
                                            $day = $slot['day'];
                                            $time_range = $slot['start_time'] . ' - ' . $slot['end_time'];
                                            $slots_by_day[$day][] = $time_range;
                                        }
                                    }
                                    
                                    $days_display = array();
                                    $days_order = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
                                    
                                    foreach ($days_order as $day) {
                                        if (isset($slots_by_day[$day])) {
                                            $days_display[] = ucfirst($day) . ' (' . implode(', ', $slots_by_day[$day]) . ')';
                                        }
                                    }
                                    
                                    if (!empty($days_display)) {
                                        echo esc_html(implode(' | ', $days_display));
                                    } else {
                                        echo 'Aucun créneau défini';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($user_bio) : ?>
                        <div class="mentor-bio">
                            <p><?php echo wp_kses_post(wp_trim_words($user_bio, 30)); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mentor-actions">
                        <button class="btn-demande-mentorat" data-mentor-id="<?php echo $user_id; ?>">
                            📨 Faire une demande de jumelage
                        </button>
                    </div>
                </div>
                <?php
            }
            
            echo '</div>';
        } else {
            echo '<div class="aucun-resultat">';
            echo '<h3>😔 Aucun mentor ne correspond à vos critères de recherche.</h3>';
            echo '<p>Essayez de modifier vos filtres ou <a href="' . esc_url(get_permalink()) . '">réinitialisez la recherche</a>.</p>';
            echo '</div>';
        }
    } else {
        // Message d'accueil quand aucune recherche n'est effectuée
        echo '<div class="message-accueil">';
        echo '<h3>👋 Bienvenue dans notre plateforme de mentorat</h3>';
        echo '<p>Utilisez les filtres ci-dessus pour trouver le mentor qui correspond à vos besoins.</p>';
        echo '<p>Vous pouvez filtrer par domaine d\'expertise, langue, région, niveau d\'expertise, disponibilité et créneaux horaires.</p>';
        echo '</div>';
    }
    
    // Ajouter le CSS pour les résultats
    ?>
    <style>
    .liste-mentors {
        margin-top: 2rem;
    }
    
    .mentor-card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #3498db;
    }
    
    .mentor-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .mentor-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #3498db;
    }
    
    .mentor-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .mentor-info h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1.3rem;
    }
    
    .mentor-expertise {
        color: #7f8c8d;
        margin: 0;
        font-style: italic;
    }
    
    .availability-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .availability-status.disponible {
        background: #d4edda;
        color: #155724;
    }
    
    .availability-status.occupe {
        background: #f8d7da;
        color: #721c24;
    }
    
    .availability-status.vacances {
        background: #fff3cd;
        color: #856404;
    }
    
    .mentor-details {
        margin-bottom: 1.5rem;
    }
    
    .mentor-skills {
        margin-bottom: 1rem;
    }
    
    .skills-list {
        margin-top: 0.5rem;
    }
    
    .skill-tag {
        background: #3498db;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        margin: 0.25rem;
        display: inline-block;
    }
    
    .mentor-availability {
        margin-top: 1rem;
    }
    
    .time-slots {
        margin-top: 0.5rem;
        font-size: 0.9rem;
        color: #555;
    }
    
    .mentor-bio {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 1.5rem;
        border-left: 3px solid #2ecc71;
    }
    
    .mentor-actions {
        text-align: center;
    }
    
    .btn-demande-mentorat {
        background: #27ae60;
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        transition: background 0.3s;
    }
    
    .btn-demande-mentorat:hover {
        background: #219a52;
    }
    
    .message-accueil, .aucun-resultat {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .aucun-resultat h3 {
        color: #e74c3c;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .mentor-header {
            flex-direction: column;
            text-align: center;
        }
        
        .mentor-details {
            text-align: center;
        }
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('mentor_search_results', 'mentor_search_results_shortcode');

// Fonction helper pour selected (si elle n'existe pas déjà)
if (!function_exists('selected')) {
    function selected($param, $value) {
        if (isset($param) && $param === $value) {
            return 'selected="selected"';
        }
        return '';
    }
}

// Shortcode principal pour le dashboard
function dashboard_utilisateur_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="alert alert-warning">🔐 Veuillez vous connecter pour accéder à votre dashboard.</div>';
    }
    
    $user = wp_get_current_user();
    $roles = $user->roles;
    
    // Charger le dashboard approprié selon le rôle
    if (in_array('mentor', $roles)) {
        return charger_dashboard_mentor($user);
    } elseif (in_array('mentore', $roles)) {
        return charger_dashboard_mentore($user);
    } else {
        return '<div class="alert alert-info">👋 Bienvenue ! Votre rôle n\'a pas de dashboard spécifique.</div>';
    }
}
add_shortcode('dashboard_utilisateur', 'dashboard_utilisateur_shortcode');

// Fonctions de chargement des dashboards
function charger_dashboard_mentor($user) {
    $dashboard_file = get_stylesheet_directory() . '/dashboard-mentor.php';
    
    if (file_exists($dashboard_file)) {
        ob_start();
        include $dashboard_file;
        return ob_get_clean();
    } else {
        return '<div class="alert alert-error">❌ Fichier dashboard mentor non trouvé.</div>';
    }
}

function charger_dashboard_mentore($user) {
    $dashboard_file = get_stylesheet_directory() . '/dashboard-mentore.php';
    
    if (file_exists($dashboard_file)) {
        ob_start();
        include $dashboard_file;
        return ob_get_clean();
    } else {
        return '<div class="alert alert-error">❌ Fichier dashboard mentoré non trouvé.</div>';
    }
}

// =============================================
// SYSTÈME DE DEMANDES DE MENTORAT
// =============================================

// Table pour stocker les demandes de mentorat
function creer_table_demandes_mentorat() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_demandes';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        mentore_id mediumint(9) NOT NULL,
        mentor_id mediumint(9) NOT NULL,
        date_demande datetime DEFAULT CURRENT_TIMESTAMP,
        statut varchar(20) DEFAULT 'en_attente',
        message_demande text,
        objectif_mentore text,
        competences_recherchees text,
        date_decision datetime,
        notes_mentor text,
        PRIMARY KEY (id),
        UNIQUE KEY demande_unique (mentore_id, mentor_id, statut)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'creer_table_demandes_mentorat');

// Fonction pour envoyer une demande de mentorat
function envoyer_demande_mentorat($mentor_id, $message = '', $objectif = '', $competences = '') {
    
    // Vérifier que l'utilisateur est connecté et est un mentoré
    if (!is_user_logged_in()) {
        return array('success' => false, 'message' => 'Vous devez être connecté pour envoyer une demande.');
    }
    
    $mentore_id = get_current_user_id();
    $user = wp_get_current_user();
    
    if (!in_array('mentore', $user->roles)) {
        return array('success' => false, 'message' => 'Seuls les mentorés peuvent envoyer des demandes.');
    }
    
    // Vérifier que le mentor existe et a le bon rôle
    $mentor = get_userdata($mentor_id);
    if (!$mentor || !in_array('mentor', $mentor->roles)) {
        return array('success' => false, 'message' => 'Mentor non trouvé.');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'mentor_demandes';
    
    // Vérifier si une demande existe déjà
    $demande_existante = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name 
         WHERE mentore_id = %d AND mentor_id = %d AND statut = 'en_attente'",
        $mentore_id, $mentor_id
    ));
    
    if ($demande_existante) {
        return array('success' => false, 'message' => 'Vous avez déjà une demande en attente avec ce mentor.');
    }
    
    // Vérifier si une assignation existe déjà
    if (est_assignation_actuelle($mentor_id, $mentore_id)) {
        return array('success' => false, 'message' => 'Vous êtes déjà assigné à ce mentor.');
    }
    
    // Récupérer les infos du mentoré si non fournies
    if (empty($objectif)) {
        $objectif = get_field('learning_goals', 'user_' . $mentore_id);
    }
    
    if (empty($competences)) {
        $competences_field = get_field('desired_skills', 'user_' . $mentore_id);
        if (is_array($competences_field)) {
            $competences = implode(', ', $competences_field);
        }
    }
    
    // Insérer la demande
    $result = $wpdb->insert(
        $table_name,
        array(
            'mentore_id' => $mentore_id,
            'mentor_id' => $mentor_id,
            'message_demande' => $message,
            'objectif_mentore' => $objectif,
            'competences_recherchees' => $competences,
            'statut' => 'en_attente'
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s')
    );
    
    if ($result) {
        // Ajouter aux métadonnées utilisateur (pour compatibilité)
        $demandes_existantes = get_user_meta($mentore_id, 'demandes_mentorat', true);
        if (empty($demandes_existantes)) {
            $demandes_existantes = array();
        }
        
        $demandes_existantes[] = array(
            'mentor_id' => $mentor_id,
            'date' => current_time('mysql'),
            'statut' => 'en_attente'
        );
        
        update_user_meta($mentore_id, 'demandes_mentorat', $demandes_existantes);
        
        // Envoyer un email de notification au mentor
        envoyer_notification_demande_mentor($mentor_id, $mentore_id);
        
        // Ajouter une activité
        ajouter_activite($mentore_id, 'demande', 'Demande envoyée à ' . $mentor->display_name);
        ajouter_activite($mentor_id, 'demande', 'Nouvelle demande de ' . $user->display_name);
        
        return array('success' => true, 'message' => 'Votre demande a été envoyée avec succès !');
    }
    
    return array('success' => false, 'message' => 'Erreur lors de l\'envoi de la demande.');
}

// Fonction pour envoyer une notification email au mentor
function envoyer_notification_demande_mentor($mentor_id, $mentore_id) {
    $mentor = get_userdata($mentor_id);
    $mentore = get_userdata($mentore_id);
    
    if (!$mentor || !$mentore) return;
    
    $mentor_email = $mentor->user_email;
    $subject = 'Nouvelle demande de mentorat - ' . get_bloginfo('name');
    
    $message = "
    Bonjour " . $mentor->display_name . ",
    
    Vous avez reçu une nouvelle demande de mentorat de la part de " . $mentore->display_name . ".
    
    Connectez-vous à votre espace mentor pour consulter cette demande :
    " . wp_login_url() . "
    
    Cordialement,
    L'équipe " . get_bloginfo('name') . "
    ";
    
    wp_mail($mentor_email, $subject, $message);
}

// Fonction pour récupérer les demandes d'un mentor
function get_demandes_mentor($mentor_id, $statut = 'en_attente') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_demandes';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT d.*, u.display_name, u.user_email 
         FROM $table_name d
         LEFT JOIN {$wpdb->users} u ON d.mentore_id = u.ID
         WHERE d.mentor_id = %d AND d.statut = %s
         ORDER BY d.date_demande DESC",
        $mentor_id, $statut
    ));
}

// Fonction pour accepter une demande
function accepter_demande_mentorat($demande_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_demandes';
    
    // Récupérer la demande
    $demande = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $demande_id
    ));
    
    if (!$demande) {
        return array('success' => false, 'message' => 'Demande non trouvée.');
    }
    
    // Mettre à jour le statut de la demande
    $result = $wpdb->update(
        $table_name,
        array(
            'statut' => 'acceptee',
            'date_decision' => current_time('mysql')
        ),
        array('id' => $demande_id),
        array('%s', '%s'),
        array('%d')
    );
    
    if ($result) {
        // Assigner le mentor au mentoré
        $assignation = assigner_mentor($demande->mentor_id, $demande->mentore_id, $demande->objectif_mentore);
        
        if (!is_wp_error($assignation)) {
            // Envoyer un email de confirmation au mentoré
            envoyer_notification_acceptation($demande->mentore_id, $demande->mentor_id);
            
            return array('success' => true, 'message' => 'Demande acceptée et mentor assigné.');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de l\'assignation: ' . $assignation->get_error_message());
        }
    }
    
    return array('success' => false, 'message' => 'Erreur lors de l\'acceptation de la demande.');
}

// Fonction pour refuser une demande
function refuser_demande_mentorat($demande_id, $raison = '') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_demandes';
    
    $result = $wpdb->update(
        $table_name,
        array(
            'statut' => 'refusee',
            'date_decision' => current_time('mysql'),
            'notes_mentor' => $raison
        ),
        array('id' => $demande_id),
        array('%s', '%s', '%s'),
        array('%d')
    );
    
    if ($result) {
        // Envoyer un email de notification
        $demande = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $demande_id));
        if ($demande) {
            envoyer_notification_refus($demande->mentore_id, $demande->mentor_id, $raison);
        }
        
        return array('success' => true, 'message' => 'Demande refusée.');
    }
    
    return array('success' => false, 'message' => 'Erreur lors du refus de la demande.');
}

// =============================================
// HANDLERS AJAX POUR LES DEMANDES
// =============================================

// Handler AJAX pour envoyer une demande
function ajax_envoyer_demande_mentorat() {
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'demande_mentor_nonce')) {
        wp_send_json_error('Erreur de sécurité.');
    }
    
    // Vérifier que l'utilisateur est connecté
    if (!is_user_logged_in()) {
        wp_send_json_error('Vous devez être connecté pour envoyer une demande.');
    }
    
    $mentor_id = intval($_POST['mentor_id']);
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    $objectif = sanitize_textarea_field($_POST['objectif'] ?? '');
    $competences = sanitize_textarea_field($_POST['competences'] ?? '');
    
    $result = envoyer_demande_mentorat($mentor_id, $message, $objectif, $competences);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_envoyer_demande_mentorat', 'ajax_envoyer_demande_mentorat');

// Handler AJAX pour accepter une demande (pour les mentors)
function ajax_accepter_demande_mentorat() {
    if (!is_user_logged_in() || !current_user_can('mentor')) {
        wp_send_json_error('Action non autorisée.');
    }
    
    $demande_id = intval($_POST['demande_id']);
    $result = accepter_demande_mentorat($demande_id);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_accepter_demande_mentorat', 'ajax_accepter_demande_mentorat');

// Handler AJAX pour refuser une demande (pour les mentors)
function ajax_refuser_demande_mentorat() {
    if (!is_user_logged_in() || !current_user_can('mentor')) {
        wp_send_json_error('Action non autorisée.');
    }
    
    $demande_id = intval($_POST['demande_id']);
    $raison = sanitize_textarea_field($_POST['raison'] ?? '');
    $result = refuser_demande_mentorat($demande_id, $raison);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_refuser_demande_mentorat', 'ajax_refuser_demande_mentorat');

// =============================================
// FONCTIONS MANQUANTES POUR LE SYSTÈME D'ASSIGNATION
// =============================================

// Table pour stocker les assignations
function creer_table_assignations() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_assignations';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        mentor_id mediumint(9) NOT NULL,
        mentore_id mediumint(9) NOT NULL,
        date_assignation datetime DEFAULT CURRENT_TIMESTAMP,
        statut varchar(20) DEFAULT 'actif',
        date_debut datetime,
        date_fin datetime,
        objectif_principal text,
        notes text,
        PRIMARY KEY (id),
        UNIQUE KEY relation_unique (mentor_id, mentore_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'creer_table_assignations');

// Fonction pour assigner un mentor à un mentoré
function assigner_mentor($mentor_id, $mentore_id, $objectif_principal = '') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_assignations';
    
    // Vérifier si l'assignation existe déjà
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE mentor_id = %d AND mentore_id = %d AND statut = 'actif'",
        $mentor_id, $mentore_id
    ));
    
    if ($existing) {
        return new WP_Error('assignation_existante', 'Cette assignation existe déjà.');
    }
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'mentor_id' => $mentor_id,
            'mentore_id' => $mentore_id,
            'date_debut' => current_time('mysql'),
            'objectif_principal' => $objectif_principal,
            'statut' => 'actif'
        ),
        array('%d', '%d', '%s', '%s', '%s')
    );
    
    if ($result) {
        // Ajouter une activité
        ajouter_activite($mentore_id, 'assignation', 'Nouveau mentor assigné: ' . get_userdata($mentor_id)->display_name);
        ajouter_activite($mentor_id, 'assignation', 'Nouveau mentoré assigné: ' . get_userdata($mentore_id)->display_name);
        
        return $wpdb->insert_id;
    }
    
    return new WP_Error('erreur_assignation', 'Erreur lors de l\'assignation.');
}

// Vérifier si une assignation existe
function est_assignation_actuelle($mentor_id, $mentore_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_assignations';
    
    return $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name 
         WHERE mentor_id = %d AND mentore_id = %d AND statut = 'actif'",
        $mentor_id, $mentore_id
    )) > 0;
}

// =============================================
// FONCTIONS UTILITAIRES POUR LES DASHBOARDS
// =============================================

// Récupérer les mentorés assignés à un mentor
function get_mentores_assignes($mentor_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_assignations';
    
    $mentores_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT mentore_id FROM $table_name 
         WHERE mentor_id = %d AND statut = 'actif'",
        $mentor_id
    ));
    
    if (empty($mentores_ids)) {
        return array();
    }
    
    return get_users(array(
        'include' => $mentores_ids,
        'orderby' => 'display_name'
    ));
}

// Récupérer les mentors assignés à un mentoré
function get_mentors_assignes($mentore_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_assignations';
    
    $mentors_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT mentor_id FROM $table_name 
         WHERE mentore_id = %d AND statut = 'actif'",
        $mentore_id
    ));
    
    if (empty($mentors_ids)) {
        return array();
    }
    
    return get_users(array(
        'include' => $mentors_ids,
        'orderby' => 'display_name'
    ));
}

// Récupérer la progression d'un mentoré
function get_progression_mentore($mentore_id) {
    // Pour l'instant, retourner une valeur aléatoire
    // À améliorer avec un vrai calcul basé sur les objectifs
    return rand(20, 95);
}

// Récupérer le nombre d'objectifs
function get_nombre_objectifs($mentore_id) {
    // Pour l'instant, retourner une valeur fixe
    // À implémenter avec la table des objectifs
    return rand(1, 5);
}

// Récupérer la dernière session
function get_derniere_session($mentor_id, $mentore_id) {
    // Pour l'instant, retourner une date fictive
    // À implémenter avec la table des sessions
    $dates = array(
        'Aujourd\'hui',
        'Hier', 
        'Il y a 2 jours',
        'Il y a 1 semaine',
        'Aucune session'
    );
    return $dates[array_rand($dates)];
}

// Récupérer les sessions avec un mentor spécifique
function get_sessions_avec_mentor($mentore_id, $mentor_id) {
    // Pour l'instant, retourner un nombre aléatoire
    return rand(1, 10);
}

// Récupérer la progression avec un mentor spécifique
function get_progression_avec_mentor($mentore_id, $mentor_id) {
    return rand(20, 95);
}

// Récupérer la note d'un mentor
function get_note_mentor($mentor_id) {
    return '4.' . rand(0, 9);
}

// Récupérer les sessions du mois
function get_sessions_ce_mois($mentor_id) {
    return rand(1, 8);
}

// Récupérer la note moyenne d'un mentor
function get_note_moyenne($mentor_id) {
    return '4.' . rand(5, 9);
}

// Récupérer les objectifs atteints
function get_objectifs_atteints($user_id) {
    return rand(0, 3);
}

// Récupérer la progression globale
function get_progression_globale($user_id) {
    return rand(30, 80);
}

// Récupérer les heures de mentorat
function get_heures_mentorat($user_id) {
    return rand(5, 25);
}

// =============================================
// SYSTÈME D'ACTIVITÉS
// =============================================

// Table pour stocker les activités
function creer_table_activites() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_activites';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        type_activite varchar(50) NOT NULL,
        description text NOT NULL,
        date_activite datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'creer_table_activites');

// Ajouter une activité
function ajouter_activite($user_id, $type, $description) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_activites';
    
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'type_activite' => $type,
            'description' => $description
        ),
        array('%d', '%s', '%s')
    );
}

// Récupérer l'activité récente
function get_activite_recente($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_activites';
    
    $activites = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE user_id = %d 
         ORDER BY date_activite DESC 
         LIMIT 5",
        $user_id
    ));
    
    if (empty($activites)) {
        // Activités par défaut pour la démo
        return '
        <div class="activity-item">
            <div class="activity-icon">👋</div>
            <div class="activity-content">
                <div class="activity-title">Bienvenue sur votre dashboard</div>
                <div class="activity-time">Maintenant</div>
            </div>
        </div>';
    }
    
    $output = '';
    foreach ($activites as $activite) {
        $time = human_time_diff(strtotime($activite->date_activite), current_time('timestamp')) . ' ago';
        $output .= '
        <div class="activity-item">
            <div class="activity-icon">📌</div>
            <div class="activity-content">
                <div class="activity-title">' . esc_html($activite->description) . '</div>
                <div class="activity-time">' . $time . '</div>
            </div>
        </div>';
    }
    
    return $output;
}

// =============================================
// FONCTIONS DE NOTIFICATION EMAIL
// =============================================

function envoyer_notification_acceptation($mentore_id, $mentor_id) {
    $mentore = get_userdata($mentore_id);
    $mentor = get_userdata($mentor_id);
    
    if (!$mentore || !$mentor) return;
    
    $mentore_email = $mentore->user_email;
    $subject = 'Votre demande de mentorat a été acceptée - ' . get_bloginfo('name');
    
    $message = "
    Félicitations " . $mentore->display_name . ",
    
    Votre demande de mentorat avec " . $mentor->display_name . " a été acceptée !
    
    Vous pouvez maintenant :
    - Planifier votre première session
    - Échanger avec votre mentor
    - Définir vos objectifs d'apprentissage
    
    Accédez à votre dashboard : " . home_url('/tableau-de-bord') . "
    
    Cordialement,
    L'équipe " . get_bloginfo('name') . "
    ";
    
    wp_mail($mentore_email, $subject, $message);
}

function envoyer_notification_refus($mentore_id, $mentor_id, $raison = '') {
    $mentore = get_userdata($mentore_id);
    $mentor = get_userdata($mentor_id);
    
    if (!$mentore || !$mentor) return;
    
    $mentore_email = $mentore->user_email;
    $subject = 'Réponse à votre demande de mentorat - ' . get_bloginfo('name');
    
    $message = "
    Bonjour " . $mentore->display_name . ",
    
    Votre demande de mentorat avec " . $mentor->display_name . " n'a malheureusement pas pu être acceptée.
    
    " . ($raison ? "Raison : " . $raison . "\n\n" : "") . "
    
    Ne vous découragez pas ! Vous pouvez :
    - Consulter d'autres mentors sur notre plateforme
    - Affiner vos objectifs d'apprentissage
    - Retenter votre chance plus tard
    
    Continuez votre recherche : " . home_url('/recherche-mentor') . "
    
    Cordialement,
    L'équipe " . get_bloginfo('name') . "
    ";
    
    wp_mail($mentore_email, $subject, $message);
}

// Shortcode pour afficher les demandes de mentorat pour les mentors
function demandes_mentorat_shortcode() {
    if (!is_user_logged_in()) {
        return '<div class="alert alert-warning">🔐 Veuillez vous connecter pour accéder à cette page.</div>';
    }
    
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    
    if (!in_array('mentor', $user->roles)) {
        return '<div class="alert alert-error">❌ Cette page est réservée aux mentors.</div>';
    }
    
    return afficher_demandes_mentor($user_id);
}
add_shortcode('demandes_mentorat', 'demandes_mentorat_shortcode');

// Fonction pour récupérer les métadonnées ACF des utilisateurs
function get_user_acf_meta($user_id, $field_name) {
    // Essayer d'abord avec get_field (ACF)
    $value = get_field($field_name, 'user_' . $user_id);
    
    // Si ACF ne retourne rien, essayer avec get_user_meta
    if (empty($value)) {
        $value = get_user_meta($user_id, $field_name, true);
    }
    
    return $value;
}

// Fonction pour afficher les demandes de mentorat pour un mentor
function afficher_demandes_mentor($mentor_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_demandes';
    
    // Récupérer les demandes en attente
    $demandes = $wpdb->get_results($wpdb->prepare(
        "SELECT d.*, u.display_name, u.user_email 
         FROM $table_name d
         LEFT JOIN {$wpdb->users} u ON d.mentore_id = u.ID
         WHERE d.mentor_id = %d AND d.statut = 'en_attente'
         ORDER BY d.date_demande DESC",
        $mentor_id
    ));
    
    ob_start();
    ?>
    <div class="mentor-demandes-container">
        <h2>📨 Demandes de mentorat en attente</h2>
        
        <?php if (empty($demandes)) : ?>
            <div class="aucune-demande">
                <p>✅ Aucune demande de mentorat en attente.</p>
            </div>
        <?php else : ?>
            <div class="demandes-list">
                <?php foreach ($demandes as $demande) : 
                    // Récupérer les infos ACF du mentoré
                    $user_photo = get_user_acf_meta($demande->mentore_id, 'user_photo');
                    $user_bio = get_user_acf_meta($demande->mentore_id, 'user_bio');
                ?>
                    <div class="demande-card" data-demande-id="<?php echo $demande->id; ?>">
                        <div class="demande-header">
                            <div class="mentore-avatar">
                                <?php if ($user_photo) : ?>
                                    <img src="<?php echo esc_url($user_photo); ?>" alt="<?php echo esc_attr($demande->display_name); ?>">
                                <?php else : ?>
                                    <?php echo get_avatar($demande->mentore_id, 60); ?>
                                <?php endif; ?>
                            </div>
                            <div class="mentore-info">
                                <h3><?php echo esc_html($demande->display_name); ?></h3>
                                <p class="demande-date">📅 <?php echo date('d/m/Y à H:i', strtotime($demande->date_demande)); ?></p>
                            </div>
                        </div>
                        
                        <div class="demande-content">
                            <?php if ($demande->message_demande) : ?>
                                <div class="demande-message">
                                    <strong>💬 Message :</strong>
                                    <p><?php echo nl2br(esc_html($demande->message_demande)); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($demande->objectif_mentore) : ?>
                                <div class="demande-objectif">
                                    <strong>🎯 Objectif :</strong>
                                    <p><?php echo nl2br(esc_html($demande->objectif_mentore)); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($demande->competences_recherchees) : ?>
                                <div class="demande-competences">
                                    <strong>💼 Compétences recherchées :</strong>
                                    <p><?php echo nl2br(esc_html($demande->competences_recherchees)); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($user_bio) : ?>
                                <div class="mentore-bio">
                                    <strong>📝 Bio du mentoré :</strong>
                                    <p><?php echo wp_trim_words(esc_html($user_bio), 30); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="demande-actions">
                            <button class="btn-accepter" data-demande-id="<?php echo $demande->id; ?>">
                                ✅ Accepter la demande
                            </button>
                            <button class="btn-refuser" data-demande-id="<?php echo $demande->id; ?>">
                                ❌ Refuser la demande
                            </button>
                        </div>
                        
                        <!-- Formulaire de refus (caché par défaut) -->
                        <div class="refus-form" id="refus-form-<?php echo $demande->id; ?>" style="display: none;">
                            <textarea 
                                class="raison-refus" 
                                placeholder="Optionnel : Expliquez brièvement la raison de votre refus..."
                                rows="3"
                            ></textarea>
                            <div class="refus-actions">
                                <button class="btn-confirmer-refus" data-demande-id="<?php echo $demande->id; ?>">
                                    🔒 Confirmer le refus
                                </button>
                                <button class="btn-annuler-refus" data-demande-id="<?php echo $demande->id; ?>">
                                    ↩️ Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
    .mentor-demandes-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .aucune-demande {
        text-align: center;
        padding: 3rem;
        background: #f8f9fa;
        border-radius: 10px;
        color: #28a745;
    }
    
    .demande-card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #3498db;
        opacity: 0;
        margin-top: 20px;
    }
    
    .demande-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .mentore-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #3498db;
    }
    
    .mentore-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .mentore-info h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }
    
    .demande-date {
        color: #7f8c8d;
        margin: 0;
        font-size: 0.9rem;
    }
    
    .demande-content > div {
        margin-bottom: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .demande-content strong {
        color: #2c3e50;
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .demande-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    .btn-accepter {
        background: #27ae60;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
    }
    
    .btn-accepter:hover {
        background: #219a52;
    }
    
    .btn-refuser {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
    }
    
    .btn-refuser:hover {
        background: #c0392b;
    }
    
    .refus-form {
        margin-top: 1rem;
        padding: 1rem;
        background: #fff3cd;
        border-radius: 5px;
        border: 1px solid #ffeaa7;
    }
    
    .raison-refus {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 1rem;
    }
    
    .refus-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    .btn-confirmer-refus {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .btn-annuler-refus {
        background: #95a5a6;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .demande-header {
            flex-direction: column;
            text-align: center;
        }
        
        .demande-actions {
            flex-direction: column;
        }
        
        .refus-actions {
            flex-direction: column;
        }
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Gérer l'affichage du formulaire de refus
        $('.btn-refuser').on('click', function() {
            var demandeId = $(this).data('demande-id');
            $('#refus-form-' + demandeId).slideDown();
            $(this).hide();
        });
        
        // Annuler le refus
        $('.btn-annuler-refus').on('click', function() {
            var demandeId = $(this).data('demande-id');
            $('#refus-form-' + demandeId).slideUp();
            $('.btn-refuser[data-demande-id="' + demandeId + '"]').show();
        });
        
        // Animation d'entrée des cartes
        $('.demande-card').each(function(index) {
            $(this).delay(100 * index).animate({
                opacity: 1,
                marginTop: 0
            }, 300);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// Handler AJAX pour récupérer les données du profil utilisateur
function ajax_get_user_profile_data() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Utilisateur non connecté');
    }
    
    $user_id = get_current_user_id();
    $data = array(
        'learning_goals' => get_user_acf_meta($user_id, 'learning_goals'),
        'desired_skills' => get_user_acf_meta($user_id, 'desired_skills')
    );
    
    // Si desired_skills est un tableau, le convertir en string
    if (is_array($data['desired_skills'])) {
        $data['desired_skills'] = implode(', ', $data['desired_skills']);
    }
    
    wp_send_json_success($data);
}
add_action('wp_ajax_get_user_profile_data', 'ajax_get_user_profile_data');

// Handler AJAX pour récupérer les demandes existantes
function ajax_get_demandes_existantes() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Utilisateur non connecté');
    }
    
    $user_id = get_current_user_id();
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mentor_demandes';
    $demandes = $wpdb->get_results($wpdb->prepare(
        "SELECT mentor_id FROM $table_name WHERE mentore_id = %d AND statut = 'en_attente'",
        $user_id
    ));
    
    wp_send_json_success(array('demandes' => $demandes));
}
add_action('wp_ajax_get_demandes_existantes', 'ajax_get_demandes_existantes');