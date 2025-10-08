<?php
/**
 * Template Name: Mes Demandes de Mentorat
 * Template Post Type: page
 */
get_header(); ?>

<div class="container mes-demandes-mentorat">
    <?php 
    // Afficher le shortcode des demandes de mentorat
    echo do_shortcode('[demandes_mentorat]');
    ?>
</div>

<style>
.mes-demandes-mentorat {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    min-height: 80vh;
}

/* Styles pour les notifications */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 5px;
    color: white;
    z-index: 1001;
    animation: slideIn 0.3s ease-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-weight: 600;
}

.notification.success {
    background: #27ae60;
    border-left: 4px solid #219a52;
}

.notification.error {
    background: #e74c3c;
    border-left: 4px solid #c0392b;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Animation de chargement */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: " ...";
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0%, 100% { content: " ."; }
    33% { content: " .."; }
    66% { content: " ..."; }
}

/* Améliorations responsives */
@media (max-width: 768px) {
    .mes-demandes-mentorat {
        padding: 10px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Fonction pour accepter une demande
    $('.btn-accepter').on('click', function() {
        var demandeId = $(this).data('demande-id');
        var card = $(this).closest('.demande-card');
        var mentoreName = card.find('h3').text();
        
        if (confirm('Êtes-vous sûr de vouloir accepter la demande de mentorat de ' + mentoreName + ' ?\n\nVous serez automatiquement assigné comme mentor.')) {
            accepterDemande(demandeId, card);
        }
    });
    
    // Fonction pour refuser une demande
    $('.btn-confirmer-refus').on('click', function() {
        var demandeId = $(this).data('demande-id');
        var raison = $('#refus-form-' + demandeId + ' .raison-refus').val();
        var card = $(this).closest('.demande-card');
        var mentoreName = card.find('h3').text();
        
        if (confirm('Confirmez-vous le refus de la demande de ' + mentoreName + ' ?')) {
            refuserDemande(demandeId, raison, card);
        }
    });
    
    // Fonction pour accepter une demande
    function accepterDemande(demandeId, card) {
        var btn = card.find('.btn-accepter');
        var btnRefuser = card.find('.btn-refuser');
        
        // Désactiver les boutons
        btn.prop('disabled', true).text('Acceptation...').addClass('loading');
        btnRefuser.prop('disabled', true);
        
        // Ajouter une classe de chargement à la carte
        card.addClass('loading');
        
        $.post('<?php echo admin_url("admin-ajax.php"); ?>', {
            action: 'accepter_demande_mentorat',
            demande_id: demandeId,
            nonce: '<?php echo wp_create_nonce("accepter_demande_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                showNotification('✅ ' + response.data, 'success');
                
                // Animation de succès
                card.css('border-left-color', '#27ae60').animate({
                    backgroundColor: '#d4edda'
                }, 500);
                
                // Supprimer la carte après un délai
                setTimeout(function() {
                    card.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Si plus de demandes en attente, afficher message
                        if ($('.demande-card').length === 0) {
                            $('.demandes-list').html('<div class="aucune-demande"><p>✅ Toutes les demandes ont été traitées.</p></div>');
                        }
                    });
                }, 1500);
                
            } else {
                showNotification('❌ ' + response.data, 'error');
                resetButtons(btn, btnRefuser, card);
            }
        }).fail(function() {
            showNotification('❌ Erreur réseau. Veuillez réessayer.', 'error');
            resetButtons(btn, btnRefuser, card);
        });
    }
    
    // Fonction pour refuser une demande
    function refuserDemande(demandeId, raison, card) {
        var btn = card.find('.btn-confirmer-refus');
        var btnAccepter = card.find('.btn-accepter');
        
        // Désactiver les boutons
        btn.prop('disabled', true);
        btnAccepter.prop('disabled', true);
        
        // Ajouter une classe de chargement à la carte
        card.addClass('loading');
        
        $.post('<?php echo admin_url("admin-ajax.php"); ?>', {
            action: 'refuser_demande_mentorat',
            demande_id: demandeId,
            raison: raison,
            nonce: '<?php echo wp_create_nonce("refuser_demande_nonce"); ?>'
        }, function(response) {
            if (response.success) {
                showNotification('✅ ' + response.data, 'success');
                
                // Animation de refus
                card.css('border-left-color', '#e74c3c').animate({
                    backgroundColor: '#f8d7da'
                }, 500);
                
                // Supprimer la carte après un délai
                setTimeout(function() {
                    card.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Si plus de demandes en attente, afficher message
                        if ($('.demande-card').length === 0) {
                            $('.demandes-list').html('<div class="aucune-demande"><p>✅ Toutes les demandes ont été traitées.</p></div>');
                        }
                    });
                }, 1500);
                
            } else {
                showNotification('❌ ' + response.data, 'error');
                resetButtons(btn, btnAccepter, card);
            }
        }).fail(function() {
            showNotification('❌ Erreur réseau. Veuillez réessayer.', 'error');
            resetButtons(btn, btnAccepter, card);
        });
    }
    
    // Réinitialiser les boutons après une erreur
    function resetButtons(btn1, btn2, card) {
        btn1.prop('disabled', false);
        if (btn1.hasClass('btn-accepter')) {
            btn1.text('✅ Accepter la demande').removeClass('loading');
        } else {
            btn1.text('🔒 Confirmer le refus').removeClass('loading');
        }
        btn2.prop('disabled', false);
        card.removeClass('loading');
    }
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        // Supprimer les notifications existantes
        $('.notification').remove();
        
        var notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        // Auto-suppression après 5 secondes
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Permettre la fermeture manuelle
        notification.on('click', function() {
            $(this).fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
});
</script>

<?php get_footer(); ?>