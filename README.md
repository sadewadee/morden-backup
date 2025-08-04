# Morden Backup

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)
![License](https://img.shields.io/badge/license-GPL%20v3-blue.svg)

Advanced backup, restore, and migration plugin for WordPress combining the best features of UpdraftPlus and All-in-One WP Migration.

## 🚀 Features

### Backup Features
- ✅ Manual and scheduled backups
- ✅ Smart file exclusion with customizable patterns
- ✅ File splitting for large websites
- ✅ Progress tracking with real-time updates
- ✅ Multiple storage destinations support
- ✅ Incremental backup capability

### Restore Features
- ✅ One-click restore process
- ✅ Automatic URL replacement for migrations
- ✅ Partial restore (database, files, or specific components)
- ✅ Zero downtime restore process
- ✅ Backup verification before restore

### Migration Features
- ✅ Token-based secure migration
- ✅ Server-to-server direct migration
- ✅ Local-to-remote deployment
- ✅ Automatic environment configuration

### Storage Support
- ✅ **SFTP/SSH** - Simple credentials setup
- ✅ **WebDAV** - Universal protocol support
- ✅ **Backblaze B2** - Cost-effective cloud storage
- ✅ **pCloud** - User-friendly cloud storage
- 🔄 **Google Cloud Storage** - Enterprise grade (Pro)
- 🔄 **Azure Blob Storage** - Microsoft ecosystem (Pro)

## 📋 Requirements

- **PHP:** 7.4+ (Recommended: 8.0+)
- **WordPress:** 5.0+
- **MySQL:** 5.7+ / MariaDB 10.2+
- **Memory:** 256MB minimum (512MB recommended)
- **Storage:** 100MB plugin space + backup storage

## 📦 Installation

1. Upload the plugin files to `/wp-content/plugins/morden-backup/`
2. Activate the plugin through WordPress admin
3. Navigate to **Morden Backup** in the admin menu
4. Configure your backup destinations and settings

## 🛠️ Development

### Setup Development Environment

### Clone repository
git clone https://github.com/sadewadee/morden-backup.git
cd morden-backup

### Install PHP dependencies
composer install

### Install Node.js dependencies (if building assets)
npm install

### Run tests
composer test

### Check code standards
composer phpcs

### Fix code standards
composer phpcbf

### Run static analysis
composer phpstan

### Project Structure
morden-backup/
├── src/ # Main source code (PSR-4)
│ ├── Core/ # Backup/Restore engines
│ ├── Adapters/ # Storage provider adapters
│ ├── Migration/ # Migration functionality
│ ├── Utils/ # Helper utilities
│ ├── Contracts/ # Interfaces
│ └── Config/ # Configuration management
├── assets/ # CSS/JS assets
├── tests/ # Test suite
├── .github/workflows/ # CI/CD pipelines
└── docs/ # Documentation


## 🧪 Testing

### Run all tests
composer test

### Run specific test suite
./vendor/bin/phpunit tests/Unit/

### Run with coverage
./vendor/bin/phpunit --coverage-html coverage/


## 🔧 GitHub Actions

This plugin includes comprehensive CI/CD pipelines:

- **Code Quality**: PHP 7.4-8.2 compatibility testing
- **WordPress Compatibility**: WordPress 5.9-6.3 testing
- **Security Scanning**: Dependency vulnerability checks
- **Automated Building**: Release package generation
- **Automated Deployment**: WordPress.org deployment

## 📝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests and code standards checks
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## 📄 License

This project is licensed under the GPL v3 or later - see the [LICENSE](LICENSE) file for details.

## 👥 Team

- **Morden Team** - [GitHub](https://github.com/sadewadee)

## 🔗 Links

- [GitHub Repository](https://github.com/sadewadee/morden-backup)
- [WordPress.org Plugin Page](https://wordpress.org/plugins/morden-backup/) (Coming Soon)
- [Documentation](https://github.com/sadewadee/morden-backup/wiki)
- [Issue Tracker](https://github.com/sadewadee/morden-backup/issues)

---

**Made with ❤️ by Morden Team**
