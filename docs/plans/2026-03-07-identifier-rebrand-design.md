# Identifier Rebrand Design

**Date:** 2026-03-07

## Goal

إعادة تسمية identifiers القديمة من `bagisto_asset(...)` إلى `medsdn_asset(...)` ومن `Webkul\\...` إلى `Medsdn\\...` بطريقة قابلة للتكرار وآمنة، بدل الاعتماد على استبدال يدوي أو أوامر نصية عمياء.

## Problem

المرحلة السابقة نقلت branding الظاهر ومسارات الحزم من `packages/Webkul` إلى `packages/medsdn`، لكنها أبقت identifiers التشغيلية القديمة لأسباب توافقية. هذا ترك الكود داخل `packages/medsdn/Admin` وما حوله يستخدم:

- helper قديم: `bagisto_asset(...)`
- namespaces قديمة: `Webkul\\...`
- strings مرجعية قديمة داخل config وBlade مثل `Webkul\\Tax\\Repositories\\...`

التغيير اليدوي لهذا النطاق واسع وسهل الكسر، خصوصًا مع وجود:

- Blade templates
- PHP namespaces و`use` statements
- strings داخل config arrays
- `composer.json` autoload mappings

## Constraints

- يجب أن يكون التغيير قابلاً للتكرار عبر سكربت واحد يمكن تشغيله بـ `--dry-run` ثم `--write`.
- يجب تجاهل الملفات المولدة مثل `vendor`, `node_modules`, وbuild outputs.
- يجب أن يدعم السكربت التغيير على مستوى scope محدد أولًا، مع إمكانية التوسيع لاحقًا.
- يجب أن توجد طبقة توافق انتقالية عند بدء التنفيذ لتقليل كسر النظام أثناء التحويل.

## Recommended Approach

اعتماد codemod script واحد في `scripts/rebrand-identifiers.php` مع ثلاث طبقات:

1. **Inventory and reporting**
   - يحصي الملفات المتأثرة ويعرض diff summary بدون كتابة.
   - يميز بين:
     - helper calls
     - namespace declarations
     - `use` imports
     - literal strings التي تشير إلى classes
     - `composer.json` autoload mappings

2. **Transformation rules**
   - `bagisto_asset(` → `medsdn_asset(`
   - `namespace Webkul\\...` → `namespace Medsdn\\...`
   - `use Webkul\\...` → `use Medsdn\\...`
   - strings من نوع `Webkul\Tax\Repositories\TaxCategoryRepository@getConfigOptions` → `Medsdn\Tax\Repositories\TaxCategoryRepository@getConfigOptions`
   - `composer.json` mappings من `Webkul\\...` إلى `Medsdn\\...`

3. **Compatibility layer**
   - تعريف `medsdn_asset()` أولًا مع إبقاء `bagisto_asset()` alias مؤقتًا.
   - الإبقاء على مسار تنفيذ يسمح بالرجوع السريع إذا كشف التحقق residue أو regressions.

## Scope Strategy

أفضل ممارسة هنا ليست “replace everything” دفعة واحدة، بل:

1. دعم السكربت نطاقًا محددًا (`--scope=admin`, `--scope=repo`)
2. تنفيذ أولي على `Admin`
3. تشغيل الاختبارات والبحث عن البقايا
4. توسيع التنفيذ لبقية المستودع بعد نجاح المرحلة الأولى

السكربت نفسه سيكون repo-capable، لكن التنفيذ يبدأ على `Admin` أولًا.

## Why This Is Better

- أسرع من التعديلات اليدوية.
- أضمن من `sed` أو replace شامل.
- يحافظ على traceability لأن كل تغيير يخرج من أداة واحدة.
- يسهل إعادة التشغيل بعد إصلاح قواعد التحويل.
- يسمح بقياس الأثر قبل الكتابة.

## Target Files

- `scripts/rebrand-identifiers.php`
- `packages/medsdn/Theme/src/Http/helpers.php`
- `composer.json`
- `packages/medsdn/Admin/composer.json`
- `packages/medsdn/Admin/src/**/*.php`
- `packages/medsdn/Admin/src/**/*.blade.php`
- اختبارات قبول جديدة أو محدثة تحت:
  - `tests/Unit/Project`
  - `packages/medsdn/Admin/tests/Feature/Admin`

## Verification Strategy

- `php scripts/rebrand-identifiers.php --scope=admin --dry-run`
- `php scripts/rebrand-identifiers.php --scope=admin --write`
- `composer dump-autoload`
- `php artisan test tests/Unit/Project/PackagePathBrandingTest.php`
- `php artisan test packages/medsdn/Admin/tests/Feature/Admin/LayoutShellTest.php`
- بحث residue:
  - `rg -n "bagisto_asset\\(|namespace Webkul\\\\|use Webkul\\\\|Webkul\\\\[A-Za-z]" packages/medsdn/Admin`

## Non-Goals

- إزالة جميع identifiers التوافقية القديمة من كامل المستودع في نفس الخطوة.
- تغيير event names مثل `bagisto.admin...` في هذه المرحلة.
- تعديل package names العامة في Composer من `bagisto/...`.
