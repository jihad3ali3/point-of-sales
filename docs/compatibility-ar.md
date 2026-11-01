# إزالة المنطق القديم — المستند الشامل الواحد

> **هذا هو المستند المرجعي الوحيد** لكل ما يتعلق بتحويل المشروع من «سلسلة إصدارات متراكمة»
> إلى «نظام مبني من الصفر مرة واحدة».
> يحتوي على: ما نُفّذ بالتفصيل الكامل (ملفًا ملفًا)، وما تبقّى (خطوة خطوة بأوامر جاهزة)،
> وكيف تتحقق من كل شيء، وكيف تُرقّي تثبيتًا قائمًا، وما هي المخاطر.
>
> **الفرع:** `arena/3e4b16f1-point-of-sales`
> **نقطة الانطلاق:** `69b9364` (آخر كوميت على `main` قبل العمل)
> **PR:** [#1](https://github.com/jihad3ali3/point-of-sales/pull/1) — مفتوح
> **آخر تحديث للمستند:** كوميت `docs(ar)` التالي

---

# جدول المحتويات

| # | القسم |
| --- | --- |
| ١ | الأرقام الكلية بنظرة واحدة |
| ٢ | المشكلة: كيف كان المشروع «متراكمًا» |
| ٣ | النموذج المستهدف — المبادئ الستة |
| ٤ | ما نُفّذ — تفصيل كامل ملفًا ملفًا |
| ٥ | ما تبقّى — خطة تنفيذ بست خطوات |
| ٦ | كيفية التحقق (Definition of Done) |
| ٧ | دليل الترقية لتثبيت قائم |
| ٨ | المخاطر والمحاذير |
| ٩ | المراجع والملفات المرتبطة |
| ١٠ | سجل تاريخي كامل للإصدارات |
| أ | ملحق: كيف استُخرج كل رقم (أوامر قابلة لإعادة التنفيذ) |
| ب | ملحق: خريطة الملفات — ماذا تغيّر في كل ملف (١٠٧ ملفًا) |
| ج | ملحق: أسئلة متوقعة |

---

# كيف تقرأ هذا المستند

| إذا كنت… | ابدأ من |
| --- | --- |
| تريد صورة سريعة | القسم ١ (جدول الحالة) |
| تراجع الكود المرفوع | القسم ٤ + الملحق ب |
| ستُكمل العمل | القسم ٥ (الخطوات الست) |
| تُرقّي تثبيتًا قائمًا | القسم ٧ |
| تريد تحققًا سريعًا | القسم ٦ (Definition of Done) |
| تشك في رقم | الملحق أ (أوامر الاستخراج) |

---

# ١) الأرقام الكلية بنظرة واحدة

## ١.١ حالة التنفيذ

| المجال | الحالة | العدد |
| --- | --- | --- |
| تجاوز الفرع الواحد (`legacy_single_outlet_bypass`) | ✅ منفّذ | محفّز + دالة + اختبار |
| حالات `Outlet::active()->count() <= 1` | ✅ منفّذ | ١٦ موضعًا في ١٥ ملفًا |
| `$includeLegacy` / `include_legacy` | ✅ منفّذ | ٧ ملفات |
| `orWhereNull(...)` كبديل توافق (فرع/مستودع) | ✅ منفّذ | ١١ موضعًا |
| أوامر ترقية قديمة | ✅ منفّذ | ٢ أمر محذوف + ١ مُعاد بناؤه |
| عمود `products.stock` | ✅ منفّذ | أُسقط + ٢٧ ملفًا يتعامل مع المخزون |
| مسار ترقية لتثبيت قائم | ✅ منفّذ | 1 migration جديدة |
| استعلامات تقرأ العمود المحذوف | ✅ منفّذ | ٩ ملفات (كانت ستُسقط التشغيل) |
| مصانع الاختبارات | ✅ منفّذ | 3 factories + 4 helpers |
| ربط المخزون بالـ pivot في الاختبارات | ⏳ **متبقٍ** | **٣٠ ملفًا / ٥٣ موضعًا** |
| `NOT NULL` على `warehouse_id` / `outlet_id` | ⏳ **متبقٍ** | **١٨ عمودًا / ١٨ جدولًا** |
| دمج migrations الإضافية | ⏳ **متبقٍ** | **٣٨ `add_*` من ١٠٢** |
| المسارات المزدوجة | ⏳ **متبقٍ** | ٥ مواضع |
| حراس `Schema::has*` في التشغيل | ⏳ **متبقٍ** | ٦ مواضع في ٥ ملفات |

## ١.٢ حجم العمل المنفّذ

```
11 كوميتًا
107 ملفًا متغيّرًا
+1201 سطرًا مضافًا
-972 سطرًا محذوفًا
```

**التوزيع حسب النوع:**

| النوع | العدد |
| --- | --- |
| ملفات تطبيق (`app/`) | ٣٧ |
| ملفات قاعدة بيانات (`database/`) | ١١ |
| ملفات اختبار (`tests/`) | ٥٠ |
| توثيق (`docs/`) | ٦ |
| توثيق جذري (`AGENTS.md`, `CHANGELOG.md`) | ٢ |
| إعداد (`config/`) | ١ |
| **الإجمالي** | **١٠٧** |
| منها محذوفة نهائيًا | ٣ |

**ملاحظة مهمة:** ملفات الاختبار الـ ٤٩ عُدّلت كلها تعديلًا ميكانيكيًا واحدًا
(`Warehouse::create([...])` → `Warehouse::factory()->create([...])`)، وليست إعادة كتابة.

---

# ٢) المشكلة: كيف كان المشروع «متراكمًا»

## ٢.١ المشهد قبل العمل

المشروع تطوّر عبر إصدارات، وكل إصدار أضاف طبقة فوق سابقه بدل تعديله:

```
v2.x   مستودع واحد + فرع واحد
        └── products.stock هو المخزون الوحيد
        └── warehouse_id nullable في كل الجداول
        └── الإعدادات صفوف عامة بلا outlet_id

v3.0.0 تعدد الفروع (Breaking)
        └── أُضيف outlets + user_outlets + OutletAccessService
        └── لكن أُبقي legacy_single_outlet_bypass للتوافق  ← الدَّين يبدأ هنا
        └── كل استعلام أُضيف له «أو السجلات بلا فرع»

v3.0.1 استخراج CheckoutService

v3.0.2 توحيد مسار الـ API

v3.0.3 تشديد أمني
```

## ٢.٢ مظاهر التراكم — الأشكال الخمسة للدَّين التقني

### الشكل ١ — علم بيئة يعطّل قاعدة أمنية

```php
// config/security.php (محذوف الآن)
'outlet' => [
    'legacy_single_outlet_bypass' => env('SECURITY_LEGACY_SINGLE_OUTLET_BYPASS', true),
],
```

القيمة الافتراضية `true` تعني: **على كل تثبيت جديد، أي مستخدم بلا إسناد فرع يمرّ**
ما دام هناك فرع نشط واحد. قاعدة أمنية معطّلة افتراضيًا.

### الشكل ٢ — شرط عددي مخفي في كل مكان

```php
// تكرّر في ١٦ موضعًا
if (Outlet::active()->count() <= 1) {
    $query->orWhereNull('warehouse_id');
}
```

هذا يجعل سلوك الاستعلام يعتمد على **عدد الصفوف في جدول آخر**. إضافة فرع ثانٍ
تغيّر نتائج التقارير بأثر رجعي.

### الشكل ٣ — عمودان لنفس الحقيقة

`products.stock` و `product_warehouse.stock` يمثّلان المخزون نفسه، ويجب مزامنتهما
يدويًا في كل عملية (`GoodsReceivingService`, `StockTransferService`, `StockOpnameController`…).
أي سهو في موضع واحد يُنتج تقارير متناقضة.

### الشكل ٤ — مسارات متوازية للمنطق نفسه

```php
// TransactionController.php:630 (ما زال موجودًا)
// ponytail: legacy single-method path kept — split tenders only activate when payload has tenders[]
$isCashPayment = empty($paymentGateway) && ! $isPayLater && ! $useTenders;
```

مسار للدفع المفرد ومسار للتقسيم، ومنطق ثالث يقرّر أيّهما.

### الشكل ٥ — أوامر صيانة تعالج الماضي

`outlet:legacy-audit [--backfill]` و `inventory:reconcile [--fix]`: أدوات يعمل وجودها
على إبقاء البيانات القديمة «مقبولة» بدل إصلاحها مرة واحدة.

---

# ٣) النموذج المستهدف — المبادئ الستة

كل تعديل في هذا المستند يخدم واحدًا من هذه المبادئ. استخدمها كمرجع عند أي قرار مستقبلي.

## المبدأ ١ — الفرع حدّ أمني إلزامي، بلا مسار ضمني

```php
// app/Services/OutletAccessService.php — الحالة الحالية
public function canUseWarehouse(User $user, ?Warehouse $warehouse): bool
{
    if (! $warehouse || ! $warehouse->is_active || ! $warehouse->outlet_id) {
        return false;                          // ← لا bypass، لا استثناء
    }

    if ($user->isSuperAdmin()) {
        return true;
    }

    $outlet = $warehouse->relationLoaded('outlet')
        ? $warehouse->outlet
        : Outlet::find($warehouse->outlet_id);

    if (! $outlet || ! $outlet->is_active) {
        return false;
    }

    return $user->outlets()
        ->where('outlets.is_active', true)
        ->where('outlets.id', $warehouse->outlet_id)
        ->exists();
}
```

**٣ شروط متتالية:** المستودع موجود ونشط ← له فرع ← الفرع نشط ومُسند للمستخدم.

## المبدأ ٢ — المخزون في مكان واحد فقط

```php
// app/Models/Product.php — قراءة مشتقّة، بلا عمود
public function stockTotal(): int
{
    if ($this->relationLoaded('warehouses')) {
        return (int) $this->warehouses->sum(fn ($w) => (int) $w->pivot->stock);
    }

    return (int) $this->warehouses()->sum('product_warehouse.stock');
}

protected function stock(): Attribute
{
    return Attribute::make(get: fn () => $this->stockTotal());
}
```

`$product->stock` ما زالت تعمل في الكود القائم، لكنها **لم تعد عمودًا** ولا تُكتب.

## المبدأ ٣ — كل سجل تشغيلي له فرع ومستودع

تُفرض في المخطط (`NOT NULL`) لا في الكود فقط. **هذا هو الجزء المتبقي الأهم.**

## المبدأ ٤ — لا قواعد تعتمد على «عدد الفروع»

القرار يجب أن يكون صريحًا وقابلًا للتدقيق، لا مشتقًا من `count()`.

## المبدأ ٥ — القيمة العامة إعداد مشترك، لا تراث

`outlet_id = NULL` في جداول الإعدادات والتسعير تعني **«قيمة افتراضية مشتركة يتجاوزها الفرع»**.

| المكان | الدلالة |
| --- | --- |
| `settings.outlet_id = NULL` | إعداد عام؛ صف الفرع يتجاوزه |
| `payment_settings.outlet_id = NULL` | إعداد بوابة عام؛ يُنسخ للفرع عند أول استخدام |
| `bank_accounts.outlet_id = NULL` | حساب بنكي مشترك يظهر لكل الفروع |
| `price_lists` / `pricing_rules` | قاعدة سعرية عامة؛ الأولوية لصف الفرع |
| `customer_vouchers` / `customer_campaigns` | عرض عام لكل الفروع |
| `dine_areas` | منطقة مشتركة |

**هذا يبقى مقصودًا.** لا يُعامَل كـ legacy، بل يُوثّق كتصميم.

## المبدأ ٦ — مسار واحد لكل عملية

لا `if ($legacy) { ... }` في طبقة التشغيل. أي حالة كهذه = دَين يجب سداده.

---

# ٤) ما نُفّذ — تفصيل كامل ملفًا ملفًا

## ٤.١ الكوميتات (11 كوميتًا)

| # | الكوميت | العنوان | الحجم |
| --- | --- | --- | --- |
| ١ | `622fe64` | `docs: add Arabic compatibility guide...` | +138 |
| ٢ | `ab97375` | `refactor(outlet): drop legacy single-outlet runtime compatibility` | 25 ملفًا، +150/−655 |
| ٣ | `3f91762` | `docs(changelog): record removal of the legacy outlet...` | +11 |
| ٤ | `35221b7` | `refactor(outlet): require an explicit warehouse when opening a cashier shift` | ملفان |
| ٥ | `3592d27` | `docs: align multi-outlet guidance with the removed legacy commands` | 6 +/−13 |
| ٦ | `748628d` | `refactor(inventory): make product_warehouse the only stock record` | 21 ملفًا، +211/−172 |
| ٧ | `b38905a` | `refactor(outlet): backfill legacy data on upgrade and align docs` | 20 ملفًا، +241/−120 |
| ٨ | `01fcca7` | `test(fixtures): give warehouses and users a real outlet` | 50 ملفًا، +203/−71 |
| ٩ | `439d720` | `fix(inventory): stop querying the dropped products.stock column` | 8 ملفات، +50/−29 |
| ١٠ | `ee2db8c` | `fix(api): route product stock through the warehouse pivot` | ملف واحد |
| ١١ | `ec012e8` | `docs(ar): rewrite the legacy-removal guide as an end-to-end roadmap` | +322/−54 |

---

## ٤.٢ المرحلة ١ — تشديد طبقة التشغيل (الكوميتات ٢، ٣، ٤)

### أ) `app/Services/OutletAccessService.php` — إعادة كتابة كاملة

**قبل:**

```php
public function canUseWarehouse(User $user, ?Warehouse $warehouse): bool
{
    if (! $warehouse) {
        return $this->legacySingleOutletBypass();      // ← المستودع الفارغ مسموح
    }
    if (! $warehouse->is_active) {
        return false;
    }
    if ($user->isSuperAdmin()) {
        return true;
    }
    $outletIds = $user->outlets()->pluck('outlets.id');
    if ($outletIds->isEmpty()) {
        return $this->legacySingleOutletBypass();      // ← بلا إسناد = مسموح
    }

    return (bool) ($warehouse->outlet_id && $outletIds->contains($warehouse->outlet_id));
}

private function legacySingleOutletBypass(): bool
{
    return config('security.outlet.legacy_single_outlet_bypass', true)
        && Outlet::active()->count() <= 1;
}
```

**بعد:** (انظر المبدأ ١) — ٣ شروط، ثم `false` في كل مسار آخر.

| الدالة | التغيير |
| --- | --- |
| `legacySingleOutletBypass()` | ❌ **حُذفت** |
| `canUseWarehouse()` | ترفض `null` / غير نشط / بلا فرع؛ الإسناد إلزامي |
| `canSellAtWarehouse()` | تُبني على السابقة + `outlet.is_sales_enabled` |
| `warehousesFor()` | تُرجع `new Collection` فارغة لمن لا إسناد (بدل كل المستودعات)؛ وتفلتر `whereNotNull('outlet_id')` |
| `salesWarehousesFor()` | تعتمد على `outlet->is_sales_enabled` بدل فحص `null` |
| `activeOutlet()` | سقوط مباشر إلى `accessibleOutlets()` بلا مسار احتياطي إضافي |
| `hasWarehouseHistory()` | حُذفت حراس `Schema::has*` من الحلقة |

### ب) `config/security.php` — حذف مجموعة كاملة

```diff
-    'outlet' => [
-        // Legacy single-outlet installs have no user/outlet assignments...
-        'legacy_single_outlet_bypass' => env('SECURITY_LEGACY_SINGLE_OUTLET_BYPASS', true),
-    ],
```

### ج) `.env.example` — حذف المتغيّر المقابل

### د) الملفات الـ ١٥ التي كانت تحتوي الشكل ٢

| الملف | الموضع | ما حُذف |
| --- | --- | --- |
| `DashboardController.php` | `index()` | `$includeLegacy` + `$scope()` المبني عليه |
| `Components` → `HandleInertiaRequests.php` | `share()` | عدّاد الموافقات + إشعار المخزون |
| `Apps/TransactionController.php` | `index()` | `orWhereNull('warehouse_id')` |
| `Apps/StockMutationController.php` | `index()` | نفس النمط |
| `Apps/DiscountApprovalController.php` | `pending()` | نفس النمط |
| `Apps/CrmReminderController.php` | `index()` | `orWhereNull('outlet_id')` |
| `Apps/DineOrderController.php` | `index()` + `ensureOutletAccess()` | نفس النمط + تسامح `null` |
| `Apps/PayableController.php` | `scopeQuery()` | `orWhereDoesntHave('purchaseOrder')` |
| `Apps/CashierShiftController.php` | `visibleCashiers()` | إرجاع كل المستخدمين |
| `Services/CashierShiftService.php` | `visibleToUser()` | إرجاع كل الورديات |
| `Services/PayableAgingService.php` | `scopeQuery()` + `singleOutlet()` | الدالة كاملة |
| `Services/ReceivableService.php` | `scopeQuery()` + `singleOutlet()` | الدالة كاملة |
| `Apps/ImportExportController.php` | `exportTransactions()` | تمرير `$includeLegacy` |
| `Reports/AdvancedSalesInsightsController.php` | `index()` | `'include_legacy'` + ٤ استعلامات |
| `Services/OutletAccessService.php` | `legacySingleOutletBypass()` | الدالة كاملة (الشكل ١) |

**ملف منفصل (ليس ضمن الـ ١٥):**

| الملف | الموضع | ما حُذف |
| --- | --- | --- |
| `Exports/TransactionsExport.php` | `__construct()` | معامل `$includeLegacy` + فرعاه |

### هـ) `CashierShiftService::openShift()` — المستودع إلزامي

```diff
-public function openShift(User $cashier, User $actor, int $openingCash,
-                           ?string $notes = null, ?int $warehouseId = null): CashierShift
+public function openShift(User $cashier, User $actor, int $openingCash,
+                           ?string $notes, int $warehouseId): CashierShift
```

بلا قيمة افتراضية ⇒ أي استدعاء ينسى المستودع يفشل وقت الترجمة، لا وقت التشغيل.

### و) حذف أمرين وإعادة بناء ثالث

| الأمر | الإجراء |
| --- | --- |
| `outlet:legacy-audit [--backfill]` | ❌ حُذف الملف + اختباره |
| `inventory:reconcile [--fix]` | ❌ حُذف الملف + اختباره |
| `outlet:audit [--strict]` | ✅ أُعيد بناؤه ليتحقق من **ثوابت التثبيت النظيف** |

**ما يتحقق منه `outlet:audit` الآن:**

| الفحص | معيار النجاح |
| --- | --- |
| مستودعات بلا فرع | ٠ |
| مستخدمون بلا إسناد | ٠ |
| فروع بيع بلا مستودع نشط | ٠ |
| `PUSAT` مفعّل للبيع؟ | لا |
| `settings` / `payment_settings` / `bank_accounts` | تقرير إحصائي فقط |

### ز) حذف اختبارات الطبقة القديمة

- `tests/Feature/Inventory/InventoryReconcileCommandTest.php` — حُذف بالكامل
- `PhaseTwoSecurityHardeningTest::test_legacy_single_outlet_bypass_can_be_disabled()` — حُذف
- `AuditOutletCommandTest` — حُذف منه اختبارا `legacy-audit`، وبقي اختباران

---

## ٤.٣ المرحلة ٢ — مصدر مخزون واحد (الكوميت ٦)

### أ) المبدأ: `product_warehouse` هو المصدر الوحيد

```
قبل:  products.stock  ←→  product_warehouse.stock    (مزامنة يدوية إلزامية)
بعد:            product_warehouse.stock              (الحقيقة الوحيدة)
                       ↑
                Product::stock (accessor للقراءة فقط)
```

### ب) `app/Models/Product.php`

```diff
  protected $casts = [
-     'stock' => 'integer',
  ];
  protected $fillable = [
-     'stock',
  ];
```

أُضيف:

| العنصر | الغرض |
| --- | --- |
| `scopeInStock(?int $warehouseId)` | منتجات لها مخزون > 0 (كل المستودعات أو مستودع محدد) |
| `scopeOutOfStock(?int $warehouseId)` | منتجات بلا مخزون |
| `stockTotal()` | مجموع الـ pivot (يستخدم العلاقة المحمّلة إن وُجدت) |
| `stock()` accessor | إبقاء `$product->stock` صالحًا للقراءة |

### ج) الملفات الـ ١٩ التي كانت تكتب على العمود

| الملف | العملية | ما تغيّر |
| --- | --- | --- |
| `Services/GoodsReceivingService.php` | استلام مشتريات | `stockBefore/After` من الـ pivot؛ حُذف `$product->increment('stock')` |
| `Services/StockTransferService.php` | إرسال / استلام / إلغاء | ٣ مواضع كتابة أُزيلت؛ كلها على الـ pivot |
| `Http/Controllers/Apps/StockOpnameController.php` | جرد | حُذف مسار «بلا مستودع»؛ الـ pivot للفرع فقط |
| `Services/SupplierReturnService.php` | مرتجع مورد | الفحص والخصم من صف الـ pivot المقفول |
| `Http/Controllers/Apps/SalesReturnController.php` | مرتجع بيع | `firstOrCreate` على مستودع الفاتورة |
| `Services/CheckoutService.php` | بيع | التوفر من `$pw->stock` المقفول (منتج + مكوّنات) |
| `Services/DineOrderService.php` | داين-إن | نفس المنطق |
| `Console/Commands/ExpirePendingTransactionsCommand.php` | إبطال دفعة | `restock()` على الـ pivot فقط |
| `Services/StockMutationService.php` | مخزون افتتاحي | يُقرأ من الـ pivot |
| `Imports/ProductsImport.php` | استيراد Excel | مخزون الافتتاح → مستودع المشغّل |
| `Http/Controllers/Api/ProductController.php` | API | `stock` → الـ pivot في create و update |
| `Http/Controllers/Apps/ProductController.php` | إنشاء منتج | `'stock'` أُزيل من `Product::create()` |
| `Exports/ProductsExport.php` | تصدير | يقرأ الـ accessor |
| `database/seeders/SampleDataSeeder.php` | بيانات عرض | `$initialStock` مُمرَّر إلى الـ pivot |
| `database/seeders/OperationalCoreSeeder.php` | مرتجع بيع | الـ pivot |
| `database/seeders/FeatureDemoSeeder.php` | عرض | حُذفت `syncGlobalStock()` بالكامل |
| `database/seeders/DatabaseSeeder.php` | تأسيس | لا يعبّئ من `products.stock` |
| `database/seeders/DemoDatabaseResetter.php` | تحقق | يفحص الـ pivot |
| `database/migrations/2024_06_13_131744_create_products_table.php` | المخطط | لا يُنشئ العمود |

### د) migration الإسقاط

**`2026_10_28_000001_drop_products_stock_column.php`**

```php
public function up(): void
{
    if (! Schema::hasColumn('products', 'stock')) {
        return;                       // تثبيت جديد: لا شيء
    }

    $pusatId = DB::table('warehouses')->where('code', 'PUSAT')->value('id');

    if ($pusatId !== null) {
        // كل منتج له مخزون قديم وبلا صف pivot ← يُنشأ له صف في PUSAT
        DB::table('products')
            ->where('stock', '>', 0)
            ->whereNotExists(...)     // لا صف pivot
            ->each(fn ($p) => DB::table('product_warehouse')->insert([...]));
    }

    Schema::table('products', fn (Blueprint $t) => $t->dropColumn('stock'));
}
```

**`down()`:** يُعيد العمود ويُعبّئه من مجموع الـ pivot — تراجع آمن.

---

## ٤.٤ المرحلة ٣ — مسار الترقية (الكوميت ٧)

### أ) `2026_10_28_000002_backfill_outlet_scope.php`

هذا الملف هو **البديل الآمن** لكل منطق التوافق المحذوف: بدل أن يتحمّل الكود البيانات
القديمة إلى الأبد، تُصلَح البيانات مرة واحدة.

| الخطوة | ماذا تفعل | لماذا |
| --- | --- | --- |
| `resolvePusatOutlet()` | تجد فرع `PUSAT` أو تنشئه | نقطة استناد لكل ما يلي |
| `resolvePusatWarehouse()` | تجد/تنشئ مستودع `PUSAT`، وتربطه بالفرع | المستودع المركزي |
| `assignUnassignedUsers()` | كل مستخدم بلا صف في `user_outlets` ← `PUSAT` (افتراضي) | الطبقة الصارمة ترفض غير المُسند |
| `adoptOrphanWarehouses()` | كل مستودع `outlet_id = NULL` ← `PUSAT` | لا مستودع يتيم |
| `backfillWarehouseColumns()` | ٩ جداول + `stock_transfers` (عمودان) | لا سجل بلا مستودع |

**خصائص الأمان:**
- `INSERT OR IGNORE` / `insertOrIgnore` ⇒ لا تكرار.
- لا `DELETE` ولا `UPDATE` مدمّر.
- محميّة بـ `Schema::hasTable` / `Schema::hasColumn` ⇒ آمنة على مخططات مختلفة.
- `down()` فارغة (إصلاح بيانات لا يُعكس).

### ب) `DatabaseSeeder` — إسناد الحسابات المتبقية

بعد إنشاء فرع `PUSAT`، يُسند إليه كل مستخدم بلا إسناد. النتيجة: `migrate --seed`
لا يُنتج **أبدًا** مستخدمًا ترفضه الطبقة الصارمة.

### ج) حذف آخر كتابة على العمود

`SampleDataSeeder` كان ما زال يفعل:

```php
$item['product']->update([
    'stock' => (int) $item['product']->warehouses()->sum('product_warehouse.stock'),
]);
```

حُذف.

### د) تنظيف الاستيرادات

حُذف `use App\Models\Outlet;` من ١٢ ملفًا بعد أن أصبح غير مستخدم.

---

## ٤.٥ المرحلة ٤ — بنية الاختبارات (الكوميت ٨)

### أ) لماذا كانت الاختبارات ستنهار

بعد تشديد الوصول، أصبح كل مستخدم في الاختبارات بلا فرع **مرفوضًا**، وكل مستودع
بلا `outlet_id` **غير قابل للاستخدام**. ومع `NOT NULL` قادمًا، يجب أن تُبنى
الـ fixtures بشكل صحيح.

### ب) `database/factories/OutletFactory.php` (جديد)

```php
public function definition(): array
{
    return [
        'code' => strtoupper(fake()->unique()->lexify('OUT-???')),
        'name' => fake()->company(),
        'is_active' => true,
        'is_sales_enabled' => true,     // افتراضيًا فرع بيع
        // ...
    ];
}

public function central(): static      // فرع مركزي لا يبيع
{
    return $this->state(fn () => [
        'code' => 'PUSAT', 'name' => 'Pusat', 'is_sales_enabled' => false,
    ]);
}
```

### ج) `database/factories/WarehouseFactory.php` (معدّل)

```php
'outlet_id' => fn () => $this->salesOutletId(),

private function salesOutletId(): int
{
    $existing = Outlet::query()
        ->where('is_active', true)
        ->where('is_sales_enabled', true)
        ->orderBy('code')
        ->value('id');

    return $existing ?? Outlet::factory()->create()->id;
}
```

**الفكرة:** أي `Warehouse::factory()->create()` في أي اختبار يحصل تلقائيًا على فرع بيع
صالح — سواء كان الاختبار قد أنشأ فرعًا أم لا.

### د) `database/factories/UserFactory.php` (معدّل)

```php
public function configure(): static
{
    return $this->afterCreating(function (User $user) {
        if ($user->outlets()->exists()) {
            return;                       // الاختبار أسنده صراحةً
        }

        $outlet = Outlet::query()
            ->where('is_active', true)
            ->orderByDesc('is_sales_enabled')   // يفضّل فرع بيع
            ->orderBy('code')
            ->first();

        if ($outlet) {
            $user->outlets()->syncWithoutDetaching([$outlet->id => ['is_default' => true]]);
        }
    });
}
```

### هـ) `tests/TestCase.php` — أربعة مساعدين

| المساعد | التوقيع | الغرض |
| --- | --- | --- |
| `defaultOutlet(bool $salesEnabled = true)` | `→ Outlet` | الفرع الافتراضي (يعيد استخدام الموجود) |
| `makeWarehouse(array $attrs = [], ?Outlet $outlet = null)` | `→ Warehouse` | مستودع صالح تلقائيًا |
| `stockProductAt(Warehouse $w, Product $p, int $qty)` | `→ void` | **ربط المخزون بالـ pivot** |
| `userInOutlet(?Outlet $o = null, array $attrs = [])` | `→ User` | مستخدم مُسند لفرع |

### و) التحويل الميكانيكي

```
٧١ موضعًا في ٤٦ ملفًا
Warehouse::create([...])  →  Warehouse::factory()->create([...])
```

هذا التغيير وحده كافٍ لضمان أن كل مستودع في الاختبارات له فرع بيع.

---

## ٤.٦ المرحلة ٥ — إصلاحات حرجة (الكوميتات ٩، ١٠)

> ⚠️ **هذا القسم هو الأهم عمليًا.** بعد إسقاط العمود بقيت استعلامات تقرأه،
> وكانت ستُفشل التشغيل بـ `SQLSTATE[42S22]: Column not found: products.stock`.

### أ) `Product` — إضافة scopes

| الـ scope | الاستخدام |
| --- | --- |
| `inStock($warehouseId = null)` | «المنتجات المتاحة للبيع» |
| `outOfStock($warehouseId = null)` | «المنتجات النافدة» |

### ب) المواضع الـ ٩ المُصلحة

| # | الملف | ما كان | ما صار |
| --- | --- | --- | --- |
| ١ | `Api/PosApiController.php` | `where('stock', '>', 0)` في فرع «بلا مستودع» | `->inStock($warehouseId)` |
| ٢ | `Apps/TransactionController.php` | نفس الشيء + `select(..., 'stock', ...)` | `->inStock($warehouseId)` + حذف العمود من `select` |
| ٣ | `DineMenuController.php` | `where('stock', '>', 0)` كبديل | `whereHas('warehouses', ...)` مع فلترة الفرع |
| ٤ | `NotificationController.php` | `where('stock', '<=', 0)` | `Product::outOfStock()` |
| ٥ | `Services/ReorderService.php` | `whereColumn('stock', '<=', 'min_stock')` | مقارنة على الـ pivot + ترتيب بمجموعه |
| ٦ | `Reports/AdvancedSalesInsightsController.php` | `COALESCE(ws.current_stock, products.stock)` × ٢ | `COALESCE(ws.current_stock, 0)` |
| ٧ | `database/seeders/DemoDatabaseResetter.php` | يقارن العمود بمجموع الـ pivot | يفحص صفوف الـ pivot السالبة |
| ٨ | `Http/Controllers/Api/ProductController.php` | `'stock' => ...` في `create` (يُتجاهل بصمت)، و`update` يُهمله | `$openingStock` → الـ pivot |
| ٩ | `Http/Controllers/Apps/ProductController.php` | `'stock'` في `Product::create()` | أُزيل |

### ج) إصلاح `Api/ProductController` بالتفصيل

```php
// create() — قبل
Product::create([...$validated, 'stock' => $validated['stock'] ?? 0, ...]);
// ← 'stock' لم يعد في $fillable ⇒ يُتجاهل بصمت ⇒ مخزون = 0 دائمًا

// بعد
$openingStock = (int) ($validated['stock'] ?? 0);
$product = Product::create([
    ...array_diff_key($validated, ['stock' => true, 'warehouse_id' => true]),
    // ...
]);
$product->warehouses()->syncWithoutDetaching([$warehouseId => ['stock' => $openingStock]]);
```

```php
// update() — قبل: التحديث يُهمل stock بصمت
$product->update($validated);

// بعد: يُوجَّه إلى الـ pivot
$stock = array_key_exists('stock', $validated) ? (int) $validated['stock'] : null;
unset($validated['stock']);
$product->update($validated);

if ($stock !== null) {
    $warehouse = $this->outletAccessService->warehousesFor($request->user())->first();
    if ($warehouse) {
        $product->warehouses()->syncWithoutDetaching([$warehouse->id => ['stock' => $stock]]);
    }
}
```

### د) ملاحظة دقيقة

`ProductBatch.stock` عمود **منفصل تمامًا** (مخزون الدفعة) ولم يُمسّ.
كل `where('stock', '>', 0)` على `ProductBatch` صحيح وباقٍ كما هو.

---

## ٤.٧ المرحلة ٦ — التوثيق (الكوميتات ١، ٣، ٥، ١١)

| الملف | التغيير |
| --- | --- |
| `CHANGELOG.md` | قسم `Removed` جديد + قسم `Migrations` |
| `docs/multi-outlet.md` | حذف المراجع للأوامر المحذوفة؛ توضيح عدم وجود bypass |
| `docs/features/inventory-stock.md` | `product_warehouse` مصدر وحيد |
| `docs/features/multi-warehouse.md` | لا fallback للعمود المحذوف |
| `docs/demo-data.md` | المخزون في الـ pivot مباشرة |
| `AGENTS.md` | حذف الأوامر المحذوفة |
| `docs/README.md` | رابط لهذا المستند |
| `docs/compatibility-ar.md` | هذا الملف |

---

# ٥) ما تبقّى — خطة تنفيذ بست خطوات

> **الترتيب إلزامي.** كل خطوة تُبقي النظام قابلًا للتشغيل، والتالية تعتمد على نجاحها.

---

## الخطوة ١ — ربط المخزون بالـ pivot في ٣٠ ملف اختبار

### المشكلة

الاختبارات ما زالت:

```php
$product = Product::create([
    'title' => 'Produk Test',
    'stock' => 50,          // ← لم يعد عمودًا، يُتجاهل بصمت
    // ...
]);
```

النتيجة: المنتج بمخزون **صفر**، فتفشل اختبارات البيع بـ «Stok tidak mencukupi».

### القائمة الكاملة (٣٠ ملفًا / ٥٣ موضعًا)

| # | الملف | عدد المواضع |
| --- | --- | --- |
| ١ | `tests/Feature/Api/ApiAuthorizationTest.php` | ١ |
| ٢ | `tests/Feature/Api/ProductApiTest.php` | ١ |
| ٣ | `tests/Feature/Api/SplitPaymentApiTest.php` | ٢ |
| ٤ | `tests/Feature/AuditLogs/AuditLogTest.php` | ٢ |
| ٥ | `tests/Feature/CashierShifts/CashierShiftTest.php` | ١ |
| ٦ | `tests/Feature/Console/ExpirePendingTransactionsTest.php` | ٧ |
| ٧ | `tests/Feature/Documents/PublicInvoiceAuthorizationTest.php` | ٢ |
| ٨ | `tests/Feature/Http/Middleware/HandleInertiaRequestsTest.php` | ٣ |
| ٩ | `tests/Feature/Inventory/OutletAccessGuardTest.php` | ١ |
| ١٠ | `tests/Feature/Inventory/OutletAccessTest.php` | ١ |
| ١١ | `tests/Feature/Inventory/StockOpnameTest.php` | ٣ |
| ١٢ | `tests/Feature/Inventory/WarehouseTest.php` | ٤ |
| ١٣ | `tests/Feature/Loyalty/LoyaltyFlowTest.php` | ١ |
| ١٤ | `tests/Feature/PaginationTest.php` | ١ |
| ١٥ | `tests/Feature/Pricing/PricingRuleTest.php` | ١ |
| ١٦ | `tests/Feature/Products/CompositeProductTest.php` | ٥ |
| ١٧ | `tests/Feature/Products/ProductTaxTest.php` | ٢ |
| ١٨ | `tests/Feature/Purchasing/SupplierReturnTest.php` | ٤ |
| ١٩ | `tests/Feature/Reports/AdvancedSalesInsightsTest.php` | ١ |
| ٢٠ | `tests/Feature/SalesReturn/SalesReturnTest.php` | ١ |
| ٢١ | `tests/Feature/Setup/DashboardChecklistTest.php` | ٢ |
| ٢٢ | `tests/Feature/Transactions/CartAuthorizationTest.php` | ١ |
| ٢٣ | `tests/Feature/Transactions/CartValidationTest.php` | ٢ |
| ٢٤ | `tests/Feature/Transactions/CashPaymentValidationTest.php` | ٢ |
| ٢٥ | `tests/Feature/Transactions/CheckoutServiceTest.php` | ٢ |
| ٢٦ | `tests/Feature/Transactions/OrderTypeNoteTest.php` | ٢ |
| ٢٧ | `tests/Feature/Transactions/QrisCheckoutTest.php` | ١ |
| ٢٨ | `tests/Feature/Transactions/SplitPaymentTest.php` | ٢ |
| ٢٩ | `tests/Feature/Transactions/TransactionFlowTest.php` | ١ |
| ٣٠ | `tests/Feature/Transactions/WalkInCustomerTest.php` | ٢ |

### النمط المطلوب

```diff
  $product = Product::create([
      'title' => 'Produk Test',
-     'stock' => 50,
      // ... باقي الحقول
  ]);
+ $this->stockProductAt($warehouse, $product, 50);
```

### معيار القبول

```bash
# لا يبقى أي 'stock' موجَّه إلى جدول products
grep -rn "'stock' =>" tests/ | grep -v "ProductWarehouse\|stockProductAt\|pivot"

# كل اختبار يبيع منتجًا لديه صف pivot
php artisan test --filter=Checkout
```

---

## الخطوة ٢ — فرض `NOT NULL` على المخطط

### المشكلة

الكود يرفض السجلات اليتيمة، لكن القاعدة ما زالت تسمح بها. أي مسار جديد ينسى التحقق
يُنتج بيانات «غير قانونية» بصمت.

### الأعمدة المستهدفة (١٨ عمودًا)

**أ) `warehouse_id` — ٩ جداول (إلزامي بلا استثناء)**

| الجدول | migration التي أضافته |
| --- | --- |
| `cashier_shifts` | `2026_06_22_000003` |
| `transactions` | `2026_06_22_000004` |
| `carts` | `2026_06_22_000004` |
| `stock_mutations` | `2026_06_22_000004` |
| `purchase_orders` | `2026_06_22_000005` |
| `goods_receivings` | `2026_06_22_000005` |
| `supplier_returns` | `2026_06_22_000005` |
| `stock_opnames` | `2026_06_22_000005` |
| `sales_returns` | `2026_06_22_000005` |

**ب) `outlet_id` — ٩ جداول (يحتاج قرارك)**

| الجدول | القرار المقترح | السبب |
| --- | --- | --- |
| `warehouses` | ✅ **NOT NULL** | مستودع بلا فرع لا معنى له |
| `dine_areas` | ✅ **NOT NULL** | منطقة بلا فرع لا معنى لها (بعد الخطوة ٤) |
| `settings` | ⚠️ **يبقى NULL** | قيمة عامة مشتركة (المبدأ ٥) |
| `payment_settings` | ⚠️ **يبقى NULL** | إعداد بوابة عام |
| `bank_accounts` | ⚠️ **يبقى NULL** | حساب مشترك |
| `pricing_rules` | ⚠️ **يبقى NULL** | قاعدة سعرية عامة |
| `price_lists` | ⚠️ **يبقى NULL** | قائمة أسعار عامة |
| `customer_vouchers` | ⚠️ **يبقى NULL** | عرض عام |
| `customer_campaigns` | ⚠️ **يبقى NULL** | حملة عامة |

> **هذا القرار يحتاج موافقتك.** الخيار البديل: تحويل كل الصفوف العامة إلى صفوف لكل فرع
> (أنظف نظريًا، لكنه يضاعف البيانات ويجعل «تغيير إعداد لكل الفروع» عملية كتابة جماعية).

### الهيكل المقترح للـ migration

```php
// database/migrations/2026_10_28_000003_enforce_warehouse_scope.php
return new class extends Migration
{
    private const REQUIRED_WAREHOUSE_TABLES = [
        'transactions', 'carts', 'stock_mutations', 'cashier_shifts',
        'purchase_orders', 'goods_receivings', 'supplier_returns',
        'stock_opnames', 'sales_returns',
    ];

    public function up(): void
    {
        // ١. امنع التنفيذ إن بقيت صفوف NULL — لا تكسر بيانات بصمت
        foreach (self::REQUIRED_WAREHOUSE_TABLES as $table) {
            if (DB::table($table)->whereNull('warehouse_id')->exists()) {
                throw new RuntimeException(
                    "{$table} still has NULL warehouse_id. Run outlet:audit --strict first."
                );
            }
        }

        // ٢. غيّر الأعمدة (يتطلب doctrine/dbal في Laravel 13)
        foreach (self::REQUIRED_WAREHOUSE_TABLES as $table) {
            Schema::table($table, fn (Blueprint $t) =>
                $t->foreignId('warehouse_id')->nullable(false)->change()
            );
        }
    }
};
```

### معيار القبول

```bash
php artisan outlet:audit --strict          # ينجح قبل التنفيذ
php artisan migrate                        # ينجح
# محاولة إدخال صف بلا مستودع تفشل على مستوى القاعدة
```

---

## الخطوة ٣ — دمج migrations «إضافة العمود» في المخطط الأساسي

### المشكلة

| النوع | العدد |
| --- | --- |
| إجمالي migrations | ١٠٢ |
| `create_*` | ٥٧ |
| `add_*` | ٣٨ |
| أخرى (index / nullable / encrypt…) | ٧ |

تثبيت جديد ينفّذ ١٠٢ ملفًا ثم يُلغي بعضها لاحقًا. هذا سجل تطوير لا وصف حالة.

### الطريقة

1. **انقل** تعريف العمود النهائي إلى ملف `create_*` الأصلي:
   - `2026_06_22_000003..000005` ← `warehouse_id` إلى جداولها الأصلية.
   - `2026_09_14_000002..000006` ← `outlet_id` إلى جداولها.
   - `2026_09_15_000001` ← `outlet_id` للحملات والقسائم.
2. **احذف** ملفات `add_*` المدمجة.
3. **أبقِ** migration الترقية (`000001` و `000002`) — تعالج بيانات تثبيتات قائمة.
4. **أضف** ملاحظة في `CHANGELOG.md`: التثبيتات القديمة يجب أن تُرقّى قبل حذف الملفات.

### معيار القبول

```bash
php artisan migrate:fresh --seed
php artisan schema:dump                    # قارن الناتج
# المخطط الناتج == المخطط الناتج من migrate على تثبيت مرقّى
```

---

## الخطوة ٤ — إنهاء المسارات المزدوجة (٥ مواضع)

| # | الملف : السطر | المشكلة | المطلوب |
| --- | --- | --- | --- |
| ١ | `TransactionController.php:630` | تعليق `ponytail: legacy single-method path` — مسار دفع مفرد منفصل | توحيد: كل دفعة تُصبح tender، حتى المفردة |
| ٢ | `RoleSeeder.php:110` | `normalizeLegacyPermissionRole()` يرقّي دور `permission-access` القديم في كل seed | حذف الدالة؛ نقل الترقية إلى migration بيانات |
| ٣ | `DineTableController.php:124` | `$area->outlet_id === null` ⇒ متاح للجميع | إزالة التسامح |
| ٤ | `DineTableController.php:141` | نفس النمط | إزالة التسامح |
| ٥ | `PosApiController.php:605` | تعليق `legacy 422` فقط (لا كود) | تحديث التعليق |

### معيار القبول

```bash
grep -rn "legacy" app/ database/seeders/ | grep -v "docs-legacy"   # لا نتائج
grep -rn "outlet_id === null" app/                                  # لا نتائج
```

---

## الخطوة ٥ — إزالة حراس المخطط الدفاعية (٦ مواضع)

### المشكلة

`Schema::hasColumn` / `Schema::hasTable` في طبقة التشغيل تعني «قد لا يكون المخطط جاهزًا»،
وهو ما لا يصح بعد فرض `NOT NULL`.

| # | الملف : السطر | الحارس |
| --- | --- | --- |
| ١ | `Apps/OutletController.php:123` | `hasTable($table) && hasColumn($table, 'warehouse_id')` |
| ٢ | `Apps/OutletController.php:129` | `hasTable('stock_transfers')` |
| ٣ | `Apps/SalesReturnController.php:678` | `hasTable('sales_returns') \|\| hasTable('sales_return_items')` |
| ٤ | `Apps/TransactionController.php:767` | نفس الفحص في `$salesReturnTablesReady` |
| ٥ | `Http/Middleware/HandleInertiaRequests.php:207` | `hasTable('settings')` |
| ٦ | `Services/OutletAccessService.php:187` | `hasTable('cashier_shifts')` |

### المطلوب

احذف الحراس من طبقة التشغيل. **أبقها فقط** في:
- `database/migrations/`
- `app/Console/Commands/` (أوامر الصيانة)

### معيار القبول

```bash
grep -rn "Schema::has" app/ | grep -v "Commands/"   # لا نتائج
```

---

## الخطوة ٦ — تنظيف نهائي

| العنصر | الإجراء |
| --- | --- |
| تعليق `ponytail: legacy` في `PosApiController:605` | تحديث أو حذف |
| `dine_in_pay_online_enabled` | إعداد ميت (`DineInSettingsSeeder:21`, `docs/features/dine-in.md:88`) — احذفه أو فعّله |
| `AGENTS.md:13` — فرع `revamp-frontend` | احذفه إن لم يكن مستخدمًا |
| `docs/architecture-overview.md:31` | يقول "97 migration" — الصحيح ١٠٢ |
| `docs/architecture-overview.md:16` | "~190+ route" — راجع العدد |
| `AGENTS.md` | أضف سياسة: **ممنوع إضافة طبقة توافق بلا migration ترقية موثّقة** |

---

# ٦) كيفية التحقق — Definition of Done

النظام يُعتبر «مبنيًا مرة واحدة» عند نجاح كل ما يلي.

## ٦.١ فحوص ثابتة (grep)

```bash
# ١. لا منطق توافق في طبقة التشغيل
grep -rn "legacy_single_outlet\|includeLegacy\|count() <= 1" app/
# المتوقع: لا نتائج

# ٢. لا قراءة للعمود المحذوف
grep -rn "products\.stock" app/ database/
# المتوقع: لا نتائج (عدا تعليق توضيحي في Product.php)

# ٣. لا حراس مخطط في التشغيل
grep -rn "Schema::has" app/ | grep -v "Commands/"
# المتوقع: لا نتائج

# ٤. لا مسارات مزدوجة
grep -rn "legacy" app/ database/seeders/
# المتوقع: لا نتائج

# ٥. لا مخزون يُكتب خارج الـ pivot
grep -rn "->increment('stock'\|->decrement('stock'\|update(\['stock'" app/
# المتوقع: كلها على $pw / $pivot / ProductWarehouse / $batch فقط
```

## ٦.٢ فحوص المخطط

```bash
php artisan migrate:fresh --seed
php artisan outlet:audit --strict
# المتوقع: "All outlet invariants hold." + exit 0
```

## ٦.٣ فحوص الاختبارات

```bash
php artisan test
# المتوقع: 428 اختبارًا في 80 ملفًا — كلها خضراء
```

## ٦.٤ فحص الترقية (سيناريو كامل)

```bash
# ١. على قاعدة بيانات v3.0.3
php artisan migrate --pretend       # راجع الخطوات
php artisan migrate
php artisan outlet:audit --strict   # ينجح

# ٢. تأكد أن المخزون لم يُفقد
php artisan tinker --execute="
  echo App\Models\Product::withSum('warehouses','product_warehouse.stock')->get()
       ->sum('warehouses_sum_product_warehouse_stock');
"
# قارن مع مجموع products.stock قبل الترقية
```

---

# ٧) دليل الترقية لتثبيت قائم

## ٧.١ قبل الترقية

```bash
# نسخة احتياطية — إلزامي
mysqldump -u root -p point_of_sales > backup_before_rebuild.sql

# سجّل الأرقام المرجعية
mysql -u root -p point_of_sales -e "
  SELECT SUM(stock) AS total_products_stock FROM products;
  SELECT COUNT(*) AS users_without_outlet FROM users u
    WHERE NOT EXISTS (SELECT 1 FROM user_outlets uo WHERE uo.user_id = u.id);
  SELECT COUNT(*) AS warehouses_without_outlet FROM warehouses WHERE outlet_id IS NULL;
"
```

## ٧.٢ الترقية

```bash
php artisan down

php artisan migrate
# ينفّذ بالترتيب:
#   2026_10_28_000001_drop_products_stock_column   ← ينقل المخزون إلى PUSAT ثم يُسقط
#   2026_10_28_000002_backfill_outlet_scope        ← يُسند المستخدمين والمستودعات

php artisan outlet:audit --strict
php artisan up
```

## ٧.٣ ما تفعله الـ migrations تلقائيًا

| migration | الخطوات | مدمّرة؟ |
| --- | --- | --- |
| `...000001` | ينشئ صف pivot في `PUSAT` لكل منتج له `stock > 0` وبلا صف pivot، ثم يُسقط العمود | لا (يحتفظ بالقيمة) |
| `...000002` | ينشئ/يربط `PUSAT`، يُسند المستخدمين، يتبنّى المستودعات، يعبّئ `warehouse_id` | لا |

## ٧.٤ بعد الترقية — تحقق

```bash
# ١. المخزون انتقل
php artisan tinker --execute="
  echo 'pivot total: '.DB::table('product_warehouse')->sum('stock');
"

# ٢. لا مستخدم بلا فرع
php artisan outlet:audit --strict

# ٣. جرّب الدخول بحساب غير super-admin
#    - يجب أن يرى فروعه فقط
#    - فتح وردية على فرع بيع
#    - إتمام بيعة نقدية
```

## ٧.٥ التراجع (Rollback)

```bash
php artisan migrate:rollback --step=2
# ...000001 تُعيد العمود وتعبّئه من مجموع الـ pivot
# ...000002 لا تفعل شيئًا (down فارغة)
```

> ⚠️ **بعد الترقية لن يعمل أي مستخدم بلا إسناد فرع.** راجع `user_outlets` قبل النشر
> إن كان لديك تشغيل متعدد الفروع.

---

# ٨) المخاطر والمحاذير

| # | الخطر | الاحتمال | الأثر | التخفيف |
| --- | --- | --- | --- | --- |
| ١ | تشديد `NOT NULL` قبل تصفير الصفوف | متوسط | فشل migration | لا تنفّذ الخطوة ٢ قبل نجاح `outlet:audit --strict` |
| ٢ | حذف migrations `add_*` | متوسط | تثبيت لم يُرقَّ يكسر | أصدر إصدارًا وسيطًا يضمن الترقية أولًا |
| ٣ | توحيد مسار الدفع | عالٍ | تغيير سلوك API | أعلنه Breaking في `CHANGELOG` مع فترة توافق |
| ٤ | إسقاط `products.stock` | مؤكد | تقارير خارجية تقرأ العمود | وثّق البديل: `product_warehouse` + `Product::stockTotal()` |
| ٥ | فقدان إسنادات الفروع | متوسط | مستخدمون يفقدون الوصول | راجع `user_outlets` قبل `up` |
| ٦ | اختبارات حمراء بعد إعادة البناء | مؤكد حاليًا | لا بوابة جودة | نفّذ الخطوة ١ أولًا وحافظ على CI أخضر |
| ٧ | لا يوجد PHP في بيئة التطوير الحالية | مؤكد | لا تحقق محلي | شغّل `php artisan test` في CI أو محليًا قبل الدمج |

---

# ٩) المراجع والملفات المرتبطة

## ٩.١ الملفات الأساسية المتأثرة

| الملف | الدور |
| --- | --- |
| `app/Services/OutletAccessService.php` | طبقة التفويض للفروع — أُعيدت كتابتها |
| `app/Models/Product.php` | مصدر المخزون + scopes جديدة |
| `app/Models/Setting.php` | إعدادات الفرع مع القيمة العامة |
| `app/Http/Middleware/HandleInertiaRequests.php` | الخصائص المشتركة مع Inertia |
| `database/migrations/2026_10_28_000001_*.php` | إسقاط العمود مع حفظ البيانات |
| `database/migrations/2026_10_28_000002_*.php` | إصلاح بيانات الفروع |
| `database/factories/{Outlet,Warehouse,User}Factory.php` | أساس الاختبارات |
| `tests/TestCase.php` | ٤ مساعدين للاختبارات |
| `app/Console/Commands/AuditOutletCommand.php` | بوابة التحقق |

## ٩.٢ التوثيق

| الملف | المحتوى |
| --- | --- |
| `docs/compatibility-ar.md` | **هذا المستند** |
| `docs/multi-outlet.md` | نموذج الفروع + دليل التشغيل |
| `docs/architecture-overview.md` | نظرة معمارية (تحتاج تحديث الأعداد) |
| `docs/features/inventory-stock.md` | المخزون |
| `docs/features/multi-warehouse.md` | المستودعات |
| `CHANGELOG.md` | سجل التغييرات |
| `AGENTS.md` | قواعد المساهمة |

---

# ١٠) سجل تاريخي كامل للإصدارات

## ١٠.١ قبل إعادة البناء

| الإصدار | التاريخ | أهم ما فيه |
| --- | --- | --- |
| v2.10.5 | — | آخر إصدار «مستودع واحد» |
| v2.10.6 | 2026-09-13 | `seed:demo` + حسابات مُتحقَّقة مسبقًا |
| v2.11.0 | 2026-09-13 | دفع مقسّم (حتى tenderين) |
| **v3.0.0** | 2026-09-18 | **Breaking**: `outlets` + `user_outlets` + `OutletAccessService`، `PUSAT` غير مفعّل للبيع، `outlet:audit` |
| v3.0.1 | 2026-09-18 | `CheckoutService` + `CheckoutContext`، Sentry، CI |
| v3.0.2 | 2026-09-18 | توحيد مسار `PosApiController::checkout` |
| v3.0.3 | 2026-09-27 | تشديد أمني: Sanctum expiry، `pos-access`، `hash_equals`، HSTS، `X-Service-Token` |

## ١٠.٢ كيف كان النظام يعمل قبل إعادة البناء

**قبل `v3.0.0`:**
- مستودع واحد + فرع واحد.
- `products.stock` هو المخزون الوحيد.
- `warehouse_id` nullable في كل الجداول، و`null` = «يعمل على العمود العام».
- لا `outlets` ولا `user_outlets`.
- الإعدادات صفوف عامة بلا `outlet_id`.

**بين `v3.0.0` و `v3.0.3`:**
- أُضيف نموذج الفروع، لكن أُبقي `legacy_single_outlet_bypass` (افتراضيًا **مفعّل**).
- كل استعلام أُضيف له «أو السجلات بلا فرع» عندما `Outlet::active()->count() <= 1`.
- `products.stock` بقي «إجماليًا مُصانًا» يُزامَن يدويًا.
- أمران للصيانة: `outlet:legacy-audit` و `inventory:reconcile`.

## ١٠.٣ بعد إعادة البناء (الفرع الحالي)

| ما تغيّر | الحالة |
| --- | --- |
| تجاوز الفرع الواحد | ❌ محذوف نهائيًا |
| القواعد العددية (`count() <= 1`) | ❌ محذوفة (١٦ موضعًا) |
| `products.stock` | ❌ محذوف العمود (لا الإجمالي المشتق) |
| أوامر الصيانة القديمة | ❌ محذوفة |
| الحالات الحرجة (استعلامات على عمود محذوف) | ✅ مُصلحة (٩ ملفات) |
| مسار ترقية للبيانات القديمة | ✅ migration واحدة |
| القيمة العامة في الإعدادات | ✅ **أُعيد تصنيفها كتصميم، لا تراث** |
| `NOT NULL` على المخطط | ⏳ متبقٍ |
| دمج migrations | ⏳ متبقٍ |
| تحديث الاختبارات | ⏳ متبقٍ (٣٠ ملفًا) |

---

## ملاحظة أخيرة عن التحقق

كل أرقام هذا المستند — أعداد الملفات، أرقام الأسطر، أسماء الجداول، قوائم الاختبارات —
مأخوذة من `grep` و`git diff` مباشرة على الشجرة الحالية للفرع، لا من الذاكرة.

**قيد مهم:** بيئة العمل الحالية لا تحتوي PHP ولا MySQL، لذا لم يُشغَّل
`php artisan test` ولا `php artisan migrate` فعليًا. التحقق النحوي تم بمحلّل PHP،
لكن **التحقق السلوكي يحتاج CI أخضر أو تشغيلًا محليًا** قبل الدمج إلى `main`.

---

# ملحق أ) كيف استُخرج كل رقم في هذا المستند

كل رقم قابل لإعادة الإنتاج بهذه الأوامر. نُفِّذت فعليًا على الشجرة الحالية.

```bash
BASE=69b93642dc4d0f5596db8fecb4e5c5d892ec5b38   # آخر كوميت على main قبل العمل

# ── ١. عدد الكوميتات والملفات
git log --oneline $BASE..HEAD | wc -l                      # 11
git diff --name-only $BASE..HEAD | wc -l                   # 107
git diff --shortstat $BASE..HEAD                           # +1201 / -972
git diff --name-status $BASE..HEAD | grep -c "^D"          # 3  (محذوفة)

# ── ٢. التوزيع حسب المنطقة
git diff --name-only $BASE..HEAD | cut -d/ -f1 | sort | uniq -c

# ── ٣. حالات count() <= 1 في الأساس
git grep -o "Outlet::active()->count() <= 1" $BASE -- app | wc -l    # 16
git grep -l "Outlet::active()->count() <= 1" $BASE -- app | wc -l    # 15

# ── ٤. includeLegacy في الأساس
git grep -l "includeLegacy" $BASE -- app | wc -l                     # 7

# ── ٥. أنماط orWhereNull التوافقية في الأساس
git grep -o "orWhereNull('warehouse_id')\|orWhereNull('outlet_id')\|orWhereNull('transactions.warehouse_id')\|orWhereNull('t.warehouse_id')\|orWhereNull(\$column)" \
  $BASE -- app database | wc -l                                      # 11

# ── ٦. ملفات المخزون في الأساس
git grep -l "products\.stock\|->stock\b\|increment('stock'\|decrement('stock'" $BASE -- app database | wc -l   # 27

# ── ٧. الاختبارات المحتاجة ربط pivot (حالة اليوم)
for f in $(grep -rlF "Product::create([" tests --include=*.php); do
  if grep -qF "'stock' =>" "$f" &&
     ! grep -qE "warehouses\(\)->attach|syncWithoutDetaching|ProductWarehouse|stockProductAt" "$f"; then
    echo "$f"
  fi
done | wc -l                                                          # 30
grep -rhoF "'stock' =>" $( ... نفس القائمة ... ) | wc -l                # 53

# ── ٨. حراس المخطط
grep -rn "Schema::hasColumn\|Schema::hasTable" app | wc -l             # 6 مواضع / 5 ملفات

# ── ٩. الأعمدة القابلة لـ NULL
grep -rn "warehouse_id')->nullable" database/migrations | wc -l        # 9
grep -rn "outlet_id')->nullable"    database/migrations | wc -l        # 8 استدعاءات ← 9 جداول
                                                                        # (000005 يضيف عمودين)

# ── ١٠. الـ migrations
ls database/migrations | wc -l            # 102
ls database/migrations | grep -c "_add_"    # 38
ls database/migrations | grep -c "_create_" # 57

# ── ١١. الاختبارات
find tests -name "*Test.php" | wc -l                      # 80
grep -rho "public function test_[a-z_]*" tests | wc -l    # 428

# ── ١٢. الحالة المستهدفة (بعد إتمام الخطوات)
grep -rn "legacy_single_outlet\|includeLegacy\|count() <= 1" app/    # 0
grep -rn "products\.stock" app/ database/                            # 0
grep -rn "Schema::has" app/ | grep -v Commands/                       # 0
```

## أ.١ فروق طفيفة في العدّ يجب معرفتها

| الحالة | التفصيل |
| --- | --- |
| `outlet_id')->nullable` | ٨ استدعاءات في ٦ ملفات، لكنها تُنشئ **٩ أعمدة** لأن `000005` يضيف عمودين في حلقة |
| `warehouse_id` في `stock_transfers` | ليس `nullable` أصلًا — لا يحتاج تشديدًا |
| `ProductBatch.stock` | عمود مستقل تمامًا عن `products.stock` — لم يُمسّ |
| `InventoryReconcileCommandTest.php` | مذكور في المستند لكنه **محذوف** — ورد في قسم «ما حُذف» لا في قائمة الـ ٣٠ |

---

# ملحق ب) خريطة الملفات — ماذا تغيّر في كل ملف

> مرجع سريع: عند مراجعة الكود، هذا هو سبب تعديل كل ملف.

## ب.١ طبقة التطبيق (٣٧ ملفًا)

| الملف | سبب التعديل |
| --- | --- |
| `Services/OutletAccessService.php` | إزالة الـ bypass + تشديد الشروط |
| `Services/CashierShiftService.php` | إزالة `singleOutlet()` + المستودع إلزامي |
| `Services/PayableAgingService.php` | إزالة `singleOutlet()` |
| `Services/ReceivableService.php` | إزالة `singleOutlet()` |
| `Services/CheckoutService.php` | المخزون من الـ pivot فقط |
| `Services/DineOrderService.php` | المخزون من الـ pivot فقط |
| `Services/GoodsReceivingService.php` | إزالة الكتابة على العمود |
| `Services/StockTransferService.php` | إزالة ٣ كتابات على العمود |
| `Services/SupplierReturnService.php` | الفحص من الـ pivot |
| `Services/StockMutationService.php` | المخزون الافتتاحي من الـ pivot |
| `Services/ReorderService.php` | استعلام بديل بـ pivot |
| `Models/Product.php` | إزالة `stock` + scopes + accessor |
| `Models/Setting.php` | إزالة حراس `Schema::hasColumn` |
| `Http/Middleware/HandleInertiaRequests.php` | إزالة `$includeLegacy` |
| `Http/Controllers/DashboardController.php` | إزالة `$includeLegacy` |
| `Http/Controllers/DineMenuController.php` | `inStock` بدل العمود |
| `Http/Controllers/NotificationController.php` | `outOfStock()` |
| `Http/Controllers/Reports/AdvancedSalesInsightsController.php` | إزالة `include_legacy` + `COALESCE` |
| `Http/Controllers/Api/PosApiController.php` | `inStock` + التوفر من الـ pivot |
| `Http/Controllers/Api/ProductController.php` | `stock` → الـ pivot |
| `Http/Controllers/Apps/ProductController.php` | إزالة `stock` من `create` |
| `Http/Controllers/Apps/TransactionController.php` | إزالة `orWhereNull` + `inStock` |
| `Http/Controllers/Apps/StockOpnameController.php` | الـ pivot فقط |
| `Http/Controllers/Apps/SalesReturnController.php` | الـ pivot فقط |
| `Http/Controllers/Apps/DineOrderController.php` | إزالة التسامح |
| `Http/Controllers/Apps/PayableController.php` | إزالة `orWhereDoesntHave` |
| `Http/Controllers/Apps/CashierShiftController.php` | إزالة «كل المستخدمين» |
| `Http/Controllers/Apps/StockMutationController.php` | إزالة `orWhereNull` |
| `Http/Controllers/Apps/DiscountApprovalController.php` | إزالة `orWhereNull` |
| `Http/Controllers/Apps/CrmReminderController.php` | إزالة `orWhereNull` |
| `Http/Controllers/Apps/ImportExportController.php` | تمرير المستودع للاستيراد |
| `Imports/ProductsImport.php` | مخزون الافتتاح → pivot |
| `Exports/TransactionsExport.php` | إزالة `$includeLegacy` |
| `Console/Commands/AuditOutletCommand.php` | إعادة بناء كاملة |
| `Console/Commands/ExpirePendingTransactionsCommand.php` | الـ pivot فقط |
| `Console/Commands/AuditLegacyOutletCommand.php` | ❌ محذوف |
| `Console/Commands/InventoryReconcileCommand.php` | ❌ محذوف |

## ب.٢ قاعدة البيانات (١١ ملفًا)

| الملف | التغيير |
| --- | --- |
| `migrations/2024_06_13_131744_create_products_table.php` | لا يُنشئ `stock` |
| `migrations/2026_10_28_000001_drop_products_stock_column.php` | 🆕 إسقاط + حفظ |
| `migrations/2026_10_28_000002_backfill_outlet_scope.php` | 🆕 إصلاح بيانات |
| `factories/OutletFactory.php` | 🆕 |
| `factories/WarehouseFactory.php` | `outlet_id` تلقائي |
| `factories/UserFactory.php` | إسناد تلقائي لفرع |
| `seeders/DatabaseSeeder.php` | إسناد + لا تعبئة من العمود |
| `seeders/SampleDataSeeder.php` | `$initialStock` → pivot |
| `seeders/OperationalCoreSeeder.php` | الـ pivot |
| `seeders/FeatureDemoSeeder.php` | حذف `syncGlobalStock()` |
| `seeders/DemoDatabaseResetter.php` | فحص الـ pivot |

## ب.٣ الاختبارات (٥٠ ملفًا)

| المجموعة | العدد | التغيير |
| --- | --- | --- |
| تحويل `Warehouse::create` → factory | ٤٦ | تعديل ميكانيكي |
| `TestCase.php` | ١ | +٤ مساعدين |
| `AuditOutletCommandTest` | ١ | حذف اختبارَي legacy |
| `PhaseTwoSecurityHardeningTest` | ١ | حذف اختبار الـ bypass |
| `StockInvariantTest` | ١ | حذف اختبار `inventory:reconcile` |
| `InventoryReconcileCommandTest` | ١ | ❌ محذوف |
| **٥٠ ملفًا** | | منها ٣٠ تحتاج الخطوة ١ |

---

# ملحق ج) أسئلة متوقعة

**س: هل حُذف المخزون القديم فعليًا؟**
لا. `2026_10_28_000001` ينقل كل قيمة `products.stock > 0` إلى صف pivot في `PUSAT`
قبل الإسقاط. والـ `down()` يُعيدها.

**س: هل يمكن التراجع بعد الترقية؟**
نعم جزئيًا: `migrate:rollback` يُعيد العمود ويُعبّئه من مجموع الـ pivot.
لكن إسنادات الفروع (`user_outlets`) تبقى — وهي إصلاح بيانات لا يُعكس.

**س: هل الإعدادات العامة (`outlet_id = NULL`) خطأ؟**
لا. هي تصميم مقصود: قيمة افتراضية مشتركة يتجاوزها صف الفرع. القرار الوحيد المطلوب
هو ما إذا كانت جداول الإعدادات تبقى قابلة لـ NULL في الخطوة ٢ (المقترح: نعم).

**س: لماذا حُذف `inventory:reconcile` بدل إصلاحه؟**
لأنه كان يطابق عمودًا بعمود. بعد إسقاط العمود لم يبقَ ما يُطابَق. التحقق البديل:
`outlet:audit` + فحص أن لا صف pivot سالب.

**س: هل `Product::stock` ستعمل في الكود القديم؟**
نعم للقراءة (accessor)، و**لا للكتابة** (ليست في `$fillable`). أي `update(['stock' => n])`
يُتجاهل بصمت — ولهذا الخطوة ١ ضرورية في الاختبارات.

**س: ما أكبر خطر الآن؟**
الاختبارات الحمراء بسبب الخطوة ١ غير المنفّذة. الكود صحيح منطقيًا، لكن ٣٠ ملف اختبار
تبني منتجات بمخزون صفر.
