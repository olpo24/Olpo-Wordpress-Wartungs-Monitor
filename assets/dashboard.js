(function($) {
    $(document).ready(function() {
        // Status für alle Karten initialisieren
        $('.site-card').each(function() {
            loadSiteStatus($(this).data('id'), $(this));
        });

        // Modal schliessen
        $(document).on('click', '.close-modal, .close-edit-modal', function() {
            $('.wpmm-modal').hide();
        });

        // Seite bearbeiten Modal öffnen
        $(document).on('click', '.btn-edit-site', function() {
            $('#edit-site-id').val($(this).data('id'));
            $('#edit-site-name').val($(this).data('name'));
            $('#edit-site-url').val($(this).data('url'));
            $('#edit-modal').show();
        });

        // AJAX Update Site
        $('#edit-site-form').on('submit', function(e) {
            e.preventDefault();
            $.post(wpmmData.ajax_url, {
                action: 'wpmm_update_site',
                nonce: wpmmData.nonce,
                id: $('#edit-site-id').val(),
                name: $('#edit-site-name').val(),
                url: $('#edit-site-url').val()
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Fehler: ' + response.data.message);
                }
            });
        });
    });

    function loadSiteStatus(id, card) {
        const content = card.find('.card-content');
        $.ajax({
            url: wpmmData.ajax_url,
            data: { action: 'wpmm_get_status', nonce: wpmmData.nonce, id: id },
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;
                    let html = `<div style="margin-bottom:8px; color:#646970; font-size:12px;">WP ${data.core_version || '??'} | PHP ${data.php_version || '??'}</div>`;
                    
                    // Extrem vorsichtige Prüfung der Update-Struktur
                    if (data.updates && typeof data.updates === 'object' && data.updates.total > 0) {
                        html += `<span class="wpmm-badge updates-msg">${data.updates.total} Updates verfügbar</span>`;
                        card.find('.btn-update-trigger').show();
                    } else {
                        html += `<span class="wpmm-badge">System aktuell</span>`;
                    }
                    content.html(html);
                } else {
                    content.html('<span class="wpmm-badge error-msg">Verbindung fehlgeschlagen</span>');
                }
            },
            error: function() {
                content.html('<span class="wpmm-badge error-msg">Server-Fehler</span>');
            }
        });
    }
})(jQuery);
