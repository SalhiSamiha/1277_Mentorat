<?php
/**
 * Dashboard spécifique pour les MENTORÉS
 */
if (!defined('ABSPATH')) exit;

$mentore_id = $user->ID;
$mentors = get_mentors_assignes($mentore_id);
?>
<div class="dashboard-mentore">
    <!-- Header du Dashboard -->
    <div class="dashboard-header mentore-header">
        <div class="header-content">
            <h1>🎓 Tableau de Bord Mentoré</h1>
            <p>Suivez votre progression et gérez votre apprentissage</p>
        </div>
        <div class="user-welcome">
            <div class="user-avatar">
                <?php 
                $photo = get_field('user_photo', 'user_' . $mentore_id);
                if ($photo): ?>
                    <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($user->display_name); ?>">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($user->display_name, 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <strong><?php echo esc_html($user->display_name); ?></strong>
                <span>Mentoré</span>
            </div>
        </div>
    </div>

    <!-- Statistiques Rapides -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🧑‍🏫</div>
            <div class="stat-content">
                <h3><?php echo count($mentors); ?></h3>
                <p>Mentors assignés</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3><?php echo get_progression_globale($mentore_id); ?>%</h3>
                <p>Progression globale</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <h3><?php echo get_heures_mentorat($mentore_id); ?>h</h3>
                <p>Heures de mentorat</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
                <h3><?php echo get_objectifs_atteints($mentore_id); ?></h3>
                <p>Objectifs atteints</p>
            </div>
        </div>
    </div>

    <!-- Sections principales -->
    <div class="dashboard-sections">
        <!-- Mes Mentors -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>🧑‍🏫 Mes Mentors</h2>
                <a href="/recherche-mentor" class="btn-link">+ Trouver un mentor</a>
            </div>
            
            <?php if (empty($mentors)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🧑‍🏫</div>
                    <h3>Aucun mentor assigné</h3>
                    <p>Vous n'avez pas encore de mentor pour vous accompagner.</p>
                    <a href="/recherche-mentor" class="btn-primary">🔍 Trouver un mentor</a>
                </div>
            <?php else: ?>
                <div class="mentors-grid">
                    <?php foreach ($mentors as $mentor): ?>
                    <div class="mentor-card">
                        <div class="mentor-main">
                            <div class="mentor-avatar">
                                <?php 
                                $mentor_photo = get_field('user_photo', 'user_' . $mentor->ID);
                                if ($mentor_photo): ?>
                                    <img src="<?php echo esc_url($mentor_photo); ?>" alt="<?php echo esc_attr($mentor->display_name); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($mentor->display_name, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mentor-info">
                                <h4><?php echo esc_html($mentor->display_name); ?></h4>
                                <p class="mentor-expertise">
                                    <?php 
                                    $skills = get_field('user_skills', 'user_' . $mentor->ID);
                                    echo $skills ? implode(', ', array_slice($skills, 0, 2)) : 'Expertise non définie';
                                    ?>
                                </p>
                                <div class="mentor-rating">
                                    <?php echo get_notes_mentor($mentor->ID); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mentor-stats">
                            <div class="stat">
                                <strong><?php echo get_sessions_avec_mentor($mentore_id, $mentor->ID); ?></strong>
                                <span>Sessions</span>
                            </div>
                            <div class="stat">
                                <strong><?php echo get_progression_avec_mentor($mentore_id, $mentor->ID); ?>%</strong>
                                <span>Progression</span>
                            </div>
                        </div>
                        
                        <div class="next-session">
                            <strong>Prochaine session:</strong>
                            <span><?php echo get_prochaine_session_mentor($mentore_id, $mentor->ID) ?: 'Non planifiée'; ?></span>
                        </div>
                        
                        <div class="mentor-actions">
                            <button class="btn btn-primary" onclick="planifierSession(<?php echo $mentor->ID; ?>)">
                                🗓️ Planifier
                            </button>
                            <button class="btn btn-secondary" onclick="contacterMentor(<?php echo $mentor->ID; ?>)">
                                💬 Contacter
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Ma Progression -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>📊 Ma Progression</h2>
            </div>
            <div class="progression-details">
                <div class="objectifs-list">
                    <h4>Mes objectifs</h4>
                    <?php echo get_objectifs_mentore($mentore_id); ?>
                </div>
                <div class="competences-progress">
                    <h4>Compétences acquises</h4>
                    <?php echo get_competences_acquises($mentore_id); ?>
                </div>
            </div>
        </section>

        <!-- Mes Sessions -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>🗓️ Mes Sessions</h2>
                <a href="#" class="btn-link">Voir l'historique</a>
            </div>
            <div class="sessions-widget">
                <div class="prochaines-sessions">
                    <h4>Prochaines sessions</h4>
                    <?php echo get_prochaines_sessions_mentore($mentore_id); ?>
                </div>
                <div class="sessions-passees">
                    <h4>Dernières sessions</h4>
                    <?php echo get_sessions_passees($mentore_id); ?>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
/* Styles spécifiques au dashboard mentoré */
.dashboard-mentore .dashboard-header.mentore-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.dashboard-mentore .mentor-card {
    border-left: 4px solid #4facfe;
}

.dashboard-mentore .btn-primary {
    background: #4facfe;
}

.dashboard-mentore .btn-primary:hover {
    background: #3a9bed;
}
</style>

<script>
function planifierSession(mentorId) {
    alert('Planifier session avec mentor ID: ' + mentorId);
    // Implémenter la logique de planification
}

function contacterMentor(mentorId) {
    alert('Contacter mentor ID: ' + mentorId);
    // Implémenter la logique de messagerie
}
</script>