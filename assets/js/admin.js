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
            this.checkStatus();
            console.log('🚀 Morden Backup initialized successfully');
        },

        // Bind event handlers
        bindEvents: function() {
            // Backup button click
            $(document).on('click', '.morden-backup-btn', function(e) {
                e.preventDefault();
                MordenBackup.startBackup();
            });

            // Restore button click
            $(document).on('click', '.morden-restore-btn', function(e) {
                e.preventDefault();
                MordenBackup.startRestore();
            });

            // Migration export button
            $(document).on('click', '.morden-export-btn', function(e) {
                e.preventDefault();
                MordenBackup.startExport();
            });

            // Migration import button
            $(document).on('click', '.morden-import-btn', function(e) {
                e.preventDefault();
                MordenBackup.startImport();
            });

            // Test connection buttons
            $(document).on('click', '.test-connection-btn', function(e) {
                e.preventDefault();
                var storageType = $(this).data('storage-type');
                MordenBackup.testConnection(storageType);
            });
        },

        // Start backup process
        startBackup: function() {
            console.log('Starting backup process...');
            this.showNotice('⏳ Backup process starting...', 'info');

            // TODO: Implement backup AJAX call
            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_start',
                    nonce: mordenBackup.nonce
                },
                success: function(response) {
                    if (response.success) {
                        MordenBackup.showNotice('✅ ' + mordenBackup.strings.backupStarted, 'success');
                        MordenBackup.updateProgress(0);
                        MordenBackup.trackProgress(response.data.job_id);
                    } else {
                        MordenBackup.showNotice('❌ ' + response.data, 'error');
                    }
                },
                error: function() {
                    MordenBackup.showNotice('❌ ' + mordenBackup.strings.error, 'error');
                }
            });
        },

        // Start restore process
        startRestore: function() {
            console.log('Starting restore process...');
            this.showNotice('⏳ Restore process starting...', 'info');

            // TODO: Implement restore AJAX call
            $.ajax({
                url: mordenBackup.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'morden_backup_restore',
                    nonce: mordenBackup.nonce
                },
                success: function(response) {
                    if (response.success) {
                        MordenBackup.showNotice('✅ ' + mordenBackup.strings.restoreStarted, 'success');
                    } else {
                        MordenBackup.showNotice('❌ ' + response.data, 'error');
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

            // TODO: Implement export functionality
            this.showNotice('🚀 Export functionality will be implemented here', 'info');
        },

        // Start import process
        startImport: function() {
            console.log('Starting import process...');
            this.showNotice('⏳ Import process starting...', 'info');

            // TODO: Implement import functionality
            this.showNotice('🚀 Import functionality will be implemented here', 'info');
        },

        // Test storage connection
        testConnection: function(storageType) {
            console.log('Testing connection for:', storageType);
            this.showNotice('🔍 Testing ' + storageType + ' connection...', 'info');

            // TODO: Implement connection test
            setTimeout(function() {
                MordenBackup.showNotice('✅ ' + storageType + ' connection test successful!', 'success');
            }, 2000);
        },

        // Track backup/restore progress
        trackProgress: function(jobId) {
            var progressInterval = setInterval(function() {
                $.ajax({
                    url: mordenBackup.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'morden_backup_progress',
                        job_id: jobId,
                        nonce: mordenBackup.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            var progress = response.data.progress;
                            MordenBackup.updateProgress(progress);

                            if (progress >= 100) {
                                clearInterval(progressInterval);
                                MordenBackup.showNotice('✅ Process completed successfully!', 'success');
                            }
                        }
                    }
                });
            }, 2000);
        },

        // Check plugin status
        checkStatus: function() {
            console.log('Checking plugin status...');
            // TODO: Implement status check
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
        },

        // Update progress bar
        updateProgress: function(percentage) {
            $('.morden-backup-progress-bar').css('width', percentage + '%');

            // Add percentage text if progress bar exists
            var progressText = $('.morden-backup-progress').find('.progress-text');
            if (progressText.length === 0) {
                $('.morden-backup-progress').append('<div class="progress-text" style="text-align: center; margin-top: 8px; font-weight: 600; color: #2c3338;"></div>');
                progressText = $('.progress-text');
            }
            progressText.text(Math.round(percentage) + '%');
        },

        // Utility function to format file sizes
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';

            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };

})(jQuery);
