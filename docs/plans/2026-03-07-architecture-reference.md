# Architecture Reference Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** إنشاء مرجعين معماريين جديدين للحزم ودمجهما مع `AGENTS.md`.

**Architecture:** سيتم استخدام الجرد الحالي للحزم وservice providers وملفات الـ API لبناء وثيقتين: خريطة dependencies ووثيقة API integration. ثم يحدَّث `AGENTS.md` ليشير إليهما كمرجع أعمق.

**Tech Stack:** Markdown, Mermaid, local package metadata, Laravel service providers

---

### Task 1: Create Package Dependency Map

**Files:**
- Create: `docs/architecture/package-dependency-map.md`

**Step 1:** توثيق طبقات الحزم والمجموعات.
**Step 2:** إضافة جدول مختصر لكل package ودوره واعتماداته الرئيسية.
**Step 3:** إضافة Mermaid graph للعلاقات عالية المستوى.

### Task 2: Create API Integration Reference

**Files:**
- Create: `docs/architecture/api-integration.md`

**Step 1:** توثيق نقاط الدخول لـ `MedsdnApi` و`GraphQLAPI`.
**Step 2:** شرح مسار الربط بينهما وبين الحزم الأساسية.
**Step 3:** توضيح الفروق بين reuse of domain logic وAPI-specific orchestration.

### Task 3: Link References from AGENTS.md

**Files:**
- Modify: `AGENTS.md`

**Step 1:** إضافة قسم `Deep References`.
**Step 2:** ربط الملفين الجديدين.
**Step 3:** إبقاء `AGENTS.md` كفهرس سريع دون تكرار مطول.

### Task 4: Verify Coverage

**Files:**
- Verify only

**Step 1:** التأكد أن كل الحزم الحالية ممثلة في المرجع.
**Step 2:** التأكد من ظهور روابط `Deep References` في `AGENTS.md`.
