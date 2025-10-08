<?php
/**
 * Template Name: Page Édition Profil
 */

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// Traitement du formulaire de mise à jour
if (isset($_POST['update_profile_nonce']) && wp_verify_nonce($_POST['update_profile_nonce'], 'update_profile_action')) {
    update_user_profile_data();
}

function update_user_profile_data() {
    $user_id = get_current_user_id();
    
    if ($user_id) {
        // Mettre à jour les champs ACF de base
        $fields_to_update = array(
            'user_bio' => 'sanitize_textarea_field',
            'user_phone' => 'sanitize_text_field',
            'user_location' => 'sanitize_text_field',
            'user_interests' => 'sanitize_textarea_field',
            'expertise_level' => 'sanitize_text_field',
            'availability_status' => 'sanitize_text_field',
            'learning_goals' => 'sanitize_textarea_field',
            'current_level' => 'sanitize_text_field'
        );
        
        foreach ($fields_to_update as $field => $sanitize_function) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_function, $_POST[$field]);
                update_field($field, $value, 'user_' . $user_id);
            }
        }
        
        // Mettre à jour les compétences (tableau)
        if (isset($_POST['user_skills'])) {
            $skills = array_map('sanitize_text_field', $_POST['user_skills']);
            update_field('user_skills', $skills, 'user_' . $user_id);
        } else {
            update_field('user_skills', array(), 'user_' . $user_id);
        }
        
        // Mettre à jour les compétences recherchées (tableau)
        if (isset($_POST['desired_skills'])) {
            $desired_skills = array_map('sanitize_text_field', $_POST['desired_skills']);
            update_field('desired_skills', $desired_skills, 'user_' . $user_id);
        } else {
            update_field('desired_skills', array(), 'user_' . $user_id);
        }
        
        // Mettre à jour les créneaux horaires
        if (isset($_POST['time_slots'])) {
            $time_slots = array();
            foreach ($_POST['time_slots'] as $slot) {
                if (!empty($slot['day']) && !empty($slot['start_time']) && !empty($slot['end_time'])) {
                    $time_slots[] = array(
                        'day' => sanitize_text_field($slot['day']),
                        'start_time' => sanitize_text_field($slot['start_time']),
                        'end_time' => sanitize_text_field($slot['end_time'])
                    );
                }
            }
            update_field('time_slots', $time_slots, 'user_' . $user_id);
        } else {
            update_field('time_slots', array(), 'user_' . $user_id);
        }
        
        // Message de succès
        echo '<div class="alert alert-success">Profil mis à jour avec succès!</div>';
        
        // Rediriger vers la page profil après 2 secondes
        echo '<script>setTimeout(function() { window.location.href = "' . home_url('/index.php/mon-profil') . '"; }, 2000);</script>';
    }
}

get_header();

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user_role = !empty($current_user->roles[0]) ? $current_user->roles[0] : '';

// Récupérer les valeurs actuelles
$current_skills = get_field('user_skills', 'user_' . $user_id) ?: array();
$current_interests = get_field('user_interests', 'user_' . $user_id);
$current_expertise = get_field('expertise_level', 'user_' . $user_id);
$current_bio = get_field('user_bio', 'user_' . $user_id);
$current_phone = get_field('user_phone', 'user_' . $user_id);
$current_location = get_field('user_location', 'user_' . $user_id);
$current_availability = get_field('availability_status', 'user_' . $user_id);
$current_learning_goals = get_field('learning_goals', 'user_' . $user_id);
$current_desired_skills = get_field('desired_skills', 'user_' . $user_id) ?: array();
$current_level = get_field('current_level', 'user_' . $user_id);
$time_slots = get_field('time_slots', 'user_' . $user_id) ?: array();
$profile_photo = get_field('user_photo', 'user_' . $user_id);
?>

<div class="user-profile-container">
    <div class="profile-edit-form">
        <div class="edit-header">
            <h1>Modifier mon profil</h1>
            <a href="<?php echo home_url('/index.php/mon-profil'); ?>" class="back-button">← Retour au profil</a>
        </div>
        
        <form method="post" class="edit-profile-form">
            <?php wp_nonce_field('update_profile_action', 'update_profile_nonce'); ?>
            
            <!-- Informations Personnelles -->
            <div class="form-section">
                <h3>Informations Personnelles</h3>
                
                <div class="form-group">
                    <label for="user_bio">Bio</label>
                    <textarea id="user_bio" name="user_bio" class="form-control" rows="5" placeholder="Décrivez-vous..."><?php echo esc_textarea($current_bio); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="user_phone">Téléphone</label>
                    <input type="tel" id="user_phone" name="user_phone" value="<?php echo esc_attr($current_phone); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="user_location">Localisation</label>
                    <input type="text" id="user_location" name="user_location" value="<?php echo esc_attr($current_location); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="user_interests">Centres d'intérêt</label>
                    <textarea id="user_interests" name="user_interests" class="form-control" rows="4" placeholder="Vos passions, hobbies..."><?php echo esc_textarea($current_interests); ?></textarea>
                </div>
            </div>
            
            <!-- Section pour les MENTORS -->
            <?php if ($user_role === 'mentor'): ?>
            <div class="form-section">
                <h3>Compétences et Disponibilités (Mentor)</h3>
                
                <div class="form-group">
                    <label>Compétences</label>
                    <div class="checkbox-group">
                        <?php
                        $skills_options = array(
                            'developpement' => 'Développement Web',
                            'design' => 'Design UX/UI',
                            'marketing' => 'Marketing Digital',
                            'data' => 'Data Science',
                            'entrepreneuriat' => 'Entrepreneuriat',
                            'gestion' => 'Gestion de projet',
                        );
                        
                        foreach ($skills_options as $value => $label) {
                            $checked = in_array($value, $current_skills) ? 'checked' : '';
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="user_skills[]" value="' . esc_attr($value) . '" ' . $checked . '>';
                            echo esc_html($label);
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="expertise_level">Niveau d'expertise</label>
                    <select id="expertise_level" name="expertise_level" class="form-control">
                        <option value="">Sélectionnez un niveau</option>
                        <?php
                        $levels = array(
                            'debutant' => 'Débutant',
                            'intermediaire' => 'Intermédiaire',
                            'avance' => 'Avancé',
                            'expert' => 'Expert',
                        );
                        
                        foreach ($levels as $value => $label) {
                            $selected = ($current_expertise == $value) ? 'selected' : '';
                            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="availability_status">Statut de disponibilité</label>
                    <select id="availability_status" name="availability_status" class="form-control">
                        <option value="">Sélectionnez un statut</option>
                        <?php
                        $availability_options = array(
                            'disponible' => 'Disponible',
                            'occupe' => 'Occupé',
                            'vacances' => 'En vacances',
                        );
                        
                        foreach ($availability_options as $value => $label) {
                            $selected = ($current_availability == $value) ? 'selected' : '';
                            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Créneaux horaires -->
                <div class="form-group">
                    <label>Créneaux horaires disponibles</label>
                    <div class="time-slots-editor">
                        <div id="timeSlotsContainer">
                            <?php if (!empty($time_slots)): ?>
                                <?php foreach ($time_slots as $index => $slot): ?>
                                <div class="time-slot-row">
                                    <select name="time_slots[<?php echo $index; ?>][day]" class="form-control time-slot-day">
                                        <option value="lundi" <?php selected($slot['day'], 'lundi'); ?>>Lundi</option>
                                        <option value="mardi" <?php selected($slot['day'], 'mardi'); ?>>Mardi</option>
                                        <option value="mercredi" <?php selected($slot['day'], 'mercredi'); ?>>Mercredi</option>
                                        <option value="jeudi" <?php selected($slot['day'], 'jeudi'); ?>>Jeudi</option>
                                        <option value="vendredi" <?php selected($slot['day'], 'vendredi'); ?>>Vendredi</option>
                                        <option value="samedi" <?php selected($slot['day'], 'samedi'); ?>>Samedi</option>
                                        <option value="dimanche" <?php selected($slot['day'], 'dimanche'); ?>>Dimanche</option>
                                    </select>
                                    <input type="time" name="time_slots[<?php echo $index; ?>][start_time]" value="<?php echo esc_attr($slot['start_time']); ?>" class="form-control time-slot-start">
                                    <span class="time-separator">à</span>
                                    <input type="time" name="time_slots[<?php echo $index; ?>][end_time]" value="<?php echo esc_attr($slot['end_time']); ?>" class="form-control time-slot-end">
                                    <button type="button" class="remove-time-slot" onclick="removeTimeSlot(this)">×</button>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="time-slot-row">
                                    <select name="time_slots[0][day]" class="form-control time-slot-day">
                                        <option value="lundi">Lundi</option>
                                        <option value="mardi">Mardi</option>
                                        <option value="mercredi">Mercredi</option>
                                        <option value="jeudi">Jeudi</option>
                                        <option value="vendredi">Vendredi</option>
                                        <option value="samedi">Samedi</option>
                                        <option value="dimanche">Dimanche</option>
                                    </select>
                                    <input type="time" name="time_slots[0][start_time]" value="09:00" class="form-control time-slot-start">
                                    <span class="time-separator">à</span>
                                    <input type="time" name="time_slots[0][end_time]" value="17:00" class="form-control time-slot-end">
                                    <button type="button" class="remove-time-slot" onclick="removeTimeSlot(this)">×</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="add-time-slot-button" onclick="addTimeSlot()">+ Ajouter un créneau</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Section pour les MENTORÉS -->
            <?php if ($user_role === 'mentore'): ?>
            <div class="form-section">
                <h3>Objectifs d'Apprentissage (Mentoré)</h3>
                
                <div class="form-group">
                    <label for="learning_goals">Objectifs d'apprentissage</label>
                    <textarea id="learning_goals" name="learning_goals" class="form-control" rows="4" placeholder="Ce que vous souhaitez apprendre..."><?php echo esc_textarea($current_learning_goals); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Compétences recherchées</label>
                    <div class="checkbox-group">
                        <?php
                        $desired_skills_options = array(
                            'developpement' => 'Développement Web',
                            'design' => 'Design UX/UI',
                            'marketing' => 'Marketing Digital',
                            'data' => 'Data Science',
                            'entrepreneuriat' => 'Entrepreneuriat',
                            'gestion' => 'Gestion de projet',
                        );
                        
                        foreach ($desired_skills_options as $value => $label) {
                            $checked = in_array($value, $current_desired_skills) ? 'checked' : '';
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="desired_skills[]" value="' . esc_attr($value) . '" ' . $checked . '>';
                            echo esc_html($label);
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="current_level">Niveau actuel</label>
                    <select id="current_level" name="current_level" class="form-control">
                        <option value="">Sélectionnez votre niveau</option>
                        <?php
                        $current_levels = array(
                            'debutant' => 'Débutant complet',
                            'debutant_avance' => 'Débutant avancé',
                            'intermediaire' => 'Intermédiaire',
                            'avance' => 'Avancé',
                        );
                        
                        foreach ($current_levels as $value => $label) {
                            $selected = ($current_level == $value) ? 'selected' : '';
                            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Boutons -->
            <div class="form-actions">
                <button type="submit" class="submit-button">Mettre à jour le profil</button>
                <a href="<?php echo home_url('/index.php/mon-profil'); ?>" class="cancel-button">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
let timeSlotCounter = <?php echo !empty($time_slots) ? count($time_slots) : 1; ?>;

function addTimeSlot() {
    const container = document.getElementById('timeSlotsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'time-slot-row';
    newRow.innerHTML = `
        <select name="time_slots[${timeSlotCounter}][day]" class="form-control time-slot-day">
            <option value="lundi">Lundi</option>
            <option value="mardi">Mardi</option>
            <option value="mercredi">Mercredi</option>
            <option value="jeudi">Jeudi</option>
            <option value="vendredi">Vendredi</option>
            <option value="samedi">Samedi</option>
            <option value="dimanche">Dimanche</option>
        </select>
        <input type="time" name="time_slots[${timeSlotCounter}][start_time]" value="09:00" class="form-control time-slot-start">
        <span class="time-separator">à</span>
        <input type="time" name="time_slots[${timeSlotCounter}][end_time]" value="17:00" class="form-control time-slot-end">
        <button type="button" class="remove-time-slot" onclick="removeTimeSlot(this)">×</button>
    `;
    container.appendChild(newRow);
    timeSlotCounter++;
}

function removeTimeSlot(button) {
    const row = button.closest('.time-slot-row');
    if (document.querySelectorAll('.time-slot-row').length > 1) {
        row.remove();
    }
}
</script>

<style>
/* Styles pour le formulaire d'édition */
.user-profile-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-edit-form {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.edit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #3498db;
}

.edit-header h1 {
    color: #2c3e50;
    margin: 0;
}

.back-button {
    background: #95a5a6;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
}

.back-button:hover {
    background: #7f8c8d;
    color: white;
}

.form-section {
    background: #f8f9fa;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.form-section h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    background: white;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.checkbox-label input[type="checkbox"] {
    margin: 0;
}

/* Styles pour les créneaux horaires */
.time-slots-editor {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.time-slot-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.time-slot-day {
    flex: 2;
}

.time-slot-start,
.time-slot-end {
    flex: 1;
}

.time-separator {
    color: #6c757d;
    font-weight: 600;
}

.remove-time-slot {
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-time-slot:hover {
    background: #c0392b;
}

.add-time-slot-button {
    background: #27ae60;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
}

.add-time-slot-button:hover {
    background: #219a52;
}

.form-actions {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #dee2e6;
}

.submit-button {
    background: #3498db;
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.submit-button:hover {
    background: #2980b9;
}

.cancel-button {
    background: #95a5a6;
    color: white;
    padding: 1rem 2rem;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 1rem;
    display: inline-block;
}

.cancel-button:hover {
    background: #7f8c8d;
    color: white;
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    text-align: center;
}
</style>

<?php get_footer(); ?>