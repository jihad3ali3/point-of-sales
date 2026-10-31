# الميزات الإضافية: الكاشير، المخزون، المشتريات، المالية، العملاء، المطعم، التقارير، الإدارة (بالعربي)

العودة لمؤشر الوثائق: [`docs/README.md`](../README.md)

---

## 1. شاشة الكاشير (POS & Transaksi)
**الميجداول (الجداول):**
- `transactions`: (id, branch_id, cashier_id, total, tax, discount, status)
- `transaction_details`: (id, transaction_id, product_id, unit_id, quantity, unit_price, conversion_factor, base_quantity)
- `profits`: (id, transaction_id, net_sale, hpp, profit)
- `receivables`: (id, customer_id, invoice_id, amount, due_date, status, paid_amount)
- `bank_accounts`: (id, branch_id, name, account_number, provider)
- `payment_settings`: (id, key, value, provider)

**القيود:**
- غرض: كاشير سريع (بحث/سلة/تثبيت/استئناف/توزيع مستندات)
- ميزات: بحث (باركود/بحث)، سلة متعددة، تحديث كمية، تثبيت (Hold)، استئناف (Resume)، مسح (Clear)، دفع (نقدي، Split-أقصى 2، تحويل، Midtrans، Xendit، آجل)، طباعة (فاتورة/إيصال/ملصق)، مشاركة عامة، إضافة عميل
- مسارات: `dashboard/transactions`، `history`، `searchProduct`، `addToCart`، `updateCart`، `destroyCart`، `hold`، `resume`، `clearHold`، `held`، `store`، `print`، `public`
- صلاحية: `transactions-access` + `active_shift` (Middleware)
- أسلوب المستخدم: فتح صفحة ← Shift نشط ← بحث/سلة ← تثبيت/استئناف ← Checkout (معاملة/تفاصيل/ربح/خصم مخزون) ← آجل (مستحق) ← مستند
- تكامل: `transactions`، `transaction_details`، `profits`، `receivables`، `bank_accounts`، `payment_settings`
- القيود الحالية: Split غير متاح أوفلاين (استرداد نقدي)، Cart/Checkout يعتمد على Shift، Gateway يعتمد على إعداد، Checkout مركز تغيير المخزون

---

## 2. المخزون والمستودعات المتعددة (Inventory & Stock)
**الميجداول (الجداول):**
- `products`: (id, name, sku, barcode, image, buy_price, sell_price, category_id, stock)
- `stock_opnames`: (id, warehouse_id, status)
- `stock_opname_items`: (id, opname_id, product_id, system_stock, physical_stock)
- `stock_mutations`: (id, product_id, warehouse_id, type, quantity, before_stock, after_stock, reference, user_id, timestamp)
- `product_warehouse`: (id, product_id, warehouse_id, stock, reserved)
- `product_notification_reads`: (id, notification_id, user_id)

**القيود:**
- غرض: دقة المخزون (منتج رئيسي، جرد، تاريخ طفرات)
- ميزات: CRUD منتج، مخزون أولي، لا تعديل مباشر، جرد (مسودة→نهائي)، قائمة طفرات، تنبيه نقص، مستودع متعدد (`product_warehouse`)، تتبع دفعات/صلاحية (FEFO)، منتجات مركبة/Kits، نقطة إعادة طلب/اقتراح شراء
- مسارات: `dashboard/products`، `stock-opnames`، `stock-mutations`
- صلاحيات: `products-access/create/edit/delete`، `stock-opnames-access/create/finalize`، `stock-mutations-access`
- أسلوب المستخدم: منتج (مخزون أولي) ← طفرة أولية ← جرد (مسودة) ← إضافة منتج ← مخزون فعلي ← نهائي (تعديل/طفرة)
- تكامل: `products`، `stock_opnames`، `stock_opname_items`، `stock_mutations`، `product_notification_reads`
- تأثير: تعديل المنتج ليس مسار طفرة، المرتجعات/الجرد يزيد المخزون، تاريخ الطفرة هو المسار الرئيسي
- القيود الحالية: `products.stock` (تجميع)، `product_warehouse` (تشغيلي)، `warehouse_id` (قابل للإلغاء)، مصادر الطفرات القديمة (بدون Pivot)

---

## 3. المشتريات (Purchasing Chain)
**الميجداول (الجداول):**
- `purchase_orders`: (id, supplier_id, status, total, created_at)
- `purchase_order_items`: (id, order_id, product_id, quantity, unit_price)
- `goods_receivings`: (id, order_id, status, received_at)
- `goods_receiving_items`: (id, receiving_id, product_id, quantity)
- `supplier_returns`: (id, order_id, status, created_at)
- `supplier_return_items`: (id, return_id, product_id, quantity)
- `payables`: (id, supplier_id, order_id, amount, due_date, status)
- `product_batches`: (id, product_id, batch_number, expiry_date, quantity)
- `warehouse_id` (في PO/GR)

**القيود:**
- غرض: دورة توريد (PO، GR، SR)
- ميزات PO: اختيار مورد/منتج، كمية/سعر، رقم (`PO-YYYYmmdd-XXXX`)، حالات (مسودة/مطلوب/جزئي/مكتمل/ملغي)، فلاتر
- ميزات GR: استقبال (كامل/جزئي)، كمية/ملاحظة، تحديث حالة PO، إدخال دفعة/صلاحية، زيادة مخزون، إنشاء مستحق (30 يوم)
- ميزات SR: إرجاع للمورد (من GR)، حالات (مسودة/مكتمل/ملغي)، خصم مخزون، تصحيح مستحق، سبب لكل صنف
- مسارات/طرق: قائمة PO/إنشاء/تخزين/تفاصيل/وضع/إلغاء | قائمة GR/إنشاء/تخزين/تفاصيل | قائمة SR/إنشاء/تخزين/تفاصيل/إكمال/إلغاء
- صلاحيات: PO (وصول/إنشاء/تحديث)، GR (وصول/إنشاء)، SR (وصول/إنشاء/تحديث)
- أسلوب المستخدم: إنشاء PO ← وضع (مسودة→مطلوب) ← GR (اختيار PO/كمية/دفعة/تخزين) ← SR (اختياري: اختيار GR/صنف/إكمال) ← مستحق (تلقائي 30 يوم)
- تكامل: PO→Items→GR (كمية/زيادة)←Mutation، GR→Items←زيادة مخزون، SR→Items←خصم مخزون/طفرة، مستحق (30 يوم)، دفعات (من GR)، `warehouse_id` (من PO للـ GR)، Audit Log

---

## 4. المالية (Finance)
**الميجداول (الجداول):**
- `receivables`: (id, customer_id, invoice_id, amount, due_date, status, paid_amount)
- `receivable_payments`: (id, receivable_id, amount, method, date)
- `transactions`: (id, branch_id, cashier_id, total, tax, discount, status)
- `customers`: (id, name, phone, email)
- `bank_accounts`: (id, branch_id, name, account_number, provider)
- `payables`: (id, supplier_id, order_id, amount, due_date, status, paid_amount)

**القيود:**
- غرض: تسجيل مستحقات (آجل) + سداد تدريجي
- ميزات: قائمة مستحقات، فلاتر (حالة/عميل/فاتورة/تاريخ)، تفاصيل، دفع جزئي، حالات (غير مدفوع/جزئي/مدفوع/متأخر)، PDF مستحق، تذكير (تلقائي CRM)، إرسال واتساب (إذا نشط)
- مسارات/طرق: `dashboard/receivables`، `show`، `pay`، `pdf.show`
- صلاحيات: `receivables-access`، `receivables-pay`
- أسلوب المستخدم: Checkout (آجل) ← مستحق ← مراقبة ← تسجيل دفع ← تحديث (مدفوع/باقي/حالة)
- تكامل: `receivables`، `receivable_payments`، `transactions`، `customers`، `bank_accounts`
- تأثير: دفع المستحق يؤثر على حالة المعاملة، المرتجعات تصحح المستحقات
- تذكير/توصيل: تذكير (قريب/متأخر/طلب متكرر) ← Scheduler، توصيل (واتساب إذا نشط/جهاز متصل) أو رابط/متابعة يدوية
- ملاحظة: لا يوجد تدفق موافقة منفصل لتسجيل الدفع

---

## 5. إدارة العملاء والولاء (CRM & Customer Segments)
**الميجداول (الجداول):**
- `customer_segments`: (id, name, criteria, type)
- `customer_segment_memberships`: (id, segment_id, customer_id, source, matched_at)
- `customer_campaigns`: (id, segment_id, type, content)
- `customer_campaign_logs`: (id, campaign_id, customer_id, status)
- `crm_reminders`: (id, type, due_date, content)

**القيود:**
- غرض: تقسيم العملاء، أتمتة الحملات، تذكير (تفاعل/تحصيل)
- ميزات الأقسام: يدوي (إضافة)، تلقائي (قواعد: منفق عالي/متكرر/غير نشط/دين/متأخر)، تكوين (عتبة/تردد/آخر شراء/حالة مستحق)، تتبع (مصدر/مطابقة)
- ميزات الحملات: إنشاء (تصفية جمهور)، معالجة (سجل لكل عميل)، أنواع (تذكير/عرض/متابعة)، مشاركة فاتورة (واتساب)، توصيل مباشر (إذا نشط/جهاز)، إلغاء
- ميزات التذكيرات: مستحق قريب (3 أيام)، متأخر، طلب متكرر (عميل قديم)
- الجداول: `customer_segments`، `customer_segment_memberships`، `customer_campaigns`، `customer_campaign_logs`، `crm_reminders`
- مسارات/طرق: `customer-segments.*` (CRUD)، `crm-campaigns.*` (CRUD/معالجة/إلغاء)، `crm-reminders.index`
- صلاحيات: `customer-segments-access/create/edit/delete`، `crm-campaigns-access/create/edit/process/cancel/delete`، `crm-reminders-access`
- أسلوب المستخدم: قسم (يدوي/تلقائي) ← حملة (تصفية/سجل) ← معالجة (سجل لكل عميل) ← توصيل (واتساب مباشر/رابط/يدوي) ← وضع علامة (مرسل/متخطى)
- تكامل: الجداول أعلاه

---

## 6. المطعم — الطلب داخل المحل (Dine-In)
**الميجداول (الجداول):**
- `dine_areas`: (id, name, sort_order, is_active)
- `dine_tables`: (id, dine_area_id, name, token, capacity, pos_x, pos_y, shape, is_active)
- `dine_orders`: (id, dine_table_id, customer_id, access_token, status, notes, payment_option, payment_method, payment_status, payment_reference, payment_url, cashier_id, transaction_id, subtotal, item_count)
- `dine_order_items`: (id, dine_order_id, product_id, unit_id, qty, price, note)
- `settings`: (key, value)

**القيود:**
- غرض: طلب داخل المحل (QR)
- ميزات: مناطق/طاولات، QR (مسح/قائمة/طلب)، قبول/رفض (لوحة)، دفع (كاشير فقط)
- جداول: `dine_areas` (اسم/ترتيب/نشط)، `dine_tables` (منطقة/اسم/Token/سعة/X/Y/شكل/نشط)، `dine_orders` (طاولة/عميل/Token/حالة/ملاحظة/خيار دفع/طريقة/حالة/مرجع/رابط/كاشير/معاملة/مجموع/عدد)، `dine_order_items` (طلب/منتج/وحدة/كمية/سعر/ملاحظة)، `settings` (مفتاح/قيمة)
- مسارات (Admin): `dashboard/dine-areas` (وصول/إنشاء/تعديل/حذف)، `dashboard/dine-tables` (وصول/إنشاء/تعديل/حذف/QR)، `dashboard/dine-orders` (وصول/معالجة)
- مسارات (عامة): `/dine/{token}` (قائمة)، `/dine/{token}/order` (تقديم)، `/dine-order/{accessToken}` (حالة)، `/dine-order/{accessToken}/check` (استقصاء JSON)
- صلاحيات: `dine-tables-access/create/update/delete`، `dine-orders-access/process` (كاشير: وصول/معالجة)
- أسلوب المستخدم: مسح QR ← قائمة/اختيار ← دفع (كاشير) ← قبول/رفض (لوحة) ← تأكيد (كاشير)
- تكامل/إعدادات: `Setting` (مفتاح/قيمة/تفعيل/طلب ذاتي/دفع أونلاين-معطّل)
- ميزات إضافية: محرر مخطط (SVG Grid 25x15/40px/سحب وإفلات/تحديد/تصفية)، توليد QR (`simplesoftwareio/simple-qrcode`/PNG)، طلب ذاتي (فئات/منتجات/سلة/ملاحظة/دفع كاشير)، استقصاء (تحديث 5 ثواني/حالة)، تحويل (قبول=خصم مخزون/جاهز، رفض=سبب، كاشير=معاملة)، دفع (نقطة نهاية تقبل `pay_at_counter` فقط/رفض أونلاين)
- ملاحظات تقنية: QR (مكتبة)، استقصاء (JSON/5 ثواني/بدون بث)، خصم مخزون (قبول)، Core POS (بدون تعديل)، سلة (كاشير/بدون تأثر)

---

## 7. التقارير والمستندات (Reports & Documents)
**الميجداول (الجداول):**
- `transactions`: (id, branch_id, cashier_id, total, tax, discount, status)
- `transaction_details`: (id, transaction_id, product_id, unit_id, quantity, unit_price, conversion_factor, base_quantity)
- `profits`: (id, transaction_id, net_sale, hpp, profit)
- `receivables`: (id, customer_id, invoice_id, amount, due_date, status, paid_amount)
- `payables`: (id, supplier_id, order_id, amount, due_date, status, paid_amount)
- `settings`: (key, value)

**القيود:**
- غرض: رؤية تشغيلية (تقارير/مستندات جاهزة)
- ميزات: تقرير مبيعات، تقرير أرباح، رؤى متقدمة، فاتورة (عامة/داخلية)، إيصال حراري (58/80مم)، ملصق شحن، PDF مستحق، PDF مستحق مورد
- مسارات: `dashboard/reports/sales`، `profits`، `insights`، `pdf.transactions.invoice`، `receipt`، `shipping`، `pdf.receivables.show`، `pdf.payables.show`
- صلاحيات: `reports-access`، `profits-access`، `reports-insights-access`، وصول المستندات (حسب الأصل)
- أسلوب المستخدم: فتح تقرير (مبيعات/أرباح) ← تصفية ← فتح مستند ← طباعة/مشاركة
- تكامل: `transactions`، `transaction_details`، `profits`، `receivables`، `payables`، `settings`
- رؤى متقدمة: مبيعات بالساعة، أداء الكاشير، عملاء متكررون، اتجاهات (قرار)
- تأثير: تقارير تعتمد على جودة البيانات، المرتجعات/تصحيح المخزون يؤثر
- القيود الحالية: نطاق المقاييس (بيانات/عميل/Shift)، التحليلات لا تحل محل المحاسبة/التنبؤ
- ملفات مركزية: `SalesReportController`، `ProfitReportController`، `DocumentController`

---

## 8. الإدارة والصلاحيات (RBAC, Users, Roles)
**الميجداول (الجداول):**
- `users`: (id, username, email, password_hash)
- `roles`: (id, name, slug)
- `permissions`: (id, role_id, module, action)
- `audit_logs`: (id, user_id, module, action, before, after, ip, timestamp)
- `branches`: (id, name, address, warehouse_id)

**القيود:**
- غرض: تحكم وصول (دور/صلاحية) لكل وحدة
- ميزات: إدارة مستخدمين/أدوار/قائمة صلاحيات/حماية مسارات (Middleware)/خريطة (Inertia)
- مسارات/طرق: `dashboard/users`، `roles`، `permissions`
- صلاحيات عامة: `transactions-access`، `sales-returns-*`، `stock-opnames-*`، `cashier-shifts-*`، `audit-logs-access`
- أدوار جاهزة: `super-admin` (تجاوز/كل الفروع)، `manager` (تشغيل/بدون إدارة مستخدمين/بوابات)، `cashier` (POS/Shift/عميل/دين/Dine-In)، أدوار لكل وحدة
- أسلوب المستخدم: Seed (صلاحيات/أدوار)، أدمن (إعداد أول `/setup`)، Middleware، Inertia (خريطة)
- ملاحظات Super Admin: تجاوز ثابت، `UserSeeder` (تجريبي)، إعادة تعيين Cache، تطبيع الأسماء
- تكامل الواجهة: `auth.permissions`، `auth.super` (قائمة/أكشن)، `authorization.js`، `Permission.jsx`
- القيود الحالية: الخلفية مصدر الحقيقة، الواجهة بوابة فقط
- ملفات مركزية: `PermissionSeeder`، `RoleSeeder`، `UserSeeder`، `HandleInertiaRequests`، `Menu.jsx`

---

*تحديث الوثائق: 2026-10-31*
