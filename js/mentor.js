jQuery(document).ready(function($) {
    // Gestion des demandes de mentorat
    $('.btn-demande-mentorat').on('click', function(e) {
        e.preventDefault();
        
        var mentorId = $(this).data('mentor-id');
        var button = $(this);
        var mentorName = $(this).closest('.mentor-card').find('.mentor-info h3').text() || 'ce mentor';
        
        // Vérifier si l'utilisateur est connecté
        if (typeof mentor_ajax === 'undefined') {
            alert('Veuillez vous connecter pour faire une demande de mentorat.');
            window.location.href = '/index.php/connexion/';
            return;
        }
        
        // Confirmation avant envoi
        if (!confirm(`Êtes-vous sûr de vouloir envoyer une demande de jumelage à ${mentorName} ?`)) {
            return;
        }
        
        // Afficher un indicateur de chargement
        var originalText = button.text();
        button.html('<span class="loading-spinner"></span> Envoi en cours...').prop('disabled', true);
        
        // Envoyer la requête AJAX
        $.ajax({
            url: mentor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'demande_mentorat',
                mentor_id: mentorId,
                nonce: mentor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.html('✅ Demande envoyée!').css({
                        'background-color': '#28a745',
                        'color': 'white'
                    }).prop('disabled', true);
                    
                    // Notification toast
                    showNotification('Votre demande de jumelage a été envoyée avec succès!', 'success');
                    
                    // Mettre à jour le compteur si existant
                    updateDemandesCount();
                    
                } else {
                    button.html(originalText).prop('disabled', false);
                    
                    // Messages d'erreur spécifiques
                    var errorMessage = 'Une erreur s\'est produite. Veuillez réessayer.';
                    if (response.data === 'Demande déjà existante') {
                        errorMessage = 'Vous avez déjà une demande en attente avec ce mentor.';
                        button.html('⏳ Demande en attente').css({
                            'background-color': '#ffc107',
                            'color': '#212529'
                        }).prop('disabled', true);
                    } else if (response.data === 'Utilisateur non connecté') {
                        errorMessage = 'Veuillez vous connecter pour faire une demande.';
                        window.location.href = '/index.php/connexion/';
                    }
                    
                    showNotification(errorMessage, 'error');
                }
            },
            error: function(xhr, status, error) {
                button.html(originalText).prop('disabled', false);
                showNotification('Erreur de connexion. Veuillez vérifier votre connexion internet.', 'error');
                console.error('Erreur AJAX:', error);
            }
        });
    });
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        // Supprimer les notifications existantes
        $('.mentor-notification').remove();
        
        var notification = $('<div class="mentor-notification"></div>');
        notification.text(message);
        
        if (type === 'success') {
            notification.css({
                'background-color': '#d4edda',
                'color': '#155724',
                'border': '1px solid #c3e6cb'
            });
        } else {
            notification.css({
                'background-color': '#f8d7da',
                'color': '#721c24',
                'border': '1px solid #f5c6cb'
            });
        }
        
        notification.css({
            'position': 'fixed',
            'top': '20px',
            'right': '20px',
            'padding': '15px 20px',
            'border-radius': '5px',
            'z-index': '10000',
            'font-weight': '600',
            'box-shadow': '0 4px 12px rgba(0,0,0,0.15)',
            'max-width': '400px',
            'animation': 'slideInRight 0.3s ease-out'
        });
        
        $('body').append(notification);
        
        // Auto-disparition après 5 secondes
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Fonction pour mettre à jour le compteur de demandes
    function updateDemandesCount() {
        $.ajax({
            url: mentor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_demandes_count',
                nonce: mentor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.demandes-count').text(response.data.count);
                }
            }
        });
    }
    
    // Animation pour le spinner de chargement
    var style = document.createElement('style');
    style.textContent = `
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .mentor-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .mentor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    `;
    document.head.appendChild(style);
    
    // Gestion des filtres de recherche avancée
    $('.form-recherche-mentor select').on('change', function() {
        var form = $(this).closest('form');
        var anyFilterSelected = form.find('select').filter(function() {
            return $(this).val() !== '';
        }).length > 0;
        
        if (anyFilterSelected) {
            form.find('.btn-rechercher').css('background-color', '#218838');
        } else {
            form.find('.btn-rechercher').css('background-color', '#007bff');
        }
    });
    
    // Confirmation de réinitialisation
    $('.btn-reinitialiser').on('click', function(e) {
        if (!confirm('Êtes-vous sûr de vouloir réinitialiser tous les filtres ?')) {
            e.preventDefault();
        }
    });
    
    // Amélioration de l'UX pour les cartes mentors
    $('.mentor-card').on('click', function(e) {
        // Ne déclencher que si on ne clique pas sur un bouton
        if (!$(e.target).is('button') && !$(e.target).closest('button').length) {
            var mentorId = $(this).find('.btn-demande-mentorat').data('mentor-id');
            console.log('Clic sur la carte du mentor ID:', mentorId);
            // Vous pourriez ajouter une modal avec plus d'informations ici
        }
    });
    
    // Gestion des états de disponibilité
    $('.availability-status').each(function() {
        var status = $(this).text().toLowerCase();
        var colors = {
            'disponible': '#28a745',
            'occupé': '#dc3545',
            'en vacances': '#ffc107'
        };
        
        if (colors[status]) {
            $(this).css('background-color', colors[status]);
        }
    });
});