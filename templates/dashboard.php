<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1 class="wp-heading-inline">WP Maintenance Monitor</h1>
    <a href="<?= admin_url('admin.php?page=wp-maintenance-monitor-settings') ?>" class="page-title-action">Seite hinzufügen</a>
    <hr class="wp-header-end">

    <?php if (empty($sites)): ?>
        <div class="notice notice-info"><p>Keine Seiten konfiguriert.</p></div>
    <?php else: ?>
        <div class="site-grid">
            <?php foreach ($sites as $site): ?>
                <div class="site-card" data-id="<?= $site->id ?>">
                    <div class="card-header">
                        <div>
                            <h3><?= esc_html($site->site_name) ?></h3>
                            <a href="<?= esc_url($site->site_url) ?>" target="_blank" class="site-url"><?= esc_url($site->site_url) ?></a>
                        </div>
                    </div>
                    <div class="card-content">
                        <span class="description">Status wird geladen...</span>
                    </div>
                    <div class="card-actions">
                        <button class="button button-small btn-update-trigger" style="display:none;">Updates verwalten</button>
                        <button class="button button-small btn-edit-site" 
                                data-id="<?= $site->id ?>" 
                                data-name="<?= esc_attr($site->site_name) ?>" 
                                data-url="<?= esc_attr($site->site_url) ?>">Details</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="edit-modal" class="wpmm-modal">
    <div class="wpmm-modal-content">
        <div class="modal-header">
            <h2>Seite bearbeiten</h2>
            <button class="close-edit-modal" style="border:none; background:none; cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-site-form">
                <input type="hidden" id="edit-site-id">
                <table class="form-table">
                    <tr>
                        <td><label>Name</label></td>
                        <td><input type="text" id="edit-site-name" class="regular-text"></td>
                    </tr>
                    <tr>
                        <td><label>URL</label></td>
                        <td><input type="url" id="edit-site-url" class="regular-text"></td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary">Speichern</button>
                    <button type="button" class="button btn-delete-site" style="color:#d63638;">Löschen</button>
                </p>
            </form>
        </div>
    </div>
</div>
