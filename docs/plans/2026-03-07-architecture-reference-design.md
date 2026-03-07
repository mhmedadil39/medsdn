# Architecture Reference Design

## Goal

إضافة مراجع معمارية عملية فوق `AGENTS.md` تشرح:

- خريطة الاعتماد بين الحزم تحت `packages/medsdn`
- طريقة ربط `MedsdnApi` و`GraphQLAPI` بكل طبقة دومين

## Chosen Approach

اعتماد أسلوب `Reference-first`:

- إبقاء `AGENTS.md` كنقطة دخول سريعة
- نقل التفاصيل الثقيلة إلى:
  - `docs/architecture/package-dependency-map.md`
  - `docs/architecture/api-integration.md`
- إضافة روابط واضحة من `AGENTS.md` إلى المرجعين

## Content Model

### package-dependency-map.md

- نظرة عامة على طبقات الحزم
- جدول package-by-package مختصر
- Mermaid graph يوضح الاعتماد عالي المستوى
- ملاحظات عن الحزم الداخلية التي لا تملك `composer.json`

### api-integration.md

- مقارنة `MedsdnApi` مقابل `GraphQLAPI`
- نقاط الدخول الرئيسية
- مسارات الربط مع الحزم الأساسية:
  - `Product`
  - `Checkout`
  - `Customer`
  - `Sales`
  - `Category`
  - `Attribute`
  - `Payment`
  - `Shipping`
  - `Theme`
  - `BankTransfer`
  - `CMS`
- توضيح ما هو domain logic reused وما هو orchestration خاص بالـ API

### AGENTS.md

- إضافة قسم `Deep References`
- روابط مباشرة إلى ملفات `docs/architecture`

## Verification

- التأكد من وجود الملفين وربطهما من `AGENTS.md`
- التأكد أن كل الحزم الحالية تحت `packages/medsdn` ممثلة في map/reference
- مراجعة سريعة للروابط والمسارات المذكورة مقابل المصدر الحالي
