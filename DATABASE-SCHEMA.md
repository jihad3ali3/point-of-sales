# توثيق قاعدة بيانات نظام نقاط البيع (POS)

**اسم المشروع:** Point of Sales (Laravel)
**مصدر التوثيق:** ملفات migration داخل `database/migrations` فقط (100 ملف migration)
**عدد الجداول:** 78 جدولًا
**تاريخ التوثيق:** 2026-11-06

> هذا الملف يوثّق **كل** جداول قاعدة البيانات كما تُنشئها ملفات migration فعليًا، دون إضافة أي جدول غير موجود ودون حذف أي جدول. الأعمدة التي تُضاف أو تُعدَّل في migrations لاحقة تم دمجها في الجدول النهائي مع الإشارة إلى ذلك عند الحاجة.

---

## كيف تقرأ هذا الملف؟

- **اسم الجدول** يُكتب بالإنجليزية أولًا ثم بالعربية، لأن اسم الجدول في قاعدة البيانات إنجليزي.
- لكل جدول قائمة كاملة بالأعمدة، وكل عمود موثّق في خمسة أعمدة:
  1. **اسم الحقل** (اسم العمود في قاعدة البيانات).
  2. **نوع الحقل** (نوع Laravel/MySQL مع القيود الأساسية: PK / FK / Unique / Nullable / Default).
  3. **الاسم بالعربية**.
  4. **الاسم بالإنجليزية**.
  5. **الوظيفة** (الغرض من العمود في النظام).
- تحت كل جدول **شرح مختصر** لوظيفة الجدول، مع **تفصيل** لأي سلوك غير بديهي (مثل الأعمدة المشفّرة، أو الجداول الوسيطة متعددة الأشكال، أو العلاقات الشرطية).
- رموز مختصرة:
  - `PK` = المفتاح الأساسي (Primary Key).
  - `FK` = مفتاح أجنبي (Foreign Key).
  - `UQ` = فريد (Unique).
  - `IDX` = فهرس (Index).
  - `NULL` = يقبل القيمة الفارغة.
  - `DEF` = قيمة افتراضية (Default).

### ملاحظات عامة على الأنواع

| النوع في migration | النوع الفعلي في MySQL | الاستخدام |
|---|---|---|
| `id()` / `bigIncrements()` | `bigint unsigned` AUTO_INCREMENT | المعرّف الأساسي لأغلب الجداول |
| `foreignId()` | `bigint unsigned` | مفاتيح أجنبية |
| `string()` | `varchar(255)` | نصوص قصيرة |
| `string(n)` | `varchar(n)` | نصوص محددة الطول |
| `char(n)` | `char(n)` | رموز ثابتة الطول (مثل رموز المناطق) |
| `text()` | `text` | نصوص طويلة |
| `longText()` | `longtext` | نصوص طويلة جدًا (مثل payload المهام) |
| `mediumText()` | `mediumtext` | نصوص متوسطة (مثل قيمة الكاش) |
| `integer()` | `int` | أعداد صحيحة |
| `unsignedInteger()` | `int unsigned` | أعداد صحيحة موجبة |
| `bigInteger()` | `bigint` | أعداد كبيرة (مبالغ) |
| `unsignedBigInteger()` | `bigint unsigned` | كميات ومبالغ موجبة |
| `decimal(p,s)` | `decimal(p,s)` | مبالغ عشرية (15,2) وأسعار الوحدات (15,4) |
| `boolean()` | `tinyint(1)` | أعلام نعم/لا |
| `enum()` | `enum(...)` | قائمة قيم محددة |
| `json()` | `json` | بيانات مرنة (إعدادات، سجلات، حمولات) |
| `uuid()` | `char(36)` | معرّف UUID |
| `date()` | `date` | تاريخ فقط |
| `timestamp()` | `timestamp` | تاريخ ووقت |
| `timestamps()` | `created_at` + `updated_at` | تاريخ الإنشاء والتحديث |

### ملاحظة على بادئة أسماء جداول المناطق

ملفات هجرة المناطق الأربعة تستخدم `config('laravolt.indonesia.table_prefix')`، وقيمة هذا الإعداد في `config/laravolt/indonesia.php` هي **`indonesia_`**. لذلك الأسماء النهائية الفعلية هي:
`indonesia_provinces` و`indonesia_cities` و`indonesia_districts` و`indonesia_villages`.

### ملاحظة على جداول الصلاحيات

جدول الصلاحيات يُنشئ خمسة جداول عبر `config('permission.table_names')`، وقيمها الافتراضية في `config/permission.php` هي:
`permissions` و`roles` و`model_has_permissions` و`model_has_roles` و`role_has_permissions`.
كما أن `config('permission.teams')` = `false`، لذلك **لا توجد** أعمدة `team_id` في هذه الجداول.

---

## فهرس الجداول (78 جدولًا)

| # | اسم الجدول | الاسم بالعربية | التصنيف |
|---:|---|---|---|
| 1 | `users` | المستخدمون | النظام والمصادقة |
| 2 | `password_reset_tokens` | رموز إعادة تعيين كلمة المرور | النظام والمصادقة |
| 3 | `sessions` | جلسات المستخدمين | النظام والمصادقة |
| 4 | `personal_access_tokens` | رموز الوصول الشخصية | النظام والمصادقة |
| 5 | `permissions` | الصلاحيات | النظام والصلاحيات |
| 6 | `roles` | الأدوار | النظام والصلاحيات |
| 7 | `model_has_permissions` | صلاحيات النماذج | النظام والصلاحيات |
| 8 | `model_has_roles` | أدوار النماذج | النظام والصلاحيات |
| 9 | `role_has_permissions` | صلاحيات الأدوار | النظام والصلاحيات |
| 10 | `audit_logs` | سجل التدقيق | النظام والصلاحيات |
| 11 | `settings` | الإعدادات | الإعدادات والفروع |
| 12 | `outlets` | الفروع | الإعدادات والفروع |
| 13 | `user_outlets` | علاقة المستخدمين بالفروع | الإعدادات والفروع |
| 14 | `indonesia_provinces` | المحافظات | المناطق الجغرافية |
| 15 | `indonesia_cities` | المدن | المناطق الجغرافية |
| 16 | `indonesia_districts` | المراكز والأحياء | المناطق الجغرافية |
| 17 | `indonesia_villages` | القرى | المناطق الجغرافية |
| 18 | `customers` | العملاء | العملاء والولاء |
| 19 | `loyalty_point_histories` | سجل نقاط الولاء | العملاء والولاء |
| 20 | `customer_vouchers` | قسائم العملاء | العملاء والولاء |
| 21 | `customer_segments` | شرائح العملاء | العملاء والتسويق |
| 22 | `customer_segment_memberships` | عضوية شرائح العملاء | العملاء والتسويق |
| 23 | `customer_campaigns` | الحملات التسويقية | العملاء والتسويق |
| 24 | `customer_campaign_logs` | سجلات الحملات | العملاء والتسويق |
| 25 | `receivables` | الذمم المدينة | العملاء والمالية |
| 26 | `receivable_payments` | دفعات الذمم المدينة | العملاء والمالية |
| 27 | `customer_credits` | أرصدة العملاء | العملاء والمالية |
| 28 | `categories` | الفئات | الكتالوج والمنتجات |
| 29 | `products` | المنتجات | الكتالوج والمنتجات |
| 30 | `units` | الوحدات | الكتالوج والمنتجات |
| 31 | `product_units` | وحدات المنتجات | الكتالوج والمنتجات |
| 32 | `product_warehouse` | مخزون المنتجات بالمستودعات | الكتالوج والمخزون |
| 33 | `product_batches` | دفعات المنتجات | الكتالوج والمخزون |
| 34 | `composite_product_items` | مكونات المنتجات المركبة | الكتالوج والمنتجات |
| 35 | `product_notification_reads` | حالة قراءة إشعارات المنتجات | الكتالوج والمنتجات |
| 36 | `price_lists` | قوائم الأسعار | التسعير والعروض |
| 37 | `price_list_items` | بنود قوائم الأسعار | التسعير والعروض |
| 38 | `pricing_rules` | قواعد التسعير | التسعير والعروض |
| 39 | `pricing_rule_qty_breaks` | شرائح كميات التسعير | التسعير والعروض |
| 40 | `pricing_rule_bundle_items` | بنود حزم التسعير | التسعير والعروض |
| 41 | `pricing_rule_buy_get_items` | بنود اشترِ واحصل | التسعير والعروض |
| 42 | `warehouses` | المستودعات | المستودعات والمخزون |
| 43 | `stock_mutations` | حركات المخزون | المستودعات والمخزون |
| 44 | `stock_transfers` | تحويلات المخزون | المستودعات والمخزون |
| 45 | `stock_transfer_items` | بنود تحويلات المخزون | المستودعات والمخزون |
| 46 | `stock_opnames` | عمليات الجرد | المستودعات والمخزون |
| 47 | `stock_opname_items` | بنود الجرد | المستودعات والمخزون |
| 48 | `purchase_orders` | أوامر الشراء | المشتريات والموردون |
| 49 | `purchase_order_items` | بنود أوامر الشراء | المشتريات والموردون |
| 50 | `goods_receivings` | استلامات البضاعة | المشتريات والموردون |
| 51 | `goods_receiving_items` | بنود استلام البضاعة | المشتريات والموردون |
| 52 | `suppliers` | الموردون | المشتريات والموردون |
| 53 | `payables` | الذمم الدائنة | المشتريات والمالية |
| 54 | `payable_payments` | دفعات الذمم الدائنة | المشتريات والمالية |
| 55 | `supplier_returns` | مرتجعات الموردين | المشتريات والموردون |
| 56 | `supplier_return_items` | بنود مرتجعات الموردين | المشتريات والموردون |
| 57 | `carts` | سلة نقطة البيع | المبيعات ونقطة البيع |
| 58 | `transactions` | المعاملات | المبيعات ونقطة البيع |
| 59 | `transaction_details` | تفاصيل المعاملات | المبيعات ونقطة البيع |
| 60 | `transaction_tenders` | وسائل دفع المعاملات | المبيعات ونقطة البيع |
| 61 | `transaction_detail_batch_allocations` | تخصيص دفعات تفاصيل المعاملات | المبيعات والمخزون |
| 62 | `profits` | الأرباح | المبيعات والمالية |
| 63 | `sales_returns` | مرتجعات المبيعات | المبيعات ونقطة البيع |
| 64 | `sales_return_items` | بنود مرتجعات المبيعات | المبيعات ونقطة البيع |
| 65 | `cashier_shifts` | ورديات الكاشير | المبيعات ونقطة البيع |
| 66 | `shift_cash_movements` | حركات النقد بالوردية | المبيعات ونقطة البيع |
| 67 | `discount_approval_logs` | سجل موافقات الخصم | المبيعات والصلاحيات |
| 68 | `dine_areas` | مناطق الجلوس | المطعم والجلوس |
| 69 | `dine_tables` | طاولات المطعم | المطعم والجلوس |
| 70 | `dine_orders` | طلبات المطعم | المطعم والجلوس |
| 71 | `dine_order_items` | بنود طلبات المطعم | المطعم والجلوس |
| 72 | `payment_settings` | إعدادات الدفع | المدفوعات |
| 73 | `bank_accounts` | الحسابات البنكية | المدفوعات |
| 74 | `cache` | ذاكرة التخزين المؤقت | النظام والتشغيل |
| 75 | `cache_locks` | أقفال ذاكرة التخزين | النظام والتشغيل |
| 76 | `jobs` | مهام قائمة الانتظار | النظام والتشغيل |
| 77 | `job_batches` | دفعات المهام | النظام والتشغيل |
| 78 | `failed_jobs` | المهام الفاشلة | النظام والتشغيل |

---

## 1. النظام والمصادقة والصلاحيات

### 1. `users` — المستخدمون (Users)

جدول الحسابات الأساسي لكل من يستخدم النظام: الكاشير، المدير، المحاسب، وغيرهم. كل العمليات في النظام (المعاملات، الورديات، الجرد، المشتريات) ترتبط بهذا الجدول.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي للمستخدم |
| 2 | `name` | `string(255)` | الاسم الكامل | Full Name | اسم المستخدم الظاهر في النظام |
| 3 | `email` | `string(255)` UQ | البريد الإلكتروني | Email | بريد الدخول، فريد ولا يتكرر |
| 4 | `email_verified_at` | `timestamp` NULL | تاريخ التحقق من البريد | Email Verified At | وقت تأكيد ملكية البريد (اختياري) |
| 5 | `password` | `string(255)` | كلمة المرور | Password | كلمة المرور المشفّرة (Hash) |
| 6 | `remember_token` | `string(100)` NULL | رمز تذكر الجلسة | Remember Token | رمز يُستخدم لإبقاء المستخدم مسجلًا |
| 7 | `avatar` | `string(255)` NULL | الصورة الرمزية | Avatar | مسار صورة المستخدم (أُضيف لاحقًا) |
| 8 | `locale` | `string(10)` DEF `id` | اللغة المفضلة | Locale | لغة واجهة المستخدم (افتراضيًا إندونيسي) |
| 9 | `completed_tours` | `json` NULL | الجولات المكتملة | Completed Tours | قائمة الجولات التعريفية التي شاهدها المستخدم |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء الحساب |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث للبيانات |

**ملاحظات:**
- عمود `avatar` أُضيف في migration منفصل، ثم أُضيف مرة أخرى بشكل شرطي (`if missing`) لضمان وجوده، والنتيجة النهائية عمود واحد فقط.
- العمود `completed_tours` يخزّن JSON لتتبّع الجولات الإرشادية (Onboarding Tour) لكل مستخدم.

---

### 2. `password_reset_tokens` — رموز إعادة تعيين كلمة المرور (Password Reset Tokens)

جدول مؤقت لتخزين رموز إعادة تعيين كلمة المرور المرسلة إلى بريد المستخدم.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `email` | `string(255)` PK | البريد الإلكتروني | Email | مفتاح أساسي، يطابق بريد المستخدم |
| 2 | `token` | `string(255)` | الرمز | Token | رمز إعادة التعيين السري |
| 3 | `created_at` | `timestamp` NULL | تاريخ الإنشاء | Created At | وقت إنشاء الرمز (يُستخدم لمعرفة انتهاء صلاحيته) |

**ملاحظة:** لا يحتوي هذا الجدول على `updated_at`؛ لأن السجل يُنشأ عند الطلب فقط، ويُحذف بعد الاستخدام أو انتهاء الصلاحية.

---

### 3. `sessions` — جلسات المستخدمين (Sessions)

جدول جلسات تسجيل الدخول، ويُستخدم لتخزين حالة الجلسة الحالية لكل مستخدم (سواء في قاعدة البيانات أو عبر driver الجلسات).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `string(255)` PK | معرّف الجلسة | Session ID | معرّف فريد للجلسة |
| 2 | `user_id` | `bigint unsigned` FK NULL IDX | معرّف المستخدم | User ID | صاحب الجلسة (مرتبط بـ `users.id`) |
| 3 | `ip_address` | `string(45)` NULL | عنوان IP | IP Address | عنوان IP المستخدم في تسجيل الدخول |
| 4 | `user_agent` | `text` NULL | متصفح/جهاز المستخدم | User Agent | بيانات المتصفح والجهاز |
| 5 | `payload` | `longtext` | حمولة الجلسة | Session Payload | بيانات الجلسة المشفّرة/المسلسلة |
| 6 | `last_activity` | `integer` IDX | آخر نشاط | Last Activity | طابع زمني لآخر نشاط (يُستخدم للتنظيف) |

---

### 4. `personal_access_tokens` — رموز الوصول الشخصية (Personal Access Tokens)

جدول رموز Sanctum للوصول إلى واجهات API نيابةً عن مستخدم (مثل تطبيق الجوال أو التكاملات).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `tokenable_type` | `string(255)` | نوع الكيان المالك | Tokenable Type | نوع النموذج المالك للرمز (Morph) |
| 3 | `tokenable_id` | `bigint unsigned` | معرّف الكيان المالك | Tokenable ID | معرّف المستخدم المالك للرمز |
| 4 | `name` | `text` | اسم الرمز | Token Name | اسم تعريفي للرمز (مثل: تطبيق الجوال) |
| 5 | `token` | `string(64)` UQ | قيمة الرمز | Token | قيمة الرمز المشفّرة (Hash) |
| 6 | `abilities` | `text` NULL | الصلاحيات | Abilities | قائمة الصلاحيات المسموحة للرمز |
| 7 | `last_used_at` | `timestamp` NULL | آخر استخدام | Last Used At | آخر وقت استُخدم فيه الرمز |
| 8 | `expires_at` | `timestamp` NULL IDX | تاريخ الانتهاء | Expires At | وقت انتهاء صلاحية الرمز |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء الرمز |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث للرمز |

**ملاحظة:** العمودان `tokenable_type` و`tokenable_id` يشكلان علاقة **متعددة الأشكال (Polymorphic)**، أي أن الرمز قد يرتبط بأي نموذج (وليس المستخدم فقط)، مع فهرس مركب عليهما.

---

### 5. `permissions` — الصلاحيات (Permissions)

جدول يعرّف كل صلاحية متاحة في النظام (مثل: إدارة المنتجات، عرض التقارير، إلغاء معاملة).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `name` | `string(255)` | اسم الصلاحية | Permission Name | الاسم البرمجي للصلاحية (مثل `products.create`) |
| 3 | `guard_name` | `string(255)` | اسم الحارس | Guard Name | حارس المصادقة (مثل `web` أو `api`) |
| 4 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء الصلاحية |
| 5 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`name`, `guard_name`)، أي لا تتكرر الصلاحية بنفس الاسم داخل نفس الحارس.

---

### 6. `roles` — الأدوار (Roles)

جدول الأدوار التي تُجمع تحتها الصلاحيات (مثل: مدير، كاشير، محاسب).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `name` | `string(255)` | اسم الدور | Role Name | اسم الدور الظاهر (مثل `admin`) |
| 3 | `guard_name` | `string(255)` | اسم الحارس | Guard Name | حارس المصادقة المرتبط بالدور |
| 4 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء الدور |
| 5 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`name`, `guard_name`).

---

### 7. `model_has_permissions` — صلاحيات النماذج (Model Has Permissions)

جدول وسيط يربط أي نموذج (مستخدم) بصلاحية مباشرة، دون المرور بدور. يستخدم علاقة متعددة الأشكال لدعم أكثر من نوع نموذج.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `permission_id` | `bigint unsigned` FK PK | معرّف الصلاحية | Permission ID | يشير إلى `permissions.id` |
| 2 | `model_type` | `string(255)` PK | نوع النموذج | Model Type | كلاس النموذج المالك (مثل `App\Models\User`) |
| 3 | `model_id` | `bigint unsigned` PK | معرّف النموذج | Model ID | معرّف السجل المالك للصلاحية |

**قيود:** مفتاح أساسي مركب على (`permission_id`, `model_id`, `model_type`)، وفهرس على (`model_id`, `model_type`)، ومفتاح أجنبي على `permission_id` مع حذف متسلسل (Cascade).

**ملاحظة:** هذا الجدول **وسيط متعدد الأشكال**؛ أي أنه يربط الصلاحية بأي نموذج وليس المستخدم فقط.

---

### 8. `model_has_roles` — أدوار النماذج (Model Has Roles)

جدول وسيط يربط أي نموذج (مستخدم) بدور، بنفس فكرة الجدول السابق.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `role_id` | `bigint unsigned` FK PK | معرّف الدور | Role ID | يشير إلى `roles.id` |
| 2 | `model_type` | `string(255)` PK | نوع النموذج | Model Type | كلاس النموذج المالك |
| 3 | `model_id` | `bigint unsigned` PK | معرّف النموذج | Model ID | معرّف السجل المالك للدور |

**قيود:** مفتاح أساسي مركب على (`role_id`, `model_id`, `model_type`)، وفهرس على (`model_id`, `model_type`)، ومفتاح أجنبي على `role_id` مع حذف متسلسل.

---

### 9. `role_has_permissions` — صلاحيات الأدوار (Role Has Permissions)

جدول وسيط يربط الدور بالصلاحية، أي يحدد ما يمكن للدور فعله.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `permission_id` | `bigint unsigned` FK PK | معرّف الصلاحية | Permission ID | يشير إلى `permissions.id` |
| 2 | `role_id` | `bigint unsigned` FK PK | معرّف الدور | Role ID | يشير إلى `roles.id` |

**قيود:** مفتاح أساسي مركب على (`permission_id`, `role_id`)، ومفاتيح أجنبية مع حذف متسلسل.

---

### 10. `audit_logs` — سجل التدقيق (Audit Logs)

سجل غير قابل للتعديل يسجل كل عملية حساسة في النظام (إنشاء، تعديل، حذف، اعتماد...) مع بيانات ما قبل وبعد التغيير.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `user_id` | `bigint unsigned` FK NULL | معرّف المستخدم | User ID | المستخدم الذي نفّذ العملية |
| 3 | `event` | `string(255)` | الحدث | Event | اسم العملية (مثل `created`، `approved`) |
| 4 | `module` | `string(255)` | الوحدة | Module | وحدة النظام (مثل `sales`، `inventory`) |
| 5 | `auditable_type` | `string(255)` NULL | نوع الكيان المتأثر | Auditable Type | كلاس النموذج المتأثر (Morph) |
| 6 | `auditable_id` | `bigint unsigned` NULL | معرّف الكيان المتأثر | Auditable ID | معرّف السجل المتأثر |
| 7 | `target_label` | `string(255)` NULL | وصف الهدف | Target Label | نص مقروء يصف الهدف (مثل رقم الفاتورة) |
| 8 | `description` | `text` | الوصف | Description | وصف تفصيلي للعملية |
| 9 | `before` | `json` NULL | البيانات قبل التغيير | Before | لقطة JSON للبيانات قبل التعديل |
| 10 | `after` | `json` NULL | البيانات بعد التغيير | After | لقطة JSON للبيانات بعد التعديل |
| 11 | `meta` | `json` NULL | بيانات إضافية | Meta | أي بيانات مساندة للعملية |
| 12 | `ip_address` | `string(45)` NULL | عنوان IP | IP Address | عنوان IP المنفّذ للعملية |
| 13 | `user_agent` | `text` NULL | متصفح/جهاز المستخدم | User Agent | بيانات الجهاز المستخدم |
| 14 | `created_at` | `timestamp` DEF `CURRENT_TIMESTAMP` | تاريخ العملية | Created At | وقت حدوث العملية (لا يوجد `updated_at`) |

**فهارس:** (`module`, `event`)، (`auditable_type`, `auditable_id`)، (`created_at`).

**ملاحظة:** هذا الجدول للقراءة فقط (Append-only)؛ لا يُعدّل ولا يُحذف، ويُستخدم للتدقيق والامتثال.

---

## 2. الإعدادات والفروع

### 11. `settings` — الإعدادات (Settings)

جدول مفتاح/قيمة لتخزين إعدادات النظام، ويدعم إعدادات عامة وإعدادات خاصة بكل فرع.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع الخاص بهذا الإعداد؛ `NULL` يعني إعدادًا عامًا |
| 3 | `key` | `string(255)` UQ | المفتاح | Key | اسم الإعداد (مثل `store_name`) |
| 4 | `value` | `text` NULL | القيمة | Value | قيمة الإعداد |
| 5 | `description` | `string(255)` NULL | الوصف | Description | وصف مختصر وظيفي الإعداد |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء الإعداد |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد في البداية على `key`، ثم أُلغي وأُعيد كفريد مركب على (`key`, `outlet_id`) لدعم إعدادات متعددة الفروع.

**ملاحظات:**
- الإعدادات العامة تُخزّن بـ `outlet_id = NULL`، وإعدادات الفرع تُخزّن بمعرّف الفرع.
- عند قراءة إعداد فرع، يبحث النظام أولًا عن قيمة الفرع، ثم يعود إلى القيمة العامة (Fallback).
- من الإعدادات المشهورة: `store_name`، `store_address`، `monthly_sales_target`، `tax_default_rate`، `store_npwp`، `store_nib`، وإعدادات الولاء والخصومات.

---

### 12. `outlets` — الفروع (Outlets)

جدول يمثل نقاط البيع/الفروع التابعة للمنشأة. كل معاملة ومخزون وإعداد يمكن أن يرتبط بفرع.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `string(20)` UQ | رمز الفرع | Outlet Code | رمز فريد مختصر (مثل `PUSAT`) |
| 3 | `name` | `string(100)` | اسم الفرع | Outlet Name | اسم الفرع الظاهر |
| 4 | `is_active` | `boolean` DEF `true` | مفعّل | Is Active | هل الفرع مستخدم حاليًا؟ |
| 5 | `is_sales_enabled` | `boolean` DEF `true` | مبيعاته مفعّلة | Sales Enabled | هل يُسمح بالبيع في هذا الفرع؟ |
| 6 | `address` | `text` NULL | العنوان | Address | عنوان الفرع |
| 7 | `phone` | `string(20)` NULL | الهاتف | Phone | رقم هاتف الفرع |
| 8 | `email` | `string(255)` NULL | البريد الإلكتروني | Email | بريد الفرع |
| 9 | `logo` | `string(255)` NULL | الشعار | Logo | مسار شعار الفرع |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء الفرع |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- الفرع المركزي `PUSAT` يُنشأ تلقائيًا من مستودع `PUSAT`، ويُجعل `is_sales_enabled = false` لأنه مخصص للمخزون المركزي وليس للبيع المباشر.
- الفروع هي مستوى أعلى من المستودعات: المستودع يمثل موقع تخزين، وقد يتبع فرعًا (عبر `warehouses.outlet_id`).

---

### 13. `user_outlets` — علاقة المستخدمين بالفروع (User Outlets)

جدول وسيط يحدد الفروع التي يمكن للمستخدم الوصول إليها، وأيها افتراضي.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `user_id` | `bigint unsigned` FK PK | معرّف المستخدم | User ID | المستخدم (مرتبط بـ `users.id`) |
| 2 | `outlet_id` | `bigint unsigned` FK PK | معرّف الفرع | Outlet ID | الفرع (مرتبط بـ `outlets.id`) |
| 3 | `is_default` | `boolean` DEF `false` | فرع افتراضي | Is Default | هل هذا هو الفرع الافتراضي للمستخدم؟ |
| 4 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إضافة الوصول |
| 5 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`user_id`, `outlet_id`)، ومفاتيح أجنبية مع حذف متسلسل.

**ملاحظة:** عند إنشاء هذا الجدول، أضيف كل المستخدمين تلقائيًا إلى فرع `PUSAT` كفرع افتراضي.

---

## 3. المناطق الجغرافية

> هذه الجداول الأربعة تأتي من حزمة Laravolt Indonesia، وتُستخدم لتخزين عناوين العملاء بشكل موحّد. البادئة الفعلية `indonesia_`، والعلاقة بينها هرمية: محافظة ← مدينة ← مركز/حي ← قرية.

### 14. `indonesia_provinces` — المحافظات (Provinces)

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `char(2)` UQ | رمز المحافظة | Province Code | رمز المحافظة (مفتاح مرجعي) |
| 3 | `name` | `string(255)` | اسم المحافظة | Province Name | اسم المحافظة |
| 4 | `meta` | `text` NULL | بيانات إضافية | Meta | بيانات JSON مساندة |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

---

### 15. `indonesia_cities` — المدن (Cities)

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `char(4)` UQ | رمز المدينة | City Code | رمز المدينة الفريد |
| 3 | `province_code` | `char(2)` FK | رمز المحافظة | Province Code | يشير إلى `indonesia_provinces.code` |
| 4 | `name` | `string(255)` | اسم المدينة | City Name | اسم المدينة/المنطقة |
| 5 | `meta` | `text` NULL | بيانات إضافية | Meta | بيانات JSON مساندة |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** مفتاح أجنبي على `province_code` مع تحديث متسلسل (Cascade) ومنع الحذف (Restrict).

---

### 16. `indonesia_districts` — المراكز والأحياء (Districts)

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `char(7)` UQ | رمز المركز/الحي | District Code | رمز فريد |
| 3 | `city_code` | `char(4)` FK | رمز المدينة | City Code | يشير إلى `indonesia_cities.code` |
| 4 | `name` | `string(255)` | اسم المركز/الحي | District Name | الاسم |
| 5 | `meta` | `text` NULL | بيانات إضافية | Meta | بيانات JSON مساندة |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** مفتاح أجنبي على `city_code` مع تحديث متسلسل ومنع حذف.

---

### 17. `indonesia_villages` — القرى (Villages)

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `char(10)` UQ | رمز القرية | Village Code | رمز فريد |
| 3 | `district_code` | `char(7)` FK | رمز المركز/الحي | District Code | يشير إلى `indonesia_districts.code` |
| 4 | `name` | `string(255)` | اسم القرية | Village Name | الاسم |
| 5 | `meta` | `text` NULL | بيانات إضافية | Meta | بيانات JSON مساندة |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** مفتاح أجنبي على `district_code` مع تحديث متسلسل ومنع حذف.

---
## 4. العملاء والولاء والتسويق

### 18. `customers` — العملاء (Customers)

جدول بيانات العملاء، ويشمل بيانات التواصل والعنوان وبيانات عضوية الولاء.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `name` | `string(255)` | اسم العميل | Customer Name | اسم العميل |
| 3 | `no_telp` | `bigint` | رقم الهاتف | Phone Number | رقم هاتف العميل (يُخزّن كرقم) |
| 4 | `address` | `text` | العنوان | Address | عنوان العميل |
| 5 | `province_id` | `string(10)` NULL | معرّف المحافظة | Province ID | رمز المحافظة (مرجعي) |
| 6 | `province_name` | `string(255)` NULL | اسم المحافظة | Province Name | اسم المحافظة |
| 7 | `regency_id` | `string(10)` NULL | معرّف المدينة | Regency ID | رمز المدينة/المنطقة |
| 8 | `regency_name` | `string(255)` NULL | اسم المدينة | Regency Name | اسم المدينة/المنطقة |
| 9 | `district_id` | `string(10)` NULL | معرّف المركز/الحي | District ID | رمز المركز/الحي |
| 10 | `district_name` | `string(255)` NULL | اسم المركز/الحي | District Name | اسم المركز/الحي |
| 11 | `village_id` | `string(10)` NULL | معرّف القرية | Village ID | رمز القرية |
| 12 | `village_name` | `string(255)` NULL | اسم القرية | Village Name | اسم القرية |
| 13 | `postal_code` | `string(10)` NULL | الرمز البريدي | Postal Code | الرمز البريدي |
| 14 | `is_loyalty_member` | `boolean` DEF `false` | عضو ولاء | Is Loyalty Member | هل العميل عضو في برنامج الولاء؟ |
| 15 | `member_code` | `string(255)` NULL UQ | رمز العضوية | Member Code | رمز فريد لعضوية الولاء |
| 16 | `loyalty_tier` | `string(30)` DEF `regular` | مستوى الولاء | Loyalty Tier | مستوى العضوية (regular/silver/gold/platinum) |
| 17 | `loyalty_points` | `unsignedInteger` DEF `0` | نقاط الولاء | Loyalty Points | رصيد النقاط الحالي |
| 18 | `loyalty_total_spent` | `unsignedBigInteger` DEF `0` | إجمالي الإنفاق | Total Spent | إجمالي ما أنفقه العميل (يُستخدم لتحديد المستوى) |
| 19 | `loyalty_transaction_count` | `unsignedInteger` DEF `0` | عدد معاملات الولاء | Loyalty Transactions | عدد المعاملات المحتسبة في الولاء |
| 20 | `loyalty_member_since` | `timestamp` NULL | تاريخ عضوية الولاء | Loyalty Member Since | متى أصبح عضوًا |
| 21 | `last_purchase_at` | `timestamp` NULL | آخر عملية شراء | Last Purchase At | آخر وقت شراء للعميل |
| 22 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء سجل العميل |
| 23 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** فهرس مركب على (`is_loyalty_member`, `loyalty_tier`).

**ملاحظات:**
- بيانات العنوان مكررة (معرّف + اسم) لتسهيل العرض دون الحاجة إلى joins متعددة.
- عمودا `loyalty_total_spent` و`loyalty_transaction_count` يُحدّثان تلقائيًا عند إتمام المعاملات، ويُستخدمان في حساب مستوى الولاء وفق الحدود الموجودة في جدول `settings`.

---

### 19. `loyalty_point_histories` — سجل نقاط الولاء (Loyalty Point Histories)

سجل زمني لكل تغيير في نقاط الولاء (كسب أو استبدال أو تصحيح).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `customer_id` | `bigint unsigned` FK | معرّف العميل | Customer ID | العميل صاحب الحركة |
| 3 | `transaction_id` | `bigint unsigned` FK NULL | معرّف المعاملة | Transaction ID | المعاملة المرتبطة (إن وجدت) |
| 4 | `type` | `string(30)` | نوع الحركة | Type | نوع الحركة (earn/redeem/adjustment...) |
| 5 | `points_delta` | `integer` DEF `0` | تغير النقاط | Points Delta | عدد النقاط المضافة أو المخصومة (سالب للاستبدال) |
| 6 | `balance_after` | `unsignedInteger` DEF `0` | الرصيد بعد الحركة | Balance After | رصيد النقاط بعد تنفيذ الحركة |
| 7 | `amount_delta` | `unsignedBigInteger` DEF `0` | تغير المبلغ | Amount Delta | أثر الحركة على المبلغ/الإنفاق |
| 8 | `reference` | `string(255)` NULL | مرجع الحركة | Reference | رقم مرجعي خارجي أو داخلي |
| 9 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الحركة |
| 10 | `created_at` | `timestamp` | تاريخ الحركة | Created At | وقت الحركة |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`customer_id`, `created_at`) و(`type`, `created_at`).

**ملاحظة:** `balance_after` يجعل إعادة بناء رصيد النقاط أمرًا سهلًا دون جمع كل الحركات.

---

### 20. `customer_vouchers` — قسائم العملاء (Customer Vouchers)

قسائم خصم صادرة لعميل محدد، بحد أدنى للطلب وتاريخ صلاحية.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع المالك للقسيمة |
| 3 | `customer_id` | `bigint unsigned` FK | معرّف العميل | Customer ID | العميل المالك للقسيمة |
| 4 | `code` | `string(255)` UQ | رمز القسيمة | Voucher Code | رمز فريد تستخدمه القسيمة |
| 5 | `name` | `string(255)` | اسم القسيمة | Voucher Name | اسم وصفي للقسيمة |
| 6 | `discount_type` | `string(20)` DEF `fixed_amount` | نوع الخصم | Discount Type | مبلغ ثابت أو نسبة مئوية |
| 7 | `discount_value` | `decimal(15,2)` | قيمة الخصم | Discount Value | قيمة الخصم (مبلغ أو نسبة) |
| 8 | `minimum_order` | `unsignedBigInteger` DEF `0` | الحد الأدنى للطلب | Minimum Order | أقل إجمالي يسمح باستخدام القسيمة |
| 9 | `is_active` | `boolean` DEF `true` | مفعّلة | Is Active | هل القسيمة سارية؟ |
| 10 | `is_used` | `boolean` DEF `false` | مستخدمة | Is Used | هل استُخدمت القسيمة؟ |
| 11 | `starts_at` | `timestamp` NULL | تاريخ البداية | Starts At | بداية صلاحية القسيمة |
| 12 | `expires_at` | `timestamp` NULL | تاريخ الانتهاء | Expires At | نهاية صلاحية القسيمة |
| 13 | `used_at` | `timestamp` NULL | تاريخ الاستخدام | Used At | وقت استخدام القسيمة |
| 14 | `used_transaction_id` | `bigint unsigned` FK NULL | معرّف المعاملة المستخدمة | Used Transaction ID | المعاملة التي استخدمت القسيمة |
| 15 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات القسيمة |
| 16 | `created_by` | `bigint unsigned` FK NULL | أنشأ بواسطة | Created By | المستخدم الذي أصدر القسيمة |
| 17 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 18 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`customer_id`, `is_active`, `is_used`) و(`code`, `is_active`).

**ملاحظة:** القسيمة مرتبطة بعميل واحد، وليست قسيمة عامة؛ لذلك لا يمكن لعميل آخر استخدامها.

---

### 21. `customer_segments` — شرائح العملاء (Customer Segments)

تعريف شرائح العملاء (يدوية أو تلقائية) لاستخدامها في العروض والحملات.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `name` | `string(255)` | اسم الشريحة | Segment Name | اسم الشريحة |
| 3 | `slug` | `string(255)` UQ | المعرّف النصي | Slug | معرّف نصي فريد للشريحة |
| 4 | `type` | `string(20)` | نوع الشريحة | Segment Type | يدوية (manual) أو تلقائية (auto) |
| 5 | `is_active` | `boolean` DEF `true` | مفعّلة | Is Active | هل الشريحة مستخدمة؟ |
| 6 | `description` | `text` NULL | الوصف | Description | وصف الشريحة |
| 7 | `auto_rule_type` | `string(40)` NULL | نوع القاعدة التلقائية | Auto Rule Type | نوع القاعدة (مثل: عدد المعاملات، إجمالي الإنفاق) |
| 8 | `rule_config` | `json` NULL | إعدادات القاعدة | Rule Config | معايير القاعدة التلقائية بصيغة JSON |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`type`, `is_active`).

**ملاحظة:** الشريحة التلقائية تُحدّث عضويتها بناءً على `rule_config`، بينما الشريحة اليدوية تُضاف إليها الأعضاء يدويًا.

---

### 22. `customer_segment_memberships` — عضوية شرائح العملاء (Customer Segment Memberships)

جدول وسيط يربط العميل بالشريحة، مع مصدر العضوية.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `customer_id` | `bigint unsigned` FK | معرّف العميل | Customer ID | العميل |
| 3 | `customer_segment_id` | `bigint unsigned` FK | معرّف الشريحة | Segment ID | الشريحة |
| 4 | `source` | `string(20)` | مصدر العضوية | Membership Source | يدوية أم تلقائية (manual/auto) |
| 5 | `matched_at` | `timestamp` NULL | تاريخ المطابقة | Matched At | متى طابق العميل قاعدة الشريحة |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإضافة |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`customer_id`, `customer_segment_id`)، وفهرس على (`customer_segment_id`, `source`)، وحذف متسلسل من الجهتين.

---

### 23. `customer_campaigns` — الحملات التسويقية (Customer Campaigns)

تعرّف الحملات التسويقية الموجهة لشرائح العملاء، مع قالب الرسالة وقناتها.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع المالك للحملة |
| 3 | `name` | `string(255)` | اسم الحملة | Campaign Name | اسم الحملة |
| 4 | `type` | `string(40)` | نوع الحملة | Campaign Type | نوع الحملة (تذكير، عرض، متابعة...) |
| 5 | `status` | `string(20)` DEF `draft` | حالة الحملة | Campaign Status | مسودة/مجدولة/منفذة/مكتملة |
| 6 | `channel` | `string(20)` DEF `internal` | القناة | Channel | قناة التواصل (داخلي، واتساب، بريد...) |
| 7 | `context_key` | `string(255)` NULL UQ | مفتاح السياق | Context Key | مفتاح فريد يمنع تكرار الحملة |
| 8 | `audience_filters` | `json` NULL | فلاتر الجمهور | Audience Filters | معايير اختيار الجمهور |
| 9 | `audience_snapshot` | `json` NULL | لقطة الجمهور | Audience Snapshot | عدد/قائمة الجمهور وقت التنفيذ |
| 10 | `message_template` | `text` NULL | قالب الرسالة | Message Template | نص الرسالة |
| 11 | `processed_at` | `timestamp` NULL | تاريخ التنفيذ | Processed At | وقت تنفيذ الحملة |
| 12 | `created_by` | `bigint unsigned` FK NULL | أنشأ بواسطة | Created By | المستخدم الذي أنشأ الحملة |
| 13 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 14 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`type`, `status`).

**ملاحظة:** `audience_snapshot` يحفظ حالة الجمهور وقت التنفيذ حتى لا تتغير نتائج الحملة إذا تغيرت بيانات العملاء لاحقًا.

---

### 24. `customer_campaign_logs` — سجلات الحملات (Customer Campaign Logs)

سجل تنفيذي لكل رسالة/إجراء مرتبط بحملة، ولمن أُرسل وما حالته.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `customer_campaign_id` | `bigint unsigned` FK | معرّف الحملة | Campaign ID | الحملة الأم |
| 3 | `customer_id` | `bigint unsigned` FK NULL | معرّف العميل | Customer ID | العميل المستهدف |
| 4 | `transaction_id` | `bigint unsigned` FK NULL | معرّف المعاملة | Transaction ID | معاملة مرتبطة (إن وجدت) |
| 5 | `receivable_id` | `bigint unsigned` FK NULL | معرّف الذمة المدينة | Receivable ID | ذمة مدينة مرتبطة (للتذكير) |
| 6 | `channel` | `string(20)` DEF `internal` | القناة | Channel | قناة الإرسال |
| 7 | `status` | `string(20)` DEF `pending` | الحالة | Status | قيد الانتظار/تم الإرسال/فشل |
| 8 | `payload` | `json` NULL | الحمولة | Payload | بيانات الرسالة المرسلة |
| 9 | `sent_at` | `timestamp` NULL | تاريخ الإرسال | Sent At | وقت الإرسال الفعلي |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء السجل |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`customer_campaign_id`, `status`) و(`customer_id`, `status`).

---

### 25. `receivables` — الذمم المدينة (Receivables)

يمثل مبلغًا مستحقًا على عميل مقابل فاتورة/معاملة، مع حالة السداد.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `customer_id` | `bigint unsigned` FK NULL | معرّف العميل | Customer ID | العميل المدين |
| 3 | `transaction_id` | `bigint unsigned` FK NULL | معرّف المعاملة | Transaction ID | المعاملة المرتبطة |
| 4 | `invoice` | `string(255)` IDX | رقم الفاتورة | Invoice Number | رقم فاتورة الذمة |
| 5 | `access_token` | `string(36)` NULL UQ | رمز الوصول | Access Token | رمز فريد لبوابة العميل لسداد الذمة |
| 6 | `total` | `decimal(15,2)` DEF `0` | الإجمالي | Total | إجمالي المبلغ المستحق |
| 7 | `paid` | `decimal(15,2)` DEF `0` | المدفوع | Paid Amount | المبلغ المسدد حتى الآن |
| 8 | `due_date` | `date` NULL | تاريخ الاستحقاق | Due Date | آخر تاريخ للسداد |
| 9 | `status` | `string(20)` DEF `unpaid` | الحالة | Status | غير مدفوع/جزئي/مدفوع/متأخر |
| 10 | `note` | `text` NULL | ملاحظة | Note | ملاحظة عامة |
| 11 | `collection_notes` | `text` NULL | ملاحظات التحصيل | Collection Notes | ملاحظات متابعة التحصيل |
| 12 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 13 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- `access_token` يسمح للعميل بفتح صفحة سداد آمنة دون تسجيل دخول.
- حالة الذمة تتغير تلقائيًا إلى `partial` عند سداد جزء، وإلى `paid` عند اكتمال السداد، وإلى `overdue` عند تجاوز `due_date`.

---

### 26. `receivable_payments` — دفعات الذمم المدينة (Receivable Payments)

سجل دفعات سداد الذمة المدينة، كل دفعة بعمود مستقل.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `receivable_id` | `bigint unsigned` FK | معرّف الذمة | Receivable ID | الذمة المدينة المرتبطة |
| 3 | `paid_at` | `date` NULL | تاريخ السداد | Paid At | تاريخ الدفعة |
| 4 | `amount` | `decimal(15,2)` DEF `0` | المبلغ | Amount | قيمة الدفعة |
| 5 | `method` | `string(30)` NULL | طريقة الدفع | Payment Method | نقدًا/تحويل بنكي/غيره |
| 6 | `bank_account_id` | `bigint unsigned` FK NULL | الحساب البنكي | Bank Account ID | الحساب البنكي المستلم (للتحويل) |
| 7 | `user_id` | `bigint unsigned` FK NULL | المستخدم المسجّل | Recorded By | المستخدم الذي سجل الدفعة |
| 8 | `note` | `text` NULL | ملاحظة | Note | ملاحظة الدفعة |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** حذف الذمة يحذف كل دفعاتها (Cascade) للحفاظ على سلامة البيانات.

---

### 27. `customer_credits` — أرصدة العملاء (Customer Credits)

أرصدة دائن للعميل تنشأ من مرتجعات المبيعات (بدل استرداد نقدي)، ويمكن استخدامها في مشتريات لاحقة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `customer_id` | `bigint unsigned` FK | معرّف العميل | Customer ID | العميل صاحب الرصيد |
| 3 | `sales_return_id` | `bigint unsigned` FK | معرّف مرتجع المبيعات | Sales Return ID | مرتجع المبيعات الذي أنشأ الرصيد |
| 4 | `amount` | `bigint` | المبلغ | Amount | قيمة الرصيد الأصلية |
| 5 | `balance` | `bigint` | الرصيد المتبقي | Balance | المتبقي من الرصيد بعد الاستخدام |
| 6 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الرصيد |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** الرصيد يُنشأ فقط عند اختيار `return_type = credit` في مرتجع المبيعات، ويُخصم منه عند الاستخدام.

---
## 5. الكتالوج والمنتجات

### 28. `categories` — الفئات (Categories)

تصنيفات المنتجات (مثل: مشروبات، أطعمة، منظفات).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `image` | `string(255)` NULL | الصورة | Image | مسار صورة الفئة (أصبح اختياريًا) |
| 3 | `name` | `string(255)` | الاسم | Name | اسم الفئة |
| 4 | `description` | `text` NULL | الوصف | Description | وصف الفئة (أصبح اختياريًا) |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** عمودا `image` و`description` كانا إلزاميين في البداية، ثم أصبحا nullable في migration لاحق.

---

### 29. `products` — المنتجات (Products)

جدول المنتجات: البيانات الأساسية للتسعير والمخزون والضرائب والوحدة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `category_id` | `bigint unsigned` FK | معرّف الفئة | Category ID | الفئة التابع لها المنتج |
| 3 | `image` | `string(255)` NULL | الصورة | Image | مسار صورة المنتج (اختياري) |
| 4 | `barcode` | `string(255)` UQ | الباركود | Barcode | باركود المنتج الفريد |
| 5 | `sku` | `string(255)` NULL UQ | رمز المنتج | SKU | رمز داخلي فريد (اختياري) |
| 6 | `title` | `string(255)` | اسم المنتج | Product Title | الاسم الظاهر للمنتج |
| 7 | `description` | `text` NULL | الوصف | Description | وصف المنتج (اختياري) |
| 8 | `buy_price` | `bigint` | سعر الشراء | Buy Price | سعر التكلفة/الشراء |
| 9 | `sell_price` | `bigint` | سعر البيع | Sell Price | سعر البيع الأساسي |
| 10 | `stock` | `integer` | المخزون | Stock | كمية المخزون الإجمالية/الافتراضية |
| 11 | `tax_type` | `string(20)` DEF `exclusive` | نوع الضريبة | Tax Type | الضريبة مضافة (exclusive) أم مشمولة (inclusive) |
| 12 | `tax_rate` | `decimal(5,2)` DEF `11.00` | نسبة الضريبة | Tax Rate | نسبة الضريبة المطبقة على المنتج |
| 13 | `min_stock` | `integer` DEF `0` | حد المخزون الأدنى | Min Stock | الحد الأدنى لتنبيه نقص المخزون |
| 14 | `max_stock` | `integer` DEF `0` | حد المخزون الأقصى | Max Stock | الحد الأقصى للمخزون |
| 15 | `is_composite` | `boolean` DEF `false` | منتج مركب | Is Composite | هل المنتج مركب من مكونات؟ |
| 16 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 17 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- `stock` هو المخزون الإجمالي/الأساسي، بينما يوجد مخزون تفصيلي لكل مستودع في `product_warehouse`.
- عند بيع منتج مركب (`is_composite = true`) تُخصم كميات مكوناته من `composite_product_items` بدلًا من مخزونه هو.
- الضريبة الافتراضية 11% وقابلة للتعديل لكل منتج، مع نوع `exclusive` أو `inclusive`.

---

### 30. `units` — الوحدات (Units)

جدول وحدات القياس المتاحة (حبة، كرتون، كيلو، لتر...).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `string(10)` UQ | رمز الوحدة | Unit Code | رمز قصير فريد (PCS, BOX...) |
| 3 | `name` | `string(50)` | اسم الوحدة | Unit Name | الاسم الكامل |
| 4 | `symbol` | `string(10)` | رمز الوحدة | Unit Symbol | الرمز المختصر للعرض (pcs, kg...) |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** عند إنشاء الجدول تُضاف 8 وحدات افتراضية: PCS، BOX، KARTON، KG، LITER، METER، PAK، DUS.

---

### 31. `product_units` — وحدات المنتجات (Product Units)

يربط المنتج بوحداته المتعددة (مثل: كرتون يحتوي 12 حبة) مع أسعار وباركود لكل وحدة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 3 | `unit_id` | `bigint unsigned` FK | معرّف الوحدة | Unit ID | الوحدة |
| 4 | `is_base` | `boolean` DEF `false` | وحدة أساسية | Is Base Unit | هل هذه الوحدة الأساسية للمنتج؟ |
| 5 | `conversion_factor` | `decimal(15,4)` DEF `1.0000` | معامل التحويل | Conversion Factor | كم وحدة أساسية في هذه الوحدة (مثال: 12) |
| 6 | `buy_price` | `bigint` | سعر الشراء | Buy Price | سعر شراء هذه الوحدة |
| 7 | `sell_price` | `bigint` | سعر البيع | Sell Price | سعر بيع هذه الوحدة |
| 8 | `barcode` | `string(100)` NULL | الباركود | Barcode | باركود خاص بهذه الوحدة |
| 9 | `sku_suffix` | `string(20)` NULL | لاحقة SKU | SKU Suffix | لاحقة مميزة لرمز المنتج |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`product_id`, `unit_id`).

**ملاحظة:** عند البيع بوحدة غير أساسية، يُضرب `conversion_factor` في الكمية لتحديد الكمية المخصومة من المخزون الأساسي.

---

### 32. `product_warehouse` — مخزون المنتجات بالمستودعات (Product Warehouse)

جدول وسيط يحفظ كمية المخزون لكل منتج في كل مستودع.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 3 | `warehouse_id` | `bigint unsigned` FK | معرّف المستودع | Warehouse ID | المستودع |
| 4 | `stock` | `integer` DEF `0` | المخزون | Stock | كمية المنتج في هذا المستودع |
| 5 | `min_stock` | `integer` DEF `0` | حد أدنى | Min Stock | حد التنبيه لهذا المستودع |
| 6 | `max_stock` | `integer` DEF `0` | حد أقصى | Max Stock | الحد الأقصى لهذا المستودع |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`product_id`, `warehouse_id`)، وحذف متسلسل عند حذف المنتج أو المستودع.

**ملاحظة:** هذا الجدول هو المصدر التفصيلي للمخزون، ويُستخدم في تقارير المستودعات وتحويلات المخزون.

---

### 33. `product_batches` — دفعات المنتجات (Product Batches)

تتبع دفعات المنتج (خاصة ذات الصلاحية) لكل مستودع.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 3 | `warehouse_id` | `bigint unsigned` FK | معرّف المستودع | Warehouse ID | المستودع |
| 4 | `batch_number` | `string(100)` | رقم الدفعة | Batch Number | رقم/رمز الدفعة |
| 5 | `expired_at` | `date` NULL | تاريخ الانتهاء | Expired At | تاريخ صلاحية الدفعة |
| 6 | `received_at` | `date` | تاريخ الاستلام | Received At | تاريخ استلام الدفعة |
| 7 | `stock` | `integer` DEF `0` | الكمية | Stock | الكمية المتاحة من الدفعة |
| 8 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 9 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`product_id`, `warehouse_id`, `batch_number`).

**ملاحظة:** تُستخدم الدفعات لتطبيق سياسة FEFO (الأقرب انتهاءً يُصرف أولًا) عند البيع.

---

### 34. `composite_product_items` — مكونات المنتجات المركبة (Composite Product Items)

جدول يحدد مكونات المنتج المركب وكمية كل مكون.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `composite_product_id` | `bigint unsigned` FK | معرّف المنتج المركب | Composite Product ID | المنتج المركب (يشير إلى `products.id`) |
| 3 | `component_product_id` | `bigint unsigned` FK | معرّف المنتج المكوّن | Component Product ID | المنتج المكوّن (يشير إلى `products.id`) |
| 4 | `qty` | `decimal(15,4)` DEF `1` | الكمية | Quantity | كمية المكوّن اللازمة لوحدة واحدة |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`composite_product_id`, `component_product_id`)، مع حذف متسلسل عند حذف المنتج المركب.

**ملاحظة:** يمنع هذا القيد تكرار نفس المكوّن داخل المنتج المركب، ويسمح باختيار مكونات متعددة.

---

### 35. `product_notification_reads` — حالة قراءة إشعارات المنتجات (Product Notification Reads)

يسجل متى قرأ كل مستخدم إشعارًا خاصًا بمنتج (مثل تنبيه تغير السعر أو المخزون).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `user_id` | `bigint unsigned` FK | معرّف المستخدم | User ID | المستخدم الذي قرأ الإشعار |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج صاحب الإشعار |
| 4 | `created_at` | `timestamp` | تاريخ القراءة | Read At | وقت قراءة الإشعار |
| 5 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`user_id`, `product_id`)، وحذف متسلسل من الجهتين.

**ملاحظة:** وجود سجل يعني أن المستخدم قرأ إشعار هذا المنتج، وغيابه يعني أنه لم يقرأه بعد.

---

### 36. `price_lists` — قوائم الأسعار (Price Lists)

قوائم أسعار بديلة (مثل: قائمة أسعار الجملة أو قائمة فرع معين) مرتبطة بشريحة عملاء.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع المالك للقائمة |
| 3 | `name` | `string(100)` | اسم القائمة | Price List Name | اسم القائمة |
| 4 | `slug` | `string(100)` UQ | المعرّف النصي | Slug | معرّف نصي فريد |
| 5 | `customer_scope` | `string(30)` DEF `all` | نطاق العملاء | Customer Scope | نطاق تطبيق القائمة (الكل/شريحة...) |
| 6 | `customer_segment_id` | `bigint unsigned` FK NULL | معرّف الشريحة | Customer Segment ID | الشريحة المستهدفة |
| 7 | `is_active` | `boolean` DEF `true` | مفعّلة | Is Active | هل القائمة سارية؟ |
| 8 | `priority` | `integer` DEF `0` | الأولوية | Priority | ترتيب الأولوية عند التعارض |
| 9 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات القائمة |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** قائمة السعار تحدد السعر الأساسي، بينما `pricing_rules` تحدد الخصومات؛ ويمكن الجمع بينهما.

---

### 37. `price_list_items` — بنود قوائم الأسعار (Price List Items)

أسعار المنتجات داخل كل قائمة أسعار.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `price_list_id` | `bigint unsigned` FK | معرّف القائمة | Price List ID | قائمة السعر |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `price` | `bigint` | السعر | Price | سعر المنتج في هذه القائمة |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`price_list_id`, `product_id`)، وحذف متسلسل.

---

### 38. `pricing_rules` — قواعد التسعير (Pricing Rules)

محرك العروض والخصومات: قاعدة واحدة قد تكون خصمًا مباشرًا أو عرض كمية أو حزمة أو اشترِ واحصل.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع المالك للقاعدة |
| 3 | `name` | `string(255)` | اسم القاعدة | Rule Name | اسم القاعدة |
| 4 | `kind` | `string(30)` DEF `standard_discount` | نوع القاعدة | Rule Kind | نوع العرض (خصم/كمية/حزمة/اشترِ واحصل) |
| 5 | `is_active` | `boolean` DEF `true` | مفعّلة | Is Active | هل القاعدة سارية؟ |
| 6 | `priority` | `unsignedInteger` DEF `0` | الأولوية | Priority | ترتيب تطبيق القاعدة |
| 7 | `target_type` | `string(20)` DEF `all` | نوع الهدف | Target Type | الهدف: الكل/منتج/فئة |
| 8 | `product_id` | `bigint unsigned` FK NULL | معرّف المنتج | Product ID | المنتج المستهدف (إن كان الهدف منتجًا) |
| 9 | `category_id` | `bigint unsigned` FK NULL | معرّف الفئة | Category ID | الفئة المستهدفة (إن كان الهدف فئة) |
| 10 | `customer_scope` | `string(20)` DEF `all` | نطاق العملاء | Customer Scope | نطاق تطبيق القاعدة (الكل/مستوى ولاء...) |
| 11 | `eligible_loyalty_tiers` | `json` NULL | مستويات الولاء المؤهلة | Eligible Loyalty Tiers | قائمة مستويات الولاء المسموح لها |
| 12 | `discount_type` | `string(20)` | نوع الخصم | Discount Type | نسبة مئوية أو مبلغ ثابت |
| 13 | `discount_value` | `decimal(15,2)` | قيمة الخصم | Discount Value | قيمة الخصم |
| 14 | `starts_at` | `timestamp` NULL | تاريخ البداية | Starts At | بداية سريان القاعدة |
| 15 | `ends_at` | `timestamp` NULL | تاريخ النهاية | Ends At | نهاية سريان القاعدة |
| 16 | `preview_quantity_multiplier` | `unsignedInteger` DEF `1` | مضاعف الكمية للمعاينة | Preview Quantity Multiplier | يُستخدم في معاينة العروض للكميات |
| 17 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات القاعدة |
| 18 | `created_by` | `bigint unsigned` FK NULL | أنشأ بواسطة | Created By | المستخدم الذي أنشأ القاعدة |
| 19 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 20 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`is_active`, `priority`)، (`target_type`, `product_id`, `category_id`)، (`customer_scope`, `starts_at`, `ends_at`)، (`kind`, `is_active`, `priority`).

**ملاحظات:**
- أنواع القواعد (`kind`): `standard_discount` (خصم عادي)، `qty_break` (خصم حسب الكمية)، `bundle` (حزمة)، `buy_get` (اشترِ واحصل).
- `target_type` يحدد هل القاعدة على منتج واحد أم فئة كاملة أم كل المنتجات.
- `customer_scope` يحدد هل القاعدة للجميع أم لمستوى ولاء معين، وتُحدد المستويات في `eligible_loyalty_tiers`.

---

### 39. `pricing_rule_qty_breaks` — شرائح كميات التسعير (Pricing Rule Quantity Breaks)

شرائح الخصم حسب الكمية لقاعدة من نوع `qty_break`.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `pricing_rule_id` | `bigint unsigned` FK | معرّف القاعدة | Pricing Rule ID | القاعدة الأم |
| 3 | `min_qty` | `unsignedInteger` | الحد الأدنى للكمية | Minimum Quantity | أقل كمية للحصول على هذا الخصم |
| 4 | `discount_type` | `string(20)` | نوع الخصم | Discount Type | نسبة أو مبلغ |
| 5 | `discount_value` | `decimal(15,2)` | قيمة الخصم | Discount Value | قيمة الخصم |
| 6 | `sort_order` | `unsignedInteger` DEF `0` | ترتيب | Sort Order | ترتيب الشرائح |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`pricing_rule_id`, `min_qty`)، وحذف متسلسل عند حذف القاعدة.

---

### 40. `pricing_rule_bundle_items` — بنود حزم التسعير (Pricing Rule Bundle Items)

منتجات الحزمة وكمياتها لقاعدة من نوع `bundle`.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `pricing_rule_id` | `bigint unsigned` FK | معرّف القاعدة | Pricing Rule ID | القاعدة الأم |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج داخل الحزمة |
| 4 | `quantity` | `unsignedInteger` DEF `1` | الكمية | Quantity | كمية المنتج في الحزمة |
| 5 | `sort_order` | `unsignedInteger` DEF `0` | ترتيب | Sort Order | ترتيب العرض |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`pricing_rule_id`, `sort_order`)، وحذف متسلسل.

---

### 41. `pricing_rule_buy_get_items` — بنود اشترِ واحصل (Pricing Rule Buy Get Items)

منتجات عرض «اشترِ واحصل» مع تحديد دور كل منتج (مشترى أم مجاني).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `pricing_rule_id` | `bigint unsigned` FK | معرّف القاعدة | Pricing Rule ID | القاعدة الأم |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `role` | `string(10)` | الدور في العرض | Offer Role | `buy` (يجب شراؤه) أو `get` (يُحصل عليه مجانًا) |
| 5 | `quantity` | `unsignedInteger` DEF `1` | الكمية | Quantity | الكمية المطلوبة |
| 6 | `sort_order` | `unsignedInteger` DEF `0` | ترتيب | Sort Order | ترتيب العرض |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`pricing_rule_id`, `role`, `sort_order`)، وحذف متسلسل.

**ملاحظة:** العمود `role` يميز بين المنتجات التي يجب شراؤها (`buy`) والمنتجات التي تُمنح مجانًا (`get`).

---
## 6. المستودعات والمخزون والمشتريات

### 42. `warehouses` — المستودعات (Warehouses)

جدول مواقع التخزين (مستودع رئيسي، مستودع فرع، مستودع...) ويرتبط بفرع اختياريًا.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع التابع له المستودع |
| 3 | `code` | `string(20)` UQ | رمز المستودع | Warehouse Code | رمز فريد (مثل `PUSAT`) |
| 4 | `name` | `string(100)` | اسم المستودع | Warehouse Name | الاسم الظاهر |
| 5 | `type` | `enum(main, branch, warehouse)` DEF `branch` | نوع المستودع | Warehouse Type | رئيسي/فرع/مستودع |
| 6 | `address` | `text` NULL | العنوان | Address | عنوان المستودع |
| 7 | `phone` | `string(20)` NULL | الهاتف | Phone | هاتف المستودع |
| 8 | `is_active` | `boolean` DEF `true` | مفعّل | Is Active | هل المستودع مستخدم؟ |
| 9 | `sort_order` | `integer` DEF `0` | ترتيب العرض | Sort Order | ترتيب المستودعات |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- المستودع الرئيسي `PUSAT` مرتبط بفرع `PUSAT` الذي لا يُفعّل عليه البيع.
- المستودع يمثل موقع المخزون، بينما الفرع يمثل نقطة البيع؛ وقد يكون للفرع أكثر من مستودع.

---

### 43. `stock_mutations` — حركات المخزون (Stock Mutations)

سجل غير قابل للتعديل لكل حركة مخزون (بيع، شراء، جرد، تحويل، مرتجع...) مع الكمية قبل وبعد.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج صاحب الحركة |
| 3 | `reference_type` | `string(50)` | نوع المرجع | Reference Type | نوع الكيان المرتبط (Morph) مثل `sale` أو `purchase` |
| 4 | `reference_id` | `bigint unsigned` NULL | معرّف المرجع | Reference ID | معرّف الكيان المرتبط |
| 5 | `mutation_type` | `string(20)` | نوع الحركة | Mutation Type | نوع الحركة (in/out/adjustment/transfer...) |
| 6 | `qty` | `integer` | الكمية | Quantity | الكمية المتأثرة (موجبة أو سالبة) |
| 7 | `stock_before` | `integer` | المخزون قبل | Stock Before | الكمية قبل الحركة |
| 8 | `stock_after` | `integer` | المخزون بعد | Stock After | الكمية بعد الحركة |
| 9 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الحركة |
| 10 | `created_by` | `bigint unsigned` FK NULL | المستخدم المنفّذ | Created By | المستخدم الذي نفّذ الحركة |
| 11 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | المستودع الذي حدثت فيه الحركة |
| 12 | `batch_number` | `string(100)` NULL | رقم الدفعة | Batch Number | الدفعة المتأثرة (إن وجدت) |
| 13 | `expired_at` | `date` NULL | تاريخ الانتهاء | Expired At | صلاحية الدفعة (إن وجدت) |
| 14 | `created_at` | `timestamp` | تاريخ الحركة | Created At | وقت الحركة |
| 15 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`reference_type`, `reference_id`).

**ملاحظات:**
- `stock_before` و`stock_after` يخزّنان صورة الحركة وقت حدوثها، حتى لو تغير المخزون لاحقًا يبقى السجل صحيحًا.
- `reference_type` + `reference_id` يشكلان علاقة **متعددة الأشكال** تربط الحركة بأي مستند (معاملة، أمر شراء، جرد...).

---

### 44. `stock_transfers` — تحويلات المخزون (Stock Transfers)

تحويل كمية من مستودع إلى آخر.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `source_warehouse_id` | `bigint unsigned` FK | مستودع المصدر | Source Warehouse ID | المستودع المحوِّل منه |
| 3 | `destination_warehouse_id` | `bigint unsigned` FK | مستودع الوجهة | Destination Warehouse ID | المستودع المحوَّل إليه |
| 4 | `document_number` | `string(30)` UQ | رقم المستند | Document Number | رقم فريد للتحويل |
| 5 | `status` | `string(20)` DEF `draft` | الحالة | Status | مسودة/مكتمل/ملغي |
| 6 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات التحويل |
| 7 | `created_by` | `bigint unsigned` FK | أنشأ بواسطة | Created By | المستخدم المنشئ |
| 8 | `completed_at` | `timestamp` NULL | تاريخ الإكمال | Completed At | وقت اكتمال التحويل |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** عند اكتمال التحويل تُخصم الكمية من المصدر وتُضاف إلى الوجهة، وتُسجل حركتان في `stock_mutations`.

---

### 45. `stock_transfer_items` — بنود تحويلات المخزون (Stock Transfer Items)

بنود المنتجات والكميات داخل مستند التحويل.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `stock_transfer_id` | `bigint unsigned` FK | معرّف التحويل | Stock Transfer ID | مستند التحويل |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `qty` | `integer` | الكمية | Quantity | الكمية المحوّلة |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** حذف مستند التحويل يحذف بنوده (Cascade).

---

### 46. `stock_opnames` — عمليات الجرد (Stock Opnames)

مستند جرد فعلي للمخزون ومقارنته بالنظام.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `string(255)` UQ | رمز الجرد | Opname Code | رمز فريد للجرد |
| 3 | `status` | `string(20)` DEF `draft` | الحالة | Status | مسودة/مكتمل |
| 4 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الجرد |
| 5 | `created_by` | `bigint unsigned` FK NULL | أنشأ بواسطة | Created By | المستخدم المنشئ |
| 6 | `finalized_by` | `bigint unsigned` FK NULL | اعتمده | Finalized By | المستخدم الذي اعتمد الجرد |
| 7 | `finalized_at` | `timestamp` NULL | تاريخ الاعتماد | Finalized At | وقت اعتماد الجرد |
| 8 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | المستودع المشمول بالجرد |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** الجرد لا يؤثر على المخزون إلا عند الاعتماد (`finalized_at`)، حيث تُنشأ حركة تسوية في `stock_mutations`.

---

### 47. `stock_opname_items` — بنود الجرد (Stock Opname Items)

بنود المنتجات في مستند الجرد مع الكمية الفعلية والفرق.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `stock_opname_id` | `bigint unsigned` FK | معرّف الجرد | Stock Opname ID | مستند الجرد |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `system_stock` | `integer` | مخزون النظام | System Stock | الكمية الدفترية في النظام |
| 5 | `physical_stock` | `integer` NULL | المخزون الفعلي | Physical Stock | الكمية المعدودة فعليًا |
| 6 | `difference` | `integer` NULL | الفرق | Difference | الفرق بين الفعلي والنظام ( موجب/سالب ) |
| 7 | `adjustment_reason` | `string(255)` NULL | سبب التسوية | Adjustment Reason | سبب الفرق |
| 8 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 9 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`stock_opname_id`, `product_id`)، وحذف متسلسل.

**ملاحظة:** `difference` يُحسب تلقائيًا = `physical_stock - system_stock`، ويُستخدم لتسوية المخزون عند الاعتماد.

---

### 48. `purchase_orders` — أوامر الشراء (Purchase Orders)

طلب شراء موجّه لمورد، يسبق استلام البضاعة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `supplier_id` | `bigint unsigned` FK NULL | معرّف المورد | Supplier ID | المورد |
| 3 | `document_number` | `string(255)` UQ IDX | رقم المستند | Document Number | رقم فريد لأمر الشراء |
| 4 | `status` | `string(20)` DEF `draft` | الحالة | Status | مسودة/مرسل/مستلم جزئيًا/مكتمل |
| 5 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الأمر |
| 6 | `created_by` | `bigint unsigned` FK NULL | أنشأ بواسطة | Created By | المستخدم المنشئ |
| 7 | `ordered_at` | `timestamp` NULL | تاريخ الطلب | Ordered At | وقت إرسال الأمر |
| 8 | `completed_at` | `timestamp` NULL | تاريخ الإكمال | Completed At | وقت اكتمال الاستلام |
| 9 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الاستلام |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** أمر الشراء هو رأس سلسلة المشتريات: أمر شراء ← استلام بضاعة ← ذمة دائنة.

---

### 49. `purchase_order_items` — بنود أوامر الشراء (Purchase Order Items)

بنود المنتجات في أمر الشراء مع الكمية المطلوبة والمستلمة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `purchase_order_id` | `bigint unsigned` FK | معرّف أمر الشراء | Purchase Order ID | أمر الشراء |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `qty_ordered` | `integer` DEF `0` | الكمية المطلوبة | Quantity Ordered | الكمية المطلوبة |
| 5 | `qty_received` | `integer` DEF `0` | الكمية المستلمة | Quantity Received | الكمية المستلمة فعليًا |
| 6 | `unit_price` | `decimal(15,2)` DEF `0` | سعر الوحدة | Unit Price | سعر الوحدة |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** `product_id` يستخدم `restrictOnDelete` لمنع حذف منتج مرتبط بأمر شراء.

---

### 50. `goods_receivings` — استلامات البضاعة (Goods Receivings)

مستند استلام فعلي للبضاعة من المورد مقابل أمر شراء.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `purchase_order_id` | `bigint unsigned` FK | معرّف أمر الشراء | Purchase Order ID | أمر الشراء المرتبط |
| 3 | `supplier_id` | `bigint unsigned` FK NULL | معرّف المورد | Supplier ID | المورد |
| 4 | `document_number` | `string(255)` UQ IDX | رقم المستند | Document Number | رقم فريد للاستلام |
| 5 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الاستلام |
| 6 | `received_by` | `bigint unsigned` FK NULL | استلم بواسطة | Received By | المستخدم الذي استلم |
| 7 | `received_at` | `timestamp` NULL | تاريخ الاستلام | Received At | وقت الاستلام |
| 8 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الاستلام |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** حذف أمر الشراء يحذف استلاماته (Cascade)؛ ولذلك يجب توخي الحذر عند الحذف.

---

### 51. `goods_receiving_items` — بنود استلام البضاعة (Goods Receiving Items)

بنود المنتجات المستلمة، ويرتبط كل بند ببند أمر الشراء المقابل.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `goods_receiving_id` | `bigint unsigned` FK | معرّف الاستلام | Goods Receiving ID | مستند الاستلام |
| 3 | `purchase_order_item_id` | `bigint unsigned` FK NULL | معرّف بند أمر الشراء | Purchase Order Item ID | البند المقابل في أمر الشراء |
| 4 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 5 | `qty_received` | `integer` DEF `0` | الكمية المستلمة | Quantity Received | الكمية المستلمة |
| 6 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات البند |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** عند الاستلام تُضاف الكمية إلى المخزون وتُحدّث `qty_received` في بند أمر الشراء.

---

### 52. `suppliers` — الموردون (Suppliers)

بيانات الموردين الذين تُشترى منهم البضاعة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `name` | `string(255)` | اسم المورد | Supplier Name | اسم المورد |
| 3 | `phone` | `string(255)` NULL | الهاتف | Phone | رقم الهاتف |
| 4 | `email` | `string(255)` NULL | البريد الإلكتروني | Email | البريد الإلكتروني |
| 5 | `address` | `text` NULL | العنوان | Address | عنوان المورد |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

---

### 53. `payables` — الذمم الدائنة (Payables)

مستحقات للمورد، تنشأ من استلام البضاعة أو أمر شراء.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `supplier_id` | `bigint unsigned` FK NULL | معرّف المورد | Supplier ID | المورد الدائن |
| 3 | `document_number` | `string(255)` IDX | رقم المستند | Document Number | رقم مستند الذمة |
| 4 | `total` | `decimal(15,2)` DEF `0` | الإجمالي | Total | إجمالي المبلغ المستحق |
| 5 | `paid` | `decimal(15,2)` DEF `0` | المدفوع | Paid Amount | المبلغ المسدد |
| 6 | `due_date` | `date` NULL | تاريخ الاستحقاق | Due Date | تاريخ الاستحقاق |
| 7 | `status` | `string(20)` DEF `unpaid` | الحالة | Status | غير مدفوع/جزئي/مدفوع/متأخر |
| 8 | `note` | `text` NULL | ملاحظة | Note | ملاحظة عامة |
| 9 | `purchase_order_id` | `bigint unsigned` FK NULL | معرّف أمر الشراء | Purchase Order ID | أمر الشراء المرتبط |
| 10 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 11 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** الذمة الدائنة يمكن أن ترتبط بأمر شراء، وتُسدد عبر `payable_payments`.

---

### 54. `payable_payments` — دفعات الذمم الدائنة (Payable Payments)

سجل دفعات سداد الذمم الدائنة للموردين.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `payable_id` | `bigint unsigned` FK | معرّف الذمة | Payable ID | الذمة الدائنة |
| 3 | `paid_at` | `date` NULL | تاريخ السداد | Paid At | تاريخ الدفعة |
| 4 | `amount` | `decimal(15,2)` DEF `0` | المبلغ | Amount | قيمة الدفعة |
| 5 | `method` | `string(30)` NULL | طريقة الدفع | Payment Method | نقدًا/تحويل بنكي |
| 6 | `bank_account_id` | `bigint unsigned` FK NULL | الحساب البنكي | Bank Account ID | الحساب البنكي المصدر |
| 7 | `user_id` | `bigint unsigned` FK NULL | المستخدم المسجّل | Recorded By | المستخدم الذي سجل الدفعة |
| 8 | `note` | `text` NULL | ملاحظة | Note | ملاحظة الدفعة |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** حذف الذمة يحذف دفعاتها (Cascade).

---

### 55. `supplier_returns` — مرتجعات الموردين (Supplier Returns)

مستند إرجاع بضاعة إلى المورد (عيب أو خطأ في الاستلام).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `supplier_id` | `bigint unsigned` FK NULL | معرّف المورد | Supplier ID | المورد |
| 3 | `goods_receiving_id` | `bigint unsigned` FK NULL | معرّف الاستلام | Goods Receiving ID | الاستلام الأصلي |
| 4 | `payable_id` | `bigint unsigned` FK NULL | معرّف الذمة الدائنة | Payable ID | الذمة الدائنة المرتبطة |
| 5 | `document_number` | `string(255)` UQ IDX | رقم المستند | Document Number | رقم فريد للمرتجع |
| 6 | `status` | `string(20)` DEF `draft` | الحالة | Status | مسودة/مكتمل |
| 7 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات المرتجع |
| 8 | `returned_at` | `timestamp` NULL | تاريخ الإرجاع | Returned At | وقت الإرجاع |
| 9 | `created_by` | `bigint unsigned` FK NULL | أنشأ بواسطة | Created By | المستخدم المنشئ |
| 10 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الإرجاع |
| 11 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 12 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** يمكن للمرتجع أن يرتبط باستلام بضاعة أو بذمة دائنة، ويُخصم من المخزون عند الإرجاع.

---

### 56. `supplier_return_items` — بنود مرتجعات الموردين (Supplier Return Items)

بنود المنتجات المرتجعة للمورد.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `supplier_return_id` | `bigint unsigned` FK | معرّف المرتجع | Supplier Return ID | مستند المرتجع |
| 3 | `goods_receiving_item_id` | `bigint unsigned` FK NULL | معرّف بند الاستلام | Goods Receiving Item ID | البند الأصلي في الاستلام |
| 4 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 5 | `qty_returned` | `integer` DEF `0` | الكمية المرتجعة | Quantity Returned | الكمية المرتجعة |
| 6 | `unit_price` | `decimal(15,2)` DEF `0` | سعر الوحدة | Unit Price | سعر الوحدة |
| 7 | `reason` | `string(100)` NULL | سبب الإرجاع | Return Reason | سبب الإرجاع |
| 8 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات البند |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** `product_id` يستخدم `restrictOnDelete` لمنع حذف منتج مرتبط بمرتجع.

---
## 7. المبيعات ونقطة البيع

### 57. `carts` — سلة نقطة البيع (Carts)

سلة مؤقتة لكل كاشير قبل إتمام المعاملة، وتدعم تعليق السلة واستعادتها.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `cashier_id` | `bigint unsigned` FK | معرّف الكاشير | Cashier ID | الكاشير صاحب السلة |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج في السلة |
| 4 | `qty` | `integer` | الكمية | Quantity | الكمية المطلوبة |
| 5 | `price` | `bigint` | السعر | Price | سعر الوحدة وقت الإضافة |
| 6 | `hold_id` | `string(255)` NULL IDX | معرّف التعليق | Hold ID | معرّف يجمع بنود السلة المعلّقة |
| 7 | `hold_label` | `string(255)` NULL | اسم التعليق | Hold Label | اسم/وصف السلة المعلّقة |
| 8 | `held_at` | `timestamp` NULL | تاريخ التعليق | Held At | وقت تعليق السلة |
| 9 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الصرف |
| 10 | `unit_id` | `bigint unsigned` FK NULL | معرّف الوحدة | Unit ID | وحدة البيع |
| 11 | `conversion_factor` | `decimal(15,4)` DEF `1.0000` | معامل التحويل | Conversion Factor | تحويل الوحدة إلى الوحدة الأساسية |
| 12 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 13 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`hold_id`) و(`cashier_id`, `hold_id`).

**ملاحظات:**
- يمكن تعليق أكثر من سلة في نفس الوقت، وتُستعاد لاحقًا بنفس `hold_id`.
- `conversion_factor` يسمح ببيع المنتج بوحدة مختلفة (مثل كرتون) مع خصم الكمية الصحيحة من المخزون الأساسي.

---

### 58. `transactions` — المعاملات (Transactions)

رأس الفاتورة/المعاملة في نقطة البيع، ويجمع بيانات الدفع والضريبة والولاء والخصومات.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `cashier_id` | `bigint unsigned` FK | معرّف الكاشير | Cashier ID | الكاشير الذي أنشأ المعاملة |
| 3 | `cashier_shift_id` | `bigint unsigned` FK NULL | معرّف الوردية | Cashier Shift ID | الوردية المرتبطة |
| 4 | `customer_id` | `bigint unsigned` FK NULL | معرّف العميل | Customer ID | العميل (اختياري) |
| 5 | `bank_account_id` | `bigint unsigned` FK NULL | الحساب البنكي | Bank Account ID | الحساب البنكي (للتحويل البنكي) |
| 6 | `invoice` | `string(255)` | رقم الفاتورة | Invoice Number | رقم الفاتورة الظاهر |
| 7 | `access_token` | `string(36)` NULL UQ | رمز الوصول | Access Token | رمز بوابة الفاتورة العامة |
| 8 | `client_uuid` | `uuid` NULL UQ | معرّف العميل (UUID) | Client UUID | معرّف فريد من جهاز البيع لمنع التكرار |
| 9 | `sync_fingerprint` | `string(255)` NULL IDX | بصمة المزامنة | Sync Fingerprint | بصمة تُستخدم لمزامنة المعاملات |
| 10 | `cash` | `bigint` | النقد المستلم | Cash Received | المبلغ النقدي المستلم |
| 11 | `change` | `bigint` | الباقي | Change | المبلغ المتبقي للعميل |
| 12 | `discount` | `bigint` | الخصم | Discount | إجمالي الخصم على الفاتورة |
| 13 | `shipping_cost` | `decimal(15,2)` DEF `0` | تكلفة الشحن | Shipping Cost | تكلفة التوصيل |
| 14 | `loyalty_points_earned` | `unsignedInteger` DEF `0` | نقاط الولاء المكتسبة | Loyalty Points Earned | النقاط التي حصل عليها العميل |
| 15 | `loyalty_points_redeemed` | `unsignedInteger` DEF `0` | نقاط الولاء المستبدلة | Loyalty Points Redeemed | النقاط التي استخدمها العميل |
| 16 | `loyalty_discount_total` | `unsignedBigInteger` DEF `0` | خصم الولاء | Loyalty Discount Total | قيمة الخصم الناتج عن النقاط |
| 17 | `customer_voucher_discount` | `unsignedBigInteger` DEF `0` | خصم القسيمة | Voucher Discount | قيمة خصم القسيمة |
| 18 | `customer_voucher_code` | `string(255)` NULL | رمز القسيمة | Voucher Code | رمز القسيمة المستخدمة |
| 19 | `customer_voucher_name` | `string(255)` NULL | اسم القسيمة | Voucher Name | اسم القسيمة المستخدمة |
| 20 | `grand_total` | `bigint` | الإجمالي النهائي | Grand Total | المبلغ النهائي المطلوب |
| 21 | `payment_method` | `string(255)` DEF `cash` | طريقة الدفع | Payment Method | نقد/تحويل/محفظة/... |
| 22 | `payment_status` | `string(255)` DEF `paid` | حالة الدفع | Payment Status | مدفوع/معلق/ملغي |
| 23 | `payment_reference` | `string(255)` NULL | مرجع الدفع | Payment Reference | رقم مرجع بوابة الدفع |
| 24 | `payment_url` | `text` NULL | رابط الدفع | Payment URL | رابط إتمام الدفع الإلكتروني |
| 25 | `qr_string` | `text` NULL | نص QR | QR String | بيانات QR المستخدمة للدفع |
| 26 | `order_type` | `enum(in_store, takeaway, delivery)` NULL | نوع الطلب | Order Type | داخل المحل/سفري/توصيل |
| 27 | `note` | `text` NULL | ملاحظة | Note | ملاحظة على المعاملة |
| 28 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الصرف |
| 29 | `price_list_id` | `bigint unsigned` FK NULL | معرّف قائمة السعر | Price List ID | قائمة السعر المطبقة |
| 30 | `tax_rate` | `decimal(5,2)` NULL DEF `11.00` | نسبة الضريبة | Tax Rate | نسبة الضريبة على الفاتورة |
| 31 | `tax_total` | `bigint` DEF `0` | إجمالي الضريبة | Tax Total | قيمة الضريبة |
| 32 | `customer_npwp` | `string(20)` NULL | الرقم الضريبي للعميل | Customer NPWP | الرقم الضريبي (لفواتير الشركات) |
| 33 | `discount_approved_by` | `bigint unsigned` FK NULL | اعتمد الخصم بواسطة | Discount Approved By | المستخدم الذي اعتمد الخصم |
| 34 | `discount_approved_at` | `timestamp` NULL | تاريخ اعتماد الخصم | Discount Approved At | وقت اعتماد الخصم |
| 35 | `discount_approval_status` | `string(20)` NULL | حالة اعتماد الخصم | Discount Approval Status | بانتظار الاعتماد/معتمد/مرفوض |
| 36 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت إنشاء المعاملة |
| 37 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`warehouse_id`, `created_at`) باسم `transactions_warehouse_created_at_index`.

**ملاحظات:**
- عمودا `cash` و`change` يبقيان للتوافق مع الدفع النقدي البسيط، بينما التفاصيل الكاملة لوسائل الدفع في `transaction_tenders`.
- `access_token` يتيح للعميل فتح صفحة الفاتورة/السداد العامة.
- `client_uuid` + `sync_fingerprint` يدعمان المزامنة من أجهزة البيع ومنع تكرار المعاملات.
- الخصم النهائي = خصم الفاتورة + خصم الولاء + خصم القسيمة، وتُخزّن القيم منفصلة للتحقق.

---

### 59. `transaction_details` — تفاصيل المعاملات (Transaction Details)

بنود المنتجات داخل المعاملة، مع أسعار الوحدة والخصم والوحدة والدفعة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `transaction_id` | `bigint unsigned` FK | معرّف المعاملة | Transaction ID | المعاملة الأم |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `qty` | `integer` | الكمية | Quantity | الكمية المباعة |
| 5 | `base_unit_price` | `bigint` DEF `0` | سعر الوحدة الأساسية | Base Unit Price | سعر الوحدة الأساسية قبل العروض |
| 6 | `unit_price` | `bigint` DEF `0` | سعر الوحدة | Unit Price | سعر الوحدة بعد العروض |
| 7 | `price` | `bigint` | الإجمالي | Line Total | إجمالي البند (سعر × كمية) |
| 8 | `discount_total` | `bigint` DEF `0` | إجمالي الخصم | Discount Total | خصم البند |
| 9 | `pricing_rule_id` | `bigint unsigned` FK NULL | معرّف قاعدة التسعير | Pricing Rule ID | القاعدة المطبقة |
| 10 | `pricing_rule_name` | `string(255)` NULL | اسم قاعدة التسعير | Pricing Rule Name | اسم القاعدة وقت البيع |
| 11 | `pricing_rule_kind` | `string(30)` NULL | نوع قاعدة التسعير | Pricing Rule Kind | نوع القاعدة المطبقة |
| 12 | `pricing_group_key` | `string(255)` NULL | مفتاح مجموعة التسعير | Pricing Group Key | مفتاح يجمع البنود في عرض واحد |
| 13 | `pricing_group_label` | `string(255)` NULL | اسم مجموعة التسعير | Pricing Group Label | اسم مجموعة العرض |
| 14 | `unit_id` | `bigint unsigned` FK NULL | معرّف الوحدة | Unit ID | وحدة البيع |
| 15 | `conversion_factor` | `decimal(15,4)` DEF `1.0000` | معامل التحويل | Conversion Factor | تحويل الوحدة إلى الأساسية |
| 16 | `product_batch_id` | `bigint unsigned` FK NULL | معرّف الدفعة | Product Batch ID | الدفعة المصروفة |
| 17 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 18 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- عند إضافة أعمدة التسعير، نُقلت القيم القديمة: `unit_price = price / qty`، و`base_unit_price = unit_price`، و`discount_total = 0`.
- `pricing_group_key` يربط البنود التي تشترك في عرض واحد (مثل حزمة أو اشترِ واحصل) حتى تُحسب المجموعة كوحدة واحدة.

---

### 60. `transaction_tenders` — وسائل دفع المعاملات (Transaction Tenders)

يدعم الدفع المقسم بأكثر من وسيلة (نقد + بطاقة + محفظة...) في معاملة واحدة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `transaction_id` | `bigint unsigned` FK | معرّف المعاملة | Transaction ID | المعاملة |
| 3 | `method` | `string(30)` | طريقة الدفع | Payment Method | نقد/تحويل/بطاقة/محفظة |
| 4 | `amount` | `unsignedBigInteger` | المبلغ | Amount | قيمة الدفعة بهذه الوسيلة |
| 5 | `cash_received` | `unsignedBigInteger` NULL | النقد المستلم | Cash Received | المبلغ النقدي المستلم |
| 6 | `change` | `unsignedBigInteger` DEF `0` | الباقي | Change | الباقي النقدي |
| 7 | `bank_account_id` | `bigint unsigned` FK NULL | الحساب البنكي | Bank Account ID | الحساب البنكي (للتحويل) |
| 8 | `payment_status` | `string(20)` DEF `paid` | حالة الدفع | Payment Status | حالة هذه الوسيلة |
| 9 | `payment_reference` | `string(255)` NULL | مرجع الدفع | Payment Reference | رقم مرجع العملية |
| 10 | `payment_url` | `text` NULL | رابط الدفع | Payment URL | رابط الدفع الإلكتروني |
| 11 | `qr_string` | `text` NULL | نص QR | QR String | بيانات QR |
| 12 | `paid_at` | `timestamp` NULL | تاريخ الدفع | Paid At | وقت اكتمال الدفع |
| 13 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 14 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`transaction_id`, `payment_status`) و(`method`, `payment_status`).

**ملاحظة:** مجموع `amount` في كل الوسائل يجب أن يساوي `grand_total` في المعاملة.

---

### 61. `transaction_detail_batch_allocations` — تخصيص دفعات تفاصيل المعاملات (Transaction Detail Batch Allocations)

يربط كل بند معاملة بدفعات محددة، لتتبع الصرف من دفعات ذات صلاحية (FEFO).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `transaction_detail_id` | `bigint unsigned` FK | معرّف تفاصيل المعاملة | Transaction Detail ID | البند |
| 3 | `product_batch_id` | `bigint unsigned` FK | معرّف الدفعة | Product Batch ID | الدفعة المصروفة |
| 4 | `qty` | `integer` | الكمية | Quantity | الكمية المصروفة من الدفعة |
| 5 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 6 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**قيود:** فريد مركب على (`transaction_detail_id`, `product_batch_id`)، وحذف متسلسل من الجهتين.

**ملاحظة:** يُستخدم هذا الجدول عندما تُصرف كمية البند من أكثر من دفعة، لمعرفة كم أُخذ من كل دفعة.

---

### 62. `profits` — الأرباح (Profits)

يحفظ ربح المعاملة (الإجمالي ناقص تكلفة الشراء) في وقت البيع.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `transaction_id` | `bigint unsigned` FK | معرّف المعاملة | Transaction ID | المعاملة |
| 3 | `total` | `bigint` | إجمالي الربح | Total Profit | قيمة الربح |
| 4 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 5 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** يُخزّن الربح وقت البيع حتى لا تتأثر التقارير بتغير أسعار الشراء لاحقًا.

---

### 63. `sales_returns` — مرتجعات المبيعات (Sales Returns)

مستند إرجاع من عميل، مع تحديد طريقة الاسترداد (نقد أو رصيد).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `code` | `string(255)` UQ | رمز المرتجع | Return Code | رمز فريد للمرتجع |
| 3 | `transaction_id` | `bigint unsigned` FK | معرّف المعاملة | Transaction ID | المعاملة الأصلية |
| 4 | `customer_id` | `bigint unsigned` FK NULL | معرّف العميل | Customer ID | العميل |
| 5 | `cashier_id` | `bigint unsigned` FK NULL | معرّف الكاشير | Cashier ID | الكاشير المنفّذ |
| 6 | `cashier_shift_id` | `bigint unsigned` FK NULL | معرّف الوردية | Cashier Shift ID | الوردية |
| 7 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الإرجاع |
| 8 | `status` | `string(20)` DEF `draft` | الحالة | Status | مسودة/مكتمل/ملغي |
| 9 | `return_type` | `string(30)` DEF `refund_cash` | نوع الاسترداد | Return Type | نقد (`refund_cash`) أو رصيد (`credit`) |
| 10 | `refund_amount` | `bigint` DEF `0` | مبلغ الاسترداد النقدي | Refund Amount | المبلغ المسترد نقدًا |
| 11 | `credited_amount` | `bigint` DEF `0` | المبلغ الدائن | Credited Amount | المبلغ المضاف كرصيد |
| 12 | `total_return_amount` | `bigint` DEF `0` | إجمالي المرتجع | Total Return Amount | إجمالي قيمة المرتجع |
| 13 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات المرتجع |
| 14 | `completed_at` | `timestamp` NULL | تاريخ الإكمال | Completed At | وقت اكتمال المرتجع |
| 15 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 16 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`transaction_id`, `status`).

**ملاحظات:**
- عند `return_type = credit` يُنشأ سجل في `customer_credits` بدل رد النقد.
- يمكن إرجاع جزء من الكمية، ويُمنع تجاوز الكمية المتبقية القابلة للإرجاع.

---

### 64. `sales_return_items` — بنود مرتجعات المبيعات (Sales Return Items)

بنود المنتجات المرتجعة مع الكمية المباعة والمرتجعة سابقًا.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `sales_return_id` | `bigint unsigned` FK | معرّف المرتجع | Sales Return ID | مستند المرتجع |
| 3 | `transaction_detail_id` | `bigint unsigned` FK | معرّف تفاصيل المعاملة | Transaction Detail ID | البند الأصلي |
| 4 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 5 | `qty_sold` | `integer` | الكمية المباعة | Quantity Sold | الكمية الأصلية المباعة |
| 6 | `qty_returned_before` | `integer` DEF `0` | المرتجع سابقًا | Quantity Returned Before | الكمية المرتجعة قبل هذا المستند |
| 7 | `qty_return` | `integer` | الكمية المرتجعة | Quantity Return | كمية هذا الإرجاع |
| 8 | `unit_price` | `bigint` | سعر الوحدة | Unit Price | سعر الوحدة |
| 9 | `subtotal` | `bigint` | الإجمالي الفرعي | Subtotal | قيمة البند المرتجع |
| 10 | `return_reason` | `string(255)` | سبب الإرجاع | Return Reason | سبب الإرجاع |
| 11 | `restock_to_inventory` | `boolean` DEF `true` | إعادة للمخزون | Restock To Inventory | هل تُعاد الكمية إلى المخزون؟ |
| 12 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 13 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** `qty_returned_before` يمنع الإرجاع المكرر، و`restock_to_inventory` يسمح بإرجاع البضاعة التالفة دون إعادتها للمخزون.

---

### 65. `cashier_shifts` — ورديات الكاشير (Cashier Shifts)

جدول فتح وإغلاق وردية الكاشير، ويحصر كل مبيعات الفترة ويحسب الفرق النقدي.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `user_id` | `bigint unsigned` FK | معرّف المستخدم | User ID | الكاشير صاحب الوردية |
| 3 | `opened_by` | `bigint unsigned` FK | فتح بواسطة | Opened By | المستخدم الذي فتح الوردية |
| 4 | `closed_by` | `bigint unsigned` FK NULL | أغلق بواسطة | Closed By | المستخدم الذي أغلق الوردية |
| 5 | `warehouse_id` | `bigint unsigned` FK NULL | معرّف المستودع | Warehouse ID | مستودع الوردية |
| 6 | `opened_at` | `timestamp` | تاريخ الفتح | Opened At | وقت فتح الوردية |
| 7 | `closed_at` | `timestamp` NULL | تاريخ الإغلاق | Closed At | وقت إغلاق الوردية |
| 8 | `opening_cash` | `unsignedBigInteger` DEF `0` | النقد الافتتاحي | Opening Cash | المبلغ النقدي في بداية الوردية |
| 9 | `expected_cash` | `unsignedBigInteger` DEF `0` | النقد المتوقع | Expected Cash | النقد المتوقع في نهاية الوردية |
| 10 | `actual_cash` | `unsignedBigInteger` NULL | النقد الفعلي | Actual Cash | المبلغ المعدود فعليًا |
| 11 | `cash_sales_total` | `unsignedBigInteger` DEF `0` | إجمالي مبيعات النقد | Cash Sales Total | مبيعات نقدية |
| 12 | `non_cash_sales_total` | `unsignedBigInteger` DEF `0` | إجمالي المبيعات غير النقدية | Non-Cash Sales Total | مبيعات بوسائل أخرى |
| 13 | `cash_refund_total` | `unsignedBigInteger` DEF `0` | إجمالي الاسترداد النقدي | Cash Refund Total | مرتجعات نقدية |
| 14 | `non_cash_refund_total` | `unsignedBigInteger` DEF `0` | إجمالي الاسترداد غير النقدي | Non-Cash Refund Total | مرتجعات غير نقدية |
| 15 | `cash_in_total` | `unsignedBigInteger` DEF `0` | إجمالي الإيداع النقدي | Cash In Total | إيداعات نقدية للوردية |
| 16 | `cash_out_total` | `unsignedBigInteger` DEF `0` | إجمالي السحب النقدي | Cash Out Total | سحوبات نقدية من الوردية |
| 17 | `transactions_count` | `unsignedInteger` DEF `0` | عدد المعاملات | Transactions Count | عدد معاملات الوردية |
| 18 | `sales_returns_count` | `unsignedInteger` DEF `0` | عدد المرتجعات | Sales Returns Count | عدد مرتجعات الوردية |
| 19 | `cash_difference` | `bigint` NULL | الفرق النقدي | Cash Difference | الفرق بين الفعلي والمتوقع |
| 20 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الوردية |
| 21 | `close_notes` | `text` NULL | ملاحظات الإغلاق | Close Notes | ملاحظات وقت الإغلاق |
| 22 | `status` | `string(20)` DEF `open` | الحالة | Status | مفتوحة/مغلقة |
| 23 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 24 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`user_id`, `status`) و(`status`, `opened_at`).

**ملاحظة:** `cash_difference = actual_cash - expected_cash`، وتُحسب `expected_cash` من النقد الافتتاحي + مبيعات النقد + الإيداعات − الاسترداد النقدي − السحوبات.

---

### 66. `shift_cash_movements` — حركات النقد بالوردية (Shift Cash Movements)

تسجيل الإيداعات والسحوبات النقدية داخل الوردية.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `cashier_shift_id` | `bigint unsigned` FK | معرّف الوردية | Cashier Shift ID | الوردية |
| 3 | `type` | `enum(in, out)` | نوع الحركة | Movement Type | إيداع (`in`) أو سحب (`out`) |
| 4 | `amount` | `unsignedBigInteger` | المبلغ | Amount | قيمة الحركة |
| 5 | `note` | `string(255)` NULL | ملاحظة | Note | سبب الحركة |
| 6 | `user_id` | `bigint unsigned` FK | المستخدم | User ID | المستخدم الذي نفّذ الحركة |
| 7 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 8 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** حذف الوردية يحذف حركاتها (Cascade).

---

### 67. `discount_approval_logs` — سجل موافقات الخصم (Discount Approval Logs)

سجل طلبات اعتماد الخصم التي تتجاوز الحد المسموح.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `transaction_id` | `bigint unsigned` FK | معرّف المعاملة | Transaction ID | المعاملة المرتبطة |
| 3 | `cashier_id` | `bigint unsigned` FK | معرّف الكاشير | Cashier ID | الكاشير مقدم الطلب |
| 4 | `requested_discount` | `bigint` | الخصم المطلوب | Requested Discount | قيمة الخصم المطلوبة |
| 5 | `status` | `string(20)` | الحالة | Status | بانتظار الاعتماد/معتمد/مرفوض |
| 6 | `responded_by` | `bigint unsigned` FK NULL | رد بواسطة | Responded By | المستخدم الذي اعتمد/رفض |
| 7 | `responded_at` | `timestamp` NULL | تاريخ الرد | Responded At | وقت الرد |
| 8 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات القرار |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- تُستخدم حدود الاعتماد الموجودة في `settings`: `discount_approval_threshold` (مبلغ)، `discount_approval_percent_threshold` (نسبة)، `discount_approval_timeout` (مهلة بالثواني).
- عند تجاوز الحد، تُعلّق المعاملة حتى يعتمدها مستخدم مخوّل، وتُسجل الحالة في `transactions.discount_approval_status`.

---
## 8. المطعم والجلوس (Dine-in)

### 68. `dine_areas` — مناطق الجلوس (Dine Areas)

تقسيم صالة المطعم إلى مناطق (داخلي، خارجي، عائلي...).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع المالك للمنطقة |
| 3 | `name` | `string(255)` | اسم المنطقة | Area Name | اسم المنطقة |
| 4 | `sort_order` | `integer` DEF `0` | ترتيب العرض | Sort Order | ترتيب المناطق |
| 5 | `is_active` | `boolean` DEF `true` | مفعّلة | Is Active | هل المنطقة مستخدمة؟ |
| 6 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 7 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

---

### 69. `dine_tables` — طاولات المطعم (Dine Tables)

طاولات المطعم مع إحداثياتها على مخطط الصالة ورمز QR الخاص بها.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `dine_area_id` | `bigint unsigned` FK NULL | معرّف المنطقة | Dine Area ID | المنطقة التابعة لها الطاولة |
| 3 | `name` | `string(255)` | اسم الطاولة | Table Name | اسم/رقم الطاولة |
| 4 | `token` | `string(255)` UQ | رمز الطاولة | Table Token | رمز فريد لفتح قائمة الطاولة عبر QR |
| 5 | `capacity` | `integer` DEF `0` | السعة | Capacity | عدد المقاعد |
| 6 | `pos_x` | `integer` DEF `0` | إحداثي X | Position X | موقع الطاولة أفقيًا على المخطط |
| 7 | `pos_y` | `integer` DEF `0` | إحداثي Y | Position Y | موقع الطاولة رأسيًا على المخطط |
| 8 | `shape` | `string(255)` DEF `circle` | الشكل | Shape | شكل الطاولة على المخطط (دائري/مربع...) |
| 9 | `sort_order` | `integer` DEF `0` | ترتيب العرض | Sort Order | ترتيب الطاولة |
| 10 | `is_active` | `boolean` DEF `true` | مفعّلة | Is Active | هل الطاولة متاحة؟ |
| 11 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 12 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`token`) و(`is_active`).

**ملاحظة:** `token` هو المفتاح المستخدم في رابط QR الذي يفتحه العميل لطلب الطعام من طاولته.

---

### 70. `dine_orders` — طلبات المطعم (Dine Orders)

طلب يبدأ من العميل عبر QR ثم يُدفع عند الكاشير أو إلكترونيًا.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `dine_table_id` | `bigint unsigned` FK | معرّف الطاولة | Dine Table ID | الطاولة |
| 3 | `customer_id` | `bigint unsigned` FK NULL | معرّف العميل | Customer ID | العميل (اختياري) |
| 4 | `access_token` | `string(255)` UQ | رمز الوصول | Access Token | رمز فريد لمتابعة الطلب |
| 5 | `status` | `string(255)` DEF `submitted` | الحالة | Status | تم الإرسال/قيد التحضير/جاهز/مكتمل |
| 6 | `notes` | `text` NULL | ملاحظات | Notes | ملاحظات الطلب |
| 7 | `payment_option` | `string(255)` DEF `pay_at_counter` | خيار الدفع | Payment Option | الدفع عند الكاشير أو إلكترونيًا |
| 8 | `payment_method` | `string(255)` NULL | طريقة الدفع | Payment Method | وسيلة الدفع الإلكتروني |
| 9 | `payment_status` | `string(255)` NULL | حالة الدفع | Payment Status | حالة الدفع |
| 10 | `payment_reference` | `string(255)` NULL | مرجع الدفع | Payment Reference | رقم مرجع الدفع |
| 11 | `payment_url` | `string(255)` NULL | رابط الدفع | Payment URL | رابط إتمام الدفع |
| 12 | `cashier_id` | `bigint unsigned` FK NULL | معرّف الكاشير | Cashier ID | الكاشير الذي حول الطلب إلى معاملة |
| 13 | `transaction_id` | `bigint unsigned` FK NULL | معرّف المعاملة | Transaction ID | المعاملة الناتجة |
| 14 | `subtotal` | `integer` DEF `0` | الإجمالي الفرعي | Subtotal | مجموع الطلب |
| 15 | `item_count` | `integer` DEF `0` | عدد البنود | Item Count | عدد الأصناف |
| 16 | `submitted_at` | `timestamp` NULL | تاريخ الإرسال | Submitted At | وقت إرسال الطلب |
| 17 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 18 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهارس:** (`status`) و(`access_token`) و(`submitted_at`).

**ملاحظة:** عند دفع الطلب عند الكاشير تُنشأ معاملة `transactions` وتُربط بالطلب عبر `transaction_id`.

---

### 71. `dine_order_items` — بنود طلبات المطعم (Dine Order Items)

أصناف الطلب مع الكمية والسعر والوحدة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `dine_order_id` | `bigint unsigned` FK | معرّف الطلب | Dine Order ID | الطلب |
| 3 | `product_id` | `bigint unsigned` FK | معرّف المنتج | Product ID | المنتج |
| 4 | `unit_id` | `bigint unsigned` FK NULL | معرّف الوحدة | Unit ID | الوحدة |
| 5 | `conversion_factor` | `decimal(10,4)` DEF `1` | معامل التحويل | Conversion Factor | تحويل الوحدة إلى الأساسية |
| 6 | `qty` | `integer` | الكمية | Quantity | الكمية |
| 7 | `price` | `integer` | السعر | Price | سعر الوحدة |
| 8 | `note` | `text` NULL | ملاحظة | Note | ملاحظة الصنف (مثل: بدون سكر) |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظة:** حذف الطلب يحذف بنوده (Cascade).

---

## 9. المدفوعات

### 72. `payment_settings` — إعدادات الدفع (Payment Settings)

إعدادات بوابات الدفع الإلكتروني لكل فرع على حدة.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL UQ | معرّف الفرع | Outlet ID | الفرع المالك للإعدادات (سجل واحد لكل فرع) |
| 3 | `default_gateway` | `string(255)` DEF `cash` | البوابة الافتراضية | Default Gateway | بوابة الدفع الافتراضية |
| 4 | `bank_transfer_enabled` | `boolean` DEF `false` | التحويل البنكي مفعّل | Bank Transfer Enabled | هل التحويل البنكي متاح؟ |
| 5 | `midtrans_enabled` | `boolean` DEF `false` | Midtrans مفعّل | Midtrans Enabled | تفعيل بوابة Midtrans |
| 6 | `midtrans_server_key` | `text` NULL | مفتاح خادم Midtrans | Midtrans Server Key | مفتاح سري **مشفّر** |
| 7 | `midtrans_client_key` | `string(255)` NULL | مفتاح عميل Midtrans | Midtrans Client Key | مفتاح عام للواجهة |
| 8 | `midtrans_production` | `boolean` DEF `false` | وضع إنتاج Midtrans | Midtrans Production | هل الاتصال ببيئة الإنتاج؟ |
| 9 | `xendit_enabled` | `boolean` DEF `false` | Xendit مفعّل | Xendit Enabled | تفعيل بوابة Xendit |
| 10 | `xendit_secret_key` | `text` NULL | مفتاح Xendit السري | Xendit Secret Key | مفتاح سري **مشفّر** |
| 11 | `xendit_public_key` | `string(255)` NULL | مفتاح Xendit العام | Xendit Public Key | مفتاح عام |
| 12 | `xendit_production` | `boolean` DEF `false` | وضع إنتاج Xendit | Xendit Production | هل الاتصال ببيئة الإنتاج؟ |
| 13 | `xendit_callback_token` | `text` NULL | رمز استدعاء Xendit | Xendit Callback Token | رمز التحقق من استدعاءات Xendit **مشفّر** |
| 14 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 15 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**ملاحظات:**
- أعمدة `midtrans_server_key` و`xendit_secret_key` و`xendit_callback_token` كانت `string` ثم أصبحت `text`، وتُشفّر قيمها عبر `Crypt::encryptString` في migration خاص، وتُفك عند الحاجة.
- `outlet_id` فريد، أي أن لكل فرع سجل إعدادات دفع واحدًا فقط.

---

### 73. `bank_accounts` — الحسابات البنكية (Bank Accounts)

الحسابات البنكية المستلمة للتحويلات، ويمكن ربطها بفرع.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `outlet_id` | `bigint unsigned` FK NULL | معرّف الفرع | Outlet ID | الفرع المالك للحساب |
| 3 | `bank_name` | `string(255)` | اسم البنك | Bank Name | اسم البنك |
| 4 | `account_number` | `string(255)` | رقم الحساب | Account Number | رقم الحساب |
| 5 | `account_name` | `string(255)` | اسم صاحب الحساب | Account Name | اسم المستفيد |
| 6 | `logo` | `string(255)` NULL | شعار البنك | Bank Logo | مسار شعار البنك |
| 7 | `is_active` | `boolean` DEF `true` | مفعّل | Is Active | هل الحساب مستخدم؟ |
| 8 | `sort_order` | `integer` DEF `0` | ترتيب العرض | Sort Order | ترتيب الحسابات |
| 9 | `created_at` | `timestamp` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `updated_at` | `timestamp` | تاريخ التحديث | Updated At | آخر تحديث |

**فهرس:** (`outlet_id`, `is_active`).

**ملاحظة:** تُستخدم هذه الحسابات في الدفع بالتحويل البنكي وفي تسجيل دفعات الذمم المدينة والدائنة.

---

## 10. النظام والتشغيل (System & Queue)

> هذه الجداول من إطار Laravel وتُستخدم internally للتخزين المؤقت والمهام الخلفية، وهي موجودة ضمن ملفات migration المشروع.

### 74. `cache` — ذاكرة التخزين المؤقت (Cache)

تخزين بيانات مؤقتة لتسريع الأداء.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `key` | `string(255)` PK | المفتاح | Key | مفتاح العنصر المخزّن |
| 2 | `value` | `mediumtext` | القيمة | Value | قيمة العنصر |
| 3 | `expiration` | `integer` | وقت الانتهاء | Expiration | طابع زمني انتهاء الصلاحية |

---

### 75. `cache_locks` — أقفال ذاكرة التخزين (Cache Locks)

أقفال تُستخدم لمنع تنفيذ عملية مرتين في نفس الوقت.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `key` | `string(255)` PK | المفتاح | Key | مفتاح القفل |
| 2 | `owner` | `string(255)` | المالك | Owner | صاحب القفل الحالي |
| 3 | `expiration` | `integer` | وقت الانتهاء | Expiration | وقت انتهاء القفل |

---

### 76. `jobs` — مهام قائمة الانتظار (Jobs)

المهام الخلفية المنتظرة للتنفيذ (مثل إرسال الإشعارات أو التقارير).

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `queue` | `string(255)` IDX | قائمة الانتظار | Queue | اسم قائمة الانتظار |
| 3 | `payload` | `longtext` | الحمولة | Payload | بيانات المهمة المسلسلة |
| 4 | `attempts` | `unsignedTinyInteger` | عدد المحاولات | Attempts | عدد مرات التنفيذ |
| 5 | `reserved_at` | `unsignedInteger` NULL | وقت الحجز | Reserved At | وقت حجز المهمة |
| 6 | `available_at` | `unsignedInteger` | وقت التوفر | Available At | موعد تنفيذ المهمة |
| 7 | `created_at` | `unsignedInteger` | تاريخ الإنشاء | Created At | وقت إنشاء المهمة |

---

### 77. `job_batches` — دفعات المهام (Job Batches)

مجموعة مهام تُنفذ كدفعة واحدة مع متابعة حالتها.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `string(255)` PK | المعرّف | ID | معرّف الدفعة |
| 2 | `name` | `string(255)` | الاسم | Name | اسم الدفعة |
| 3 | `total_jobs` | `integer` | إجمالي المهام | Total Jobs | عدد المهام |
| 4 | `pending_jobs` | `integer` | المهام المعلقة | Pending Jobs | عدد المهام المنتظرة |
| 5 | `failed_jobs` | `integer` | المهام الفاشلة | Failed Jobs | عدد المهام الفاشلة |
| 6 | `failed_job_ids` | `longtext` | معرّفات المهام الفاشلة | Failed Job IDs | قائمة المعرّفات |
| 7 | `options` | `mediumtext` NULL | خيارات الدفعة | Options | خيارات إضافية |
| 8 | `cancelled_at` | `integer` NULL | تاريخ الإلغاء | Cancelled At | وقت إلغاء الدفعة |
| 9 | `created_at` | `integer` | تاريخ الإنشاء | Created At | وقت الإنشاء |
| 10 | `finished_at` | `integer` NULL | تاريخ الانتهاء | Finished At | وقت انتهاء الدفعة |

---

### 78. `failed_jobs` — المهام الفاشلة (Failed Jobs)

سجل المهام التي فشلت بعد كل المحاولات، مع سبب الفشل.

| # | اسم الحقل | النوع | الاسم بالعربية | الاسم بالإنجليزية | الوظيفة |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint unsigned` PK | المعرّف | ID | المفتاح الأساسي |
| 2 | `uuid` | `string(255)` UQ | المعرّف UUID | UUID | معرّف فريد للمهمة |
| 3 | `connection` | `text` | الاتصال | Connection | اسم اتصال قائمة الانتظار |
| 4 | `queue` | `text` | قائمة الانتظار | Queue | اسم القائمة |
| 5 | `payload` | `longtext` | الحمولة | Payload | بيانات المهمة |
| 6 | `exception` | `longtext` | الاستثناء | Exception | نص الخطأ/الاستثناء |
| 7 | `failed_at` | `timestamp` DEF `CURRENT_TIMESTAMP` | تاريخ الفشل | Failed At | وقت فشل المهمة |

---

## ملخص الإحصاءات

| التصنيف | عدد الجداول |
|---|---:|
| النظام والمصادقة والصلاحيات | 10 |
| الإعدادات والفروع | 3 |
| المناطق الجغرافية | 4 |
| العملاء والولاء والتسويق | 10 |
| الكتالوج والمنتجات | 14 |
| المستودعات والمخزون والمشتريات | 15 |
| المبيعات ونقطة البيع | 11 |
| المطعم والجلوس | 4 |
| المدفوعات | 2 |
| النظام والتشغيل | 5 |
| **الإجمالي** | **78** |

---

**تم التوثيق اعتمادًا على ملفات migration فقط، دون الرجوع إلى ملفات التصميم السابقة، ودون إضافة أو حذف أي جدول.**
