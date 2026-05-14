<div class="projet-detail-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="index.php?controller=post&action=index" style="color: #3498db; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
            <span>←</span> Retour au forum
        </a>
    </div>

    <div class="projet-hero" style="text-align: center; margin-bottom: 30px; padding: 40px 20px; background: linear-gradient(135deg, #1a1a2e 0%, #0B1C48 100%); border-radius: 16px; color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <h1 style="margin: 0 0 10px 0; font-size: 2.5em; font-weight: 800; background: linear-gradient(90deg, #3498db, #2ecc71); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: flex; align-items: center; justify-content: center; gap: 10px;"><i data-lucide="message-square" style="color: #3498db; width: 36px; height: 36px;"></i> Smart Discussion Creator</h1>
        <p style="margin: 0; font-size: 1.1em; color: #bdc3c7;">Lancez un sujet pertinent. Notre moteur l'analysera et le géolocalisera automatiquement sur la carte mondiale !</p>
    </div>

    <?php if(isset($error)) { echo "<div style='background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>$error</div>"; } ?>
    <div id="error-msg-post" style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; display:none; margin-bottom:20px; font-weight:bold;"></div>

    <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <form id="formPost" onsubmit="return submitForm(event)">
            
            <h3 style="margin-top: 0; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 25px; color: #2c3e50;">Détails de la discussion</h3>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Titre de votre sujet *</label>
                <input type="text" id="titre_post" name="titre" placeholder="Ex: Recherche de co-fondateur tech à Paris..." style="width: 100%; padding: 14px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 16px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#3498db'" onblur="this.style.borderColor='#dce1e6'">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Sujet Principal</label>
                    <select id="topic_post" name="topic" style="width: 100%; padding: 14px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; background-color: #f8f9fa; cursor: pointer;">
                        <?php foreach ($topics as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Visibilité</label>
                    <select id="statut_post" name="statut" style="width: 100%; padding: 14px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; background-color: #f8f9fa; cursor: pointer;">
                        <option value="actif">Publié (Public)</option>
                        <option value="brouillon">Brouillon (Privé)</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
            </div>

            <?php if (!empty($userProjects)): ?>
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Lier à un de vos projets (Optionnel)</label>
                <div style="padding: 15px; background: #f0f8ff; border-radius: 8px; border: 1px dashed #bcdcff;">
                    <select id="projet_id" name="projet_id" style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 6px; font-size: 15px; background-color: white;">
                        <option value="">-- Ne lier à aucun projet --</option>
                        <?php foreach ($userProjects as $p): ?>
                            <option value="<?php echo $p['id']; ?>">🚀 <?php echo htmlspecialchars($p['nomprojet']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="display:block; margin-top: 8px; color: #7f8c8d;">Astuce : Lier un projet ajoutera un badge direct vers votre startup sur ce post.</small>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Contenu de la discussion *</label>
                <div class="dictation-wrapper">
                    <textarea id="contenu_post" name="contenu" rows="8" placeholder="Détaillez votre pensée ici. Vous pouvez aussi utiliser le microphone pour dicter votre texte !" style="width: 100%; padding: 15px; padding-bottom: 40px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; font-family: inherit; resize: vertical; transition: border-color 0.3s;" onfocus="this.style.borderColor='#3498db'" onblur="this.style.borderColor='#dce1e6'"></textarea>
                    
                    <div class="dictation-controls">
                        <select class="dictation-lang" title="Choisir la langue de dictée">
                            <option value="en-US">EN</option>
                            <option value="fr-FR" selected>FR</option>
                            <option value="ar-SA">AR</option>
                        </select>
                        <button type="button" class="dictation-btn" onclick="toggleDictation('contenu_post', this)" title="Cliquer pour parler">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                    </div>
                    <div class="dictation-interim"></div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Pièces jointes (Max 5MB)</label>
                <div style="padding: 20px; border: 2px dashed #dce1e6; border-radius: 8px; text-align: center; background: #fafafa; cursor: pointer;" onclick="document.getElementById('attachments').click()">
                    <i data-lucide="paperclip" style="width: 24px; height: 24px; color: #7f8c8d; margin-bottom: 10px;"></i>
                    <p style="margin: 0; color: #7f8c8d; font-size: 14px;">Cliquez pour ajouter des images ou documents PDF</p>
                    <input type="file" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" style="display: none;" onchange="this.parentElement.querySelector('p').innerText = this.files.length + ' fichier(s) sélectionné(s)'">
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button type="submit" style="flex: 2; padding: 16px; background: linear-gradient(135deg, #3498db, #2980b9); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 18px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Publier la Discussion</button>
                <a href="index.php?controller=post&action=index" style="flex: 1; padding: 16px; background: #f8f9fa; color: #2c3e50; border: 1px solid #dce1e6; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
async function submitForm(e) {
    e.preventDefault();
    let errorMsg = document.getElementById('error-msg-post');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let titre = document.getElementById('titre_post').value.trim();
    let contenu = document.getElementById('contenu_post').value.trim();
    let statut = document.getElementById('statut_post').value.trim();
    let topic = document.getElementById('topic_post').value.trim();
    const projetInput = document.getElementById('projet_id');
    let projet_id = projetInput ? (projetInput.value || null) : null;

    if (titre.length < 3) {
        erreurs.push("Le titre doit contenir au moins 3 caractères.");
        document.getElementById('titre_post').style.borderColor = '#e74c3c';
    } else {
        document.getElementById('titre_post').style.borderColor = '#dce1e6';
    }
    
    if (contenu.length < 5) {
        erreurs.push("Le contenu doit contenir au moins 5 caractères.");
        document.getElementById('contenu_post').style.borderColor = '#e74c3c';
    } else {
        document.getElementById('contenu_post').style.borderColor = '#dce1e6';
    }
    
    if (statut === '') {
        erreurs.push("Veuillez sélectionner un statut.");
    }

    if (erreurs.length > 0) {
        errorMsg.innerHTML = '<strong>Veuillez corriger les erreurs suivantes :</strong><br>' + erreurs.join('<br>');
        errorMsg.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
    }

    // Submit via API
    try {
        const formData = new FormData(document.getElementById('formPost'));
        const res = await fetch('public/api.php?controller=post&action=create', { method: 'POST', credentials: 'include', body: formData });
        const json = await res.json();
        if (json.success) {
            window.location.href = 'index.php?controller=post&action=show&id=' + json.id;
        } else {
            errorMsg.innerHTML = json.message || 'Erreur lors de la création';
            errorMsg.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (err) {
        console.error(err);
        errorMsg.innerHTML = 'Erreur réseau: ' + err.message;
        errorMsg.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    return false;
}
</script>
