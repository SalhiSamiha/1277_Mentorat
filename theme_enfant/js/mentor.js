jQuery(document).ready(function($) {
    $('.btn-demande-mentorat').on('click', function(e) {
        e.preventDefault();
        
        var mentorId = $(this).data('mentor-id');
        var button = $(this);
        
        // Afficher un indicateur de chargement
        button.text('Envoi en cours...').prop('disabled', true);
        
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
                    button.text('Demande envoyée!').css('background-color', '#6c757d');
                    alert('Votre demande de jumelage a été envoyée avec succès!');
                } else {
                    button.text('Faire une demande de jumelage').prop('disabled', false);
                    alert('Une erreur s\'est produite. Veuillez réessayer.');
                }
            },
            error: function() {
                button.text('Faire une demande de jumelage').prop('disabled', false);
                alert('Une erreur s\'est produite. Veuillez réessayer.');
            }
        });
    });
});