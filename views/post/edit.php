<div class="projet-detail-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div id="backLink" style="margin-bottom: 20px;"></div>

    <div class="projet-hero" style="text-align: center; margin-bottom: 30px; padding: 40px 20px; background: linear-gradient(135deg, #1a1a2e 0%, #0B1C48 100%); border-radius: 16px; color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <h1 style="margin: 0 0 10px 0; font-size: 2.5em; font-weight: 800; background: linear-gradient(90deg, #f1c40f, #e67e22); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: flex; align-items: center; justify-content: center; gap: 10px;"><i data-lucide="edit-3" style="color: #f1c40f; width: 36px; height: 36px;"></i> Modifier la Discussion</h1>
        <p style="margin: 0; font-size: 1.1em; color: #bdc3c7;">Mettez à jour votre sujet. Le moteur ajustera automatiquement les mots-clés et la géolocalisation !</p>
    </div>

    <?php if(isset($error)) { echo "<div style='background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>$error</div>"; } ?>
    <div id="error-msg-post-edit" style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; display:none; margin-bottom:20px; font-weight:bold;"></div>

    <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <form id="formPostEdit" onsubmit="return submitFormEdit(event)">
            
            <h3 style="margin-top: 0; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 25px; color: #2c3e50;">Détails de la discussion</h3>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Titre de votre sujet *</label>
                <input type="text" id="titre_post_edit" name="titre" style="width: 100%; padding: 14px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 16px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#3498db'" onblur="this.style.borderColor='#dce1e6'">
                <input type="hidden" id="post_id_edit" name="id" value="">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Sujet Principal</label>
                    <select id="topic_post_edit" name="topic" style="width: 100%; padding: 14px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; background-color: #f8f9fa; cursor: pointer;">
                        <option value="Général">Général</option>
                        <option value="Opportunités">Opportunités</option>
                        <option value="Partenariats">Partenariats</option>
                        <option value="Support">Support</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Visibilité</label>
                    <select id="statut_post_edit" name="statut" style="width: 100%; padding: 14px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; background-color: #f8f9fa; cursor: pointer;">
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
                    <select id="projet_id_edit" name="projet_id" style="width: 100%; padding: 12px; border: 1px solid #dce1e6; border-radius: 6px; font-size: 15px; background-color: white;">
                        <option value="">-- Ne lier à aucun projet --</option>
                        <?php foreach ($userProjects as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($this->post->projet_id == $p['id']) ? 'selected' : ''; ?>>🚀 <?php echo htmlspecialchars($p['nomprojet']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #34495e;">Contenu de la discussion *</label>
                <textarea id="contenu_post_edit" name="contenu" rows="8" style="width: 100%; padding: 15px; border: 1px solid #dce1e6; border-radius: 8px; font-size: 15px; font-family: inherit; resize: vertical; transition: border-color 0.3s;" onfocus="this.style.borderColor='#3498db'" onblur="this.style.borderColor='#dce1e6'"></textarea>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button type="submit" style="flex: 2; padding: 16px; background: linear-gradient(135deg, #f1c40f, #e67e22); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 18px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(230, 126, 34, 0.4);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Mettre à jour</button>
                <a id="cancelLink" href="#" style="flex: 1; padding: 16px; background: #f8f9fa; color: #2c3e50; border: 1px solid #dce1e6; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
const POST_ID = new URLSearchParams(window.location.search).get('id') || 0;

async function loadPost() {
    if (!POST_ID) return;
    try {
        const res = await fetch('public/api.php?controller=post&action=show&id=' + encodeURIComponent(POST_ID) + '&format=json', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        const json = await res.json();
        const p = json.success ? json.data : json.post || {};
        
        document.getElementById('post_id_edit').value = p.id_post || POST_ID;
        document.getElementById('titre_post_edit').value = p.titre || '';
        document.getElementById('topic_post_edit').value = p.topic || 'Général';
        document.getElementById('statut_post_edit').value = p.statut || 'actif';
        document.getElementById('contenu_post_edit').value = p.contenu || '';
        document.getElementById('cancelLink').href = 'index.php?controller=post&action=show&id=' + POST_ID;
        document.getElementById('backLink').innerHTML = '<a href="index.php?controller=post&action=show&id=' + POST_ID + '" style="color: #3498db; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;"><span>←</span> Retour à la discussion</a>';
    } catch (err) { console.error('loadPost error', err); }
}

async function submitFormEdit(e) {
    e.preventDefault();
    let errorMsg = document.getElementById('error-msg-post-edit');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let titre = document.getElementById('titre_post_edit').value.trim();
    let contenu = document.getElementById('contenu_post_edit').value.trim();
    let statut = document.getElementById('statut_post_edit').value.trim();

    if (titre.length < 3) {
        erreurs.push("Le titre doit contenir au moins 3 caractères.");
        document.getElementById('titre_post_edit').style.borderColor = '#e74c3c';
    } else {
        document.getElementById('titre_post_edit').style.borderColor = '#dce1e6';
    }
    
    if (contenu.length < 5) {
        erreurs.push("Le contenu doit contenir au moins 5 caractères.");
        document.getElementById('contenu_post_edit').style.borderColor = '#e74c3c';
    } else {
        document.getElementById('contenu_post_edit').style.borderColor = '#dce1e6';
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
    const postId = POST_ID || document.getElementById('post_id_edit').value;
    try {
        const payload = {
            titre: titre,
            topic: document.getElementById('topic_post_edit').value,
            statut: statut,
            contenu: contenu,
            projet_id: document.getElementById('projet_id_edit')?.value || null
        };
        const res = await fetch('public/api.php?controller=post&action=edit&id=' + encodeURIComponent(postId), { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            credentials: 'include', 
            body: JSON.stringify(payload) 
        });
        const json = await res.json();
        if (json.success) {
            window.location.href = 'index.php?controller=post&action=show&id=' + postId;
        } else {
            errorMsg.innerHTML = json.message || 'Erreur lors de la mise à jour';
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

document.addEventListener('DOMContentLoaded', () => loadPost());
</script>

