<?php
/**
 * Template Name: Recherche de Mentor
 */
get_header(); ?>

<div class="container recherche-mentor">
    <h1>Rechercher un Mentor</h1>
    
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
            $expertise_level = isset($_GET['expertise_level']) ? sanitize_text_field($_GET['expertise_level']) : '';
            $availability_status = isset($_GET['availability_status']) ? sanitize_text_field($_GET['availability_status']) : '';
            
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
            
            // Exécuter la requête
            $mentors = get_users($args);
            
            // Afficher les résultats
            if (!empty($mentors)) {
                echo '<div class="liste-mentors">';
                echo '<h2>' . sprintf(__('%d mentors trouvés', 'text-domain'), count($mentors)) . '</h2>';
                
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
                                    <p class="mentor-expertise">Niveau <?php echo esc_html($expertise_level); ?></p>
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
                                    <strong>Compétences :</strong>
                                    <?php foreach ($user_skills as $skill) : ?>
                                        <span class="skill-tag"><?php echo esc_html($skill); ?></span>
                                    <?php endforeach; ?>
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
                                            $day = $slot['day'];
                                            $time_range = $slot['start_time'] . ' - ' . $slot['end_time'];
                                            $slots_by_day[$day][] = $time_range;
                                        }
                                        
                                        $days_display = array();
                                        foreach ($slots_by_day as $day => $times) {
                                            $days_display[] = ucfirst($day) . ' (' . implode(', ', $times) . ')';
                                        }
                                        echo esc_html(implode(' | ', $days_display));
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
                echo '<h3>Aucun mentor ne correspond à vos critères de recherche.</h3>';
                echo '<p>Essayez de modifier vos filtres ou <a href="' . esc_url(get_permalink()) . '">réinitialisez la recherche</a>.</p>';
                echo '</div>';
            }
        } else {
            // Message d'accueil quand aucune recherche n'est effectuée
            echo '<div class="message-accueil">';
            echo '<h3>Bienvenue dans notre plateforme de mentorat</h3>';
            echo '<p>Utilisez les filtres ci-dessus pour trouver le mentor qui correspond à vos besoins.</p>';
            echo '<p>Vous pouvez filtrer par domaine d\'expertise, langue, région, niveau d\'expertise et disponibilité.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>
