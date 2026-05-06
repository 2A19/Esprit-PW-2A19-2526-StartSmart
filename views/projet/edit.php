<div class="projet-detail-container" style="max-width: 800px;">
    <div style="margin-bottom: 20px;">
        <a href="index.php?controller=projet&action=show&id=<?php echo $this->projet->id; ?>" style="color: #0066cc; text-decoration: none; font-weight: 600;">← Retour au Projet</a>
    </div>

    <div class="projet-hero" style="text-align: center; margin-bottom: 30px; padding: 30px 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2.2em; font-weight: 800; color: #2c3e50; display:flex; align-items:center; justify-content:center; gap:10px;"><i data-lucide="edit-3" style="width:32px;height:32px;color:#f1c40f;"></i> Modifier le Projet</h1>
        <p style="margin: 0; font-size: 1.1em; color: #7f8c8d; font-family: monospace;">Ref: <?php echo htmlspecialchars($this->projet->num); ?></p>
    </div>

    <?php if(isset($error)) { echo "<div style='background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>$error</div>"; } ?>

    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <form id="formProjetEdit" action="index.php?controller=projet&action=edit&id=<?php echo htmlspecialchars($this->projet->id); ?>" method="POST" onsubmit="return validerProjetEdit(event)">
            <div id="error-msg-edit" style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; display:none; margin-bottom:20px; font-weight:bold;"></div>
            
            <input type="hidden" id="num_edit" name="num" value="<?php echo htmlspecialchars($this->projet->num); ?>">

            <h3 style="margin-top: 0; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px;">L'Essentiel</h3>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="nomprojet_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Nom de la Startup / Projet *</label>
                <input type="text" id="nomprojet_edit" name="nomprojet" value="<?php echo htmlspecialchars($this->projet->nomprojet); ?>" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 16px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="description_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Le Pitch (Description) *</label>
                <textarea id="description_edit" name="description" rows="6" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-family: inherit; font-size: 15px; resize: vertical;"><?php echo htmlspecialchars($this->projet->description ?? ''); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label for="city_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Ville *</label>
                    <input type="text" id="city_edit" name="city" value="<?php echo htmlspecialchars($this->projet->city ?? ''); ?>" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px;">
                </div>
                <div class="form-group">
                    <label for="country_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Pays *</label>
                    <input type="text" id="country_edit" name="country" value="<?php echo htmlspecialchars($this->projet->country ?? ''); ?>" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label for="categorie_id_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Secteur d'Activité *</label>
                    <select id="categorie_id_edit" name="categorie_id" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; background-color: white;">
                        <option value="">-- Sélectionnez un secteur --</option>
                        <?php foreach ($categoriesList as $categorie): ?>
                            <option value="<?php echo $categorie['id']; ?>" <?php echo ($this->projet->categorie_id == $categorie['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categorie['typeprojet']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="statut_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Visibilité</label>
                    <select id="statut_edit" name="statut" style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; background-color: white;">
                        <option value="actif" <?php echo ($this->projet->statut === 'actif') ? 'selected' : ''; ?>>Publié (Actif)</option>
                        <option value="draft" <?php echo ($this->projet->statut === 'draft') ? 'selected' : ''; ?>>Brouillon (Privé)</option>
                        <option value="archived" <?php echo ($this->projet->statut === 'archived') ? 'selected' : ''; ?>>Archivé</option>
                        <?php if (function_exists('isAdmin') && isAdmin()): ?>
                            <option value="deleted" <?php echo ($this->projet->statut === 'deleted') ? 'selected' : ''; ?>>Supprimé</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <h3 style="margin-top: 0; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px;">Planning & Financement</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="datedebut_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Date de Lancement *</label>
                    <input type="date" id="datedebut_edit" name="datedebut" value="<?php echo htmlspecialchars($this->projet->datedebut); ?>" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px;">
                </div>
                <div class="form-group">
                    <label for="datefin_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Date d'Objectif (Clôture) *</label>
                    <input type="date" id="datefin_edit" name="datefin" value="<?php echo htmlspecialchars($this->projet->datefin); ?>" required style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label for="budget_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Fonds Requis (TND) *</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 15px; top: 12px; color: #7f8c8d; font-weight: bold;">DT</span>
                        <input type="number" id="budget_edit" name="budget" step="0.01" min="0" value="<?php echo htmlspecialchars($this->projet->budget); ?>" required style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 16px;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="gain_edit" style="display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50;">Gains Estimés (TND) *</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 15px; top: 12px; color: #7f8c8d; font-weight: bold;">DT</span>
                        <input type="number" id="gain_edit" name="gain" step="0.01" min="0" value="<?php echo htmlspecialchars($this->projet->gain); ?>" required style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 16px;">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 40px;">
                <button type="submit" class="btn-primary" style="flex: 2; padding: 15px; background: #2ecc71; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 18px; transition: transform 0.2s;">✓ Enregistrer les modifications</button>
                <a href="index.php?controller=projet&action=show&id=<?php echo $this->projet->id; ?>" style="flex: 1; padding: 15px; background: #f8f9fa; color: #2c3e50; border: 1px solid #dce1e6; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 16px; display: flex; align-items: center; justify-content: center;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function validerProjetEdit(e) {
    let errorMsg = document.getElementById('error-msg-edit');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let nomprojet = document.getElementById('nomprojet_edit').value.trim();
    let description = document.getElementById('description_edit').value.trim();
    let datedebut = document.getElementById('datedebut_edit').value.trim();
    let datefin = document.getElementById('datefin_edit').value.trim();
    let budget = document.getElementById('budget_edit').value.trim();
    let gain = document.getElementById('gain_edit').value.trim();
    let categorie_id = document.getElementById('categorie_id_edit').value;

    if (nomprojet.length < 3) {
        erreurs.push("• Le nom du projet doit contenir au moins 3 caractères.");
    }
    if (description.length < 20) {
        erreurs.push("• Le pitch est trop court. Donnez un peu plus de détails (20 caractères min).");
    }
    if (datedebut === '') {
        erreurs.push("• Veuillez sélectionner une date de lancement.");
    }
    if (datefin === '') {
        erreurs.push("• Veuillez sélectionner une date de clôture.");
    }
    if (datedebut !== '' && datefin !== '' && new Date(datefin) <= new Date(datedebut)) {
        erreurs.push("• La date de clôture doit être ultérieure à la date de lancement.");
    }
    if (budget === '' || isNaN(budget) || Number(budget) <= 0) {
        erreurs.push("• Les fonds requis doivent être supérieurs à 0.");
    }
    if (categorie_id === '') {
        erreurs.push("• Veuillez sélectionner un secteur d'activité.");
    }

    if (erreurs.length > 0) {
        e.preventDefault();
        errorMsg.innerHTML = erreurs.join('<br>');
        errorMsg.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
    }
    return true;
}
</script>
