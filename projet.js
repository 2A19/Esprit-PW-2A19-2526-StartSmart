// projet.js
if (typeof window.toggleProjetReaction === 'undefined') {
    window.toggleProjetReaction = function(projetId, reactionType = 'LIKE') {
        const isLike = reactionType === 'LIKE';
        const buttonId = (isLike ? 'btnLike_' : 'btnDislike_') + projetId;
        const fallbackButtonId = isLike ? 'btnLike' : 'btnDislike';
        const countId = (isLike ? 'like-count_' : 'dislike-count_') + projetId;
        const button = document.getElementById(buttonId) || document.getElementById(fallbackButtonId);
        if (!button || button.disabled) return;
        
        // Add small animation class
        button.classList.add('pop-animation');
        setTimeout(() => button.classList.remove('pop-animation'), 300);

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        fetch('index.php?controller=projet_reaction&action=toggleProjet', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_projet: projetId, reaction_type: reactionType })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeCount = document.getElementById('like-count_' + projetId) || document.getElementById('like-count');
                const dislikeCount = document.getElementById('dislike-count_' + projetId) || document.getElementById('dislike-count');
                if (likeCount) likeCount.innerText = data.likes_count;
                if (dislikeCount) dislikeCount.innerText = data.dislikes_count;

                const likeButton = document.getElementById('btnLike_' + projetId) || document.getElementById('btnLike');
                const dislikeButton = document.getElementById('btnDislike_' + projetId) || document.getElementById('btnDislike');
                if (likeButton && dislikeButton) {
                    likeButton.classList.toggle('liked', data.current_type === 'LIKE');
                    dislikeButton.classList.toggle('disliked', data.current_type === 'DISLIKE');
                }
            } else {
                console.error(data.message || 'Erreur');
            }
            button.disabled = false;
            button.removeAttribute('aria-busy');
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.removeAttribute('aria-busy');
        });
    };
}

if (typeof window.setReply === 'undefined') {
    window.setReply = function(commentId, authorName) {
        const p = document.getElementById('parent_id'); if (p) p.value = commentId;
        const r = document.getElementById('replying-to'); if (r) r.style.display = 'block';
        const n = document.getElementById('reply-to-name'); if (n) n.innerText = authorName;
        const ta = document.getElementById('contenu'); if (ta) ta.focus();
    };
}

if (typeof window.cancelReply === 'undefined') {
    window.cancelReply = function() {
        const p = document.getElementById('parent_id'); if (p) p.value = '';
        const r = document.getElementById('replying-to'); if (r) r.style.display = 'none';
    };
}
