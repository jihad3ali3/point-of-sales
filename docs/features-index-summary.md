# ميزات المشروع (بالترتيب) مع الجداول والقيود

العودة لمؤشر الوثائق: [`docs/README.md`](../README.md)

---

## 1. تكامل الواجهات (API Integration)
**الجداول:**
- `api_keys`: (id, service, key, status, expires_at)
- `webhooks`: (id, endpoint, event, secret, last_triggered)

**القيود:**
- الحد الأقصى للمفاتيح لكل خدمة: 5
- سرية الـ Webhook لا تُكشف في السجلات
- المهلة الزمنية للاستجابة: 30 ثانية

---

## 2. إصدارات التطبيق (App Versioning)
**الجداول:**
- `app_versions`: (id, version, platform, release_date, status)
- `version_updates`: (id, version_id, description, mandatory)

**القيود:**
- الإصدار الإلزامي يمنع الدخول للنسخ القديمة
- لا يمكن حذف إصدار نشط
- تاريخ الإصدار لا يمكن تغييره بعد النشر

---

## 3. سجلات التدقيق (Audit Logs)
**الجداول:**
- `audit_logs`: (id, user_id, action, model, record_id, ip, timestamp)
- `log_archives`: (id, log_id, storage_path, compressed)

**القيود:**
- السجلات للقراءة فقط (لا تعديل/حذف)
- الاحتفاظ لمدة 365 يوماً
- التشفير إلزامي للسجلات الحساسة

---

## 4. نوبات الكاشير (Cashier Shifts)
**الجداول:**
- `shifts`: (id, cashier_id, branch_id, start_time, end_time, balance_start, balance_end)
- `shift_transactions`: (id, shift_id, transaction_id, amount, type)

**القيود:**
- لا يمكن إغلاق نوبة بدون مطابقة الرصيد
- نوبة واحدة نشطة لكل كاشير في الفرع
- التعديل ممنوع بعد الإغلاق

---

## 5. شرائح العملاء (CRM Segments)
**الجداول:**
- `segments`: (id, name, description, criteria)
- `segment_customers`: (id, segment_id, customer_id, assigned_at)

**القيود:**
- المعايير (Criteria) تُخزن كـ JSON
- العميل يمكن أن ينتمي لشرائح متعددة
- تحديث المعايير يعيد حساب الأعضاء تلقائياً

---

## 6. بوابة العميل (Customer Portal)
**الجداول:**
- `portal_users`: (id, customer_id, email, password_hash, last_login)
- `portal_sessions`: (id, user_id, token, expires_at, device)

**القيود:**
- البريد الإلكتروني فريد
- الجلسة تنتهي بعد 24 ساعة (أو عند تسجيل الخروج)
- الحد الأقصى للجلسات النشطة: 3

---

## 7. المناطق والعملاء (Customers Regions)
**الجداول:**
- `regions`: (id, name, code, parent_id)
- `customer_regions`: (id, customer_id, region_id, priority)

**القيود:**
- التسلسل الهرمي للمناطق (أب/ابن)
- الأولوية تحدد منطقة الشحن الافتراضية
- لا يمكن حذف منطقة مرتبطة بعميل

---

## 8. تناول الطعام في المطعم (Dine-In)
**الجداول:**
- `tables`: (id, branch_id, number, capacity, status)
- `table_orders`: (id, table_id, order_id, started_at, ended_at)

**القيود:**
- لا يمكن حجز طاولة مشغولة
- السعة القصوى: 20 شخصاً
- حالة الطاولة (متاحة/مشغولة/تحت التنظيف)

---

## 9. موافقة الخصم (Discount Approval)
**الجداول:**
- `discount_rules`: (id, name, max_percent, threshold, approver_role)
- `discount_requests`: (id, rule_id, order_id, requested_percent, status, approved_by)

**القيود:**
- الخصم الأقصى: 50%
- الموافقة مطلوبة فوق 20%
- السجل لا يُمسح بعد الموافقة/الرفض

---

## 10. الاستيراد والتصدير (Import Export)
**الجداول:**
- `import_jobs`: (id, file_path, type, status, rows_processed, errors)
- `export_jobs`: (id, query, format, file_path, generated_at)

**القيود:**
- الحد الأقصى لحجم الملف: 50 ميجابايت
- تنسيقات مدعومة: CSV, Excel, JSON
- الحذف التلقائي للملفات بعد 7 أيام

---

## 11. المخزون والأسهم (Inventory Stock)
**الجداول:**
- `products`: (id, name, sku, category_id, base_unit)
- `stock_levels`: (id, product_id, warehouse_id, quantity, reserved)
- `stock_movements`: (id, stock_id, type, quantity, reference, timestamp)

**القيود:**
- الكمية لا يمكن أن تكون سالبة (إلا في حالات معينة)
- SKU فريد عالمياً
- الحجز يقلل المخزون المتاح فوراً

---

## 12. إدارة الأعضاء (Member Management)
**الجداول:**
- `members`: (id, name, phone, email, membership_tier, points)
- `member_history`: (id, member_id, action, points_change, reference)

**القيود:**
- النقاط لا تقل عن الصفر
- المستوى (Tier) يتغير تلقائياً بناءً على النقاط
- رقم الهاتف فريد

---

## 13. نقطة البيع المتنقلة (Mobile POS)
**الجداول:**
- `mobile_devices`: (id, serial, branch_id, last_sync, status)
- `mobile_sessions`: (id, device_id, cashier_id, started_at, ended_at)

**القيود:**
- الجهاز مرتبط بفرع واحد
- المزامنة مطلوبة كل 5 دقائق
- الجلسة تنتهي عند فقدان الاتصال لأكثر من 10 دقائق

---

## 14. المستودعات المتعددة (Multi Warehouse)
**الجداول:**
- `warehouses`: (id, name, location, manager_id)
- `warehouse_transfers`: (id, from_warehouse_id, to_warehouse_id, product_id, quantity, status)

**القيود:**
- النقل يتطلب موافقة مدير المستودع المستلم
- لا يمكن نقل كمية أكبر من المخزون المتاح
- الموقع إلزامي

---

## 15. الوضع غير المتصل (Offline Mode)
**الجداول:**
- `offline_queue`: (id, operation, payload, timestamp, synced)
- `offline_logs`: (id, device_id, message, level, timestamp)

**القيود:**
- الحد الأقصى للطابور: 10,000 عملية
- المزامنة تُحذف بعد النجاح
- الحجم الأقصى للحمولة: 1 ميجابايت

---

## 16. الموردون والمدفوعات (Payables Suppliers)
**الجداول:**
- `suppliers`: (id, name, contact, tax_id, payment_terms)
- `payables`: (id, supplier_id, invoice_id, amount, due_date, status)

**القيود:**
- تاريخ الاستحقاق لا يمكن أن يكون في الماضي عند الإنشاء
- الحالة: (Pending, Paid, Overdue, Cancelled)
- مبلغ الدفع لا يتجاوز الفاتورة

---

## 17. معاملات نقطة البيع (POS Transactions)
**الجداول:**
- `transactions`: (id, branch_id, cashier_id, total, tax, discount, status)
- `transaction_items`: (id, transaction_id, product_id, quantity, unit_price, total_price)

**القيود:**
- الحالة: (Draft, Completed, Cancelled, Refunded)
- لا يمكن تعديل العناصر بعد الإكمال
- الضريبة تُحسب بناءً على القواعد

---

## 18. العروض والولاء (Promotions Loyalty)
**الجداول:**
- `promotions`: (id, name, type, start_date, end_date, condition)
- `loyalty_points`: (id, member_id, promotion_id, points, earned_at)

**القيود:**
- التاريخ النهائي يجب أن يكون بعد التاريخ الابتدائي
- الشرط يُخزن كـ JSON
- النقاط تنتهي بعد سنة

---

## 19. سلسلة الشراء (Purchasing Chain)
**الجداول:**
- `purchase_orders`: (id, supplier_id, total, status, created_at)
- `purchase_items`: (id, order_id, product_id, quantity, unit_price)
- `receipts`: (id, order_id, amount, date, reference)

**القيود:**
- الحالة: (Draft, Ordered, Received, Partially Received, Cancelled)
- الكمية المستلمة لا تتجاوز المطلوبة
- الإيصال مرتبط بالطلب

---

## 20. الصلاحيات والمستخدمون والأدوار (RBAC Users Roles)
**الجداول:**
- `roles`: (id, name, slug)
- `permissions`: (id, role_id, resource, action)
- `users`: (id, username, email, role_id, status)
- `user_tokens`: (id, user_id, token, expires_at)

**القيود:**
- اسم المستخدم فريد
- الدور الافتراضي (Admin) لا يمكن حذفه
- الرمز (Token) ينتهي بعد 30 يوماً

---

## 21. المستحقات (Receivables)
**الجداول:**
- `invoices`: (id, customer_id, total, due_date, status)
- `payments`: (id, invoice_id, amount, method, date)

**القيود:**
- الحالة: (Unpaid, Partially Paid, Paid, Overdue)
- المبلغ المدفوع لا يتجاوز الفاتورة
- تاريخ الدفع لا يمكن أن يكون مستقبلياً

---

## 22. التقارير والوثائق (Reports Documents)
**الجداول:**
- `reports`: (id, name, type, parameters, generated_by)
- `report_files`: (id, report_id, file_path, generated_at, size)

**القيود:**
- النوع: (Sales, Inventory, Financial, Custom)
- حجم الملف الأقصى: 100 ميجابايت
- الحذف بعد 30 يوماً

---

## 23. مرجعات المبيعات (Sales Returns)
**الجداول:**
- `returns`: (id, transaction_id, reason, status, approved_by)
- `return_items`: (id, return_id, product_id, quantity, refund_amount)

**القيود:**
- الحالة: (Requested, Approved, Rejected, Processed)
- الكمية المرجعة لا تتجاوز المشتراة
- المبالغ المستردة تُسجل في الحساب

---

## 24. إعدادات المدفوعات (Settings Payments)
**الجداول:**
- `payment_gateways`: (id, name, provider, config, status)
- `payment_methods`: (id, gateway_id, method_name, fee_percent, active)

**القيود:**
- الحالة: (Active, Inactive, Maintenance)
- الرسوم لا تقل عن 0%
- الإعدادات مشفرة

---

## 25. إدارة الضرائب (Tax Management)
**الجداول:**
- `taxes`: (id, name, rate, type, jurisdiction)
- `tax_rules`: (id, tax_id, product_category, condition)

**القيود:**
- النوع: (Percentage, Fixed)
- المعدل لا يتجاوز 100%
- القواعد تُطبق حسب الفئة

---

## 26. الطابعة الحرارية (Thermal Printer)
**الجداول:**
- `printers`: (id, branch_id, name, ip, model, status)
- `print_jobs`: (id, printer_id, document_type, content, status, printed_at)

**القيود:**
- الحالة: (Pending, Printing, Completed, Failed)
- المحتوى يُخزن كـ Base64 أو نص
- الحد الأقصى للمهام المعلقة: 50

---

## 27. تحويل الوحدات (Unit Conversion)
**الجداول:**
- `units`: (code, name, symbol)
- `product_units`: (product_id, unit_id, is_base, conversion_factor, buy_price, sell_price, barcode)
- `carts`: (unit_id, conversion_factor)
- `transaction_details`: (unit_id, conversion_factor)

**القيود:**
- المنتجات الحالية: PCS (معامل 1)
- الحذف: ممنوع إذا مستخدم
- الأسعار: مخزنة مباشرة
- المخزون: يُدار بالأساسية
- الباركود: خاص بالوحدة
- POS Checkout: يعتمد على الكمية الأساسية

---

## 28. بوابة واتساب (WhatsApp Gateway)
**الجداول:**
- `whatsapp_accounts`: (id, phone, api_key, status, provider)
- `whatsapp_messages`: (id, account_id, to, content, type, status, sent_at)

**القيود:**
- الحالة: (Pending, Sent, Delivered, Failed)
- المحتوى: نص/صورة/ملف (حجم أقصى 16 ميجابايت)
- الحد الأقصى للرسائل في الدقيقة: 60

---

*تحديث الوثائق: 2026-10-31*
