// forum.js
if (typeof window.toggleReaction === 'undefined') {
    window.toggleReaction = function(postId, reactionType = 'LIKE') {
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
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_post: postId, reaction_type: reactionType })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeEl = document.getElementById('like-count_' + postId);
                const dislikeEl = document.getElementById('dislike-count_' + postId);
                if (likeEl) likeEl.innerText = data.likes_count;
                if (dislikeEl) dislikeEl.innerText = data.dislikes_count;

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
    };
}

if (typeof window.toggleCommentReaction === 'undefined') {
    window.toggleCommentReaction = function(commentId, reactionType = 'LIKE') {
        const isLike = reactionType === 'LIKE';
        const btnLike = document.getElementById((isLike ? 'btnCommentLike_' : 'btnCommentDislike_') + commentId);
        if (!btnLike || btnLike.disabled) return;

        btnLike.classList.add('pop-animation');
        setTimeout(() => btnLike.classList.remove('pop-animation'), 300);

        btnLike.disabled = true;
        btnLike.setAttribute('aria-busy', 'true');

        fetch('index.php?controller=reaction&action=toggleComment', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_commentaire: commentId, reaction_type: reactionType })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeEl = document.getElementById('comment-like-count_' + commentId);
                const dislikeEl = document.getElementById('comment-dislike-count_' + commentId);
                if (likeEl) likeEl.innerText = data.likes_count;
                if (dislikeEl) dislikeEl.innerText = data.dislikes_count;

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
    };
}

if (typeof window.setReply === 'undefined') {
    window.setReply = function(commentId, authorName) {
        const p = document.getElementById('parent_id'); if (p) p.value = commentId;
        const r = document.getElementById('replying-to'); if (r) r.style.display = 'block';
        const n = document.getElementById('reply-to-name'); if (n) n.innerText = authorName;
        const ta = document.getElementById('comment_contenu'); if (ta) ta.focus();
    };
}

if (typeof window.cancelReply === 'undefined') {
    window.cancelReply = function() {
        const p = document.getElementById('parent_id'); if (p) p.value = '';
        const r = document.getElementById('replying-to'); if (r) r.style.display = 'none';
    };
}

if (typeof window.submitComment === 'undefined') {
    window.submitComment = function(e) {
        e.preventDefault();
        const btnSubmit = document.getElementById('btnSubmitComment');
        const textarea = document.getElementById('comment_contenu');
        if (btnSubmit) { btnSubmit.disabled = true; btnSubmit.innerText = 'Publication...'; }
        const postIdEl = document.getElementById('post_id');
        const postId = postIdEl ? postIdEl.value : null;
        const parentEl = document.getElementById('parent_id');
        const parentId = parentEl ? parentEl.value : null;
        const contenu = textarea ? textarea.value.trim() : '';

        if (!textarea || contenu.length < 2) {
            alert('Votre commentaire doit contenir au moins 2 caractères.');
            if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerText = 'Publier'; }
            if (textarea) textarea.focus();
            return;
        }

        const form = document.getElementById('commentForm');
        const formData = new FormData(form);

        fetch('index.php?controller=commentaire&action=addAsync', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Erreur');
                if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerText = 'Publier'; }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerText = 'Publier'; }
        });
    };
}

if (typeof window.translateText === 'undefined') {
    window.translateText = function(contentId, targetLang) {
        const contentEl = document.getElementById(contentId);
        if (!contentEl) return;
        if (!contentEl.hasAttribute('data-original')) {
            contentEl.setAttribute('data-original', contentEl.innerHTML);
        }
        if (targetLang === 'original') {
            contentEl.innerHTML = contentEl.getAttribute('data-original');
            return;
        }
        const originalText = contentEl.getAttribute('data-original');
        fetch('index.php?controller=forumApi&action=translate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ text: originalText, targetLang: targetLang })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) contentEl.innerHTML = data.translatedText;
        })
        .catch(err => console.error('Translation error:', err));
    };
}
