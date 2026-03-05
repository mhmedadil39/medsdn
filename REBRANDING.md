# MedSDN Rebranding Guide

This document outlines the rebranding changes from Bagisto to MedSDN.

## Overview

MedSDN is a specialized fork of Bagisto, focused on medical and healthcare eCommerce solutions. The platform maintains all the powerful features of Bagisto while adding healthcare-specific capabilities.

## Changes Made

### 1. Core Identity
- **Project Name**: Bagisto → MedSDN
- **Version**: Maintained as 2.3.13
- **Focus**: General eCommerce → Medical & Healthcare eCommerce

### 2. Updated Files

#### Configuration Files
- `composer.json` - Updated package name, description, and support URLs
- `.env.example` - Changed APP_NAME to MedSDN
- `packages/Webkul/Core/src/Core.php` - Updated version constant and methods
- `packages/Webkul/Core/src/Resources/manifest.php` - Updated package name

#### Package Files
- `packages/Webkul/Admin/composer.json` - Updated to medsdn/laravel-admin
- `packages/Webkul/Core/composer.json` - Updated to medsdn/laravel-core
- `packages/Webkul/SocialShare/composer.json` - Updated to medsdn/laravel-social-share

#### Documentation Files
- `.kiro/steering/product.md` - Updated product overview with medical focus
- `.kiro/steering/structure.md` - Updated project structure references
- `AGENTS.md` - Updated GitNexus project name
- `CLAUDE.md` - Updated GitNexus project name

### 3. Namespace & Code Structure

The following remain unchanged to maintain compatibility:
- PHP namespaces: `Webkul\*` (maintained for backward compatibility)
- Package structure under `packages/Webkul/`
- Database table prefixes
- Configuration file names

### 4. URLs & Support

**Old URLs:**
- https://bagisto.com
- https://forums.bagisto.com
- https://devdocs.bagisto.com
- support@bagisto.com

**New URLs:**
- https://medsdn.com (to be configured)
- https://forums.medsdn.com (to be configured)
- https://docs.medsdn.com (to be configured)
- support@medsdn.com

## Next Steps

### Required Actions

1. **Update Environment Variables**
   ```bash
   # Copy .env.example to .env if not exists
   cp .env.example .env
   
   # Update APP_NAME in .env
   APP_NAME=MedSDN
   ```

2. **Update Composer Dependencies**
   ```bash
   composer update
   ```

3. **Clear Application Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

4. **Rebuild Assets**
   ```bash
   npm install
   npm run build
   ```

5. **Update GitNexus Index**
   ```bash
   npx gitnexus analyze --force
   ```

### Optional Customizations

1. **Logo & Branding Assets**
   - Update logo files in `public/themes/*/images/`
   - Update favicon files
   - Update email templates with new branding

2. **Language Files**
   - Review and update translation files in `lang/` directories
   - Update any Bagisto references in user-facing strings

3. **Email Templates**
   - Update email templates in package Resources/views/mail/
   - Replace Bagisto branding with MedSDN

4. **Admin Panel**
   - Update admin panel footer and header branding
   - Update copyright notices

## Medical & Healthcare Features

### Planned Enhancements

1. **Medical Product Attributes**
   - Prescription requirements
   - Drug interaction warnings
   - Expiry date management
   - Batch/lot number tracking

2. **Compliance Features**
   - HIPAA compliance tools
   - FDA regulation support
   - Medical license verification
   - Prescription upload and verification

3. **Healthcare-Specific Integrations**
   - Electronic Health Records (EHR) integration
   - Pharmacy management systems
   - Insurance claim processing
   - Telemedicine integration

4. **Specialized Workflows**
   - Prescription approval workflow
   - Cold chain logistics for temperature-sensitive products
   - Controlled substance tracking
   - Medical professional verification

## Backward Compatibility

The rebranding maintains backward compatibility with existing Bagisto extensions and themes by:
- Keeping the `Webkul` namespace
- Maintaining the same package structure
- Preserving database schema
- Keeping configuration file names

## Support & Resources

- **Documentation**: Coming soon at docs.medsdn.com
- **Community Forum**: Coming soon at forums.medsdn.com
- **Issue Tracker**: GitHub repository (to be configured)
- **Email Support**: support@medsdn.com

## License

MedSDN maintains the MIT License from the original Bagisto project, ensuring it remains fully open-source and free to use.

---

**Last Updated**: March 5, 2026
**Version**: 2.3.13
