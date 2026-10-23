# التتبع البرمجي الكامل لعملية التثبيت لأول مرة (First-Install Execution Trace)

هذا المستند يشرح بالتفصيل المنهجي والمتسلسل (خطوة بخطوة كما ينفذها مفسر PHP/Laravel — **Execution Trace / Call Stack**) كل ما يحدث عند تشغيل واستخدام النظام لأول مرة، بدءاً من وصول طلب HTTP إلى ملف الراوت (`routes/web.php`)، مروراً بالـ Middleware، والـ Controllers، والدوال، وصفحات الواجهة الأمامية (Inertia/React)، وصولاً إلى الجداول والاستعلامات في قاعدة البيانات.

---

## أولاً: البنية التحتية — الجداول الـ 8 المرتبطة بعملية التثبيت

هذه هي الجداول التي يقرأ منها الكود أو يكتب فيها أثناء عملية التثبيت الأولي:

| # | اسم الجدول | الأعمدة الرئيسية | دوره في عملية التثبيت |
|---|---|---|---|
| 1 | `settings` | `id`, `outlet_id` *(nullable FK)*, `key`, `value`, `description`, `timestamps` | يحدد هل النظام مثبّت أم لا عبر المفتاح `app_setup_completed`، ويحفظ معرف المستودع الرئيسي في `setup_warehouse_id`، وبيانات المتجر الأساسية (`store_name`, `store_business_type`, `store_address`, `store_phone`, `store_email`, `store_logo`). |
| 2 | `outlets` | `id`, `code` *(unique)*, `name`, `is_active`, `is_sales_enabled`, `address`, `phone`, `email`, `logo`, `timestamps` | يخزن الفرع المركزي (`code = 'PUSAT'`) مع تعطيل البيع فيه (`is_sales_enabled = false`)، ويخزن الفروع البيعية المضافة في معالج التثبيت مع تفعيل البيع فيها (`is_sales_enabled = true`). |
| 3 | `warehouses` | `id`, `outlet_id` *(FK -> outlets.id)*, `code` *(unique)*, `name`, `type` *(`main`\|`branch`\|`warehouse`)*, `address`, `phone`, `is_active`, `sort_order`, `timestamps` | كل فرع في `outlets` يتبعه مستودع في `warehouses`. المستودع المركزي نوعه `main` (`sort_order = 0`)، ومستودعات الفروع نوعها `branch`. |
| 4 | `users` | `id`, `name`, `email` *(unique)*, `email_verified_at`, `password`, `avatar`, `locale`, `completed_tours`, `remember_token`, `timestamps` | يُنشأ فيه أول مستخدم في النظام (حساب المالك / المدير العام `super-admin`). |
| 5 | `user_outlets` | `user_id` *(FK)*, `outlet_id` *(FK)*, `is_default` *(boolean)*, `timestamps` | جدول وسيط (Pivot) يربط المدير العام بالمنفذ المركزي `PUSAT` كفرع افتراضي (`is_default = true`)، ويربطه بكل الفروع الجديدة (`is_default = false`). |
| 6 | `categories` | `id`, `name`, `image` *(nullable)*, `description` *(nullable)*, `timestamps` | تُحفظ فيه التصنيفات الأولية للمنتجات التي يختارها المستخدم حسب نوع نشاطه التجاري. |
| 7 | `roles` | `id`, `name`, `guard_name`, `timestamps` | جدول أدوار Spatie؛ يحتوي على دور `super-admin` الذي يُمنح للمستخدم الأول. |
| 8 | `model_has_roles` | `role_id`, `model_type`, `model_id` | جدول ربط Spatie؛ يربط معرف المستخدم الجديد (`model_id`) برقم دور `super-admin` (`role_id`). |

---

## ثانياً: مرحلة ما قبل التشغيل (عند تنفيذ `php artisan migrate --seed`)

قبل فتح المتصفح لأول مرة، عند تشغيل الـ Seeder ينفذ النظام الملف `database/seeders/DatabaseSeeder.php`:

1. **تشغيل `DatabaseSeeder::run()`:**
   * يستدعي `PermissionSeeder` و `RoleSeeder`: فيتم ملء جدول `permissions` بجميع الصلاحيات، وإنشاء دور `super-admin` في جدول `roles` وإعطاؤه كافة الصلاحيات.
   * يستدعي الدالة الخاصة `$this->seedDefaultWarehouse()` (`database/seeders/DatabaseSeeder.php` السطر 31):
     1. تنشئ المستودع المركزي في جدول `warehouses` بكود `PUSAT` واسم `Gudang Pusat` ونوع `main`.
     2. تنشئ المنفذ المركزي في جدول `outlets` بكود `PUSAT` مع `is_sales_enabled = false`.
     3. تربط المستودع بالمنفذ عبر تحديث `warehouses.outlet_id = $outlet->id`.
     4. تحفظ رقم هذا المستودع في جدول `settings` عبر:
        ```php
        Setting::set('setup_warehouse_id', $pusat->id);
        ```
   * **ملاحظة جوهرية:** المفتاح `app_setup_completed` **لا يتم إنشاؤه** هنا، وبالتالي يعتبر النظام نفسه في حالة "غير مثبّت بعد".

---

## ثالثاً: التتبع الأول — طلب الصفحة الرئيسية `GET /`

عند فتح المتصفح وطلب الرابط الرئيسي للمشروع `http://localhost:8000/`:

### الخطوة 1: المرور عبر Web Middleware Stack (`bootstrap/app.php` السطر 37)
يمر الطلب بالترتيب على الـ Middlewares العامة لمجموعة `web`:
1. **`SetLocale::handle()`** (`app/Http/Middleware/SetLocale.php`): يفحص لغة المستخدم أو الجلسة أو الكوكي أو ترويسة المتصفح، ويضبط لغة التطبيق (`id` أو `en`).
2. **`EnforceAbsoluteSessionLifetime::handle()`** (`app/Http/Middleware/EnforceAbsoluteSessionLifetime.php`): يفحص `$request->user()`، وبما أن الزائر غير مسجل دخول (`null`) يكمل التنفيذ فوراً عبر `return $next($request)`.
3. **`HandleInertiaRequests::handle()`** (`app/Http/Middleware/HandleInertiaRequests.php`): يجهز البيانات المشتركة لـ Inertia (مثل `storeProfile` من جدول `settings` ومعلومات اللغة `locale`).

### الخطوة 2: الدخول إلى أول Route في `routes/web.php` (السطر 60)
يصل المفسر إلى أول راوت في الملف:
```php
Route::get('/', function () {
    if (! Setting::getBool('app_setup_completed', false)) {
        return redirect()->route('setup.index');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => config('security.auth.public_registration'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
```

### الخطوة 3: القفز إلى المودل `Setting::getBool()` (`app/Models/Setting.php` السطر 36)
1. تُستدعى `Setting::getBool('app_setup_completed', false)`.
2. بداخلها تستدعي `static::get('app_setup_completed', '0')` (السطر 21 في `Setting.php`).
3. دالة `get()` تنفذ الاستعلام التالي على جدول `settings`:
   ```sql
   SELECT * FROM `settings` WHERE `key` = 'app_setup_completed' AND `outlet_id` IS NULL LIMIT 1;
   ```
4. عند الاستخدام لأول مرة، يرجع الاستعلام `null`، فتعيد دالة `get()` القيمة الافتراضية `'0'`.
5. تحول `filter_var('0', FILTER_VALIDATE_BOOL)` القيمة إلى `false` وتعيدها إلى `routes/web.php`.

### الخطوة 4: قرار التوجيه في `routes/web.php`
* الشرط `if (! false)` يتحقق (`true`).
* ينفذ `return redirect()->route('setup.index')` — فيرجع للمتصفح استجابة **HTTP 302 Redirect** إلى الرابط `/setup` (ويتجاهل صفحة `Welcome.jsx`).

---

## رابعاً: التتبع الثاني — فتح معالج التثبيت `GET /setup`

المتصفح يتبع التحويل تلقائياً ويطلب `GET /setup`:

### الخطوة 1: مطابقة الراوت في `routes/web.php` (الأسطر 74–77)
```php
// First-install setup wizard (locked out once app_setup_completed = true)
Route::middleware('setup.notinstalled')->group(function () {
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup', [SetupController::class, 'store'])->middleware('throttle:10,1')->name('setup.store');
});
```
قبل الدخول إلى `SetupController@index`، يمر الطلب أولاً عبر الـ Middleware المسمى `setup.notinstalled`.

### الخطوة 2: المرور بحارس التثبيت `EnsureNotInstalled` (`app/Http/Middleware/EnsureNotInstalled.php`)
* في `bootstrap/app.php` (السطر 53)، الاسم `'setup.notinstalled'` مربوط بالكلاس `EnsureNotInstalled::class`.
* ينفذ المفسر الدالة `EnsureNotInstalled::handle()`:
  ```php
  public function handle(Request $request, Closure $next): Response
  {
      if (Setting::getBool('app_setup_completed', false)) {
          return redirect()->route('login');
      }

      return $next($request);
  }
  ```
* **وظيفته:** يفحص جدول `settings`؛ إذا كان النظام قد ثُبّت سابقاً (`app_setup_completed == true`)، يمنع فتح صفحة التثبيت ويحول الزائر إلى `/login`. أما قبل التثبيت (`false`)، فيسمح للطلب بالمرور عبر `$next($request)` إلى الكنترولر.

### الخطوة 3: تنفيذ `SetupController::index()` (`app/Http/Controllers/SetupController.php` السطر 28)
يدخل المفسر إلى دالة `index()`:
```php
public function index(): Response
{
    $primaryWarehouse = Warehouse::find(Setting::get('setup_warehouse_id'));

    return Inertia::render('Setup/Wizard', [
        'businessTypes' => array_map(
            fn (string $key, array $categories) => ['key' => $key, 'categories' => $categories],
            array_keys(self::BUSINESS_TYPES),
            self::BUSINESS_TYPES,
        ),
        'primaryWarehouse' => $primaryWarehouse?->only(['id', 'code', 'name']),
    ]);
}
```
1. يستدعي `Setting::get('setup_warehouse_id')` ليقرأ رقم المستودع المركزي من جدول `settings`.
2. يمرر الرقم إلى `Warehouse::find(...)` لينفذ استعلام `SELECT * FROM warehouses WHERE id = ? LIMIT 1` ويجلب بيانات المستودع المركزي (`PUSAT`).
3. يقرأ الثابت `SetupController::BUSINESS_TYPES` (السطر 19) الذي يضم 6 قطاعات عمل (`food`, `retail`, `grocery`, `fashion`, `pharmacy`, `services`) والتصنيفات المقترحة لكل قطاع، ويهيئها في مصفوفة.
4. يستدعي `Inertia::render('Setup/Wizard', [...])` ويرسل البيانات (`businessTypes` و `primaryWarehouse`) إلى الواجهة الأمامية.

---

## خامساً: التتبع الثالث — داخل صفحة الواجهة الأمامية `resources/js/Pages/Setup/Wizard.jsx`

1. **تحميل الصفحة:**
   * السيرفر يرجع قالب `resources/views/app.blade.php`، فيقوم `resources/js/app.jsx` بتحميل المكون `resources/js/Pages/Setup/Wizard.jsx` وتمرير الـ Props (`businessTypes`, `primaryWarehouse`) إليه.
2. **تهيئة حالة النموذج (`useForm` - السطر 52):**
   * يُنشأ متغير الخطوة الحالية `const [step, setStep] = useState(0)` المربوط بمصفوفة الخطوات الخمس:
     `STEPS = ["store", "businessType", "categories", "branches", "account"]`.
   * يتم تهيئة كائن البيانات `data` عبر `useForm`، ويملأ بيانات المستودع المركزي تلقائياً من الـ Props التي أرسلها `SetupController::index`:
     ```js
     warehouse_id: primaryWarehouse?.id ?? "",
     warehouse_code: primaryWarehouse?.code ?? "", // "PUSAT"
     warehouse_name: primaryWarehouse?.name ?? "", // "Gudang Pusat"
     ```
3. **مرور المستخدم بالخطوات الخمس (Steps 0 إلى 4) والتحقق المحلي `validateStep()` (السطر 130):**
   عند كل ضغطة على زر "التالي" (`next`)، تشتغل دالة `validateStep()` في المتصفح:
   * **الخطوة 0 (`store` - بيانات المتجر):** تطلب `store_name` (إجباري)، و `store_address`, `store_phone`, `store_email` (اختيارية).
   * **الخطوة 1 (`businessType` - نوع النشاط):** تعرض الـ 6 أنواع القادمة من `businessTypes`، وتجبر المستخدم على اختيار نوع واحد في `data.business_type`.
   * **الخطوة 2 (`categories` - التصنيفات):** تعرض التصنيفات المقترحة للنشاط المختار، وعند اختيار أي منها يُحفظ بصيغة مفتاح داخلي `__setup:{business_type}:{category}`، كما تتيح إضافة تصنيفات مخصصة عبر `addCustomCategory()`. تشترط اختيار تصنيف واحد على الأقل.
   * **الخطوة 3 (`branches` - الفروع والمستودعات الفرعية):** تعرض نموذجاً لإدخال فرع واحد على الأقل (أو إضافة عدة فروع بزر `addBranch`). لكل فرع تطلب:
     * `outlet_code` (كود الفرع) و `outlet_name` (اسم الفرع).
     * `warehouse_code` (كود مستودع الفرع) و `warehouse_name` (اسم مستودع الفرع).
     * `address` و `phone`.
     * وتتحقق دالة `validateStep()` عبر `new Set()` من عدم تكرار أكواد الفروع أو المستودعات.
   * **الخطوة 4 (`account` - حساب المدير والمستودع المركزي):** تطلب بيانات أول مدير في النظام (`user_name`, `user_email`, `password`) بالإضافة إلى كود واسم المستودع المركزي (`warehouse_code`, `warehouse_name`).
4. **إرسال النموذج النهائي `submit()` (السطر 196):**
   عند الضغط على زر الإنهاء (`setup.finish`):
   * تشتغل `transform()` لتحويل مفاتيح التصنيفات `__setup:...` إلى نصوصها المترجمة الفعلية (مثلاً `"Makanan"` أو `"Food"`).
   * تُستدعى `post(route("setup.store"), { forceFormData: true })` التي ترسل طلب **HTTP POST إلى `/setup`**.

---

## سادساً: التتبع الرابع — معالجة الحفظ وبناء النظام في `SetupController::store()`

عند وصول طلب `POST /setup` إلى السيرفر، ينفذ المفسر المسار التالي بالترتيب:

### الخطوة 1: المرور بالـ Middleware (`routes/web.php` السطر 74 و 76)
1. يمر على `setup.notinstalled` (`EnsureNotInstalled::handle`) للتأكد مرة أخرى أن `app_setup_completed` ما زال `false`.
2. يمر على `throttle:10,1` لمنع إرسال أكثر من 10 طلبات في الدقيقة.

### الخطوة 2: التحقق الصارم من البيانات `$request->validate(...)` (`SetupController.php` الأسطر 43–97)
يفحص الكنترولر جميع المدخلات مقابل قاعدة البيانات قبل البدء بأي عملية حفظ:
* **`user_email`**: يفحص جدول `users` (`unique:users,email`) للتأكد من عدم وجود البريد مسبقاً.
* **`warehouse_id`**: إذا أُرسل معرف مستودع، ينفذ Closure تحقق خاص (السطر 57):
  ```php
  if ($value && ! Warehouse::whereKey($value)->where('code', 'PUSAT')->exists()) {
      $fail('Wizard setup hanya dapat memakai gudang PUSAT.');
  }
  ```
  للتأكد أن المستودع الذي سيتم تحديثه هو المستودع المركزي `PUSAT` حصراً.
* **`warehouse_code`**: ينفذ استعلاماً على جدول `warehouses` للتأكد أن الكود غير مستخدم من قِبل مستودع آخر.
* **`branches.*.outlet_code`**: يفحص جدول `outlets` (`unique:outlets,code` + `distinct`).
* **`branches.*.warehouse_code`**: يفحص جدول `warehouses` (`unique:warehouses,code` + `distinct`).

### الخطوة 3: فتح معاملة قاعدة البيانات `DB::transaction(...)` (الأسطر 99–182)
يفتح الكنترولر `DB::transaction` بحيث إما أن تنجح جميع العمليات الـ 7 التالية معاً (`COMMIT`) أو يتم التراجع عنها بالكامل (`ROLLBACK`) إذا حدث أي خطأ:

#### 1) إنشاء المدير العام وتعيين صلاحياته (الأسطر 100–106)
```php
$user = User::create([
    'name' => $validated['user_name'],
    'email' => $validated['user_email'],
    'password' => Hash::make($validated['password']),
]);
$user->assignRole('super-admin');
$user->markEmailAsVerified();
```
* **الجدول `users`:** تنفيذ `INSERT` للمستخدم الجديد مع تشفير كلمة المرور بـ Bcrypt.
* **الجدول `model_has_roles`:** دالة `assignRole('super-admin')` تجلب دور `super-admin` من جدول `roles` وتربطه بـ `$user->id`.
* **الجدول `users`:** دالة `markEmailAsVerified()` تحدث حقل `email_verified_at` بالوقت الحالي.

#### 2) تأكيد أو إنشاء المنفذ المركزي `PUSAT` (الأسطر 108–116)
```php
$pusat = Outlet::firstOrCreate(
    ['code' => 'PUSAT'],
    ['name' => 'Gudang Pusat', 'is_active' => true, 'is_sales_enabled' => false],
);
$pusat->update(['is_active' => true, 'is_sales_enabled' => false]);
```
* **الجدول `outlets`:** يبحث عن السجل ذي `code = 'PUSAT'` (أو ينشئه إن لم يكن موجوداً)، ويضمن أن `is_sales_enabled = false` لأنه مقر/مستودع مركزي وليس نقطة بيع كاشير.

#### 3) تحديث أو إنشاء المستودع المركزي وربطه بـ `settings` (الأسطر 118–137)
* إذا كان `warehouse_id` مرسلاً (موجوداً من الـ Seeder):
  * **الجدول `warehouses`:** يحدث بيانات المستودع (`code`, `name`, `outlet_id = $pusat->id`, `type = 'main'`, `is_active = true`).
  * **الجدول `settings`:** يستدعي `Setting::set('setup_warehouse_id', $validated['warehouse_id'])`.
* إذا كان `warehouse_id` فارغاً:
  * **الجدول `warehouses`:** ينشئ مستودعاً جديداً (`Warehouse::create`) بنوع `main` و `sort_order = 0` ويربطه بـ `$pusat->id`.
  * **الجدول `settings`:** يحفظ رقم المستودع الجديد عبر `Setting::set('setup_warehouse_id', $warehouse->id)`.

#### 4) ربط المدير العام بالمنفذ المركزي كفرع افتراضي (الأسطر 139–141)
```php
$user->outlets()->syncWithoutDetaching([
    $pusat->id => ['is_default' => true],
]);
```
* **الجدول `user_outlets`:** يستدعي علاقة `outlets()` المعرفة في `app/Models/User.php` (السطر 127) ليُدرج سجلاً يربط `$user->id` مع `$pusat->id` ويضع `is_default = 1`.

#### 5) إنشاء الفروع ومستودعاتها وربطها بالمدير (الأسطر 143–165)
يدور المفسر في حلقة `foreach ($validated['branches'] as $i => $branch)`، وفي كل دورة ينفذ 3 عمليات على 3 جداول:
1. **الجدول `outlets`:** ينشئ الفرع عبر `Outlet::create(...)` مع تحويل الكود لأحرف كبيرة `strtoupper($branch['outlet_code'])` وتفعيل البيع `is_sales_enabled = true`.
2. **الجدول `warehouses`:** ينشئ مستودع الفرع عبر `Warehouse::create(...)` ويربطه بالفرع (`outlet_id = $branchOutlet->id`)، ويضع نوعه `type = 'branch'` وترتيبه `sort_order = $i + 1`.
3. **الجدول `user_outlets`:** يربط المدير بهذا الفرع عبر `$user->outlets()->syncWithoutDetaching([$branchOutlet->id => ['is_default' => false]])`.

#### 6) إنشاء تصنيفات المنتجات (الأسطر 167–169)
```php
foreach (array_unique($validated['categories']) as $name) {
    Category::create(['name' => $name]);
}
```
* **الجدول `categories`:** يزيل أي تكرار بـ `array_unique` وينفذ `INSERT INTO categories (name, ...)` لكل تصنيف.

#### 7) حفظ إعدادات المتجر وإقفال عملية التثبيت (الأسطر 171–181)
* **الجدول `settings`:** يستدعي `Setting::set($key, $value)` (التي تنفذ `updateOrCreate` مع `outlet_id = null`) لحفظ المفاتيح الـ 5:
  * `store_name`
  * `store_address`
  * `store_phone`
  * `store_email`
  * `store_business_type`
* ثم ينفذ السطر الفاصل في السطر 181:
  ```php
  Setting::set('app_setup_completed', true, 'Penanda wizard setup awal sudah diselesaikan');
  ```
  وهنا يُكتب في جدول `settings` السجل `key = 'app_setup_completed'` بقيمة `1`.
* عند هذا السطر تنتهي `DB::transaction` ويتم تنفيذ **`COMMIT`** في قاعدة البيانات.

### الخطوة 4: رفع شعار المتجر (الأسطر 184–187)
إذا أرفق المستخدم ملف صورة في `store_logo`:
* يُحفظ الملف في المسار `storage/app/public/store`.
* يُحفظ مسار الصورة في جدول `settings` تحت مفتاح `store_logo`.

### الخطوة 5: التحويل إلى صفحة تسجيل الدخول (السطر 189)
```php
return redirect()->route('login')->with('success', __('setup.completed'));
```
* يعيد الكنترولر استجابة **HTTP 302 Redirect** إلى الراوت `login` (`/login`) مع رسالة نجاح في الجلسة (`session('success')`).

---

## سابعاً: ماذا يحدث بعد انتهاء التثبيت مباشرة؟

1. **فتح `/login` (`routes/auth.php` السطر 19 -> `AuthenticatedSessionController::create`):**
   * يعرض صفحة `resources/js/Pages/Auth/Login.jsx`.
2. **عند تسجيل الدخول بحساب المدير الذي أنشأته (`POST /login` -> `AuthenticatedSessionController::store`):**
   * يتحقق من البريد وكلمة المرور عبر `$request->authenticate()`.
   * يسجل عملية الدخول في جدول `audit_logs` عبر `AuditLogService::log('auth.login_succeeded', ...)`.
   * يفحص صلاحيات المستخدم لتحديد صفحة الهبوط بعد الدخول؛ وبما أن المستخدم يحمل دور `super-admin`، فإن الدالة `User::hasPermissionTo()` (`app/Models/User.php` السطر 105) ترجع `true` لكل الصلاحيات، فيتم توجيهه مباشرة إلى النظام لبدء العمل.
3. **قفل مسارات التثبيت نهائياً:**
   * لو حاول أي شخص طلب `GET /setup` أو `POST /setup` مجدداً، سيعترضه `EnsureNotInstalled`، يقرأ `app_setup_completed = 1` من جدول `settings`، ويحوله فوراً إلى `/login`.
   * لو طلب أي زائر `GET /`، سيجد الشرط في `routes/web.php` أن `app_setup_completed = 1`، فيعرض له صفحة الواجهة العامة `Welcome.jsx` بدلاً من تحويله إلى `/setup`.

---

## ملخص مخطط سير العمليات (Call Stack Diagram)

```text
[1] GET /
 └── routes/web.php (Closure)
      └── Setting::getBool('app_setup_completed', false)
           └── Setting::get('app_setup_completed', '0') ──> [DB: SELECT FROM settings] ──> null ('0')
      └── return redirect()->route('setup.index') (302 -> /setup)

[2] GET /setup
 └── Middleware: EnsureNotInstalled::handle()
      └── Setting::getBool('app_setup_completed', false) ──> false (يمر الطلب)
 └── SetupController::index()
      ├── Setting::get('setup_warehouse_id') ──> [DB: SELECT FROM settings]
      ├── Warehouse::find(...)               ──> [DB: SELECT FROM warehouses]
      └── Inertia::render('Setup/Wizard', [businessTypes, primaryWarehouse])
           └── resources/js/Pages/Setup/Wizard.jsx (5 Steps: store -> businessType -> categories -> branches -> account)

[3] POST /setup (من دالة submit في Wizard.jsx)
 └── Middleware: EnsureNotInstalled::handle() + throttle:10,1
 └── SetupController::store(Request $request)
      ├── 1. $request->validate([...])       ──> [DB: يفحص users, warehouses, outlets]
      ├── 2. DB::transaction(function () {
      │       ├── User::create(...)          ──> [DB: INSERT INTO users]
      │       ├── $user->assignRole(...)     ──> [DB: INSERT INTO model_has_roles]
      │       ├── Outlet::firstOrCreate(...) ──> [DB: INSERT/UPDATE outlets (PUSAT)]
      │       ├── Warehouse::update/create   ──> [DB: INSERT/UPDATE warehouses (main)]
      │       ├── Setting::set('setup_warehouse_id') ──> [DB: UPSERT settings]
      │       ├── $user->outlets()->sync...  ──> [DB: INSERT INTO user_outlets (default=1)]
      │       ├── foreach (branches):
      │       │    ├── Outlet::create(...)   ──> [DB: INSERT INTO outlets (branch)]
      │       │    ├── Warehouse::create(...)──> [DB: INSERT INTO warehouses (branch)]
      │       │    └── $user->outlets()...   ──> [DB: INSERT INTO user_outlets (default=0)]
      │       ├── foreach (categories):
      │       │    └── Category::create(...) ──> [DB: INSERT INTO categories]
      │       ├── Setting::set(store_*)      ──> [DB: UPSERT settings (x5)]
      │       └── Setting::set('app_setup_completed', true) ──> [DB: UPSERT settings (قفل التثبيت)]
      │   })
      ├── 3. حفظ store_logo (إن وجد)         ──> [Storage + DB: UPSERT settings]
      └── 4. return redirect()->route('login') (302 -> /login)
```
