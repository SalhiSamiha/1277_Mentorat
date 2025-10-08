<?php
/**
 * Template Name: Page Mon Profil
 */

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
?>

<div class="user-profile-container">
    <div class="profile-content">
        <!-- Informations Personnelles -->
        <div class="profile-section">
            <h2>Informations Personnelles</h2>
            <div class="personal-info-grid">
                <div class="profile-photo-section">
                    <?php
                    $profile_photo = get_field('user_photo', 'user_' . $user_id);
                    if ($profile_photo) {
                        echo '<div class="profile-photo"><img src="' . esc_url($profile_photo) . '" alt="Photo de profil"></div>';
                    } else {
                        echo '<div class="profile-photo placeholder">' . get_avatar($user_id, 150) . '</div>';
                    }
                    ?>
                </div>
                
                <div class="personal-details">
                    <div class="info-item">
                        <strong>Nom :</strong>
                        <span><?php echo esc_html($current_user->display_name); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email :</strong>
                        <span><?php echo esc_html($current_user->user_email); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Téléphone :</strong>
                        <span><?php echo esc_html(get_field('user_phone', 'user_' . $user_id) ?: 'Non renseigné'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Localisation :</strong>
                        <span><?php echo esc_html(get_field('user_location', 'user_' . $user_id) ?: 'Non renseignée'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="bio-section">
                <h3>Bio</h3>
                <div class="bio-content">
                    <?php 
                    $bio = get_field('user_bio', 'user_' . $user_id);
                    echo $bio ? wp_kses_post(wpautop($bio)) : '<p class="no-info">Aucune bio renseignée.</p>';
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Compétences et Intérêts -->
        <div class="profile-section">
            <h2>Compétences et Intérêts</h2>
            
            <div class="skills-section">
                <h3>Compétences</h3>
                <?php
                $skills = get_field('user_skills', 'user_' . $user_id);
                $expertise_level = get_field('expertise_level', 'user_' . $user_id);
                
                if ($skills) {
                    echo '<div class="skills-list">';
                    foreach ($skills as $skill) {
                        echo '<span class="skill-tag">' . esc_html($skill) . '</span>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="no-info">Aucune compétence renseignée.</p>';
                }
                
                if ($expertise_level) {
                    echo '<div class="expertise-level">';
                    echo '<strong>Niveau d\'expertise :</strong> ' . esc_html($expertise_level);
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="interests-section">
                <h3>Centres d'intérêt</h3>
                <?php
                $interests = get_field('user_interests', 'user_' . $user_id);
                echo $interests ? wp_kses_post(wpautop($interests)) : '<p class="no-info">Aucun centre d\'intérêt renseigné.</p>';
                ?>
            </div>
        </div>
        
        <!-- Expériences Professionnelles -->
        <div class="profile-section">
            <h2>Expériences Professionnelles</h2>
            <?php
            $experiences = get_field('user_experiences', 'user_' . $user_id);
            
            if ($experiences) {
                echo '<div class="experiences-list">';
                foreach ($experiences as $experience) {
                    echo '<div class="experience-item">';
                    echo '<h4>' . esc_html($experience['job_title']) . '</h4>';
                    echo '<div class="experience-meta">';
                    echo '<span class="company">' . esc_html($experience['company']) . '</span>';
                    if ($experience['period']) {
                        echo '<span class="period">' . esc_html($experience['period']) . '</span>';
                    }
                    echo '</div>';
                    if ($experience['job_description']) {
                        echo '<div class="experience-description">' . wp_kses_post(wpautop($experience['job_description'])) . '</div>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p class="no-info">Aucune expérience professionnelle renseignée.</p>';
            }
            ?>
        </div>
        
        <!-- Disponibilités -->
        <div class="profile-section">
            <h2>Disponibilités</h2>
            
            <div class="availability-status">
                <?php
                $availability_status = get_field('availability_status', 'user_' . $user_id);
                $status_class = $availability_status ? strtolower($availability_status) : 'inconnu';
                echo '<div class="status-indicator ' . esc_attr($status_class) . '">';
                echo '<strong>Statut :</strong> ' . ($availability_status ? esc_html($availability_status) : 'Non défini');
                echo '</div>';
                ?>
            </div>
            
            <div class="availability-calendar">
                <h3>Créneaux disponibles</h3>
                <?php
                $time_slots = get_field('time_slots', 'user_' . $user_id);
                $available_days = get_field('available_days', 'user_' . $user_id);
                
                if ($available_days) {
                    echo '<div class="available-days">';
                    echo '<h4>Jours disponibles :</h4>';
                    echo '<div class="days-list">';
                    foreach ($available_days as $day) {
                        echo '<span class="day-tag">' . esc_html($day) . '</span>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                
                if ($time_slots) {
                    echo '<div class="time-slots">';
                    echo '<h4>Créneaux horaires :</h4>';
                    echo '<div class="slots-list">';
                    foreach ($time_slots as $slot) {
                        echo '<div class="time-slot">';
                        echo '<span class="day">' . esc_html($slot['day']) . '</span>';
                        echo '<span class="time">' . esc_html($slot['start_time']) . ' - ' . esc_html($slot['end_time']) . '</span>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p class="no-info">Aucun créneau horaire défini.</p>';
                }
                ?>
            </div>
            <div class="time-slots-section">
    <h3>Créneaux horaires disponibles</h3>
    <?php
    $time_slots = get_field('time_slots', 'user_' . $user_id);
    
    if ($time_slots) {
        echo '<div class="time-slots-grid">';
        
        // Grouper par jour
        $slots_by_day = array();
        foreach ($time_slots as $slot) {
            $day = $slot['day'];
            $time_range = $slot['start_time'] . ' - ' . $slot['end_time'];
            $slots_by_day[$day][] = $time_range;
        }
        
        // Afficher par jour
        $days_order = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
        
        foreach ($days_order as $day) {
            if (isset($slots_by_day[$day])) {
                $day_label = ucfirst($day);
                echo '<div class="day-slot">';
                echo '<h4>' . esc_html($day_label) . '</h4>';
                echo '<div class="time-ranges">';
                foreach ($slots_by_day[$day] as $time_range) {
                    echo '<span class="time-range">' . esc_html($time_range) . '</span>';
                }
                echo '</div>';
                echo '</div>';
            }
        }
        
        echo '</div>';
    } else {
        echo '<p class="no-info">Aucun créneau horaire défini.</p>';
    }
    ?>
</div>
        </div>
        
        <!-- Bouton d'édition -->

        <!-- Dans la section des actions du profil -->
<div class="profile-actions">
    <a href="<?php echo home_url('/index.php/edition-profil'); ?>" class="edit-profile-button">
        Modifier mon profil
    </a>
</div>
        <!--<div class="profile-actions">
            <a href="<?php echo esc_url(get_edit_profile_url()); ?>" class="edit-profile-button">
                Modifier mon profil
            </a>
        </div>  -->
    </div>
</div>

<?php get_footer(); ?>