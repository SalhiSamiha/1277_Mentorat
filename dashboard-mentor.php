<?php
/**
 * Dashboard spécifique pour les MENTORS
 */
if (!defined('ABSPATH')) exit;

$mentor_id = $user->ID;
$mentores = get_mentores_assignes($mentor_id);
?>
<div class="dashboard-mentor">
    <!-- Header du Dashboard -->
    <div class="dashboard-header mentor-header">
        <div class="header-content">
            <h1>🧑‍🏫 Tableau de Bord Mentor</h1>
            <p>Gérez vos mentorés et suivez leur progression</p>
        </div>
        <div class="user-welcome">
            <div class="user-avatar">
                <?php 
                $photo = get_field('user_photo', 'user_' . $mentor_id);
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
                <span>Mentor</span>
            </div>
        </div>
    </div>

    <!-- Statistiques Rapides -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo count($mentores); ?></h3>
                <p>Mentorés actifs</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-content">
                <h3><?php echo get_sessions_ce_mois($mentor_id); ?></h3>
                <p>Sessions ce mois</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <h3><?php echo get_heures_mentorat($mentor_id); ?>h</h3>
                <p>Heures de mentorat</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-content">
                <h3><?php echo get_note_moyenne($mentor_id); ?>/5</h3>
                <p>Note moyenne</p>
            </div>
        </div>
    </div>

    <!-- Sections principales -->
    <div class="dashboard-sections">
        <!-- Mes Mentorés -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>👥 Mes Mentorés</h2>
                <span class="badge"><?php echo count($mentores); ?></span>
            </div>
            
            <?php if (empty($mentores)): ?>
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <h3>Aucun mentoré assigné</h3>
                    <p>Vous n'avez pas encore de mentorés assignés à votre profil.</p>
                    <a href="/recherche-demandes" class="btn-primary">Voir les demandes</a>
                </div>
            <?php else: ?>
                <div class="mentores-grid">
                    <?php foreach ($mentores as $mentore): ?>
                    <div class="mentore-card">
                        <div class="mentore-main">
                            <div class="mentore-avatar">
                                <?php 
                                $mentore_photo = get_field('user_photo', 'user_' . $mentore->ID);
                                if ($mentore_photo): ?>
                                    <img src="<?php echo esc_url($mentore_photo); ?>" alt="<?php echo esc_attr($mentore->display_name); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($mentore->display_name, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mentore-info">
                                <h4><?php echo esc_html($mentore->display_name); ?></h4>
                                <p class="mentore-email"><?php echo esc_html($mentore->user_email); ?></p>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo get_progression_mentore($mentore->ID); ?>%"></div>
                                    <span class="progress-text"><?php echo get_progression_mentore($mentore->ID); ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mentore-details">
                            <div class="detail">
                                <span class="label">Objectifs:</span>
                                <span class="value"><?php echo get_nombre_objectifs($mentore->ID); ?></span>
                            </div>
                            <div class="detail">
                                <span class="label">Dernière session:</span>
                                <span class="value"><?php echo get_derniere_session($mentor_id, $mentore->ID); ?></span>
                            </div>
                        </div>
                        
                        <div class="mentore-actions">
                            <button class="btn btn-secondary" onclick="voirProgression(<?php echo $mentore->ID; ?>)">
                                📊 Progression
                            </button>
                            <button class="btn btn-primary" onclick="contacterMentore(<?php echo $mentore->ID; ?>)">
                                💬 Contacter
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Planning -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>🗓️ Mon Planning</h2>
                <a href="#" class="btn-link">Voir tout</a>
            </div>
            <div class="planning-widget">
                <div class="prochaines-sessions">
                    <h4>Prochaines sessions</h4>
                    <?php echo get_prochaines_sessions($mentor_id); ?>
                </div>
                <div class="disponibilites">
                    <h4>Mes disponibilités</h4>
                    <?php echo get_disponibilites_mentor($mentor_id); ?>
                </div>
            </div>
        </section>

        <!-- Progression globale -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>📈 Progression Globale</h2>
            </div>
            <div class="progression-globale">
                <div class="stats-comparison">
                    <div class="stat-comparison">
                        <span class="stat-value"><?php echo get_taux_reussite($mentor_id); ?>%</span>
                        <span class="stat-label">Taux de réussite</span>
                    </div>
                    <div class="stat-comparison">
                        <span class="stat-value"><?php echo get_satisfaction_moyenne($mentor_id); ?>/5</span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                    <div class="stat-comparison">
                        <span class="stat-value"><?php echo get_taux_retention($mentor_id); ?>%</span>
                        <span class="stat-label">Rétention</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>


<!-- Ajoutez cette modale pour le formulaire de demande -->
<div id="modale-demande-mentorat" class="modale" style="display: none;">
    <div class="modale-contenu">
        <span class="fermer-modale">&times;</span>
        <h3>📨 Envoyer une demande de mentorat</h3>
        
        <form id="form-demande-mentorat">
            <input type="hidden" id="mentor-id-demande" name="mentor_id">
            
            <div class="form-group">
                <label for="message-demande">Message personnalisé (optionnel)</label>
                <textarea id="message-demande" name="message" rows="4" 
                          placeholder="Présentez-vous et expliquez pourquoi vous souhaitez être mentoré par cette personne..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="objectif-demande">Vos objectifs d'apprentissage</label>
                <textarea id="objectif-demande" name="objectif" rows="3" 
                          placeholder="Que souhaitez-vous apprendre ? Quels sont vos objectifs ?"></textarea>
            </div>
            
            <div class="form-group">
                <label for="competences-demande">Compétences recherchées</label>
                <textarea id="competences-demande" name="competences" rows="2" 
                          placeholder="Quelles compétences souhaitez-vous développer ?"></textarea>
            </div>
            
            <div class="actions-modale">
                <button type="button" class="btn-annuler">Annuler</button>
                <button type="submit" class="btn-envoyer">Envoyer la demande</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Styles pour la modale */
.modale {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modale-contenu {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 10px;
    width: 90%;
    max-width: 600px;
    position: relative;
}

.fermer-modale {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.fermer-modale:hover {
    color: #000;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: inherit;
    resize: vertical;
}

.actions-modale {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-annuler {
    background: #95a5a6;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
}

.btn-envoyer {
    background: #27ae60;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    cursor: pointer;
}

.btn-annuler:hover {
    background: #7f8c8d;
}

.btn-envoyer:hover {
    background: #219a52;
}

/* Style pour les boutons désactivés */
.btn-demande-mentorat.envoye {
    background: #95a5a6;
    cursor: not-allowed;
}

.btn-demande-mentorat.envoye::after {
    content: " ✓ Demande envoyée";
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 5px;
    color: white;
    z-index: 1001;
    animation: slideIn 0.3s ease-out;
}

.notification.success {
    background: #27ae60;
}

.notification.error {
    background: #e74c3c;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Variables globales
    var nonce = '<?php echo wp_create_nonce("demande_mentor_nonce"); ?>';
    
    // Ouvrir la modale de demande
    $('.btn-demande-mentorat').on('click', function() {
        var mentorId = $(this).data('mentor-id');
        var mentorName = $(this).closest('.mentor-card').find('h3').text();
        
        // Vérifier si l'utilisateur est connecté
        <?php if (!is_user_logged_in()): ?>
            alert('Vous devez être connecté pour envoyer une demande de mentorat.');
            window.location.href = '<?php echo wp_login_url(get_permalink()); ?>';
            return;
        <?php endif; ?>
        
        // Vérifier le rôle de l'utilisateur
        <?php 
        $user = wp_get_current_user();
        if (is_user_logged_in() && !in_array('mentore', $user->roles)): ?>
            alert('Seuls les mentorés peuvent envoyer des demandes de mentorat.');
            return;
        <?php endif; ?>
        
        $('#mentor-id-demande').val(mentorId);
        $('#modale-demande-mentorat h3').html('📨 Demande à <strong>' + mentorName + '</strong>');
        $('#modale-demande-mentorat').show();
    });
    
    // Fermer la modale
    $('.fermer-modale, .btn-annuler').on('click', function() {
        $('#modale-demande-mentorat').hide();
    });
    
    // Soumettre le formulaire de demande
    $('#form-demande-mentorat').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'envoyer_demande_mentorat',
            nonce: nonce,
            mentor_id: $('#mentor-id-demande').val(),
            message: $('#message-demande').val(),
            objectif: $('#objectif-demande').val(),
            competences: $('#competences-demande').val()
        };
        
        var submitBtn = $(this).find('.btn-envoyer');
        submitBtn.prop('disabled', true).text('Envoi en cours...');
        
        $.post('<?php echo admin_url("admin-ajax.php"); ?>', formData, function(response) {
            if (response.success) {
                // Afficher notification de succès
                showNotification(response.data, 'success');
                
                // Fermer la modale
                $('#modale-demande-mentorat').hide();
                
                // Désactiver le bouton et changer son texte
                $('.btn-demande-mentorat[data-mentor-id="' + formData.mentor_id + '"]')
                    .addClass('envoye')
                    .prop('disabled', true);
                    
                // Réinitialiser le formulaire
                $('#form-demande-mentorat')[0].reset();
            } else {
                showNotification(response.data, 'error');
            }
            
            submitBtn.prop('disabled', false).text('Envoyer la demande');
        }).fail(function() {
            showNotification('Erreur réseau. Veuillez réessayer.', 'error');
            submitBtn.prop('disabled', false).text('Envoyer la demande');
        });
    });
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        var notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Fermer la modale en cliquant à l'extérieur
    $(window).on('click', function(e) {
        if ($(e.target).is('#modale-demande-mentorat')) {
            $('#modale-demande-mentorat').hide();
        }
    });
});
</script>



<style>
/* Styles spécifiques au dashboard mentor */
.dashboard-mentor .dashboard-header.mentor-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.dashboard-mentor .mentore-card {
    border-left: 4px solid #667eea;
}

.dashboard-mentor .btn-primary {
    background: #667eea;
}

.dashboard-mentor .btn-primary:hover {
    background: #5a6fd8;
}
</style>

<script>
function voirProgression(mentoreId) {
    alert('Voir progression du mentoré ID: ' + mentoreId);
    // Implémenter la logique pour voir la progression détaillée
}

function contacterMentore(mentoreId) {
    alert('Contacter mentoré ID: ' + mentoreId);
    // Implémenter la logique de messagerie
}
</script>