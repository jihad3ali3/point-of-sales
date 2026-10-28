# الفروق بين الإصدارات وطبقة التوافق (شرح عربي)

> مرجع مبني على الكود الفعلي في هذا المستودع + `CHANGELOG.md` + `docs/*`.
> الحالة الحالية للمستودع: `APP_VERSION=v3.0.3` (`config/app.php` + `.env.example`).

---

## 1) هل كان المشروع يدعم مستودعًا واحدًا وفرعًا واحدًا؟

**نعم.** قبل `v3.0.0` كان النظام يعمل بـ «مستودع واحد + فرع واحد» فعليًا:

- `products.stock` كان هو المخزون الوحيد (عمود واحد على المنتج).
- `cashier_shifts.warehouse_id` و `transactions.warehouse_id` كانا **nullable** وغير إلزاميين.
- `settings` / `payment_settings` / `bank_accounts` كانت صفوفًا **عامة** بلا `outlet_id`.
- لا توجد جداول `outlets` ولا `user_outlets` ولا صلاحيات مرتبطة بفرع.

ابتداءً من `v3.0.0` (تغيير **Breaking**) أصبح «الفرع/المستودع» حدًّا أمنيًا وتشغيليًا:

- جدول `outlets` + `warehouses.outlet_id` + `user_outlets`.
- `PUSAT` = مستودع مركزي **غير مفعّل للبيع**، والبيع فقط من مستودعات الفروع.
- معظم البيانات التشغيلية (فواتير، مخزون، ورديات، مشتريات، مرتجعات، تقارير، إعدادات) أصبحت مقيّدة بالفرع.
- `php artisan outlet:audit --strict` صار بوابة إصدار.

---

## 2) ما الذي اختلف بين الإصدار الحالي `v3.0.3` والإصدار السابق `v2.11.0`

| الإصدار | أهم ما تغيّر |
| --- | --- |
| **v3.0.0** (2026-09-18) | **Breaking**: تعدد الفروع `outlets` + `user_outlets` + `OutletAccessService`، تبديل الفرع من الـ navbar، إعدادات لكل فرع مع fallback عام، تقييد العمليات والتقارير بالفرع، أوامر `outlet:audit` / `outlet:legacy-audit`، فصل بذور العرض (`seed:demo`) عن التثبيت الإنتاجي (`migrate --seed` ينشئ `PUSAT` فقط). |
| **v3.0.1** | استخراج منطق الـ checkout (~250 سطر) من `TransactionController` إلى `CheckoutService` + `CheckoutContext` DTO؛ إضافة Sentry (معطّل بدون DSN)؛ CI يشغّل `php artisan test`؛ فهرس `transactions (warehouse_id, created_at)`. |
| **v3.0.2** | `PosApiController::checkout()` أعيد استخدامه لـ `CheckoutService` بدل تكرار المنطق — مسار الويب و API صارا واحدًا. |
| **v3.0.3** (2026-09-27) | **أمن**: إصلاح انتهاء صلاحية توكن Sanctum (كان `expiration` معرّفًا مرتين فيصبح `null`)، إلزام ability `pos-access` على `/api/v1/pos/*`، منع المستخدم من حذف حسابه أو حذف آخر super-admin، منع تعديل/حذف دور `super-admin`، مقارنة توقيع Midtrans بـ `hash_equals`، rate-limit لمسارات البوابة العامة + `Cache-Control: no-store`، `X-Service-Token` لخدمة واتساب + ربطها بـ `127.0.0.1`، HSTS و CSP-Report-Only في الإنتاج، وتحويل تجاوز الفرع الواحد القديم إلى علم `SECURITY_LEGACY_SINGLE_OUTLET_BYPASS`. |

باختصار: **v3.0.0 = تحوّل معماري إلى تعدد الفروع**، و**v3.0.1/3.0.2 = إعادة هيكلة (Refactor) لمسار البيع**، و**v3.0.3 = تشديد أمني**.

كذلك (ضمن `Unreleased`): تعطيل التحقق من البريد بالكامل (لا `MustVerifyEmail`، لا middleware `verified`)، إضافة دور `manager` وحساب `manager@gmail.com` مقيّد بفرعي `MAL` و `TKB`، وتوسيع بيانات العرض في `DemoSeeder`.

---

## 3) الدوال/الآليات التي تعمل من أجل التوافق بين الإصدارات

هذه هي «طبقة التوافق» التي تُبقي التثبيتات القديمة (فرع واحد) تعمل بعد الترقية:

### أ. في `app/Services/OutletAccessService.php`

| الدالة | كيف تعمل للتوافق |
| --- | --- |
| `legacySingleOutletBypass()` (private) | ترجع `true` فقط إذا كان `config('security.outlet.legacy_single_outlet_bypass')` مفعّلًا **و** عدد الفروع النشطة `<= 1`. |
| `canUseWarehouse(User, ?Warehouse)` | إن كان المستودع `null` → تسمح بالمرور عبر الـ bypass. وإن كان المستخدم بلا أي إسناد فرع (`user_outlets` فارغ) → تسمح أيضًا عبر الـ bypass (تثبيتات قديمة). |
| `canSellAtWarehouse(User, ?Warehouse)` | تقبل المستودع الذي بلا `outlet_id` (مستودعات قديمة) عبر الـ bypass، وإلا تشترط `outlet.is_sales_enabled`. |
| `salesWarehousesFor(User)` | تُرجع المستودعات القديمة بلا فرع فقط عند تفعيل الـ bypass. |
| `warehousesFor(User)` | إن كان المستخدم بلا إسناد فرع **لا تفلتر** بـ `outlet_id` (ترجع كل المستودعات النشطة) — سلوك الفرع الواحد القديم. |
| `defaultOutlet()` / `accessibleOutlets()` | ترجعان `null`/مجموعة فارغة بأمان عند عدم وجود فروع، فلا تتعطل الصفحات القديمة. |
| `activeOutlet(Request)` | أولوية: فرع الوردية المفتوحة → `session('active_outlet_id')` → أول فرع متاح → `null` (تعمل بدون أي فرع). |

### ب. في `config/security.php` و `.env`

```php
'outlet' => [
    'legacy_single_outlet_bypass' => env('SECURITY_LEGACY_SINGLE_OUTLET_BYPASS', true),
],
```

- القيمة الافتراضية `true` للحفاظ على سلوك ما قبل v3.0.0.
- على تثبيت متعدد الفروع يجب ضبطها `false` لفرض الإسناد الصريح.
- يوجد اختبار يغطي الإيقاف: `tests/Feature/Security/PhaseTwoSecurityHardeningTest.php::test_legacy_single_outlet_bypass_can_be_disabled`.

### ج. دوال الإعدادات: قيمة الفرع أولًا ثم fallback عام

| الدالة | الملف | السلوك |
| --- | --- | --- |
| `Setting::get()` | `app/Models/Setting.php` | يقرأ الصف العام (`outlet_id IS NULL`) فقط، ويتجاهل عمود `outlet_id` إن لم يكن موجودًا (`Schema::hasColumn`) — توافق مع مخطط قديم. |
| `Setting::getForOutlet()` / `getBoolForOutlet()` | نفس الملف | إن وُجد صف للفرع أعاده، وإلا رجع للقيمة العامة، وإن لم يوجد عمود `outlet_id` رجع لـ `get()`. |
| `Setting::setForOutlet()` | نفس الملف | بدون فرع أو بدون عمود → كتابة عامة (`set()`)، أي لا كسر للتثبيت القديم. |
| `PaymentSetting::forOutlet()` | `app/Models/PaymentSetting.php` | إن لم يوجد صف للفرع → الصف العام `outlet_id IS NULL`. |
| `PaymentSetting::forOutletOrCreate()` | نفس الملف | ينسخ (`replicate`) إعدادات البوابة العامة إلى صف خاص بالفرع عند أول استخدام. |
| `BankAccount::scopeForOutlet()` | `app/Models/BankAccount.php` | يعيد الحسابات العامة + حسابات الفرع (`whereNull OR outlet_id = ?`) — الحسابات القديمة تظل ظاهرة. |

### د. fallback على مستوى البيانات التشغيلية (سجلات `warehouse_id = NULL`)

| المكان | كيف يعمل |
| --- | --- |
| `TransactionController::index()` | يفلتر بالمستودعات المتاحة، **ويضيف `orWhereNull('warehouse_id')`** عندما `Outlet::active()->count() <= 1`. |
| `DashboardController::index()` | `$includeLegacy = Outlet::active()->count() <= 1` ويُستخدم في `$scope()` (معاملات/مبيعات) وفي استعلام «مخزون منخفض» (`COALESCE(products.stock)`). |
| `HandleInertiaRequests` | نفس `$includeLegacy` لعدّاد موافقات الخصم وإشعارات المخزون المنخفض، مع `COALESCE(warehouse_totals.warehouse_stock, products.stock)`. |
| `AdvancedSalesInsightsController` | `'include_legacy' => Outlet::active()->count() <= 1` في التقارير (ساعات، أداء الكاشير، العملاء المتكررون، الحملات). |
| `CashierShiftService::visibleToUser()` | من يملك `cashier-shifts-force-close` يرى كل الورديات إذا كان الفرع النشط واحدًا فقط؛ وإلا تُقيَّد بمستودعاته. |
| `CashierShiftService::openShift(..., ?int $warehouseId = null)` | `warehouse_id` اختياري → ورديات قديمة بلا مستودع ما زالت قابلة للإنشاء/القراءة. |
| `Product::stockTotal()` / `isLowStock()` / `suggestedOrderQty()` | تعمل على pivot `product_warehouse`، و`products.stock` يبقى الإجمالي القديم (`COALESCE` في الاستعلامات). |
| `PriceListService`, `PricingService`, `LoyaltyService` (voucher) | استعلام `whereNull('outlet_id') OR outlet_id = ?` + `orderByDesc('outlet_id')` → أولوية لصف الفرع ثم العام. |
| `CrmCampaignController`, `CustomerVoucherController`, `DineAreaController`, `DineTableController`, `PricingRuleController` | نفس نمط «العام + الخاص بالفرع». |

### هـ. المخطط (Migrations) والأوامر

- كل migration خاصة بالفروع محميّة بـ `Schema::hasColumn` / `Schema::hasTable` (19 موضعًا) → قابلة للتشغيل على قاعدة بيانات نصف مرقّاة دون أخطاء، و`down()` يعيد الحالة السابقة.
- `2026_09_14_000002_add_outlet_id_to_warehouses_table.php` ينشئ فرع `PUSAT` ويربط مستودع `PUSAT` به تلقائيًا (تعبئة رجعية).
- `2026_09_14_000003_create_user_outlets_table.php` يسند كل مستخدم موجود إلى `PUSAT` كفرع افتراضي.
- `php artisan outlet:audit [--strict]` — قراءة فقط، يحصي المستودعات بلا فرع، المستخدمين بلا إسناد، سجلات `warehouse_id IS NULL`، وفروق المخزون.
- `php artisan outlet:legacy-audit [--backfill]` — تصنيف السجلات القديمة؛ `--backfill` يملأ `stock_mutations.warehouse_id` **فقط** من المرجع غير الغامض، ويطبع عدد «ambiguous».
- `php artisan inventory:reconcile [--fix]` — يقارن `products.stock` بمجموع `product_warehouse.stock`؛ `--fix` يجعل الـ pivot هو المصدر.

### و. على مستوى API

- `CheckoutContext` DTO + `CheckoutService` (v3.0.1/3.0.2) جعلا الويب و`/api/v1/pos/checkout` يمران بنفس المسار — أي توافق سلوكي بين الواجهتين.
- `PotApiController` ما زال يقبل `warehouse_id` من الطلب لكن يتحقق منه عبر `OutletAccessService::canSellAtWarehouse` (v3.0.0+).
- v3.0.3 أضاف ability `pos-access`: التوكنات القديمة (التي تحمل `user:read` فقط) **تحتاج إعادة تسجيل دخول** للحصول عليها.

---

## 4) ملاحظات وثائقية مهمة

1. `docs/features/app-versioning.md` مكتوب فيه **«Rilis saat ini: v2.10.5»** في حين أن `config/app.php` و `.env.example` على `v3.0.3` → الملف قديم ويحتاج تحديثًا.
2. `docs/multi-outlet.md` يذكر أن الـ fallback العام مخصّص للتثبيتات القديمة، لكنه **لا يوثّق** اسم المتغيّر `SECURITY_LEGACY_SINGLE_OUTLET_BYPASS` ولا قيمته الافتراضية `true` (وهي مهمة أمنيًا: تعني أن أي مستخدم بلا إسناد فرع يمرّ ما دام فرع نشط واحد).
3. الـ bypass مرتبط بعدد **الفروع النشطة**، لا بعدد المستودعات؛ فمستودع ثانٍ داخل نفس الفرع لا يُعطّله.
4. `products.stock` ما زال يُحدَّث بجانب الـ pivot في `GoodsReceivingService` و `StockTransferService` و `StockOpnameController` (تعليقات `// Increment legacy stock`) → إبقاء تقارير قديمة دقيقة.

---

## 5) قائمة تحقق مختصرة عند الترقية من v2.x إلى v3.0.3

```bash
php artisan migrate --pretend && php artisan migrate
php artisan outlet:audit
php artisan outlet:legacy-audit
php artisan inventory:reconcile
# بعد الموافقة على التصنيف فقط:
# php artisan outlet:legacy-audit --backfill
# php artisan inventory:reconcile --fix
php artisan outlet:audit --strict   # يجب أن ينجح قبل تفعيل فرع بيع ثانٍ
```

وتأكيدات الإعداد:

- `SECURITY_LEGACY_SINGLE_OUTLET_BYPASS=false` عند تعدد الفروع.
- `SANCTUM_TOKEN_EXPIRATION` مضبوط (الافتراضي 1440 دقيقة).
- إعادة تسجيل دخول عملاء API للحصول على ability `pos-access`.
- `WA_SERVICE_TOKEN` مضبوط (خدمة واتساب ترفض الطلبات بدونه).
