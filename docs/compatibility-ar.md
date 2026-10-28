# الفروق بين الإصدارات وطبقة التوافق (شرح عربي)

> ⚠️ **هذا الملف سجل تاريخي.** بعد إعادة البناء حُذفت كل طبقة التوافق الموصوفة هنا،
> وأصبح الكود يعمل بنموذج صارم واحد كأن المشروع ابتُني مرة واحدة.
> أُبقي الملف لأن تثبيتات `v3.0.x` القائمة قد تحتاج معرفة ما كان موجودًا قبل الترقية.

**الحالة التاريخية:** `APP_VERSION=v3.0.3` (`config/app.php` + `.env.example`).

---

## 1) هل كان المشروع يدعم مستودعًا واحدًا وفرعًا واحدًا؟

**نعم.** قبل `v3.0.0` كان النظام يعمل بـ «مستودع واحد + فرع واحد» فعليًا:

- `products.stock` كان هو المخزون الوحيد (عمود واحد على المنتج).
- `cashier_shifts.warehouse_id` و `transactions.warehouse_id` كانا **nullable** وغير إلزاميين.
- `settings` / `payment_settings` / `bank_accounts` كانت صفوفًا **عامة** بلا `outlet_id`.
- لا توجد جداول `outlets` ولا `user_outlets`.

ابتداءً من `v3.0.0` (تغيير **Breaking**) أصبح «الفرع/المستودع» حدًّا أمنيًا وتشغيليًا:

- جدول `outlets` + `warehouses.outlet_id` + `user_outlets`.
- `PUSAT` = مستودع مركزي **غير مفعّل للبيع**، والبيع فقط من مستودعات الفروع.

---

## 2) ما الذي اختلف بين `v3.0.3` و `v2.11.0`

| الإصدار | أهم ما تغيّر |
| --- | --- |
| **v3.0.0** (2026-09-18) | **Breaking**: تعدد الفروع `outlets` + `user_outlets` + `OutletAccessService`، تبديل الفرع من الـ navbar، إعدادات لكل فرع مع fallback عام، تقييد العمليات والتقارير بالفرع، أوامر `outlet:audit` / `outlet:legacy-audit`، فصل بذور العرض عن التثبيت الإنتاجي. |
| **v3.0.1** | استخراج منطق الـ checkout إلى `CheckoutService` + `CheckoutContext`؛ Sentry (معطّل بدون DSN)؛ CI يشغّل الاختبارات. |
| **v3.0.2** | `PosApiController::checkout()` أعاد استخدام `CheckoutService` — مسار الويب و API صارا واحدًا. |
| **v3.0.3** (2026-09-27) | **أمن**: إصلاح انتهاء توكن Sanctum، إلزام `pos-access`، حماية `super-admin`، `hash_equals` لتوقيع Midtrans، rate-limit للبوابة العامة، `X-Service-Token` للواتساب، HSTS و CSP-Report-Only. |

---

## 3) طبقة التوافق القديمة — كيف كانت تعمل (محذوفة الآن)

| الآلية القديمة | ما كان يفعل | الحالة |
| --- | --- | --- |
| `OutletAccessService::legacySingleOutletBypass()` | يسمح بالمرور إن كان الفرع النشط واحدًا فقط | ❌ محذوفة — الإسناد عبر `user_outlets` إلزامي دائمًا |
| `SECURITY_LEGACY_SINGLE_OUTLET_BYPASS` | مفتاح تفعيل/تعطيل التجاوز | ❌ محذوف من `config/security.php` و `.env.example` |
| `canUseWarehouse()` تقبل مستودعًا `null` | توافق مع ورديات بلا مستودع | ❌ ترفض أي مستودع غير مرتبط بفرع نشط مُسند |
| `warehousesFor()` بلا فلترة لمن لا إسناد له | إرجاع كل المستودعات | ❌ تُرجع مجموعة فارغة |
| `$includeLegacy` / `orWhereNull('warehouse_id')` في ١٤ ملفًا | إظهار السجلات القديمة بلا فرع | ❌ محذوفة |
| `Setting::getForOutlet()` بحماية `Schema::hasColumn` | توافق مع مخطط قديم | ✅ الدالة باقية بلا حماية مخطط |
| `PaymentSetting::forOutlet()` / `BankAccount::scopeForOutlet()` | عام + خاص بالفرع | ✅ باقية كتصميم مقصود |
| `outlet:legacy-audit` | تصنيف السجلات القديمة + `--backfill` | ❌ محذوف |
| `inventory:reconcile` | مطابقة `products.stock` مع الـ pivot | ❌ محذوف — العمود نفسه لم يبقَ |
| `products.stock` كإجمالي عام | مصدر ثانٍ للمخزون | ❌ أُسقط العمود |

---

## 4) الحالة الحالية بعد إعادة البناء

### المخزون
- `product_warehouse` هو **المصدر الوحيد**؛ لا وجود لعمود `products.stock`.
- `Product::stock` accessor للقراءة فقط يجمع كل صفوف الـ pivot.
- كل حركة مخزون تكتب على صف الـ pivot المقفول `lockForUpdate()`.

### الوصول للفروع
- `canUseWarehouse()`: المستودع نشط + مرتبط بفرع نشط + مُسند للمستخدم (أو المستخدم super-admin).
- `canSellAtWarehouse()`: كل ما سبق + `outlet.is_sales_enabled = true`.
- الوردية تتطلب `warehouse_id` صريحًا.

### الإعدادات
- `outlet_id = NULL` يعني «قيمة افتراضية مشتركة»، وصف الفرع يتجاوزها — تصميم مقصود لا تراث.

### أوامر الصيانة
- `php artisan outlet:audit [--strict]` يتحقق من: كل مستودع مرتبط بفرع، كل مستخدم مُسند لفرع،
  كل فرع بيع لديه مستودع نشط، `PUSAT` غير مفعّل للبيع.

---

## 5) عند الترقية من تثبيت قديم

```bash
php artisan migrate
php artisan outlet:audit --strict
```

migration `2026_10_28_000002_backfill_outlet_scope` تعالج البيانات القديمة تلقائيًا:
- تنشئ فرع `PUSAT` إن لم يوجد وتربط به المستودعات اليتيمة.
- تُسند كل مستخدم بلا فرع إلى `PUSAT`.
- تعبّئ `warehouse_id` الفارغ في جداول المعاملات والمخزون بـ `PUSAT`.
- لا تحذف أي شيء، وقابلة للتشغيل أكثر من مرة.

> **تنبيه:** بعد الترقية لن يعمل أي مستخدم بلا إسناد فرع.
