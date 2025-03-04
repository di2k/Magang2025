<div class="container-fluid mt-4 h-100">
    <div class="row mb-3">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('telaah?q=ch4tRAG'); ?>">RAGFlow</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($session_info['session_name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row chat-container">
        <!-- Area Chat -->
        <div class="col-md-12">
            <div class="card chat-card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($session_info['session_name']); ?></h5>
                        <div>
                            <a href="<?= base_url('telaah?q=ch4tRAG'); ?>" class="btn btn-sm btn-light">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body chat-body" id="chatMessages">
                    <!-- Pesan-pesan chat akan ditampilkan di sini -->
                    <?php if (empty($messages)) : ?>
                        <div class="text-center text-muted initial-message">
                            <p>Mulai percakapan dengan mengirim pesan.</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($messages as $message) : ?>
                            <?php if ($message['role'] === 'user') : ?>
                                <!-- Pesan Pengguna -->
                                <div class="message user-message">
                                    <div class="message-content">
                                        <?= nl2br(htmlspecialchars($message['content'])); ?>
                                    </div>
                                    <div class="message-time">
                                        <small><?= date('H:i', strtotime($message['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php else : ?>
                                <!-- Pesan Asisten -->
                                <div class="message assistant-message">
                                    <div class="message-content">
                                        <?= nl2br(htmlspecialchars($message['content'])); ?>
                                    </div>
                                    <div class="message-time">
                                        <small><?= date('H:i', strtotime($message['created_at'])); ?></small>
                                    </div>
                                    <?php if (!empty($message['reference'])) : ?>
                                        <?php 
                                            $references = json_decode($message['reference'], true);
                                            if (!empty($references) && isset($references['chunks']) && is_array($references['chunks'])) :
                                        ?>
                                            <div class="references-container">
                                                <button class="btn btn-sm btn-outline-secondary toggle-references" type="button" data-toggle="collapse" data-target="#references-<?= $message['id']; ?>">
                                                    <i class="fas fa-book"></i> Lihat Sumber (<?= count($references['chunks']); ?>)
                                                </button>
                                                <div class="collapse mt-2" id="references-<?= $message['id']; ?>">
                                                    <div class="references-content">
                                                        <?php foreach ($references['chunks'] as $index => $chunk) : ?>
                                                            <div class="reference-item">
                                                                <div class="reference-header">
                                                                    <strong><?= isset($chunk['document_name']) ? htmlspecialchars($chunk['document_name']) : 'Dokumen #' . ($index + 1); ?></strong>
                                                                    <span class="badge badge-info">Kemiripan: <?= number_format($chunk['similarity'] * 100, 1); ?>%</span>
                                                                </div>
                                                                <div class="reference-text">
                                                                    <?= nl2br(htmlspecialchars($chunk['content'])); ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <form id="messageForm" class="message-form">
                        <div class="input-group">
                            <textarea class="form-control" id="messageInput" placeholder="Ketik pesan..." rows="2" required></textarea>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" id="sendButton">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="typingIndicator" class="typing-indicator d-none">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-text">RAGFlow sedang mengetik...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
            
            // Disable input dan tampilkan indikator
            $('#messageInput').prop('disabled', true);
            $('#sendButton').prop('disabled', true);
            $('#typingIndicator').removeClass('d-none');
            
            // Tambahkan pesan user ke UI
            const userMessageHtml = `
                <div class="message user-message">
                    <div class="message-content">
                        ${messageText.replace(/\n/g, '<br>')}
                    </div>
                    <div class="message-time">
                        <small>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>
                    </div>
                </div>
            `;
            
            // Hapus pesan awal jika ada
            $('.initial-message').remove();
            
            // Tambahkan pesan ke container
            $('#chatMessages').append(userMessageHtml);
            scrollToBottom();
            
            // Kirim pesan ke server
            $.ajax({
                url: '<?= base_url('telaah/send_message'); ?>',
                type: 'POST',
                data: {
                    session_id: '<?= $session_info['session_id']; ?>',
                    chat_id: '<?= $session_info['chat_id']; ?>',
                    message: messageText
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Format referensi jika ada
                        let referencesHtml = '';
                        
                        if (response.message.references && response.message.references.length > 0) {
                            const referenceId = 'ref-' + Date.now();
                            
                            referencesHtml = `
                                <div class="references-container">
                                    <button class="btn btn-sm btn-outline-secondary toggle-references" type="button" data-toggle="collapse" data-target="#${referenceId}">
                                        <i class="fas fa-book"></i> Lihat Sumber (${response.message.references.length})
                                    </button>
                                    <div class="collapse mt-2" id="${referenceId}">
                                        <div class="references-content">
                            `;
                            
                            response.message.references.forEach((ref, index) => {
                                referencesHtml += `
                                    <div class="reference-item">
                                        <div class="reference-header">
                                            <strong>${ref.document_name || 'Dokumen #' + (index + 1)}</strong>
                                            <span class="badge badge-info">Kemiripan: ${(ref.similarity * 100).toFixed(1)}%</span>
                                        </div>
                                        <div class="reference-text">
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
                        
                        // Tambahkan pesan assistant ke UI
                        const assistantMessageHtml = `
                            <div class="message assistant-message">
                                <div class="message-content">
                                    ${response.message.answer.replace(/\n/g, '<br>')}
                                </div>
                                <div class="message-time">
                                    <small>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>
                                </div>
                                ${referencesHtml}
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
                    $('#typingIndicator').addClass('d-none');
                }
            });
        });
        
        // Auto-resize textarea
        $('#messageInput').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Keyboard shortcut (Enter to send, Shift+Enter for new line)
        $('#messageInput').on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $('#messageForm').submit();
            }
        });
    });
</script>

<style>
    .chat-container {
        height: calc(100vh - 150px);
    }
    
    .chat-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .message {
        max-width: 75%;
        padding: 0.8rem 1rem;
        border-radius: 1rem;
        position: relative;
    }
    
    .user-message {
        align-self: flex-end;
        background-color: #dcf8c6;
        border-bottom-right-radius: 0.25rem;
    }
    
    .assistant-message {
        align-self: flex-start;
        background-color: #f1f1f1;
        border-bottom-left-radius: 0.25rem;
    }
    
    .message-content {
        word-wrap: break-word;
    }
    
    .message-time {
        text-align: right;
        font-size: 0.75rem;
        color: #777;
        margin-top: 0.3rem;
    }
    
    .references-container {
        margin-top: 0.75rem;
    }
    
    .references-content {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.75rem;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .reference-item {
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .reference-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .reference-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .reference-text {
        font-size: 0.85rem;
        color: #555;
    }
    
    .typing-indicator {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        margin-top: 0.5rem;
        color: #666;
    }
    
    .typing-dot {
        width: 8px;
        height: 8px;
        margin: 0 1px;
        background-color: #666;
        border-radius: 50%;
        animation: typing-animation 1.4s infinite both;
    }
    
    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }
    
    .typing-text {
        margin-left: 0.5rem;
    }
    
    @keyframes typing-animation {
        0%, 60%, 100% {
            transform: translateY(0);
        }
        30% {
            transform: translateY(-4px);
        }
    }
    
    @media (max-width: 768px) {
        .message {
            max-width: 85%;
        }
    }
</style>