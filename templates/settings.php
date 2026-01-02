<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>WP Maintenance Monitor - Einstellungen</h1>

    <?php if (isset($_GET['wpmm_added'])): ?>
        <div class="notice notice-success is-dismissible" style="padding: 15px; border-left: 4px solid #46b450;">
            <p style="font-size: 1.1em;"><strong>✔ Seite erfolgreich registriert!</strong></p>
            <p>Laden Sie jetzt das vorbereitete Bridge-Plugin herunter und installieren Sie es auf der Zielseite:</p>
            
            <div style="margin: 15px 0;">
                <a href="admin.php?action=download_bridge&api_key=<?= esc_attr($_GET['api_key']) ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-archive" style="vertical-align: middle; margin-top: 4px;"></span> Bridge-Plugin (.zip) herunterladen
                </a>
            </div>
            
            <p class="description">Gehen Sie auf der Zielseite zu <strong>Plugins -> Installieren -> Plugin hochladen</strong> und wählen Sie die heruntergeladene ZIP-Datei aus.</p>
        </div>
    <?php endif; ?>

    <div class="postbox" style="margin-top:20px;">
        <div class="postbox-header"><h2 class="hndle">Neue Website hinzufügen</h2></div>
        <div class="inside">
            <form id="add-site-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="site-name">Anzeigename</label></th>
                        <td><input type="text" id="site-name" class="regular-text" placeholder="Kundenprojekt Alpha" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="site-url">Website URL</label></th>
                        <td><input type="url" id="site-url" class="regular-text" placeholder="https://beispiel-seite.de" required></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Seite registrieren & ZIP generieren">
                </p>
            </form>
        </div>
    </div>
</div>
