(function($) {
    $(document).ready(function() {
        // Status für alle Karten beim Laden initialisieren
        $('.site-card').each(function() {
            loadSiteStatus($(this).data('id'), $(this));
        });

        // Modals schließen
        $(document).on('click', '.close-modal, .close-edit-modal', function() {
            $('.wpmm-modal').hide();
        });

        // Seite hinzufügen (AJAX & stabiler Redirect)
        $('#add-site-form').on('submit', function(e) {
            e.preventDefault();
            const siteName = $('#site-name').val();
            const siteUrl = $('#site-url').val();
            
            $.post(wpmmData.ajax_url, {
                action: 'wpmm_add_site',
                nonce: wpmmData.nonce,
                name: siteName,
                url: siteUrl
            }, function(response) {
                if (response.success && response.data) {
                    // Stabiler Redirect-Bau ohne White Screen Risiko
                    const baseUrl = window.location.origin + window.location.pathname;
                    const params = new URLSearchParams(window.location.search);
                    params.set('wpmm_added', '1');
                    params.set('api_key', response.data.api_key);
                    params.set('site_id', response.data.site_id);
                    
                    window.location.href = baseUrl + '?' + params.toString();
                } else {
                    alert('Fehler: ' + (response.data ? response.data.message : 'Unbekannter Fehler'));
                }
            }).fail(function() {
                alert('Server-Fehler beim Hinzufügen der Seite.');
            });
        });

        // Bearbeiten-Modal öffnen
        $(document).on('click', '.btn-edit-site', function() {
            $('#edit-site-id').val($(this).data('id'));
            $('#edit-site-name').val($(this).data('name'));
            $('#edit-site-url').val($(this).data('url'));
            $('#edit-modal').show();
        });

        // Seite aktualisieren
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
                    alert('Fehler beim Speichern: ' + response.data.message);
                }
            });
        });

        // Seite löschen
        $(document).on('click', '.btn-delete-site', function(e) {
            e.preventDefault();
            const id = $('#edit-site-id').val();
            
            if (confirm('Möchtest du diese Seite wirklich unwiderruflich aus dem Monitor löschen?')) {
                const btn = $(this);
                btn.prop('disabled', true).text('Lösche...');

                $.post(wpmmData.ajax_url, {
                    action: 'wpmm_delete_site',
                    nonce: wpmmData.nonce,
                    id: id
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Fehler: ' + response.data.message);
                        btn.prop('disabled', false).text('Löschen');
                    }
                });
            }
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
