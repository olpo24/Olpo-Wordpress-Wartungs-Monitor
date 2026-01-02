<?php
/**
 * Settings Template
 * Einstellungen und neue Seiten hinzufügen
 */

// Display Mode des aktuellen Users laden
$display_mode = get_user_meta(get_current_user_id(), 'wpmm_display_mode', true) ?: 'grid';

// Parameter aus der URL abfangen (für die Erfolgsmeldung nach dem AJAX-Redirect)
$show_success = isset($_GET['wpmm_added']) && $_GET['wpmm_added'] == '1';
$new_api_key  = isset($_GET['api_key']) ? sanitize_text_field($_GET['api_key']) : '';
$new_site_id  = isset($_GET['site_id']) ? intval($_GET['site_id']) : 0;
?>

<div class="wrap wpmm-container">
    <h1>Einstellungen</h1>
    
    <div style="max-width: 900px;">
        
        <?php if ($show_success && !empty($new_api_key)): ?>
            <div class="notice notice-success is-dismissible" style="margin: 20px 0; padding: 20px; border-left-width: 4px;">
                <h2 style="margin-top: 0; color: #27ae60;">✅ Seite erfolgreich hinzugefügt!</h2>
                <p>Verwende den folgenden API-Key für die Verbindung im Child-Plugin:</p>
                
                <div style="background: #f0f0f1; padding: 15px; border: 1px solid #c3c4c7; border-radius: 4px; font-family: monospace; font-size: 16px; margin: 15px 0; display: flex; justify-content: space-between; align-items: center;">
                    <span id="api-key-display" style="font-weight: bold;"><?= esc_html($new_api_key) ?></span>
                    <button id="copy-key-btn" class="button button-secondary" data-key="<?= esc_attr($new_api_key) ?>">
                        📋 API-Key kopieren
                    </button>
                </div>

                <div style="background: #e7f3ff; padding: 15px; border: 1px solid #b8daff; border-radius: 4px; margin-top: 20px;">
                    <h3 style="margin-top: 0;">📦 Nächster Schritt: Bridge installieren</h3>
                    <p>Lade das vorkonfigurierte Bridge-Plugin herunter und installiere es auf der Zielseite unter <strong>Plugins > Installieren > Plugin hochladen</strong>:</p>
                    <a href="<?= admin_url('admin-ajax.php?action=wpmm_download_bridge&id=' . $new_site_id) ?>" class="button button-primary button-large">
                        ⬇️ Bridge-Plugin (ZIP) herunterladen
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle">📊 Ansichts-Optionen</h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <p>Wähle aus, wie deine WordPress-Seiten im Dashboard dargestellt werden sollen:</p>
                
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button id="set-grid-mode" class="button <?= $display_mode === 'grid' ? 'button-primary' : '' ?>">
                        📱 Card Ansicht (Grid)
                    </button>
                    <button id="set-list-mode" class="button <?= $display_mode === 'list' ? 'button-primary' : '' ?>">
                        📋 Listen Ansicht (Table)
                    </button>
                </div>
            </div>
        </div>
        
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle">➕ Neue WordPress-Seite hinzufügen</h2>
            </div>
            <div class="inside" style="padding: 20px;">
                <form id="add-site-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="site-name">Name der Seite</label></th>
                            <td>
                                <input name="site_name" type="text" id="site-name" value="" class="regular-text" placeholder="z.B. Mein Blog" required>
                                <p class="description">Ein interner Name zur Identifizierung im Dashboard.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="site-url">URL der Seite</label></th>
                            <td>
                                <input name="site_url" type="url" id="site-url" value="" class="regular-text" placeholder="https://beispiel.de" required>
                                <p class="description">Die vollständige URL inklusive https://</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            🚀 Seite hinzufügen & Key generieren
                        </button>
                    </p>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // API-Key Kopier-Funktion
    $('#copy-key-btn').on('click', function() {
        var key = $(this).data('key');
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(key).then(function() {
                var btn = $('#copy-key-btn');
                btn.text('✅ Kopiert!');
                setTimeout(function() {
                    btn.text('📋 API-Key kopieren');
                }, 2000);
            }).catch(function() {
                fallbackCopyToClipboard(key);
            });
        } else {
            fallbackCopyToClipboard(key);
        }
    });
    
    function fallbackCopyToClipboard(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            var successful = document.execCommand('copy');
            if (successful) {
                var btn = $('#copy-key-btn');
                btn.text('✅ Kopiert!');
                setTimeout(function() {
                    btn.text('📋 API-Key kopieren');
                }, 2000);
            } else {
                alert('Kopieren fehlgeschlagen.');
            }
        } catch (err) {
            alert('Kopieren fehlgeschlagen.');
        }
        document.body.removeChild(textArea);
    }
});
</script>
