<div class="wrap wpmm-container">
    <h1>Activity Logs</h1>
    
    <div style="margin: 20px 0;">
        <button id="clear-logs-btn" class="button button-secondary">
            🗑️ Alle Logs löschen
        </button>
    </div>
    
    <?php if (empty($logs)): ?>
        <div class="notice notice-info">
            <p>Keine Aktivitäten vorhanden.</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 150px;">Zeit</th>
                    <th style="width: 200px;">Seite</th>
                    <th style="width: 150px;">Aktion</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= esc_html(date('d.m.Y H:i', strtotime($log->created_at))) ?></td>
                        <td><strong><?= esc_html($log->site_name ?: 'System') ?></strong></td>
                        <td>
                            <span style="display: inline-block; background: #0073aa; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                <?= esc_html($log->action) ?>
                            </span>
                        </td>
                        <td style="font-family: monospace; font-size: 12px; color: #666;">
                            <?= esc_html($log->details) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .wpmm-container .wp-list-table th {
        background: #f0f0f1;
        font-weight: 600;
    }
    .wpmm-container .wp-list-table td {
        vertical-align: middle;
    }
</style>

<script>
jQuery(document).ready(function($) {
    $('#clear-logs-btn').on('click', function() {
        if (!confirm('Wirklich alle Logs unwiderruflich löschen?')) return;
        
        $.post(wpmmData.ajax_url, {
            action: 'wpmm_clear_logs',
            nonce: wpmmData.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        });
    });
});
</script>
