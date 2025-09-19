<?php
/**
 * Template Name: Recherche de Mentor
 */
get_header(); ?>

<div class="container recherche-mentor">
    <h1><?php the_title(); ?></h1>
    
    <?php 
    // Afficher le formulaire de recherche 
    include get_stylesheet_directory() . '/formulaire-recherche-mentor.php'; 
    ?>
    
    <div class="resultats-recherche">
        <?php
        // Vérifier si une recherche a été effectuée
        if (isset($_GET['recherche_mentor']) && $_GET['recherche_mentor'] == '1') {
            
            // Récupérer et sécuriser les critères de recherche
            $domaine = isset($_GET['domaine']) ? sanitize_text_field($_GET['domaine']) : '';
            $langue = isset($_GET['langue']) ? sanitize_text_field($_GET['langue']) : '';
            $region = isset($_GET['region']) ? sanitize_text_field($_GET['region']) : '';
            
            // Construire la requête WP_Query
            $args = array(
                'post_type' => 'mentor', 
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND'
                )
            );
            
            // Ajouter les filtres de métadonnées si des valeurs sont spécifiées
            if (!empty($domaine)) {
                $args['meta_query'][] = array(
                    'key' => 'domaine',
                    'value' => $domaine,
                    'compare' => '='
                );
            }
            
            if (!empty($langue)) {
                $args['meta_query'][] = array(
                    'key' => 'langue',
                    'value' => $langue,
                    'compare' => '='
                );
            }
            
            if (!empty($region)) {
                $args['meta_query'][] = array(
                    'key' => 'region',
                    'value' => $region,
                    'compare' => '='
                );
            }
            
            // Exécuter la requête
            $query = new WP_Query($args);
            
            // Afficher les résultats
            if ($query->have_posts()) {
                echo '<div class="liste-mentors">';
                echo '<h2>' . sprintf(__('%d mentors trouvés', 'text-domain'), $query->found_posts) . '</h2>';
                
                while ($query->have_posts()) {
                    $query->the_post();
                    ?>
                    <div class="mentor-card">
                        <h3><?php the_title(); ?></h3>
                        
                        <div class="mentor-details">
                            <?php if ($domaine_mentor = get_post_meta(get_the_ID(), 'domaine', true)) : ?>
                                <p><strong>Domaine:</strong> <?php echo esc_html($domaine_mentor); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($langue_mentor = get_post_meta(get_the_ID(), 'langue', true)) : ?>
                                <p><strong>Langue:</strong> <?php echo esc_html($langue_mentor); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($region_mentor = get_post_meta(get_the_ID(), 'region', true)) : ?>
                                <p><strong>Région:</strong> <?php echo esc_html($region_mentor); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mentor-description">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <button class="btn-demande-mentorat" data-mentor-id="<?php the_ID(); ?>">
                            Faire une demande de jumelage
                        </button>
                    </div>
                    <?php
                }
                
                echo '</div>';
            } else {
                echo '<p class="aucun-resultat">Aucun mentor ne correspond à vos critères de recherche.</p>';
            }
            
            wp_reset_postdata();
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>