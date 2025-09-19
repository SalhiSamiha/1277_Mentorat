<?php
/**
 * Formulaire de recherche de mentor
 */
?>
<form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="form-recherche-mentor">
    <input type="hidden" name="recherche_mentor" value="1">
    
    <div class="form-group">
        <label for="domaine">Domaine d'expertise</label>
        <select name="domaine" id="domaine">
            <option value="">Tous les domaines</option>
            <?php 
            $domaines = get_option('mentor_domaines', array());
            foreach ($domaines as $domaine) {
                $selected = (isset($_GET['domaine']) && $_GET['domaine'] === $domaine) ? 'selected' : '';
                echo '<option value="' . esc_attr($domaine) . '" ' . $selected . '>' . esc_html($domaine) . '</option>';
            }
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="langue">Langue</label>
        <select name="langue" id="langue">
            <option value="">Toutes les langues</option>
            <?php 
            $langues = get_option('mentor_langues', array());
            foreach ($langues as $langue) {
                $selected = (isset($_GET['langue']) && $_GET['langue'] === $langue) ? 'selected' : '';
                echo '<option value="' . esc_attr($langue) . '" ' . $selected . '>' . esc_html($langue) . '</option>';
            }
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="region">Région</label>
        <select name="region" id="region">
            <option value="">Toutes les régions</option>
            <?php 
            $regions = get_option('mentor_regions', array());
            foreach ($regions as $region) {
                $selected = (isset($_GET['region']) && $_GET['region'] === $region) ? 'selected' : '';
                echo '<option value="' . esc_attr($region) . '" ' . $selected . '>' . esc_html($region) . '</option>';
            }
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <input type="submit" value="Rechercher" class="btn-rechercher">
    </div>
</form>