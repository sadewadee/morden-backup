// Morden Backup Admin JavaScript

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        MordenBackup.init();
    });

    // Main plugin object
    window.MordenBackup = {

        // Initialize the plugin
        init: function() {
            this.bindEvents();
            console.log('🚀 Morden Backup initialized successfully');
        },

        // Bind event handlers
        bindEvents: function() {
            // Backup button click
            $(document).on('click', '#morden-backup-create', function(e) {
                e.preventDefault();
                MordenBackup.startBackup();
            });

            // Restore button click
            $(document).on('click', '.morden-restore-btn', function(e) {
                e.preventDefault();
                MordenBackup.startRestore($(this).data('backup-path'));
            });

            // Migration export button
            $(document).on('click', '#morden-backup-export', function(e) {
                e.preventDefault();
                MordenBackup.startExport();
            });

            // Migration import button
            $(document).on('click', '#morden-backup-import', function(e) {
                e.preventDefault();
                MordenBackup.startImport();
            });

            // Delete button click
            $(document).on('click', '.morden-delete-btn', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this backup?')) {
                    MordenBackup.deleteBackup($(this).data('backup-path'));
                }
            });
        },

        // Start backup process
        startBackup: function() {
            console.log('Starting backup process...');
            this.showNotice('⏳ Backup process starting...', 'info');

            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_create',
                    _ajax_nonce: mordenBackup.nonce
                },
                success: function(response) {
                    if (response.status === 'success') {
                        MordenBackup.showNotice('✅ ' + response.message, 'success');
                    } else {
                        MordenBackup.showNotice('❌ ' + response.message, 'error');
                    }
                },
                error: function() {
                    MordenBackup.showNotice('❌ ' + mordenBackup.strings.error, 'error');
                }
            });
        },

        // Start restore process
        startRestore: function(backupPath) {
            console.log('Starting restore process...');
            this.showNotice('⏳ Restore process starting...', 'info');

            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_restore',
                    _ajax_nonce: mordenBackup.nonce,
                    backup_path: backupPath
                },
                success: function(response) {
                    if (response.status === 'success') {
                        MordenBackup.showNotice('✅ ' + response.message, 'success');
                    } else {
                        MordenBackup.showNotice('❌ ' + response.message, 'error');
                    }
                },
                error: function() {
                    MordenBackup.showNotice('❌ ' + mordenBackup.strings.error, 'error');
                }
            });
        },

        // Start export process
        startExport: function() {
            console.log('Starting export process...');
            this.showNotice('⏳ Export process starting...', 'info');

            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_export',
                    _ajax_nonce: mordenBackup.nonce
                },
                success: function(response) {
                    if (response.status === 'success') {
                        MordenBackup.showNotice('✅ Export created successfully. Token: ' + response.token, 'success');
                    } else {
                        MordenBackup.showNotice('❌ ' + response.message, 'error');
                    }
                },
                error: function() {
                    MordenBackup.showNotice('❌ ' + mordenBackup.strings.error, 'error');
                }
            });
        },

        // Start import process
        startImport: function() {
            console.log('Starting import process...');
            this.showNotice('⏳ Import process starting...', 'info');
            var token = $('#morden-backup-token').val();

            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_import',
                    _ajax_nonce: mordenBackup.nonce,
                    token: token
                },
                success: function(response) {
                    if (response.status === 'success') {
                        MordenBackup.showNotice('✅ ' + response.message, 'success');
                    } else {
                        MordenBackup.showNotice('❌ ' + response.message, 'error');
                    }
                },
                error: function() {
                    MordenBackup.showNotice('❌ ' + mordenBackup.strings.error, 'error');
                }
            });
        },

        // Delete backup
        deleteBackup: function(backupPath) {
            console.log('Deleting backup...');
            this.showNotice('⏳ Deleting backup...', 'info');

            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_delete_backup',
                    _ajax_nonce: mordenBackup.nonce,
                    backup_path: backupPath
                },
                success: function(response) {
                    if (response.success) {
                        MordenBackup.showNotice('✅ ' + response.data.message, 'success');
                        // Refresh the page to update the backup list
                        location.reload();
                    } else {
                        MordenBackup.showNotice('❌ ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    MordenBackup.showNotice('❌ ' + mordenBackup.strings.error, 'error');
                }
            });
        },

        // Show admin notice
        showNotice: function(message, type) {
            type = type || 'info';
            var iconMap = {
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'info': 'ℹ️'
            };

            var icon = iconMap[type] || '';
            var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + icon + ' ' + message + '</p></div>');

            // Remove existing notices
            $('.notice').fadeOut(300, function() {
                $(this).remove();
            });

            // Add new notice
            $('.wrap h1').first().after(notice);

            // Auto-hide success and info notices
            if (type === 'success' || type === 'info') {
                setTimeout(function() {
                    notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        }
    };

})(jQuery);
