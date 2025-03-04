<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>RAGFlow Chat</h1>
            <p>Berinteraksi dengan asisten berbasis pengetahuan menggunakan RAGFlow.</p>
        </div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newSessionModal">
                <i class="fas fa-plus"></i> Percakapan Baru
            </button>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('success'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Daftar Percakapan -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Percakapan Tersimpan</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($chat_sessions)) : ?>
                        <div class="text-center p-4">
                            <p class="text-muted">Belum ada percakapan. Buat percakapan baru untuk mulai menggunakan RAGFlow.</p>
                        </div>
                    <?php else : ?>
                        <div class="list-group">
                            <?php foreach ($chat_sessions as $session) : ?>
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($session['session_name']); ?></h5>
                                        <p class="mb-1 text-muted">
                                            <small>Dibuat: <?= date('d M Y, H:i', strtotime($session['created_at'])); ?></small>
                                            <span class="badge badge-primary ml-2"><?= $session['message_count']; ?> pesan</span>
                                        </p>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('telaah/chat/' . $session['session_id']); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-comments"></i> Lanjutkan
                                        </a>
                                        <a href="<?= base_url('telaah/delete_session/' . $session['session_id']); ?>" class="btn btn-sm btn-danger delete-session">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Percakapan Baru -->
<div class="modal fade" id="newSessionModal" tabindex="-1" role="dialog" aria-labelledby="newSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSessionModalLabel">Buat Percakapan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('telaah/create_session'); ?>" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="session_name">Nama Percakapan</label>
                        <input type="text" class="form-control" id="session_name" name="session_name" required>
                    </div>
                    <div class="form-group">
                        <label for="chat_id">Pilih Asisten</label>
                        <select class="form-control" id="chat_id" name="chat_id" required>
                            <option value="">-- Pilih Asisten --</option>
                            <?php foreach ($chat_assistants as $assistant) : ?>
                                <option value="<?= $assistant['id']; ?>"><?= htmlspecialchars($assistant['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat Percakapan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Konfirmasi sebelum menghapus percakapan
    $(document).ready(function() {
        $('.delete-session').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            
            if (confirm('Anda yakin ingin menghapus percakapan ini? Semua pesan akan dihapus secara permanen.')) {
                window.location.href = url;
            }
        });
    });
</script>