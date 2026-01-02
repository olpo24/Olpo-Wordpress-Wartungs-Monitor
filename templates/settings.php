<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>Einstellungen</h1>
    <?php if (isset($_GET['wpmm_added'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Seite registriert! <a href="admin.php?action=download_bridge&api_key=<?= esc_attr($_GET['api_key']) ?>" class="button button-small">Bridge laden</a></p>
        </div>
    <?php endif; ?>
    <form id="add-site-form" class="postbox" style="padding:20px;">
        <p><label>Name</label><br><input type="text" id="site-name" class="regular-text" required></p>
        <p><label>URL</label><br><input type="url" id="site-url" class="regular-text" required></p>
        <input type="submit" class="button button-primary" value="Registrieren">
    </form>
</div>
