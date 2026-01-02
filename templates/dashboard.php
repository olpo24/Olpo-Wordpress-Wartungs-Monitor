<div class="wrap wpmm-container">
    <h1>WP Maintenance Monitor</h1>
    
    <div class="site-grid <?= $display_mode === 'list' ? 'mode-list' : '' ?>">
        <?php foreach ($sites as $site): ?>
            <div class="site-card" data-id="<?= $site->id ?>">
                <div class="card-header">
                    <h3><?= esc_html($site->name) ?></h3>
                    <span class="version-label">v?</span>
                </div>
                <div class="status-container">
                    <span class="badge">Lade...</span>
                </div>
                <button class="btn-update-trigger">Updates verwalten 🔄</button>
                <div class="card-actions">
                    <a href="<?= esc_url($site->url . '/wp-admin') ?>" target="_blank" class="btn btn-success">Login</a>
                    <button class="btn btn-danger" onclick="if(confirm('Löschen?')) deleteSite(<?= $site->id ?>)">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div id="update-modal" class="wpmm-modal">
    <div class="wpmm-modal-content">
        <div class="modal-header">
            <h2 id="modal-site-name">Updates</h2>
            <button class="close-modal">&times;</button>
        </div>
        <div id="modal-loading">Lade Details...</div>
        <div id="modal-body" style="display:none;">
            <div id="core-update-section"></div>
            <div id="plugin-update-section"></div>
            <div id="theme-update-section"></div>
        </div>
    </div>
</div>
