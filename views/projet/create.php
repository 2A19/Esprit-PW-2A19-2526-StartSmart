<div class="projet-detail-container" style="max-width: 800px;">
    <div style="margin-bottom: 20px;">
        <a href="index.php?controller=projet&action=index" style="color: #0066cc; text-decoration: none; font-weight: 600;">← Retour aux Startups</a>
    </div>

    <div class="projet-hero" style="text-align: center; margin-bottom: 30px; padding: 30px 20px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2.5em; font-weight: 800; display:flex; align-items:center; justify-content:center; gap:10px;"><i data-lucide="rocket" style="width:36px;height:36px;color:#3498db;"></i> Smart Project Creator</h1>
        <p style="margin: 0; font-size: 1.1em; opacity: 0.85;">Créez un profil de startup complet pour matcher avec les meilleurs investisseurs et collaborateurs.</p>
    </div>

    <?php if(isset($error)) { echo "<div class='alert-error'>$error</div>"; } ?>
    <div id="error-msg" class="alert-error" style="display:none;"></div>

    <div class="wizard-container">
        <!-- Progress Bar -->
        <div class="wizard-progress">
            <div class="progress-step active" id="step-indicator-1">1. L'Essentiel</div>
            <div class="progress-step" id="step-indicator-2">2. Compétences</div>
            <div class="progress-step" id="step-indicator-3">3. Secteur & Stade</div>
            <div class="progress-step" id="step-indicator-4">4. Finance</div>
        </div>

        <form id="formProjet" action="index.php?controller=projet&action=create" method="POST">
            
            <!-- STEP 1: BASICS -->
            <div class="wizard-step active" id="step-1">
                <h3>L'Essentiel</h3>
                <div class="form-group">
                    <label>Nom de la Startup / Projet *</label>
                    <input type="text" id="nomprojet" name="nomprojet" placeholder="Ex: EcoTech Solutions">
                </div>
                <div class="form-group">
                    <label>Le Pitch (Description) *</label>
                    <textarea id="description" name="description" placeholder="Quel problème résolvez-vous ? (Min 50 caractères)" rows="5"></textarea>
                    <small style="color:#7f8c8d; float:right;" id="desc-counter">0 / 50 min</small>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Ville *</label>
                        <input type="text" id="city" name="city" placeholder="Ex: Paris" required>
                    </div>
                    <div class="form-group">
                        <label>Pays *</label>
                        <input type="text" id="country" name="country" placeholder="Ex: France" required>
                    </div>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn-next" onclick="nextStep(1)">Suivant →</button>
                </div>
            </div>

            <!-- STEP 2: SKILLS & ROLES -->
            <div class="wizard-step" id="step-2">
                <h3>Compétences & Rôles Requis</h3>
                <p style="color:#7f8c8d; margin-bottom:15px;">Sélectionnez au moins 1 compétence dont votre projet a besoin (idéalement 2 ou 3) pour optimiser le matching.</p>
                <div class="checkbox-grid">
                    <?php foreach ($allSkills as $skill): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" class="skill-checkbox">
                            <span class="checkbox-custom"></span>
                            <?php echo htmlspecialchars($skill['nom']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn-prev" onclick="prevStep(2)">← Précédent</button>
                    <button type="button" class="btn-next" onclick="nextStep(2)">Suivant →</button>
                </div>
            </div>

            <!-- STEP 3: CATEGORY & STAGE -->
            <div class="wizard-step" id="step-3">
                <h3>Secteur & Stade d'Avancement</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Secteur d'Activité *</label>
                        <select id="categorie_id" name="categorie_id">
                            <option value="">-- Sélectionnez --</option>
                            <?php foreach ($categoriesList as $categorie): ?>
                                <option value="<?php echo $categorie['id']; ?>"><?php echo htmlspecialchars($categorie['typeprojet']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stade du Projet *</label>
                        <select id="etape" name="etape">
                            <option value="idea">💡 Simple Idée</option>
                            <option value="mvp">🛠️ Prototype (MVP) en cours</option>
                            <option value="in_progress">🏗️ En développement actif</option>
                            <option value="launched">🚀 Lancé / Sur le marché</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Visibilité</label>
                    <select id="statut" name="statut">
                        <option value="actif">Public (Visible pour le Matching)</option>
                        <option value="draft">Brouillon (Privé)</option>
                    </select>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn-prev" onclick="prevStep(3)">← Précédent</button>
                    <button type="button" class="btn-next" onclick="nextStep(3)">Suivant →</button>
                </div>
            </div>

            <!-- STEP 4: FINANCE & PLANNING -->
            <div class="wizard-step" id="step-4">
                <h3>Planning & Financement</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Date de Lancement *</label>
                        <input type="date" id="datedebut" name="datedebut">
                    </div>
                    <div class="form-group">
                        <label>Date de Clôture Objectif *</label>
                        <input type="date" id="datefin" name="datefin">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Fonds Requis (Optionnel)</label>
                        <input type="number" id="budget" name="budget" step="0.01" min="0" placeholder="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Gains Estimés (Optionnel)</label>
                        <input type="number" id="gain" name="gain" step="0.01" min="0" placeholder="0" value="0">
                    </div>
                </div>
                <div class="wizard-actions">
                    <button type="button" class="btn-prev" onclick="prevStep(4)">← Précédent</button>
                    <button type="button" class="btn-submit" onclick="submitForm()" style="display:flex; align-items:center; justify-content:center; gap:8px;"><i data-lucide="rocket"></i> Lancer la Startup</button>
                </div>
            </div>

        </form>
    </div>
</div>

<style>
/* WIZARD CSS */
.wizard-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.wizard-progress {
    display: flex;
    margin-bottom: 30px;
    border-bottom: 2px solid #f0f2f5;
}
.progress-step {
    flex: 1;
    text-align: center;
    padding: 15px;
    color: #bdc3c7;
    font-weight: bold;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: 0.3s;
}
.progress-step.active {
    color: #3498db;
    border-bottom-color: #3498db;
}
.wizard-step {
    display: none;
    animation: fadeIn 0.4s ease-in-out;
}
.wizard-step.active {
    display: block;
}
.wizard-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #f0f2f5;
}
.btn-next, .btn-prev, .btn-submit {
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    border: none;
}
.btn-next { background: #3498db; color: white; }
.btn-prev { background: #f8f9fa; color: #2c3e50; border: 1px solid #dce1e6; }
.btn-submit { background: #27ae60; color: white; }
.form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #2c3e50; }
.form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; }
.alert-error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight:bold; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
/* Checkbox Grid reused from match.css logic */
.checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
.checkbox-label { display: flex; align-items: center; cursor: pointer; font-size: 15px; }
.checkbox-label input { margin-right: 10px; width: 18px; height: 18px; }
</style>

<script>
// Description char counter
document.getElementById('description').addEventListener('input', function() {
    let len = this.value.length;
    let counter = document.getElementById('desc-counter');
    counter.innerText = len + " / 50 min";
    counter.style.color = len >= 50 ? '#27ae60' : '#e74c3c';
});

function showError(msg) {
    let err = document.getElementById('error-msg');
    err.innerHTML = "⚠️ " + msg;
    err.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function nextStep(currentStep) {
    document.getElementById('error-msg').style.display = 'none';

    // Validation
    if (currentStep === 1) {
        if (document.getElementById('nomprojet').value.trim().length < 3) {
            return showError("Le nom du projet doit contenir au moins 3 caractères.");
        }
        if (document.getElementById('description').value.trim().length < 50) {
            return showError("La description est trop courte (minimum 50 caractères). Mettez en valeur votre idée !");
        }
    }
    else if (currentStep === 2) {
        let skills = document.querySelectorAll('.skill-checkbox:checked');
        if (skills.length === 0) {
            return showError("Vous devez sélectionner au moins une compétence requise pour votre projet.");
        }
    }
    else if (currentStep === 3) {
        if (document.getElementById('categorie_id').value === "") {
            return showError("Veuillez sélectionner un secteur d'activité.");
        }
    }

    // Switch step
    document.getElementById('step-' + currentStep).classList.remove('active');
    document.getElementById('step-indicator-' + currentStep).classList.remove('active');
    
    let next = currentStep + 1;
    document.getElementById('step-' + next).classList.add('active');
    document.getElementById('step-indicator-' + next).classList.add('active');
}

function prevStep(currentStep) {
    document.getElementById('error-msg').style.display = 'none';
    document.getElementById('step-' + currentStep).classList.remove('active');
    document.getElementById('step-indicator-' + currentStep).classList.remove('active');
    
    let prev = currentStep - 1;
    document.getElementById('step-' + prev).classList.add('active');
    document.getElementById('step-indicator-' + prev).classList.add('active');
}

function submitForm() {
    let datedebut = document.getElementById('datedebut').value;
    let datefin = document.getElementById('datefin').value;
    
    if (datedebut === '' || datefin === '') {
        return showError("Veuillez remplir les dates de lancement et de clôture.");
    }
    if (new Date(datefin) <= new Date(datedebut)) {
        return showError("La date de clôture doit être ultérieure à la date de lancement.");
    }

    document.getElementById('formProjet').submit();
}
</script>
