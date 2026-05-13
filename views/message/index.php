<?php
// Message Index View
// Expects: $conversations array
?>

<div class="message-container" style="display: flex; height: 75vh; background: white; border-radius: 16px; box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 30px;">
    
    <!-- Left Sidebar (Conversations) -->
    <div class="conversations-list" style="width: 350px; border-right: 1px solid var(--gray-200); display: flex; flex-direction: column; background: var(--gray-50);">
        <div style="padding: 20px; border-bottom: 1px solid var(--gray-200); background: white;">
            <h2 style="margin: 0; font-size: 20px; color: var(--navy); font-weight: 800;">Messagerie</h2>
        </div>
        
        <div style="flex: 1; overflow-y: auto;">
            <?php if (empty($conversations)): ?>
                <div style="padding: 30px 20px; text-align: center; color: var(--gray-500);">
                    <i class="fa-regular fa-comments" style="font-size: 32px; margin-bottom: 10px;"></i>
                    <p>Aucune conversation en cours.</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item" data-other-id="<?php echo $conv['other_user_id']; ?>" data-projet-id="<?php echo $conv['projet_id']; ?>" onclick="loadConversation(<?php echo $conv['other_user_id']; ?>, <?php echo $conv['projet_id']; ?>, '<?php echo htmlspecialchars(addslashes($conv['other_user_name'])); ?>', '<?php echo htmlspecialchars(addslashes($conv['nomprojet'])); ?>')" style="padding: 15px 20px; border-bottom: 1px solid var(--gray-200); cursor: pointer; transition: background 0.2s; display: flex; align-items: center; gap: 15px; background: white;">
                        <div class="avatar" style="width: 45px; height: 45px; background: var(--grad-primary); border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                            <?php echo strtoupper(substr($conv['other_user_name'], 0, 1)); ?>
                        </div>
                        <div style="flex: 1; overflow: hidden;">
                            <h4 style="margin: 0 0 4px 0; color: var(--navy); font-size: 15px; font-weight: 700; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                                <?php echo htmlspecialchars($conv['other_user_name']); ?>
                            </h4>
                            <span style="font-size: 12px; color: var(--gray-500); display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                                Projet: <?php echo htmlspecialchars($conv['nomprojet']); ?>
                            </span>
                        </div>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <div class="unread-badge" style="background: var(--blue); color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                <?php echo $conv['unread_count']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Pane (Chat Area) -->
    <div class="chat-area" style="flex: 1; display: flex; flex-direction: column; background: #fff;">
        <!-- Chat Header -->
        <div id="chatHeader" style="padding: 20px; border-bottom: 1px solid var(--gray-200); background: white; display: none; align-items: center; gap: 15px;">
            <div id="chatAvatar" style="width: 45px; height: 45px; background: var(--grad-primary); border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800;"></div>
            <div>
                <h3 id="chatUserName" style="margin: 0 0 4px 0; color: var(--navy); font-size: 18px; font-weight: 800;"></h3>
                <span id="chatProjectName" style="font-size: 13px; color: var(--gray-500); font-weight: 600;"></span>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatMessages" style="flex: 1; padding: 20px; overflow-y: auto; background: var(--gray-50); display: flex; flex-direction: column; gap: 15px;">
            <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--gray-400); flex-direction: column;">
                <i class="fa-solid fa-paper-plane" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>Sélectionnez une conversation pour commencer à discuter</p>
            </div>
        </div>

        <!-- Chat Input -->
        <div id="chatInputArea" style="padding: 20px; border-top: 1px solid var(--gray-200); background: white; display: none;">
            <form id="sendMessageForm" onsubmit="sendMessage(event)" style="display: flex; gap: 10px;">
                <input type="hidden" id="receiverId" name="receiver_id">
                <input type="hidden" id="chatProjetId" name="projet_id">
                <input type="text" id="messageInput" placeholder="Écrivez votre message..." required style="flex: 1; padding: 12px 20px; border: 1px solid var(--gray-200); border-radius: 99px; font-size: 15px; outline: none; transition: border-color 0.2s;">
                <button type="submit" class="btn" style="background: var(--grad-primary); color: white; border: none; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: var(--shadow-blue);">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.conversation-item:hover {
    background: var(--gray-100) !important;
}
.conversation-item.active {
    background: rgba(100, 197, 235, 0.1) !important;
    border-left: 4px solid var(--blue);
}
#messageInput:focus {
    border-color: var(--blue) !important;
}

.msg-bubble {
    max-width: 70%;
    padding: 12px 18px;
    border-radius: 18px;
    font-size: 15px;
    line-height: 1.5;
    position: relative;
    word-break: break-word;
}
.msg-sent {
    align-self: flex-end;
    background: var(--grad-primary);
    color: white;
    border-bottom-right-radius: 4px;
}
.msg-received {
    align-self: flex-start;
    background: white;
    color: var(--navy);
    border: 1px solid var(--gray-200);
    border-bottom-left-radius: 4px;
}
.msg-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 4px;
    display: block;
    text-align: right;
}
</style>

<script>
let currentPoll = null;

function loadConversation(otherId, projetId, userName, projectName) {
    // Update UI headers
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('chatInputArea').style.display = 'block';
    document.getElementById('chatUserName').textContent = userName;
    document.getElementById('chatProjectName').textContent = "Projet: " + projectName;
    document.getElementById('chatAvatar').textContent = userName.charAt(0).toUpperCase();
    
    // Set hidden fields
    document.getElementById('receiverId').value = otherId;
    document.getElementById('chatProjetId').value = projetId;

    // Highlight active conversation
    document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
    const activeItem = document.querySelector(`.conversation-item[data-other-id="${otherId}"][data-projet-id="${projetId}"]`);
    if(activeItem) {
        activeItem.classList.add('active');
        // Hide unread badge
        const badge = activeItem.querySelector('.unread-badge');
        if(badge) badge.style.display = 'none';
    }

    // Load messages
    fetchMessages(otherId, projetId);

    // Start polling
    if(currentPoll) clearInterval(currentPoll);
    currentPoll = setInterval(() => fetchMessages(otherId, projetId, false), 3000); // poll every 3 seconds
}

function fetchMessages(otherId, projetId, scrollToBottom = true) {
    fetch(`index.php?controller=message&action=getHistory&other_user_id=${otherId}&projet_id=${projetId}`)
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            const chatBox = document.getElementById('chatMessages');
            let html = '';
            
            if(data.messages.length === 0) {
                html = `<div style="text-align:center; color: var(--gray-400); margin-top: 20px; font-size: 14px;">Commencez la discussion!</div>`;
            } else {
                data.messages.forEach(m => {
                    const isSent = (m.receiver_id == otherId);
                    const bubbleClass = isSent ? 'msg-sent' : 'msg-received';
                    const time = new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    html += `
                        <div class="msg-bubble ${bubbleClass}">
                            ${m.content}
                            <span class="msg-time">${time}</span>
                        </div>
                    `;
                });
            }
            
            // Only update if content changed to avoid scrolling jank
            if (chatBox.innerHTML !== html) {
                chatBox.innerHTML = html;
                if(scrollToBottom) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }
        }
    })
    .catch(err => console.error("Error fetching messages", err));
}

function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    if(!content) return;

    const receiverId = document.getElementById('receiverId').value;
    const projetId = document.getElementById('chatProjetId').value;

    // Optimistic UI update
    const chatBox = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    chatBox.innerHTML += `
        <div class="msg-bubble msg-sent" style="opacity: 0.7;">
            ${content}
            <span class="msg-time">${time}</span>
        </div>
    `;
    chatBox.scrollTop = chatBox.scrollHeight;
    input.value = '';

    fetch('index.php?controller=message&action=send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            receiver_id: receiverId,
            projet_id: projetId,
            content: content
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            fetchMessages(receiverId, projetId, true);
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error("Error sending", err));
}
</script>
