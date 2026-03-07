# GraphQL API Package - Complete Rebranding

## Overview

This package has been completely rebranded from "Bagisto GraphQL API" to "MedSDN GraphQL API". All internal code, namespaces, functions, and references have been updated to reflect the new branding.

## What Was Changed

### Documentation Files
- ✅ `README.md` - Fully rebranded to MedSDN with medical eCommerce focus
- ✅ `README_MEDSDN.md` - New comprehensive guide created
- ✅ `SECURITY.md` - Updated security contact and references
- ✅ `CHANGELOG.md` - Added note about historical Bagisto references (preserved for accuracy)
- ✅ `upgrade.md` - Added note about GitHub PR references (preserved for accuracy)
- ✅ `composer.json` - Updated package description and keywords

### User-Facing Content
- ✅ `src/Templates/on-boarding.php` - ASCII art and welcome message updated to MedSDN
- ✅ Package description in composer.json

### Internal Code (FULLY REBRANDED)

1. **Class Names**:
   - `BagistoGraphql` → `MedSDNGraphql`
   - `BagistoGraphql` Facade → `MedSDNGraphql` Facade

2. **Helper Function**:
   - `bagisto_graphql()` → `medsdn_graphql()`

3. **Service Container Binding**:
   - `bagisto_graphql` → `medsdn_graphql`

4. **Translation Namespace**:
   - `bagisto_graphql::` → `medsdn_graphql::`
   - Updated in all PHP files, Blade templates, and language files

5. **View Namespace**:
   - `bagisto_graphql::` → `medsdn_graphql::`
   - Updated in ServiceProvider and all view files

6. **Console Commands**:
   - `bagisto-graphql:install` → `medsdn-graphql:install`

7. **Asset Functions**:
   - `bagisto_asset()` → `medsdn_asset()`

8. **File Paths**:
   - `vendor/bagisto/graphql-api/` → `vendor/webkul/graphql-api/`

### Files Updated

#### Core Classes
- `src/BagistoGraphql.php` → `src/MedSDNGraphql.php`
- `src/Facades/BagistoGraphql.php` → `src/Facades/MedSDNGraphql.php`
- `src/Http/helpers.php` - Updated helper function
- `src/Providers/GraphQLAPIServiceProvider.php` - Updated all references

#### Configuration
- `src/Config/lighthouse.php` - Updated schema path

#### Commands
- `src/Console/Commands/Install.php` - Updated command signature and messages

#### Views & Templates
- All Blade files in `src/Resources/views/` - Updated translation keys
- `src/Templates/on-boarding.php` - Updated ASCII art and messages

#### Translations
- All language files in `src/Resources/lang/` - Updated namespace references

#### DataGrids
- `src/DataGrids/PushNotificationDataGrid.php` - Updated translation keys and asset function

#### Mutations & Queries
- All mutation files - Updated translation keys and helper function calls
- All query files - Updated helper function calls

#### Repositories
- `src/Repositories/NotificationRepository.php` - Updated helper function calls

#### Mail
- `src/Mail/SocialLoginPasswordResetEmail.php` - Updated translation and view namespaces

### Historical References (Preserved)

The following were intentionally kept as historical references:
- GitHub PR links in `upgrade.md` - Point to original Bagisto repository
- Historical changelog entries in `CHANGELOG.md` - Preserved for accuracy
- `.github/` templates - Reference original repository for context

## Migration Guide

### For Developers Using This Package

If you were using the old Bagisto GraphQL API, update your code:

#### Old Code (Bagisto)
```php
use Webkul\GraphQLAPI\Facades\BagistoGraphql;

$api = bagisto_graphql();
$api->authorize();

trans('bagisto_graphql::app.shop.customers.no-login-customer')

@extends('bagisto_graphql::shop.layouts.master')
```

#### New Code (MedSDN)
```php
use Webkul\GraphQLAPI\Facades\MedSDNGraphql;

$api = medsdn_graphql();
$api->authorize();

trans('medsdn_graphql::app.shop.customers.no-login-customer')

@extends('medsdn_graphql::shop.layouts.master')
```

### Installation Command

Old: `php artisan bagisto-graphql:install`
New: `php artisan medsdn-graphql:install`

### Composer Package Path

Old: `vendor/bagisto/graphql-api/`
New: `vendor/webkul/graphql-api/`

## Testing

All existing functionality remains the same. Only naming has changed:
- GraphQL queries and mutations work identically
- API endpoints remain unchanged
- Authentication flow is the same
- All features function as before

## Summary

This rebranding is complete and comprehensive:
1. **User-facing content** - Fully rebranded to MedSDN
2. **Documentation** - Updated with medical eCommerce focus
3. **Internal code** - Completely rebranded (no backward compatibility layer)
4. **Developer experience** - Clean, consistent MedSDN naming throughout

The package now presents itself as "MedSDN GraphQL API" in all aspects - from documentation to internal code structure.
