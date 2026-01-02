<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>Einstellungen</h1>
    <?php if (isset($_GET['wpmm_added'])): ?>
        <div class="notice notice-success is-dismissible" style="padding:15px;">
            <p><strong>✔ Seite registriert!</strong></p>
            <p>1. Lade das Bridge-Plugin herunter: 
               <a href="admin.php?action=download_bridge&api_key=<?= esc_attr($_GET['api_key']) ?>" class="button button-primary">Bridge (.php) laden</a>
            </p>
            <p>2. Installiere es auf der Zielseite unter <code>/wp-content/plugins/</code>.</p>
        </div>
    <?php endif; ?>

    <div class="postbox" style="margin-top:20px;">
        <div class="inside">
            <form id="add-site-form">
                <table class="form-table">
                    <tr><th>Anzeigename</th><td><input type="text" id="site-name" class="regular-text" required></td></tr>
                    <tr><th>Website URL</th><td><input type="url" id="site-url" class="regular-text" required></td></tr>
                </table>
                <input type="submit" class="button button-primary" value="Seite registrieren">
            </form>
        </div>
    </div>
</div>
