// projet.js
function toggleProjetReaction(projetId, reactionType = 'LIKE') {
    const isLike = reactionType === 'LIKE';
    const btnLike = document.getElementById((isLike ? 'btnLike_' : 'btnDislike_') + projetId);
    const countSpan = document.getElementById((isLike ? 'like-count_' : 'dislike-count_') + projetId);
    if (!btnLike || btnLike.disabled) return;
    
    // Add small animation class
    btnLike.classList.add('pop-animation');
    setTimeout(() => btnLike.classList.remove('pop-animation'), 300);

    btnLike.disabled = true;
    btnLike.setAttribute('aria-busy', 'true');

    fetch('index.php?controller=projet_reaction&action=toggleProjet', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id_projet: projetId, reaction_type: reactionType })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('like-count_' + projetId).innerText = data.likes_count;
            document.getElementById('dislike-count_' + projetId).innerText = data.dislikes_count;

            const likeButton = document.getElementById('btnLike_' + projetId);
            const dislikeButton = document.getElementById('btnDislike_' + projetId);
            if (likeButton && dislikeButton) {
                likeButton.classList.toggle('liked', data.current_type === 'LIKE');
                dislikeButton.classList.toggle('disliked', data.current_type === 'DISLIKE');
            }
        } else {
            console.error(data.message || 'Erreur');
        }
        btnLike.disabled = false;
        btnLike.removeAttribute('aria-busy');
    })
    .catch(error => {
        console.error('Error:', error);
        btnLike.disabled = false;
        btnLike.removeAttribute('aria-busy');
    });
}

function setReply(commentId, authorName) {
    document.getElementById('parent_id').value = commentId;
    document.getElementById('reply-to-name').innerText = authorName;
    document.getElementById('replying-to').style.display = 'block';
    document.getElementById('contenu').focus();
}

function cancelReply() {
    document.getElementById('parent_id').value = '';
    document.getElementById('replying-to').style.display = 'none';
}
