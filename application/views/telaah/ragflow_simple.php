<div class="chat-container">
    <div class="chat-header">
        <div class="chat-title">
            <h4>Chat <span class="badge badge-primary"><?= count($messages) ?? []?></span></h4>
        </div>
        <div class="chat-actions">
            <a href="<?= base_url('telaah/new_session'); ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus"></i> Chat Baru
            </a>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        <?php if (empty($messages)): ?>
            <div class="empty-chat">
                <div class="text-center">
                    <img src="<?= base_url('assets/img/chat-icon.png'); ?>" alt="Chat" class="empty-chat-icon">
                    <p class="empty-chat-text">Selamat datang di RAGFlow Chat.</p>
                    <p class="empty-chat-subtext">Ajukan pertanyaan untuk memulai percakapan.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <?php if ($message['role'] === 'user'): ?>
                    <div class="message user-message">
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($message['content'])); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="message assistant-message">
                        <div class="message-avatar">
                            <img src="<?= base_url('assets/img/sapa-icon.png'); ?>" alt="SAPA">
                        </div>
                        <div class="message-body">
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($message['content'])); ?>
                            </div>
                            
                            <?php if (!empty($message['reference'])): ?>
                                <?php 
                                    $references = json_decode($message['reference'], true);
                                    if (!empty($references) && isset($references['chunks']) && is_array($references['chunks'])):
                                ?>
                                    <div class="message-sources">
                                        <div class="source-toggle" data-toggle="collapse" data-target="#sources-<?= $message['id']; ?>">
                                            <i class="fas fa-info-circle"></i> Lihat Sumber
                                        </div>
                                        <div class="collapse" id="sources-<?= $message['id']; ?>">
                                            <div class="sources-container">
                                                <?php foreach ($references['chunks'] as $index => $chunk): ?>
                                                    <div class="source-item">
                                                        <div class="source-header">
                                                            <strong><?= isset($chunk['document_name']) ? htmlspecialchars($chunk['document_name']) : 'Dokumen #' . ($index + 1); ?></strong>
                                                        </div>
                                                        <div class="source-content">
                                                            <?= nl2br(htmlspecialchars($chunk['content'])); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div class="message-actions">
                                <button class="btn btn-sm btn-light btn-thumbs-up" title="Suka">
                                    <i class="far fa-thumbs-up"></i>
                                </button>
                                <button class="btn btn-sm btn-light btn-thumbs-down" title="Tidak Suka">
                                    <i class="far fa-thumbs-down"></i>
                                </button>
                                <button class="btn btn-sm btn-light btn-copy" title="Salin" data-content="<?= htmlspecialchars($message['content']); ?>">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="chat-input">
        <form id="messageForm">
            <div class="input-group">
                <textarea class="form-control" id="messageInput" placeholder="Ketik pesan..." rows="1"></textarea>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit" id="sendButton">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </form>
        <div class="chat-input-footer">
            <small class="text-muted">Powered by RAGFlow</small>
        </div>
    </div>
</div>

<style>
.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 120px);
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
}

.chat-title h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    margin-bottom: 15px;
    max-width: 80%;
}

.user-message {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.assistant-message {
    align-self: flex-start;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 12px;
    flex-shrink: 0;
}

.user-message .message-avatar {
    margin-right: 0;
    margin-left: 12px;
}

.message-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.message-body {
    display: flex;
    flex-direction: column;
    max-width: calc(100% - 48px);
}

.message-content {
    padding: 12px 16px;
    border-radius: 15px;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
}

.user-message .message-content {
    background-color: #0084ff;
    color: white;
    border-bottom-right-radius: 5px;
}

.assistant-message .message-content {
    background-color: #f1f0f0;
    color: #333;
    border-bottom-left-radius: 5px;
}

.message-sources {
    margin-top: 8px;
}

.source-toggle {
    color: #0084ff;
    font-size: 12px;
    cursor: pointer;
    display: inline-block;
    padding: 3px 0;
}

.source-toggle:hover {
    text-decoration: underline;
}

.sources-container {
    margin-top: 8px;
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 10px;
    font-size: 12px;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
}

.source-item {
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.source-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.source-header {
    margin-bottom: 5px;
    font-size: 12px;
}

.source-content {
    font-size: 12px;
    color: #555;
    padding-left: 5px;
    border-left: 2px solid #ddd;
}

.message-actions {
    display: flex;
    gap: 5px;
    margin-top: 5px;
    justify-content: flex-end;
}

.message-actions button {
    padding: 3px 8px;
    font-size: 12px;
    border-radius: 4px;
    background-color: transparent;
    border: none;
    color: #777;
}

.message-actions button:hover {
    background-color: #f1f1f1;
    color: #333;
}

.chat-input {
    padding: 15px;
    background-color: #fff;
    border-top: 1px solid #e9ecef;
}

.chat-input .form-control {
    border-radius: 20px;
    padding: 10px 15px;
    resize: none;
    max-height: 100px;
    overflow-y: auto;
}

.chat-input .btn {
    border-radius: 50%;
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 10px;
}

.chat-input-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}

.empty-chat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
}

.empty-chat-icon {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-chat-text {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 5px;
}

.empty-chat-subtext {
    font-size: 14px;
}

@media (max-width: 768px) {
    .message {
        max-width: 90%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Scroll ke bawah saat membuka chat
    function scrollToBottom() {
        const chatContainer = document.getElementById('chatMessages');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    scrollToBottom();

    // Form submit handler
    $('#messageForm').on('submit', function(e) {
        e.preventDefault();
        
        const messageText = $('#messageInput').val().trim();
        if (!messageText) return;
        
        // Disable input selama proses
        $('#messageInput').prop('disabled', true);
        $('#sendButton').prop('disabled', true);
        
        // Tampilkan pesan user
        const userMessageHtml = `
            <div class="message user-message">
                <div class="message-content">
                    ${messageText.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
        
        // Hapus pesan kosong jika ada
        $('.empty-chat').remove();
        
        // Tambahkan pesan ke container
        $('#chatMessages').append(userMessageHtml);
        scrollToBottom();
        
        // Kirim pesan ke server
        $.ajax({
            url: '<?= base_url('telaah/send_message'); ?>',
            type: 'POST',
            data: {
                message: messageText
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Format referensi jika ada
                    let referencesHtml = '';
                    
                    if (response.message.references && response.message.references.length > 0) {
                        const referenceId = 'sources-' + Date.now();
                        
                        referencesHtml = `
                            <div class="message-sources">
                                <div class="source-toggle" data-toggle="collapse" data-target="#${referenceId}">
                                    <i class="fas fa-info-circle"></i> Lihat Sumber
                                </div>
                                <div class="collapse" id="${referenceId}">
                                    <div class="sources-container">
                        `;
                        
                        response.message.references.forEach((ref, index) => {
                            referencesHtml += `
                                <div class="source-item">
                                    <div class="source-header">
                                        <strong>${ref.document_name || 'Dokumen #' + (index + 1)}</strong>
                                    </div>
                                    <div class="source-content">
                                        ${ref.content.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                            `;
                        });
                        
                        referencesHtml += `
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Tambahkan pesan AI ke UI
                    const assistantMessageHtml = `
                        <div class="message assistant-message">
                            <div class="message-avatar">
                                <img src="${baseUrl}assets/img/sapa-icon.png" alt="SAPA">
                            </div>
                            <div class="message-body">
                                <div class="message-content">
                                    ${response.message.answer.replace(/\n/g, '<br>')}
                                </div>
                                
                                ${referencesHtml}
                                
                                <div class="message-actions">
                                    <button class="btn btn-sm btn-light btn-thumbs-up" title="Suka">
                                        <i class="far fa-thumbs-up"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light btn-thumbs-down" title="Tidak Suka">
                                        <i class="far fa-thumbs-down"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light btn-copy" title="Salin" data-content="${response.message.answer.replace(/"/g, '&quot;')}">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#chatMessages').append(assistantMessageHtml);
                    scrollToBottom();
                } else {
                    // Tampilkan error
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
            },
            complete: function() {
                // Reset form input
                $('#messageInput').val('').prop('disabled', false).focus();
                $('#sendButton').prop('disabled', false);
            }
        });
    });
    
    // Auto-resize textarea
    $('#messageInput').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight < 100 ? this.scrollHeight : 100) + 'px';
    });
    
    // Keyboard shortcut (Enter to send, Shift+Enter for new line)
    $('#messageInput').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#messageForm').submit();
        }
    });
    
    // Tombol suka/tidak suka (hanya UI, tidak ada aksi backend)
    $(document).on('click', '.btn-thumbs-up, .btn-thumbs-down', function() {
        // Toggle icon filled/outline
        const icon = $(this).find('i');
        if (icon.hasClass('far')) {
            icon.removeClass('far').addClass('fas');
            // Jika ini thumbs up, hilangkan thumbs down yang aktif (jika ada)
            if ($(this).hasClass('btn-thumbs-up')) {
                $(this).siblings('.btn-thumbs-down').find('i').removeClass('fas').addClass('far');
            } else {
                $(this).siblings('.btn-thumbs-up').find('i').removeClass('fas').addClass('far');
            }
        } else {
            icon.removeClass('fas').addClass('far');
        }
    });
    
    // Tombol salin
    $(document).on('click', '.btn-copy', function() {
        const text = $(this).data('content');
        navigator.clipboard.writeText(text).then(
            function() {
                // Notifikasi berhasil
                const btn = $(this);
                const originalIcon = btn.html();
                btn.html('<i class="fas fa-check"></i>');
                setTimeout(function() {
                    btn.html(originalIcon);
                }, 1500);
            }.bind(this),
            function(err) {
                console.error('Could not copy text: ', err);
                alert('Gagal menyalin teks');
            }
        );
    });
});

// Tambahkan baseUrl untuk referensi gambar
const baseUrl = '<?= base_url(); ?>';
</script>