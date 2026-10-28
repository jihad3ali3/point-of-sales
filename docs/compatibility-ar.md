# دليل إزالة المنطق القديم — نحو نظام «مبني من الصفر مرة واحدة»

> **الغرض من هذا الملف:** شرح كامل لما يجب تعديله كي يتصرّف المشروع كأنه بُني مرة واحدة،
> بلا طبقات توافق تراكمية. يبدأ بالحالة الحالية، ثم يشرح النموذج المستهدف، ثم يقدّم
> قائمة التعديلات المطلوبة مرتّبة من البداية للنهاية، مع الملفات ومعايير القبول.
>
> **الحالة:** المراحل ١–٣ منفّذة ومرفوعة. المرحلة ٤ (المخطط + الاختبارات + المسارات المزدوجة) متبقية.
> **الفرع:** `arena/3e4b16f1-point-of-sales` — **PR:** [#1](https://github.com/jihad3ali3/point-of-sales/pull/1)

---

## ١) الخلاصة التنفيذية

| المجال | الحالة | التفصيل |
| --- | --- | --- |
| تجاوز الفرع الواحد (`legacy_single_outlet_bypass`) | ✅ منفّذ | المحفّز والدالة حُذفا نهائيًا |
| حالات `Outlet::active()->count() <= 1` | ✅ منفّذ | أُزيلت من ١٤ ملفًا |
| `$includeLegacy` / `orWhereNull('warehouse_id')` | ✅ منفّذ | أُزيلت من الاستعلامات والتقارير والتصدير |
| أوامر الترقية القديمة | ✅ منفّذ | `outlet:legacy-audit` و `inventory:reconcile` حُذفا |
| عمود `products.stock` | ✅ منفّذ | أُسقط + migration تعبئة رجعية |
| مسار ترقية لتثبيت قائم | ✅ منفّذ | `2026_10_28_000002_backfill_outlet_scope` |
| استعلامات تقرأ العمود المحذوف | ✅ منفّذ | أُصلحت ٩ ملفات (كانت ستُسقط التشغيل بـ unknown column) |
| بنية مصانع الاختبارات | ✅ جزئيًا | `OutletFactory` + `WarehouseFactory` + `UserFactory` + helpers |
| ربط المخزون بالـ pivot في الاختبارات | ⏳ متبقٍ | ٣٠ ملف اختبار |
| `warehouse_id` / `outlet_id` إلزامي (`NOT NULL`) | ⏳ متبقٍ | ١٨ عمودًا في ١٨ جدولًا |
| دمج migrations الإضافية في المخطط الأساسي | ⏳ متبقٍ | ٣٨ migration من نوع `add_*` |
| المسارات المزدوجة (دفع مفرد/تقسيم، دور `permission-access`) | ⏳ متبقٍ | ٤ مواضع |
| حراس `Schema::hasColumn` الدفاعية | ⏳ متبقٍ | ٦ مواضع في ٥ ملفات |

---

## ٢) النموذج المستهدف — «المبادئ الستة»

هذه هي القواعد التي يجب أن يحقّقها الكود في نهاية العمل. كل تعديل في هذا الملف يخدم واحدة منها.

### المبدأ ١ — الفرع حدّ أمني إلزامي
لا يوجد مستخدم «بلا فرع». الوصول يُشتق دائمًا من `user_outlets`، ولا يوجد أي مسار ضمني.

```php
// app/Services/OutletAccessService.php
public function canUseWarehouse(User $user, ?Warehouse $warehouse): bool
{
    if (! $warehouse || ! $warehouse->is_active || ! $warehouse->outlet_id) {
        return false;                       // ← لا يوجد bypass
    }

    if ($user->isSuperAdmin()) {
        return true;
    }

    return $user->outlets()
        ->where('outlets.is_active', true)
        ->where('outlets.id', $warehouse->outlet_id)
        ->exists();
}
```

### المبدأ ٢ — المخزون في مكان واحد فقط
`product_warehouse` هو مصدر الحقيقة الوحيد. لا عمود إجمالي، لا `COALESCE`، لا مزامنة يدوية.

```php
// app/Models/Product.php — قراءة فقط، مشتقّة
protected function stock(): Attribute
{
    return Attribute::make(get: fn () => $this->stockTotal());
}
```

### المبدأ ٣ — كل سجل تشغيلي له فرع ومستودع
لا صفوف «يتيمة». القاعدة تُفرض في المخطط (`NOT NULL`) لا في الكود فقط.

### المبدأ ٤ — لا قيم افتراضية تعتمد على «عدد الفروع»
«إن كان لدينا فرع واحد فقط فاسمح» قاعدة ضمنية وخطيرة. القرار يجب أن يكون صريحًا وقابلًا للتدقيق.

### المبدأ ٥ — القيمة العامة إعداد مشترك لا تراث
`outlet_id = NULL` في `settings` / `payment_settings` / `bank_accounts` / قوائم الأسعار يعني
«قيمة افتراضية مشتركة يتجاوزها الفرع». **هذا تصميم مقصود ويبقى**، ويُوثّق كذلك لا كـ legacy.

### المبدأ ٦ — مسار واحد لكل عملية
لا مسارين لدفع واحد، ولا منطقين لإنشاء مخزون. أي `if ($legacy) { ... }` هو دَيْن تقني يجب سداده.

---

## ٣) ما تم تنفيذه

### المرحلة ١ — تشديد طبقة التشغيل

| الملف | التغيير |
| --- | --- |
| `app/Services/OutletAccessService.php` | حذف `legacySingleOutletBypass()`؛ `canUseWarehouse()` ترفض المستودع الفارغ/غير المرتبط؛ `warehousesFor()` تُرجع مجموعة فارغة لمن لا إسناد له |
| `config/security.php` | حذف مجموعة `outlet` كاملة |
| `.env.example` | حذف `SECURITY_LEGACY_SINGLE_OUTLET_BYPASS` |
| `DashboardController`, `HandleInertiaRequests`, `TransactionController`, `StockMutationController`, `DiscountApprovalController`, `CrmReminderController`, `DineOrderController`, `PayableController`, `CashierShiftController`, `AdvancedSalesInsightsController` | حذف كل `Outlet::active()->count() <= 1` وكل `$includeLegacy` |
| `CashierShiftService`, `PayableAgingService`, `ReceivableService` | حذف `singleOutlet()` وحالاتها |
| `TransactionsExport` | حذف معامل `$includeLegacy` |
| `app/Models/Setting.php` | حذف حراس `Schema::hasColumn` عن `settings.outlet_id` |
| `CashierShiftService::openShift()` | المستودع إلزامي: `int $warehouseId` بدل `?int $warehouseId = null` |

### المرحلة ٢ — مصدر مخزون واحد

| الملف | التغيير |
| --- | --- |
| `app/Models/Product.php` | حذف `stock` من `$fillable`/`$casts`، وإضافة accessor للقراءة + `scopeInStock()` / `scopeOutOfStock()` |
| `GoodsReceivingService` | الاستلام يزيد الـ pivot فقط، و`stockBefore/After` من الـ pivot |
| `StockTransferService` | الإرسال/الاستلام/الإلغاء تتلاعب بالـ pivot فقط |
| `StockOpnameController` | الجرد يكتب على صف الـ pivot للفرع فقط، وحُذف مسار «بلا مستودع» |
| `SupplierReturnService` | الشروط والخصم من الـ pivot مباشرة |
| `SalesReturnController` | إعادة التخزين في مستودع الفاتورة فقط |
| `CheckoutService` / `DineOrderService` | التوفر يُقرأ من صف الـ pivot المقفول `lockForUpdate()` |
| `ExpirePendingTransactionsCommand` | إعادة المخزون إلى الـ pivot فقط |
| `ProductsImport` | مخزون الافتتاح يُكتب في مستودع المشغّل |
| `Api/ProductController` | `stock` → الـ pivot عند الإنشاء والتحديث |
| `SampleDataSeeder`, `OperationalCoreSeeder`, `FeatureDemoSeeder`, `DatabaseSeeder`, `DemoDatabaseResetter` | الكتابة عبر الـ pivot، وحذف `syncGlobalStock()` |
| `create_products_table` | لم يعد يُنشئ العمود |
| `2026_10_28_000001_drop_products_stock_column` | تعبئة `PUSAT` من القيم القديمة ثم الإسقاط |

### المرحلة ٣ — مسار الترقية والتوثيق

| الملف | التغيير |
| --- | --- |
| `2026_10_28_000002_backfill_outlet_scope` | ينشئ/يربط `PUSAT`، يُسند المستخدمين، يتبنّى المستودعات اليتيمة، يعبّئ `warehouse_id` — idempotent وغير مدمّر |
| `DatabaseSeeder` | يُسند الحسابات المتبقية إلى `PUSAT` |
| `AuditOutletCommand` | أُعيد بناؤه ليتحقق من ثوابت التثبيت النظيف |
| `docs/*`, `CHANGELOG.md`, `AGENTS.md` | تحديث كل ما كان يصف `products.stock` كإجمالي مُصان |

---

## ٤) التعديلات المتبقية — خطة من البداية للنهاية

> نفّذها بهذا الترتيب. كل خطوة تُبقي النظام قابلًا للتشغيل، والخطوة التالية تعتمد على نجاح ما قبلها.

---

### الخطوة ١ — ربط المخزون بالـ pivot في الاختبارات (٣٠ ملفًا)

**المشكلة:** الاختبارات تنشئ `Product::create(['stock' => 50])` دون ربط الـ pivot. القيمة تُتجاهل
بشكل صحيح (لم يعد العمود موجودًا)، لكن المنتج يصبح بمخزون صفر — فتفشل اختبارات البيع والدفع.

**الملفات (٣٠):**
```
tests/Feature/Api/ApiAuthorizationTest.php
tests/Feature/Api/ProductApiTest.php
tests/Feature/Api/SplitPaymentApiTest.php
tests/Feature/AuditLogs/AuditLogTest.php
tests/Feature/CashierShifts/CashierShiftTest.php
tests/Feature/Console/ExpirePendingTransactionsTest.php
tests/Feature/Documents/PublicInvoiceAuthorizationTest.php
tests/Feature/Http/Middleware/HandleInertiaRequestsTest.php
tests/Feature/Inventory/OutletAccessTest.php
tests/Feature/Inventory/OutletAccessGuardTest.php
tests/Feature/Inventory/StockOpnameTest.php
tests/Feature/Inventory/WarehouseTest.php
tests/Feature/Loyalty/LoyaltyFlowTest.php
tests/Feature/PaginationTest.php
tests/Feature/Pricing/PricingRuleTest.php
tests/Feature/Products/CompositeProductTest.php
tests/Feature/Products/ProductTaxTest.php
tests/Feature/Purchasing/SupplierReturnTest.php
tests/Feature/Reports/AdvancedSalesInsightsTest.php
tests/Feature/SalesReturn/SalesReturnTest.php
tests/Feature/Setup/DashboardChecklistTest.php
tests/Feature/Transactions/CartAuthorizationTest.php
tests/Feature/Transactions/CartValidationTest.php
tests/Feature/Transactions/CashPaymentValidationTest.php
tests/Feature/Transactions/CheckoutServiceTest.php
tests/Feature/Transactions/OrderTypeNoteTest.php
tests/Feature/Transactions/QrisCheckoutTest.php
tests/Feature/Transactions/SplitPaymentTest.php
tests/Feature/Transactions/TransactionFlowTest.php
tests/Feature/Transactions/WalkInCustomerTest.php
```

**طريقة التنفيذ:** استبدل كل `Product::create([... 'stock' => $n ...])` بـ:

```php
$product = Product::create([...  /* بدون stock */ ...]);
$this->stockProductAt($warehouse, $product, $n);
```

`stockProductAt()` موجودة في `tests/TestCase.php`.

**معيار القبول:** `grep -r "'stock' =>" tests/` لا يُرجع أي حالة مرتبطة بجدول `products`؛
وكل اختبار يبيع منتجًا لديه صف pivot بمخزون كافٍ.

---

### الخطوة ٢ — فرض `NOT NULL` على المخطط

**المشكلة:** الكود يرفض السجلات اليتيمة، لكن قاعدة البيانات ما زالت تسمح بها. أي مسار جديد
بلا تحقّق سيُنتج بيانات «غير قانونية» بصمت.

**الأعمدة المستهدفة (١٨ عمودًا):**

| العمود | الجداول |
| --- | --- |
| `warehouse_id` (٩) | `transactions`, `carts`, `stock_mutations`, `cashier_shifts`, `purchase_orders`, `goods_receivings`, `supplier_returns`, `stock_opnames`, `sales_returns` |
| `outlet_id` (٩) | `warehouses`, `settings`, `payment_settings`, `bank_accounts`, `pricing_rules`, `price_lists`, `dine_areas`, `customer_vouchers`, `customer_campaigns` |

**إنشاء migration جديدة** بعد `2026_10_28_000002`:

```php
public function up(): void
{
    // 1. تحقّق أن لا صفوف NULL متبقية (المرحلة ٣ عبّأتها)
    // 2. غيّر الأعمدة إلى NOT NULL
    // 3. settings/payment_settings: انقل الصف العام إلى كل فرع ثم أزل الدعم العام
}
```

**قرار مطلوب قبل التنفيذ:** هل يبقى `settings.outlet_id` قابلًا لـ `NULL`؟
إن كان الجواب «القيمة العامة مقصودة» (المبدأ ٥) فاستثنِ: `settings`, `payment_settings`,
`bank_accounts`, `pricing_rules`, `price_lists`, `customer_vouchers`, `customer_campaigns`
من `NOT NULL` ووثّقها كتصميم. أَفرِد `NOT NULL` على:
`warehouses`, `transactions`, `carts`, `stock_mutations`, `cashier_shifts`, `purchase_orders`,
`goods_receivings`, `supplier_returns`, `stock_opnames`, `sales_returns`, `dine_areas`.

**معيار القبول:** `php artisan outlet:audit --strict` ينجح، ومحاولة إدخال صف بلا مستودع تفشل على مستوى القاعدة.

---

### الخطوة ٣ — دمج migrations «إضافة العمود» في المخطط الأساسي

**المشكلة:** ٣٨ migration من نوع `add_*` تسجّل تاريخ التطوير لا الحالة النهائية. تثبيت جديد
ينفّذها كلها ثم يلغي بعضها في migrations لاحقة.

**الطريقة:**
1. انقل تعريفات الأعمدة النهائية إلى ملفات `create_*` الأصلية.
2. احذف كل `add_*` أصبحت مدمجة (ابدأ بمجموعة `2026_06_22_000003..000005` و `2026_09_14_000002..006`).
3. أبقِ migration الترقية للمخطط (`000001` و `000002`) لأنها تعالج بيانات تثبيتات قائمة.
4. أضف ملاحظة في `CHANGELOG.md` بأن التثبيتات التي اعتمدت على الملفات المحذوفة يجب أن تكون
   على الإصدار السابق قبل الترقية.

**الهدف:** migration واحدة تُنتج المخطط النهائي الصحيح لتثبيت جديد، بلا خطوات إلغاء لاحقة.

**معيار القبول:** `php artisan migrate:fresh --seed` ينتج مخططًا مطابقًا لما ينتجه
`migrate` على تثبيت قائم مرقّى (قارن بـ `php artisan schema:dump`).

---

### الخطوة ٤ — إنهاء المسارات المزدوجة

| الموضع | المشكلة | المطلوب |
| --- | --- | --- |
| `app/Http/Controllers/Apps/TransactionController.php:630` | مسار دفع مفرد (`cash`) + مسار tenders منفصل | توحيدهما: كل دفعة تُصبح tender، حتى المفردة |
| `database/seeders/RoleSeeder.php:110` | `normalizeLegacyPermissionRole()` يرقّي دور `permission-access` القديم | حذف الدالة؛ أضف هجرة بيانات منفصلة للمشاريع القائمة فقط |
| `app/Http/Controllers/Apps/DineTableController.php:124,141` | `$area->outlet_id === null` يُعتبر متاحًا للجميع | حذف التسامح؛ المنطقة بلا فرع تصبح غير متاحة |
| `app/Http/Controllers/Apps/PayableController.php` | `orWhereDoesntHave('purchaseOrder')` أُزيل لكن السلوك يحتاج تأكيدًا | تأكد أن كل payable له PO أو مصدر صريح |

**معيار القبول:** لا يوجد `if ($legacy)` أو `=== null` كبديل عن فرع في طبقة التشغيل.

---

### الخطوة ٥ — إزالة حراس المخطط الدفاعية

**المشكلة:** `Schema::hasColumn` / `Schema::hasTable` في طبقة التشغيل تعني «قد لا يكون المخطط
جاهزًا» — وهو ما لا يصح بعد فرض `NOT NULL`.

| الملف | الموضع |
| --- | --- |
| `app/Http/Controllers/Apps/OutletController.php` | ١٢٣، ١٢٩ |
| `app/Http/Controllers/Apps/SalesReturnController.php` | ٦٧٨ |
| `app/Http/Controllers/Apps/TransactionController.php` | ٧٦٧ |
| `app/Http/Middleware/HandleInertiaRequests.php` | ٢٠٧ |
| `app/Services/OutletAccessService.php` | ١٨٧ |

**المطلوب:** احذف الحراس من طبقة التشغيل. أبقِها فقط في migrations وأوامر الصيانة.

**معيار القبول:** `grep -r "Schema::has" app/` يُرجع migrations/commands فقط.

---

### الخطوة ٦ — تنظيف نهائي

- احذف تعليقات `// ponytail: legacy ...` من `PosApiController` و `TransactionController`.
- راجع `dine_in_pay_online_enabled` في `docs/features/dine-in.md` — إعداد ميت، احذفه أو فعّله.
- أزل فرع `revamp-frontend` المذكور في `AGENTS.md:13` إن لم يكن مستخدمًا.
- حدّث `docs/architecture-overview.md` بالأعداد الحقيقية بعد الدمج.
- ثبّت سياسة في `AGENTS.md`: **ممنوع إضافة طبقة توافق بلا migration ترقية موثّقة**.

---

## ٥) تعريف «تم» — Definition of Done

النظام يُعتبر «مبنيًا مرة واحدة» عند تحقّق كل ما يلي:

```bash
# 1. لا منطق توافق في طبقة التشغيل
grep -rn "legacy_single_outlet\|includeLegacy\|count() <= 1" app/     # لا نتائج

# 2. لا قراءة للعمود المحذوف
grep -rn "products\.stock" app/                                        # لا نتائج

# 3. لا حراس مخطط في التشغيل
grep -rn "Schema::has" app/ | grep -v migrations | grep -v Commands     # لا نتائج

# 4. المخطط نظيف
php artisan migrate:fresh --seed
php artisan outlet:audit --strict                                       # ينجح

# 5. الاختبارات خضراء
php artisan test
```

---

## ٦) الترقية لتثبيت قائم

```bash
# نسخة احتياطية أولًا — إلزامي
mysqldump -u root -p point_of_sales > backup.sql

php artisan down
php artisan migrate      # يشغّل 000001 ثم 000002 (تعبئة رجعية غير مدمّرة)
php artisan outlet:audit --strict
php artisan up
```

ماذا تفعل `2026_10_28_000002` تلقائيًا:
1. تنشئ فرع `PUSAT` إن لم يوجد، وتربط به المستودعات بلا فرع.
2. تُسند كل مستخدم بلا إسناد إلى `PUSAT` (فرع افتراضي).
3. تعبّئ `warehouse_id` الفارغ في ٩ جداول + `stock_transfers`.
4. لا تحذف أي شيء، وقابلة للتشغيل المتكرر.

> ⚠️ **بعد الترقية لن يعمل أي مستخدم بلا إسناد فرع.** راجع الإسنادات قبل النشر إن كان
> لديك تشغيل متعدد الفروع.

---

## ٧) المخاطر والمحاذير

| الخطر | الأثر | التخفيف |
| --- | --- | --- |
| تشديد `NOT NULL` قبل تصفير الصفوف اليتيمة | فشل الـ migration | نفّذ الخطوة ٢ فقط بعد نجاح `outlet:audit --strict` |
| حذف migrations `add_*` | تثبيت قديم لم يُرقَّ يكسر | لا تحذفها إلا بعد إصدار إصدار وسيط يضمن الترقية |
| توحيد مسار الدفع | تغيير سلوك الـ API للعملاء القدامى | أعلنه في `CHANGELOG` كتغيير Breaking مع فترة توافق |
| إسقاط `products.stock` | تقارير خارجية تقرأ العمود | وثّق البديل: `product_warehouse` + `Product::stockTotal()` |
| فقدان إسنادات الفروع | مستخدمون يفقدون الوصول بعد الترقية | راجع `user_outlets` قبل `up` |

---

## ٨) سجل تاريخي — كيف كان النظام يعمل قبل إعادة البناء

أُبقي هذا القسم لأن تثبيتات `v3.0.x` القائمة قد تحتاج معرفة السلوك السابق.

**قبل `v3.0.0`:** مستودع واحد + فرع واحد. `products.stock` هو المخزون، `warehouse_id` nullable،
الإعدادات صفوف عامة، ولا وجود لـ `outlets` ولا `user_outlets`.

**`v3.0.0` (Breaking):** أُضيف `outlets` + `user_outlets` + `OutletAccessService`، وأُبقي
`legacy_single_outlet_bypass` للتوافق. `PUSAT` مستودع مركزي غير مفعّل للبيع.

**`v3.0.1` / `v3.0.2`:** استخراج `CheckoutService` + `CheckoutContext`، وتوحيد مسار الويب و API.

**`v3.0.3`:** تشديد أمني (Sanctum expiry، `pos-access`، `hash_equals`، HSTS، `X-Service-Token`).

**بعد إعادة البناء (الفرع الحالي):** حُذفت كل الآليات أعلاه عدا طبقة الإعدادات العامة
التي أُعيد تصنيفها كتصميم مقصود، وأُسقط `products.stock` نهائيًا.
