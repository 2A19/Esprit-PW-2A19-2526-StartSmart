// forum.js
function toggleReaction(postId, reactionType = 'LIKE') {
    const isLike = reactionType === 'LIKE';
    const btnLike = document.getElementById((isLike ? 'btnLike_' : 'btnDislike_') + postId);
    const countSpan = document.getElementById((isLike ? 'like-count_' : 'dislike-count_') + postId);
    if (!btnLike || btnLike.disabled) return;
    
    // Add small animation class
    btnLike.classList.add('pop-animation');
    setTimeout(() => btnLike.classList.remove('pop-animation'), 300);

    btnLike.disabled = true;
    btnLike.setAttribute('aria-busy', 'true');

    fetch('index.php?controller=reaction&action=togglePost', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id_post: postId, reaction_type: reactionType })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('like-count_' + postId).innerText = data.likes_count;
            document.getElementById('dislike-count_' + postId).innerText = data.dislikes_count;

            const likeButton = document.getElementById('btnLike_' + postId);
            const dislikeButton = document.getElementById('btnDislike_' + postId);
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

function toggleCommentReaction(commentId, reactionType = 'LIKE') {
    const isLike = reactionType === 'LIKE';
    const btnLike = document.getElementById((isLike ? 'btnCommentLike_' : 'btnCommentDislike_') + commentId);
    if (!btnLike || btnLike.disabled) return;

    btnLike.classList.add('pop-animation');
    setTimeout(() => btnLike.classList.remove('pop-animation'), 300);

    btnLike.disabled = true;
    btnLike.setAttribute('aria-busy', 'true');

    fetch('index.php?controller=reaction&action=toggleComment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id_commentaire: commentId, reaction_type: reactionType })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('comment-like-count_' + commentId).innerText = data.likes_count;
            document.getElementById('comment-dislike-count_' + commentId).innerText = data.dislikes_count;

            const likeButton = document.getElementById('btnCommentLike_' + commentId);
            const dislikeButton = document.getElementById('btnCommentDislike_' + commentId);
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
    document.getElementById('comment_contenu').focus();
}

function cancelReply() {
    document.getElementById('parent_id').value = '';
    document.getElementById('replying-to').style.display = 'none';
}

function submitComment(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmitComment');
    const textarea = document.getElementById('comment_contenu');
    btnSubmit.disabled = true;
    btnSubmit.innerText = 'Publication...';
    
    const postId = document.getElementById('post_id').value;
    const parentId = document.getElementById('parent_id').value;
    const contenu = textarea.value.trim();

    const form = document.getElementById('commentForm');
    const formData = new FormData(form);

    if (contenu.length < 2) {
        alert('Votre commentaire doit contenir au moins 2 caractères.');
        btnSubmit.disabled = false;
        btnSubmit.innerText = 'Publier';
        textarea.focus();
        return;
    }
    
    fetch('index.php?controller=commentaire&action=addAsync', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erreur');
            btnSubmit.disabled = false;
            btnSubmit.innerText = 'Publier';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btnSubmit.disabled = false;
        btnSubmit.innerText = 'Publier';
    });
}

function translateText(contentId, targetLang) {
    const contentEl = document.getElementById(contentId);
    if (!contentEl) return;
    
    // Save original if not saved yet
    if (!contentEl.hasAttribute('data-original')) {
        contentEl.setAttribute('data-original', contentEl.innerHTML);
    }
    
    if (targetLang === 'original') {
        contentEl.innerHTML = contentEl.getAttribute('data-original');
        return;
    }

    const originalText = contentEl.getAttribute('data-original');
    
    // Call the translate endpoint
    fetch('index.php?controller=forumApi&action=translate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ text: originalText, targetLang: targetLang })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            contentEl.innerHTML = data.translatedText;
        }
    })
    .catch(err => console.error('Translation error:', err));
}
