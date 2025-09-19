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