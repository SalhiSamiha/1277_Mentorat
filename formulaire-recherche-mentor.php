<?php
/**
 * Formulaire de recherche de mentor
 */
?>
<div class="form-recherche-mentor-container">
    <form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="form-recherche-mentor">
        <input type="hidden" name="recherche_mentor" value="1">
        
        <div class="form-group">
            <label for="domaine">Domaine d'expertise</label>
            <select name="domaine" id="domaine">
                <option value="">Tous les domaines</option>
                <option value="developpement" <?php echo selected($_GET['domaine'] ?? '', 'developpement'); ?>>Développement Web</option>
                <option value="design" <?php echo selected($_GET['domaine'] ?? '', 'design'); ?>>Design UX/UI</option>
                <option value="marketing" <?php echo selected($_GET['domaine'] ?? '', 'marketing'); ?>>Marketing Digital</option>
                <option value="data" <?php echo selected($_GET['domaine'] ?? '', 'data'); ?>>Data Science</option>
                <option value="entrepreneuriat" <?php echo selected($_GET['domaine'] ?? '', 'entrepreneuriat'); ?>>Entrepreneuriat</option>
                <option value="gestion" <?php echo selected($_GET['domaine'] ?? '', 'gestion'); ?>>Gestion de projet</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="expertise_level">Niveau d'expertise</label>
            <select name="expertise_level" id="expertise_level">
                <option value="">Tous les niveaux</option>
                <option value="debutant" <?php echo selected($_GET['expertise_level'] ?? '', 'debutant'); ?>>Débutant</option>
                <option value="intermediaire" <?php echo selected($_GET['expertise_level'] ?? '', 'intermediaire'); ?>>Intermédiaire</option>
                <option value="avance" <?php echo selected($_GET['expertise_level'] ?? '', 'avance'); ?>>Avancé</option>
                <option value="expert" <?php echo selected($_GET['expertise_level'] ?? '', 'expert'); ?>>Expert</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="availability_status">Statut de disponibilité</label>
            <select name="availability_status" id="availability_status">
                <option value="">Tous les statuts</option>
                <option value="disponible" <?php echo selected($_GET['availability_status'] ?? '', 'disponible'); ?>>🟢 Disponible</option>
                <option value="occupe" <?php echo selected($_GET['availability_status'] ?? '', 'occupe'); ?>>🔴 Occupé</option>
                <option value="vacances" <?php echo selected($_GET['availability_status'] ?? '', 'vacances'); ?>>🟡 En vacances</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="jour_creneau">Jour de disponibilité</label>
            <select name="jour_creneau" id="jour_creneau">
                <option value="">Tous les jours</option>
                <option value="lundi" <?php echo selected($_GET['jour_creneau'] ?? '', 'lundi'); ?>>Lundi</option>
                <option value="mardi" <?php echo selected($_GET['jour_creneau'] ?? '', 'mardi'); ?>>Mardi</option>
                <option value="mercredi" <?php echo selected($_GET['jour_creneau'] ?? '', 'mercredi'); ?>>Mercredi</option>
                <option value="jeudi" <?php echo selected($_GET['jour_creneau'] ?? '', 'jeudi'); ?>>Jeudi</option>
                <option value="vendredi" <?php echo selected($_GET['jour_creneau'] ?? '', 'vendredi'); ?>>Vendredi</option>
                <option value="samedi" <?php echo selected($_GET['jour_creneau'] ?? '', 'samedi'); ?>>Samedi</option>
                <option value="dimanche" <?php echo selected($_GET['jour_creneau'] ?? '', 'dimanche'); ?>>Dimanche</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="user_location">Localisation</label>
            <input type="text" name="user_location" id="user_location" 
                   value="<?php echo esc_attr($_GET['user_location'] ?? ''); ?>" 
                   placeholder="Ville ou pays">
        </div>
        
        <div class="form-group">
            <label for="competences_recherchees">Compétences spécifiques</label>
            <select name="competences_recherchees" id="competences_recherchees">
                <option value="">Toutes les compétences</option>
                <option value="developpement" <?php echo selected($_GET['competences_recherchees'] ?? '', 'developpement'); ?>>Développement Web</option>
                <option value="design" <?php echo selected($_GET['competences_recherchees'] ?? '', 'design'); ?>>Design UX/UI</option>
                <option value="marketing" <?php echo selected($_GET['competences_recherchees'] ?? '', 'marketing'); ?>>Marketing Digital</option>
                <option value="data" <?php echo selected($_GET['competences_recherchees'] ?? '', 'data'); ?>>Data Science</option>
                <option value="entrepreneuriat" <?php echo selected($_GET['competences_recherchees'] ?? '', 'entrepreneuriat'); ?>>Entrepreneuriat</option>
                <option value="gestion" <?php echo selected($_GET['competences_recherchees'] ?? '', 'gestion'); ?>>Gestion de projet</option>
            </select>
        </div>
        
        <div class="form-group actions">
            <input type="submit" value="🔍 Rechercher" class="btn-rechercher">
            <a href="<?php echo esc_url(get_permalink()); ?>" class="btn-reinitialiser">🔄 Réinitialiser</a>
        </div>
    </form>
</div>

<style>
.form-recherche-mentor-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.form-recherche-mentor {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-recherche-mentor .form-group {
    margin-bottom: 0;
}

.form-recherche-mentor label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95rem;
}

.form-recherche-mentor select,
.form-recherche-mentor input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    box-sizing: border-box;
}

.form-recherche-mentor select:focus,
.form-recherche-mentor input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-recherche-mentor .actions {
    grid-column: 1 / -1;
    text-align: center;
    margin-top: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.btn-rechercher {
    background: #3498db;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    margin-right: 1rem;
    transition: background 0.3s;
}

.btn-rechercher:hover {
    background: #2980b9;
}

.btn-reinitialiser {
    background: #95a5a6;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    transition: background 0.3s;
}

.btn-reinitialiser:hover {
    background: #7f8c8d;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .form-recherche-mentor {
        grid-template-columns: 1fr;
    }
    
    .form-recherche-mentor-container {
        padding: 1.5rem;
    }
    
    .btn-rechercher,
    .btn-reinitialiser {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
    }
}

/* Amélioration visuelle des selects */
.form-recherche-mentor select {
    background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'><path fill='%23333' d='M2 0L0 2h4zm0 5L0 3h4z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 0.65rem;
    padding-right: 2.5rem;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>

<?php
// Fonction helper pour selected si elle n'existe pas
if (!function_exists('selected')) {
    function selected($param, $value) {
        if (isset($param) && $param === $value) {
            return 'selected="selected"';
        }
        return '';
    }
}