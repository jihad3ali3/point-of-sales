# الميزات الإضافية: الكاشير، المخزون، المشتريات، المالية، العملاء، المطعم، التقارير، الإدارة (بالعربي)

العودة لمؤشر الوثائق: [`docs/README.md`](../README.md)

---

## 1. شاشة الكاشير (POS & Transaksi)
**الميجداول (الجداول):**
- `pos_carts`: (id, cashier_id, warehouse_id, status, note, created_at)
- `pos_cart_items`: (id, cart_id, product_id, unit_id, quantity, unit_price, conversion_factor, base_quantity)
- `pos_transactions`: (id, cart_id, branch_id, cashier_id, total, tax, discount, status, payment_method)
- `pos_payments`: (id, transaction_id, method, amount, bank_account_id, gateway_ref, status)
- `pos_holds`: (id, cart_id, name, note, resumed_at)

**القيود:**
- السلة مرتبطة بالكاشير والمستودع (ليست متصفح)
- تعليق السلة (Hold/Resume) بالاسم/الملاحظة
- تحويل الوحدات للأساسية (conversion_factor) والمخزون يُخصم بالأساسية
- الدفع: نقدي (حساب الباقي)، تحويل (حساب بنكي)، بوابات (Midtrans/Xendit)، آجل (دين)، مقسّم (Split)
- الخصم اليدوي: موافقة مدير (Discount Approval) فوق الحد
- الضريبة (PPN 11%): شاملة أو مضافة
- الطباعة: ESC/POS (80/58مم) أو PDF/عادية
- الأوفلاين: IndexedDB محلي + مزامنة (بدون تكرار)
- وردية مفتوحة (Shift) مطلوبة (بداية/نهاية/تسوية)

---

## 2. المخزون والمستودعات المتعددة (Inventory & Multi-Warehouse)
**الميجداول (الجداول):**
- `products`: (id, name, sku, barcode, image, buy_price, sell_price, category_id, stock_total, min_stock, max_stock)
- `categories`: (id, name, parent_id)
- `product_warehouse`: (id, product_id, warehouse_id, stock, reserved)
- `stock_transfers`: (id, from_warehouse_id, to_warehouse_id, status, created_at)
- `stock_transfer_items`: (id, transfer_id, product_id, quantity)
- `stock_opnames`: (id, warehouse_id, status, created_at)
- `stock_opname_items`: (id, opname_id, product_id, system_stock, physical_stock, difference)
- `stock_mutations`: (id, product_id, warehouse_id, type, quantity, before_stock, after_stock, reference, user_id, timestamp)
- `batches`: (id, product_id, batch_number, expiry_date, quantity, warehouse_id)

**القيود:**
- `product_warehouse` هو السجل التشغيلي؛ `products.stock_total` مجموع إجمالي (تحديث معاً في Transaction)
- نقل المخزون: مسودة ← إرسال (خصم) ← استلام (إضافة) / إلغاء
- الجرد (Opname): مقارنة فعلي/دفتر ← اعتماد وتصحيح
- حركات المخزون: نوع/كمية/قبل/بعد/مرجع/مستخدم/وقت
- الدفعات (Batch/Expiry): FEFO (أقرب صلاحية أولاً)
- المنتجات المركبة (Kits): خصم المكونات؛ المخزون = أقل كمية تكفي
- إعادة الطلب (Reorder Point): مقترح شراء تلقائي يومياً
- تنبيهات: نقص المخزون / صلاحية قريبة

---

## 3. المشتريات (Purchasing)
**الميجداول (الجداول):**
- `purchase_orders`: (id, supplier_id, status, total, created_at)
- `purchase_order_items`: (id, order_id, product_id, quantity, unit_price)
- `goods_receivings`: (id, order_id, status, received_at)
- `receiving_items`: (id, receiving_id, product_id, quantity, batch_number, expiry_date)
- `payables`: (id, supplier_id, order_id, amount, due_date, status)

**القيود:**
- دورة: مسودة ← مطلوب ← استلام (جزئي/كامل) ← دين
- الاستلام يزيد المخزون ويسجل حركة + دفعات/صلاحية
- إرجاع للمورد: يخصم المخزون ويقلل الدين

---

## 4. المالية (Finance)
**الميجداول (الجداول):**
- `receivables`: (id, customer_id, invoice_id, amount, due_date, status, paid_amount)
- `payables`: (id, supplier_id, order_id, amount, due_date, status, paid_amount)
- `aging_analysis`: (id, type, range_0_30, range_30_60, range_60_90, range_90_plus, notes)
- `customer_statements`: (id, customer_id, total, paid, balance, pdf_path)
- `supplier_statements`: (id, supplier_id, total, paid, balance, pdf_path)
- `portal_links`: (id, customer_id, token, expires_at, invoice_id)
- `taxes`: (id, name, rate, type, product_id_nullable)

**القيود:**
- دين العميل (آجل) ← دفع جزئي
- دين المورد ← استلام/يدوي
- تحليل الأعمار: 0-30/30-60/60-90/+90 + ملاحظات
- كشف حساب + PDF
- بوابة العميل: توكن آمن + دفع أونلاين
- ضرائب: معدل/نوع لكل منتج + إجمالي/صافي

---

## 5. إدارة العملاء والولاء (CRM & Loyalty)
**الميجداول (الجداول):**
- `customers`: (id, name, phone, email, region_id, tier, points)
- `regions`: (id, name, province, city, district, village)
- `member_tiers`: (id, name, min_points, benefits)
- `loyalty_points`: (id, customer_id, points, type, reference, earned_at)
- `vouchers`: (id, customer_id, code, discount_amount, condition, expires_at, used)
- `segments`: (id, name, criteria, type)
- `segment_customers`: (id, segment_id, customer_id, updated_at)
- `campaigns`: (id, segment_id, type, content, scheduled_at, sent_at)

**القيود:**
- مناطق إندونيسيا (محافظة/مدينة/حي/قرية)
- درجات: عادي/فضي/ذهبي/بلاتيني
- نقاط: كسب/استبدال (Earn/Redeem)
- قسائم: شرط/انتهاء/استخدام
- شرائح: يدوية/تلقائية (قواعد) ← تحديث ليلي
- حملات: جدولة/إرسال (واتساب/رسائل)

---

## 6. المطعم — الطلب داخل المحل (Dine-In)
**الميجداول (الجداول):**
- `dining_areas`: (id, branch_id, name)
- `dining_tables`: (id, area_id, number, capacity, status)
- `dining_orders`: (id, table_id, customer_phone, status, created_at)
- `dining_order_items`: (id, order_id, product_id, quantity, unit_id, conversion_factor)
- `dining_qr_links`: (id, table_id, token, expires_at)

**القيود:**
- مناطق/طاولات/أرقام/سعة/حالة
- QR للطاولة ← طلب بدون تسجيل ← متابعة صفحة عامة
- الإدارة تقبل/ترفض
- الدفع عند الكاشير فقط (الأونلاين معطّل)

---

## 7. التقارير والمستندات (Reports & Documents)
**الميجداول (الجداول):**
- `reports`: (id, name, type, filters, generated_by, generated_at)
- `sales_summaries`: (id, date, cashier_id, warehouse_id, payment_method, total, profit)
- `profit_margins`: (id, product_id, transaction_id, net_sale, hpp, profit)
- `insights`: (id, metric, hour, cashier_id, customer_repeat_count)
- `documents`: (id, type, path, generated_at)
- `exports`: (id, type, query, format, path, generated_at)
- `imports`: (id, type, file_path, status, rows_processed)

**القيود:**
- فلاتر: تاريخ/كاشير/مستودع/دفع + ملخص
- أرباح/هوامش: صافي − HPP لكل منتج
- رؤى: ساعة/كاشير/عملاء متكررون
- PDF: فاتورة/حراري/شحن/ديون
- تصدير/استيراد Excel: قوالب جاهزة

---

## 8. الإدارة والصلاحيات (Admin)
**الميجداول (الجداول):**
- `users`: (id, username, email, password_hash, role_id, branch_id, status)
- `roles`: (id, name, slug)
- `permissions`: (id, role_id, module, action)
- `audit_logs`: (id, user_id, module, action, before, after, ip, timestamp)
- `branches`: (id, name, address, warehouse_id)
- `store_settings`: (id, key, value)
- `app_version`: (id, version, released_at)

**القيود:**
- RBAC: مستخدم/دور/صلاحية دقيقة (module/create/edit/delete) ← واجهة تختفي/تظهر
- سجل تدقيق: قبل/بعد/من/متى/IP
- فروع: مستودع/صلاحية تبديل
- معالج الإعداد الأول (/setup): أدمن/متجر/نشاط/مستودع ← قفل نهائي
- إعدادات: متجر/طابعة/ولاء/بنوك/بوابات/واتساب/أسعار/وحدات/مستودعات/فروع
- إصدار التطبيق: مركزي/واجهة

---

*تحديث الوثائق: 2026-10-31*
