# المرجع الشامل والتتبع التنفيذي الكامل لعملية تثبيت النظام لأول مرة (First-Install Complete Code & Execution Trace)

هذا المستند يشرح **كل ملف، وكل كلاس، وكل دالة، وكل مودل، وكل كونترولر، وكل جدول، وكل صفحة** تمر بها عملية تثبيت النظام وتشغيله لأول مرة، مع إيراد **الأكواد البرمجية الفعلية كاملةً** من المشروع، متبوعة بتتبع تنفيذي متسلسل (**Compiler Execution Trace**) يوضح كيف ينتقل التنفيذ من دالة إلى أخرى ومن ملف إلى آخر حتى النهاية.

---

## فهرس الملفات المشتركة في العملية داخل المشروع

1. **ملف توجيه المسارات (Routes):**
   * `routes/web.php` (الأسطر 60–77)
   * `routes/auth.php` (الأسطر 19–24)
2. **ملف تسجيل الـ Middleware العام والأسماء المستعارة:**
   * `bootstrap/app.php` (الأسطر 36–56)
3. **ملفات الـ Middleware:**
   * `app/Http/Middleware/EnsureNotInstalled.php` (الحارس الخاص بمسارات `/setup`)
   * `app/Http/Middleware/SetLocale.php`
   * `app/Http/Middleware/EnforceAbsoluteSessionLifetime.php`
   * `app/Http/Middleware/HandleInertiaRequests.php`
4. **ملف الكنترولر الرئيسي للتثبيت (Controller):**
   * `app/Http/Controllers/SetupController.php`
5. **ملفات المودلات (Models) ودوالها وعلاقاتها:**
   * `app/Models/Setting.php`
   * `app/Models/User.php`
   * `app/Models/Outlet.php`
   * `app/Models/Warehouse.php`
   * `app/Models/Category.php`
6. **ملفات التهيئة الأولية لقاعدة البيانات (Seeders):**
   * `database/seeders/DatabaseSeeder.php`
   * `database/seeders/PermissionSeeder.php`
   * `database/seeders/RoleSeeder.php`
   * `database/seeders/PaymentSettingSeeder.php`
   * `database/seeders/DineInSettingsSeeder.php`
7. **ملفات الواجهة الأمامية (Frontend / Inertia React Pages):**
   * `resources/views/app.blade.php`
   * `resources/js/app.jsx`
   * `resources/js/Pages/Setup/Wizard.jsx`
   * `resources/js/Pages/Welcome.jsx`

---

## القسم الأول: الجداول (Database Tables) وملفات الـ Migrations الخاصة بها

عملية التثبيت تتعامل مباشرة مع **8 جداول** في قاعدة البيانات:

### 1. جدول `settings`
* **ملفات الـ Migration:**
  * `database/migrations/2025_12_25_200000_create_settings_table.php`
  * `database/migrations/2025_12_31_010000_add_store_settings_defaults.php`
  * `database/migrations/2026_09_14_000004_add_outlet_id_to_settings_table.php`
* **كود إنشاء الجدول وتعديله:**
```php
// 2025_12_25_200000_create_settings_table.php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('description')->nullable();
    $table->timestamps();
});

// 2026_09_14_000004_add_outlet_id_to_settings_table.php
Schema::table('settings', function (Blueprint $table) {
    $table->foreignId('outlet_id')->nullable()->after('id')->constrained()->nullOnDelete();
});
Schema::table('settings', function (Blueprint $table) {
    $table->dropUnique('settings_key_unique');
    $table->unique(['key', 'outlet_id']);
});
```
* **الأعمدة النهائية:** `id`, `outlet_id` (nullable FK -> `outlets.id`), `key`, `value`, `description`, `created_at`, `updated_at`.
* **المفاتيح التي تُقرأ أو تُكتب أثناء التثبيت:**
  * `app_setup_completed`: علامة إتمام التثبيت (`1` / `true` بعد انتهاء المعالج).
  * `setup_warehouse_id`: رقم `id` المستودع المركزي `PUSAT`.
  * `store_name`, `store_address`, `store_phone`, `store_email`, `store_business_type`, `store_logo`.

---

### 2. جدول `outlets` (الفروع / المنافذ)
* **ملف الـ Migration:** `database/migrations/2026_09_14_000001_create_outlets_table.php`
```php
Schema::create('outlets', function (Blueprint $table) {
    $table->id();
    $table->string('code', 20)->unique();
    $table->string('name', 100);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_sales_enabled')->default(true);
    $table->text('address')->nullable();
    $table->string('phone', 20)->nullable();
    $table->string('email')->nullable();
    $table->string('logo')->nullable();
    $table->timestamps();
});
```
* **وظيفته في التثبيت:**
  * يُخزن المنفذ المركزي `code = 'PUSAT'` مع `is_sales_enabled = false` (لأنه مستودع مركزي للإمداد وليس نقطة بيع كاشير).
  * يُخزن جميع الفروع التي يضيفها المستخدم في الخطوة الرابعة من المعالج مع `is_sales_enabled = true`.

---

### 3. جدول `warehouses` (المستودعات / المخازن)
* **ملفات الـ Migration:**
  * `database/migrations/2026_06_22_000001_create_warehouses_table.php`
  * `database/migrations/2026_09_14_000002_add_outlet_id_to_warehouses_table.php`
```php
Schema::create('warehouses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
    $table->string('code', 20)->unique();
    $table->string('name', 100);
    $table->enum('type', ['main', 'branch', 'warehouse'])->default('branch');
    $table->text('address')->nullable();
    $table->string('phone', 20)->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```
* **وظيفته في التثبيت:**
  * يخزن المستودع المركزي بنوع `type = 'main'` و `sort_order = 0` مربوطاً بـ `outlet_id` الخاص بـ `PUSAT`.
  * يخزن مستودعاً فرعياً (`type = 'branch'`) لكل فرع يتم إنشاؤه في المعالج مع `sort_order = $i + 1`.

---

### 4. جدول `users` (المستخدمون)
* **ملف الـ Migration:** `database/migrations/0001_01_01_000000_create_users_table.php` (+ إضافات `avatar`, `locale`, `completed_tours`)
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
});
```
* **وظيفته في التثبيت:** يُنشأ فيه حساب المالك (`super-admin`) في الخطوة الخامسة من المعالج.

---

### 5. جدول `user_outlets` (جدول الربط Pivot بين المستخدمين والفروع)
* **ملف الـ Migration:** `database/migrations/2026_09_14_000003_create_user_outlets_table.php`
```php
Schema::create('user_outlets', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    $table->unique(['user_id', 'outlet_id']);
});
```
* **وظيفته في التثبيت:** يربط المدير الجديد بالمنفذ المركزي `PUSAT` (`is_default = true`) وبجميع الفروع المنشأة (`is_default = false`).

---

### 6. جدول `categories` (تصنيفات المنتجات)
* **ملفات الـ Migration:**
  * `database/migrations/2024_06_13_130507_create_categories_table.php`
  * `database/migrations/2026_09_09_000001_make_category_image_and_description_nullable.php`
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('image')->nullable();
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();
});
```
* **وظيفته في التثبيت:** تُدرج فيه التصنيفات التي يختارها المستخدم في الخطوة الثالثة من المعالج.

---

### 7 & 8. جدولا `roles` و `model_has_roles` (صلاحيات Spatie)
* **ملف الـ Migration:** `database/migrations/2024_06_13_082620_create_permission_tables.php`
* **وظيفتهما في التثبيت:** عند استدعاء `$user->assignRole('super-admin')`، يبحث Spatie في جدول `roles` عن الدور `name = 'super-admin'`، ثم يدرج سجلاً في جدول `model_has_roles` يحتوي على:
  * `role_id` = معرف دور `super-admin`
  * `model_type` = `'App\Models\User'`
  * `model_id` = `$user->id`

---

## القسم الثاني: المودلات (Models) — الكود الكامل وشرح كل دالة

### 1. المودل `App\Models\Setting` (`app/Models/Setting.php`)

هذا المودل هو المسؤول عن قراءة وكتابة الإعدادات في جدول `settings`.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'outlet_id',
        'value',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $query = static::where('key', $key);
        if (Schema::hasColumn('settings', 'outlet_id')) {
            $query->whereNull('outlet_id');
        }

        $setting = $query->first();

        return $setting ? $setting->value : $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }

    public static function getForOutlet(string $key, ?Outlet $outlet, $default = null)
    {
        if ($outlet) {
            if (! Schema::hasColumn('settings', 'outlet_id')) {
                return static::get($key, $default);
            }

            $value = static::where('key', $key)->where('outlet_id', $outlet->id)->value('value');
            if ($value !== null) {
                return $value;
            }
        }

        return static::get($key, $default);
    }

    public static function getBoolForOutlet(string $key, ?Outlet $outlet, bool $default = false): bool
    {
        return filter_var(static::getForOutlet($key, $outlet, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }

    public static function setForOutlet(string $key, $value, ?Outlet $outlet, ?string $description = null)
    {
        if (! $outlet) {
            return static::set($key, $value, $description);
        }

        if (! Schema::hasColumn('settings', 'outlet_id')) {
            return static::set($key, $value, $description);
        }

        return static::updateOrCreate(
            ['key' => $key, 'outlet_id' => $outlet->id],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        $attributes = ['key' => $key];
        if (Schema::hasColumn('settings', 'outlet_id')) {
            $attributes['outlet_id'] = null;
        }

        return static::updateOrCreate(
            $attributes,
            ['value' => $value, 'description' => $description]
        );
    }

    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $payload) {
            static::set(
                $key,
                $payload['value'] ?? null,
                $payload['description'] ?? null
            );
        }
    }
}
```

#### تفصيل دوال `Setting`:
* **`Setting::get(string $key, $default = null)`**:
  1. تبني استعلام `WHERE key = $key`.
  2. تفحص هل عمود `outlet_id` موجود في جدول `settings`؛ إن وجد تضيف شرط `AND outlet_id IS NULL` (لجلب الإعداد العام للنظام وليس إعداد فرع معين).
  3. تنفذ `$query->first()`. إذا وجدت السجل ترجع `$setting->value`، وإذا لم تجده ترجع `$default`.
* **`Setting::getBool(string $key, bool $default = false): bool`**:
  1. تستدعي `static::get($key, $default ? '1' : '0')`.
  2. تمرر النتيجة على دالة PHP القياسية `filter_var(..., FILTER_VALIDATE_BOOL)` التي تحول `'1'`, `'true'`, `true` إلى `true`، وتحول `'0'`, `'false'`, `false`, `''` إلى `false`.
  3. **أين تُستخدم في التثبيت؟** في `routes/web.php` (السطر 61) وفي `EnsureNotInstalled.php` (السطر 14) لفحص `Setting::getBool('app_setup_completed', false)`.
* **`Setting::set(string $key, $value, ?string $description = null)`**:
  1. تجهز مصفوفة البحث `['key' => $key, 'outlet_id' => null]`.
  2. تستدعي دالة Eloquent `static::updateOrCreate($attributes, ['value' => $value, 'description' => $description])`:
     * إذا كان المفتاح موجوداً في جدول `settings` (مع `outlet_id IS NULL`)، تنفذ `UPDATE`.
     * إذا لم يكن موجوداً، تنفذ `INSERT`.
  3. **أين تُستخدم في التثبيت؟** في `DatabaseSeeder.php` لحفظ `setup_warehouse_id`، وفي `SetupController::store()` لحفظ بيانات المتجر و `app_setup_completed = true`.

---

### 2. المودل `App\Models\User` (`app/Models/User.php`)

```php
<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable {
        hasPermissionTo as protected spatieHasPermissionTo;
        checkPermissionTo as protected spatieCheckPermissionTo;
    }
    use MustVerifyEmailTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'locale',
        'completed_tours',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'completed_tours' => 'array',
        ];
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super-admin');
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->spatieHasPermissionTo($permission, $guardName);
    }

    public function checkPermissionTo($permission, $guardName = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->spatieCheckPermissionTo($permission, $guardName);
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'user_outlets')->withPivot('is_default')->withTimestamps();
    }
}
```

#### تفصيل الدوال والـ Traits في `User` التي تُستدعى في `SetupController::store()`:
1. **`User::create([...])`**: تنشئ السجل في جدول `users` للحقول الموجودة في `$fillable` (`name`, `email`, `password`).
2. **`$user->assignRole('super-admin')`**: قادمة من `use HasRoles` (حزمة Spatie Permission)؛ تربط المستخدم بدور `super-admin` في جدول `model_has_roles`.
3. **`$user->markEmailAsVerified()`**: قادمة من `use MustVerifyEmailTrait`؛ تضع الوقت الحالي `now()` في عمود `email_verified_at` وتحفظه في جدول `users`.
4. **`$user->outlets()`**: علاقة `BelongsToMany` تربط `User` بمودل `Outlet` عبر الجدول الوسيط `user_outlets` مع الحقل الإضافي `is_default` في الـ Pivot. عندما يستدعي الكنترولر `$user->outlets()->syncWithoutDetaching([...])`، فإنه يضيف الفروع للمستخدم في جدول `user_outlets` دون حذف ارتباطاته السابقة.
5. **`isSuperAdmin()` و `hasPermissionTo()`**: تتجاوز فحص الصلاحيات العادي بحيث لو كان المستخدم يحمل دور `super-admin` ترجع `true` دائماً لأي صلاحية في النظام.

---

### 3. المودل `App\Models\Outlet` (`app/Models/Outlet.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'is_sales_enabled',
        'address',
        'phone',
        'email',
        'logo',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_sales_enabled' => 'boolean',
        ];
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets')->withPivot('is_default')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

---

### 4. المودل `App\Models\Warehouse` (`app/Models/Warehouse.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'code',
        'name',
        'type',
        'address',
        'phone',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('stock')
            ->using(ProductWarehouse::class)
            ->withTimestamps();
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

---

### 5. المودل `App\Models\Category` (`app/Models/Category.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
    ];

    protected $fillable = [
        'image', 'name', 'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class);
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('/storage/category/'.$value) : null,
        );
    }
}
```

---

## القسم الثالث: مرحلة البذر الأولي (Database Seeders) قبل فتح المتصفح

عندما ينزل المبرمج المشروع ويشغل الأمر `php artisan migrate --seed`، يستدعي Laravel الكلاس `Database\Seeders\DatabaseSeeder` (`database/seeders/DatabaseSeeder.php`):

```php
<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Setting;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            PaymentSettingSeeder::class,
            DineInSettingsSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedDefaultWarehouse();
    }

    private function seedDefaultWarehouse(): void
    {
        $pusat = Warehouse::firstOrCreate(
            ['code' => 'PUSAT'],
            [
                'name' => 'Gudang Pusat',
                'type' => 'main',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        $outlet = Outlet::firstOrCreate(
            ['code' => 'PUSAT'],
            [
                'name' => $pusat->name,
                'is_active' => true,
                'is_sales_enabled' => false,
                'address' => $pusat->address,
                'phone' => $pusat->phone,
            ],
        );

        if (! $pusat->outlet_id) {
            $pusat->update(['outlet_id' => $outlet->id]);
        }

        if (! Setting::get('setup_warehouse_id')) {
            Setting::set('setup_warehouse_id', $pusat->id);
        }

        $products = DB::table('products')->select('id', 'stock')->get()
            ->map(fn ($product) => [
                'product_id' => $product->id,
                'warehouse_id' => $pusat->id,
                'stock' => $product->stock,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($products) {
            DB::table('product_warehouse')->insertOrIgnore($products);
        }
    }
}
```

### تسلسل ما يحدث في `DatabaseSeeder::run()`:
1. يمسح كاش الصلاحيات عبر `forgetCachedPermissions()`.
2. يشغل `PermissionSeeder::run()` (`database/seeders/PermissionSeeder.php`): فينشئ كل الصلاحيات في جدول `permissions` عبر `Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])`.
3. يشغل `RoleSeeder::run()` (`database/seeders/RoleSeeder.php`): فينشئ الأدوار في جدول `roles`، ومن أهمها السطر 74:
   ```php
   $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
   $superAdminRole->syncPermissions(Permission::all());
   ```
4. يشغل `PaymentSettingSeeder::run()`: ينشئ السجل الافتراضي لبوابة الدفع النقدية (`default_gateway = 'cash'`) في جدول `payment_settings`.
5. يشغل `DineInSettingsSeeder::run()`: يحفظ إعدادات الطاولات الافتراضية في جدول `settings` عبر `Setting::setMany(...)`.
6. ينفذ `$this->seedDefaultWarehouse()`:
   * ينشئ المستودع المركزي `PUSAT` في جدول `warehouses`.
   * ينشئ المنفذ المركزي `PUSAT` في جدول `outlets` مع `is_sales_enabled = false`.
   * يربط `warehouses.outlet_id` بـ `outlets.id`.
   * يستدعي `Setting::set('setup_warehouse_id', $pusat->id)` ليحفظ معرف المستودع المركزي في جدول `settings`.
   * **انتبه:** لم يتم وضع `app_setup_completed` في جدول `settings`، ولذلك النظام جاهز لبدء معالج التثبيت فور فتح المتصفح.

---

## القسم الرابع: الكود الكامل للـ Routes والـ Middleware والـ Controller

### 1. كود الروتات الأولى في `routes/web.php` (الأسطر 60–77)

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

// First-install setup wizard (locked out once app_setup_completed = true)
Route::middleware('setup.notinstalled')->group(function () {
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup', [SetupController::class, 'store'])->middleware('throttle:10,1')->name('setup.store');
});
```

### 2. كود تسجيل الـ Middleware في `bootstrap/app.php` (الأسطر 36–56)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        SetLocale::class,
        SecureHeaders::class,
        EnforceAbsoluteSessionLifetime::class,
        HandleInertiaRequests::class,
        AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->alias([
        'role' => RoleMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'role_or_permission' => RoleOrPermissionMiddleware::class,
        'active_shift' => EnsureActiveCashierShift::class,
        'registration.enabled' => EnsurePublicRegistrationEnabled::class,
        'bot.guard' => EnsureBotGuard::class,
        'setup.notinstalled' => EnsureNotInstalled::class,
        'step_up' => EnsureRecentPasswordConfirmation::class,
        'abilities' => CheckAbilities::class,
    ]);
})
```

### 3. كود الـ Middleware الحارس `EnsureNotInstalled` (`app/Http/Middleware/EnsureNotInstalled.php`)

```php
<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::getBool('app_setup_completed', false)) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
```

### 4. الكود الكامل للكنترولر `SetupController` (`app/Http/Controllers/SetupController.php`)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    public const BUSINESS_TYPES = [
        'food' => ['food', 'beverages', 'snacks', 'coffeeTea'],
        'retail' => ['general', 'electronics', 'homeSupplies', 'beauty'],
        'grocery' => ['staples', 'produce', 'meatSeafood', 'snacks'],
        'fashion' => ['clothing', 'shoes', 'bags', 'accessories'],
        'pharmacy' => ['otcMedicine', 'prescriptionMedicine', 'vitamins', 'medicalSupplies'],
        'services' => ['services', 'products', 'packages'],
    ];

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'store_logo' => 'nullable|image|max:2048',
            'business_type' => 'required|string|in:'.implode(',', array_keys(self::BUSINESS_TYPES)),
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'warehouse_id' => [
                'nullable',
                'integer',
                'exists:warehouses,id',
                function ($attribute, $value, $fail) {
                    if ($value && ! Warehouse::whereKey($value)->where('code', 'PUSAT')->exists()) {
                        $fail('Wizard setup hanya dapat memakai gudang PUSAT.');
                    }
                },
            ],
            'warehouse_code' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    $warehouseId = $request->input('warehouse_id');
                    $exists = DB::table('warehouses')
                        ->where('code', $value)
                        ->when($warehouseId, fn ($q) => $q->where('id', '!=', $warehouseId))
                        ->exists();
                    if ($exists) {
                        $fail('Kode gudang sudah digunakan.');
                    }
                },
            ],
            'warehouse_name' => 'required|string|max:255',
            'branches' => 'required|array|min:1|max:10',
            'branches.*.outlet_code' => [
                'required',
                'string',
                'max:20',
                'distinct',
                'unique:outlets,code',
            ],
            'branches.*.outlet_name' => 'required|string|max:100',
            'branches.*.warehouse_code' => [
                'required',
                'string',
                'max:20',
                'distinct',
                'unique:warehouses,code',
            ],
            'branches.*.warehouse_name' => 'required|string|max:100',
            'branches.*.address' => 'nullable|string|max:500',
            'branches.*.phone' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['user_name'],
                'email' => $validated['user_email'],
                'password' => Hash::make($validated['password']),
            ]);
            $user->assignRole('super-admin');
            $user->markEmailAsVerified();

            $pusat = Outlet::firstOrCreate(
                ['code' => 'PUSAT'],
                [
                    'name' => 'Gudang Pusat',
                    'is_active' => true,
                    'is_sales_enabled' => false,
                ],
            );
            $pusat->update(['is_active' => true, 'is_sales_enabled' => false]);

            if (! empty($validated['warehouse_id'])) {
                Warehouse::where('id', $validated['warehouse_id'])->update([
                    'code' => $validated['warehouse_code'],
                    'name' => $validated['warehouse_name'],
                    'outlet_id' => $pusat->id,
                    'type' => 'main',
                    'is_active' => true,
                ]);
                Setting::set('setup_warehouse_id', $validated['warehouse_id']);
            } else {
                $warehouse = Warehouse::create([
                    'outlet_id' => $pusat->id,
                    'code' => $validated['warehouse_code'],
                    'name' => $validated['warehouse_name'],
                    'type' => 'main',
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
                Setting::set('setup_warehouse_id', $warehouse->id);
            }

            $user->outlets()->syncWithoutDetaching([
                $pusat->id => ['is_default' => true],
            ]);

            foreach ($validated['branches'] as $i => $branch) {
                $branchOutlet = Outlet::create([
                    'code' => strtoupper($branch['outlet_code']),
                    'name' => $branch['outlet_name'],
                    'is_active' => true,
                    'is_sales_enabled' => true,
                    'address' => $branch['address'] ?? null,
                    'phone' => $branch['phone'] ?? null,
                ]);

                Warehouse::create([
                    'outlet_id' => $branchOutlet->id,
                    'code' => strtoupper($branch['warehouse_code']),
                    'name' => $branch['warehouse_name'],
                    'type' => 'branch',
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]);

                $user->outlets()->syncWithoutDetaching([
                    $branchOutlet->id => ['is_default' => false],
                ]);
            }

            foreach (array_unique($validated['categories']) as $name) {
                Category::create(['name' => $name]);
            }

            foreach ([
                'store_name' => $validated['store_name'],
                'store_address' => $validated['store_address'] ?? '',
                'store_phone' => $validated['store_phone'] ?? '',
                'store_email' => $validated['store_email'] ?? '',
                'store_business_type' => $validated['business_type'],
            ] as $key => $value) {
                Setting::set($key, $value);
            }
            Setting::set('app_setup_completed', true, 'Penanda wizard setup awal sudah diselesaikan');
        });

        if ($request->file('store_logo')) {
            $path = $request->file('store_logo')->store('store', 'public');
            Setting::set('store_logo', $path, 'Logo toko');
        }

        return redirect()->route('login')->with('success', __('setup.completed'));
    }
}
```

---

## القسم الخامس: التتبع التنفيذي المتسلسل (Compiler Execution Trace) من البداية للنهاية

الآن نتتبع التنفيذ كما ينفذه مفسر PHP و React خطوة بخطوة:

---

### المرحلة (A): المستخدم يطلب `GET /` في المتصفح

1. **نقطة البداية (`public/index.php` -> `bootstrap/app.php`):**
   * يدخل طلب `GET /` إلى Laravel Router، وبما أنه معرف في `routes/web.php`، يتم تفعيل مجموعة الـ Middleware الخاصة بـ `web` المعرفة في `bootstrap/app.php` (السطر 37).

2. **الخطوة A.1 — الدخول إلى `SetLocale::handle($request, $next)` (`app/Http/Middleware/SetLocale.php`):**
   * يفحص `$request->user()` -> قيمته `null`.
   * يفحص `$request->session()->has('locale')` ثم `$request->cookie('locale')` ثم هيدر `Accept-Language`.
   * يضبط اللغة عبر `app()->setLocale($locale)` (الافتراضي `'id'`) ثم يستدعي `return $next($request)` لينتقل للـ Middleware التالي.

3. **الخطوة A.2 — الدخول إلى `EnforceAbsoluteSessionLifetime::handle($request, $next)` (`app/Http/Middleware/EnforceAbsoluteSessionLifetime.php`):**
   * ينفذ السطر 15:
     ```php
     if (! $request->user()) {
         return $next($request);
     }
     ```
   * بما أنه لا يوجد مستخدم مسجل، يمرر الطلب مباشرة عبر `$next($request)`.

4. **الخطوة A.3 — الدخول إلى `HandleInertiaRequests::handle($request, $next)` (`app/Http/Middleware/HandleInertiaRequests.php`):**
   * ينفذ الدالة `share(Request $request)` لتجهيز البيانات العامة لكل صفحات Inertia:
     * الشرط `if ($request->user())` (السطر 49) يكون `false`، فيتخطى جلب إشعارات المخزون والديون والورديات.
     * يفحص `if (Schema::hasTable('settings'))` (السطر 218)، ويقرأ بيانات المتجر الافتراضية عبر `Setting::getForOutlet(...)`.
     * يمرر الطلب عبر `$next($request)` ليصل إلى الراوت المطابق في `routes/web.php`.

5. **الخطوة A.4 — تنفيذ Closure الراوت `GET /` في `routes/web.php` (السطر 60):**
   * ينفذ المفسر السطر 61:
     ```php
     if (! Setting::getBool('app_setup_completed', false)) {
         return redirect()->route('setup.index');
     }
     ```
   * **انتقال المفسر إلى `Setting::getBool('app_setup_completed', false)` (`app/Models/Setting.php` السطر 36):**
     * تستدعي بدورها `static::get('app_setup_completed', '0')` (السطر 21).
     * داخل `Setting::get()`:
       1. تنشئ الاستعلام `Setting::where('key', 'app_setup_completed')->whereNull('outlet_id')->first()`.
       2. يُنفذ استعلام SQL في قاعدة البيانات على جدول `settings`.
       3. يرجع `null` (لأن المفتاح غير موجود بعد).
       4. ترجع دالة `get()` القيمة الافتراضية `'0'` إلى `getBool()`.
     * داخل `Setting::getBool()`:
       1. تنفذ `filter_var('0', FILTER_VALIDATE_BOOL)` فترجع `false` إلى `routes/web.php`.
   * **العودة إلى `routes/web.php`:**
     * الشرط `if (! false)` يصبح `if (true)`.
     * ينفذ `return redirect()->route('setup.index')` (أي التحويل إلى المسار `/setup`).
     * يخرج الرد عبر `SecureHeaders::handle()` التي تضيف ترويسات الأمان (`X-Frame-Options: DENY` وغيرها) ويرجع للمتصفح كود `302 Found` مع `Location: http://localhost:8000/setup`.

---

### المرحلة (B): المتصفح يطلب `GET /setup`

1. **الخطوة B.1 — المرور بـ Web Middlewares ثم الوصول إلى `EnsureNotInstalled::handle()` (`app/Http/Middleware/EnsureNotInstalled.php`):**
   * الراوت `GET /setup` في `routes/web.php` (السطر 75) محمي بالـ Middleware `setup.notinstalled` المربوط بالكلاس `EnsureNotInstalled`.
   * ينفذ المفسر `EnsureNotInstalled::handle()` (السطر 12):
     ```php
     if (Setting::getBool('app_setup_completed', false)) {
         return redirect()->route('login');
     }
     return $next($request);
     ```
   * يستدعي `Setting::getBool('app_setup_completed', false)` -> ترجع `false`.
   * يتجاوز الشرط وينفذ `return $next($request)` ليصل إلى `SetupController::index()`.

2. **الخطوة B.2 — تنفيذ `SetupController::index()` (`app/Http/Controllers/SetupController.php` السطر 28):**
   * **السطر 30:**
     ```php
     $primaryWarehouse = Warehouse::find(Setting::get('setup_warehouse_id'));
     ```
     * أولاً: يستدعي `Setting::get('setup_warehouse_id')` -> يقرأ من جدول `settings` قيمة `setup_warehouse_id` (التي وضعها `DatabaseSeeder` سابقاً، مثلاً `1`).
     * ثانياً: يستدعي `Warehouse::find(1)` -> ينفذ `SELECT * FROM warehouses WHERE id = 1 LIMIT 1` على جدول `warehouses`، ويرجع كائن `Warehouse` الخاص بـ `PUSAT`.
   * **الأسطر 32–39:**
     ```php
     return Inertia::render('Setup/Wizard', [
         'businessTypes' => array_map(
             fn (string $key, array $categories) => ['key' => $key, 'categories' => $categories],
             array_keys(self::BUSINESS_TYPES),
             self::BUSINESS_TYPES,
         ),
         'primaryWarehouse' => $primaryWarehouse?->only(['id', 'code', 'name']),
     ]);
     ```
     * يحول الثابت `self::BUSINESS_TYPES` إلى مصفوفة كائنات:
       ```json
       [
         {"key": "food", "categories": ["food", "beverages", "snacks", "coffeeTea"]},
         {"key": "retail", "categories": ["general", "electronics", "homeSupplies", "beauty"]},
         ...
       ]
       ```
     * يأخذ فقط الحقول `['id', 'code', 'name']` من `$primaryWarehouse` عبر دالة المودل `only()`.
     * يستدعي `Inertia::render('Setup/Wizard', ...)` التي تضع اسم المكون `'Setup/Wizard'` والـ Props داخل قالب `resources/views/app.blade.php` عند `@inertia` وترسل HTML إلى المتصفح.

---

### المرحلة (C): تشغيل الواجهة الأمامية `resources/js/Pages/Setup/Wizard.jsx`

1. **الخطوة C.1 — إقلاع React وتحميل الصفحة (`resources/js/app.jsx`):**
   * يقرأ `createInertiaApp` اسم المكون `'Setup/Wizard'`، فيحمل الملف `resources/js/Pages/Setup/Wizard.jsx` ويمرر له الـ Props `{ businessTypes, primaryWarehouse }`.

2. **الخطوة C.2 — الكود الكامل للدوال والحالة داخل `resources/js/Pages/Setup/Wizard.jsx` (الأسطر 18–215):**

```jsx
const STEPS = [
    "store",
    "businessType",
    "categories",
    "branches",
    "account",
];

export default function Wizard({ businessTypes, primaryWarehouse }) {
    const { t, i18n } = useTranslation();
    const { props } = usePage();
    const [step, setStep] = useState(0);

    // تهيئة بيانات النموذج عبر Inertia useForm
    const {
        data,
        setData,
        post,
        transform,
        processing,
        errors,
        setError,
        clearErrors,
    } = useForm({
        store_name: "",
        store_address: "",
        store_phone: "",
        store_email: "",
        business_type: "",
        categories: [],
        branches: [
            {
                outlet_code: "",
                outlet_name: "",
                warehouse_code: "",
                warehouse_name: "",
                address: "",
                phone: "",
            },
        ],
        user_name: "",
        user_email: "",
        password: "",
        warehouse_id: primaryWarehouse?.id ?? "",
        warehouse_code: primaryWarehouse?.code ?? "",
        warehouse_name: primaryWarehouse?.name ?? "",
    });
    const [customCategory, setCustomCategory] = useState("");

    // دوال إدارة الفروع (الخطوة 3)
    const addBranch = () => {
        setData("branches", [
            ...data.branches,
            {
                outlet_code: "",
                outlet_name: "",
                warehouse_code: "",
                warehouse_name: "",
                address: "",
                phone: "",
            },
        ]);
    };

    const removeBranch = (index) => {
        if (data.branches.length <= 1) return;
        setData(
            "branches",
            data.branches.filter((_, i) => i !== index),
        );
        clearErrors(`branches.${index}`);
    };

    const updateBranch = (index, field, value) => {
        const next = data.branches.map((branch, i) =>
            i === index ? { ...branch, [field]: value } : branch,
        );
        setData("branches", next);
        clearErrors(`branches.${index}.${field}`);
    };

    // دوال إدارة التصنيفات (الخطوة 2)
    const categoryValue = (category) =>
        `__setup:${data.business_type}:${category}`;

    const toggleCategory = (name) => {
        setData(
            "categories",
            data.categories.includes(name)
                ? data.categories.filter((c) => c !== name)
                : [...data.categories, name],
        );
        clearErrors("categories");
    };

    const addCustomCategory = () => {
        const name = customCategory.trim();
        if (!name || data.categories.includes(name)) return;
        setData("categories", [...data.categories, name]);
        setCustomCategory("");
    };

    // دالة التحقق من كل خطوة قبل الانتقال للخطوة التالية
    const validateStep = () => {
        const nextErrors = {};
        if (step === 0 && !data.store_name.trim()) {
            nextErrors.store_name = t("setup.required");
        }
        if (step === 1 && !data.business_type) {
            nextErrors.business_type = t("setup.required");
        }
        if (step === 2 && data.categories.length === 0) {
            nextErrors.categories = t("setup.required");
        }
        if (step === 3) {
            const branchErrors = {};
            const outletCodes = new Set();
            const warehouseCodes = new Set();
            data.branches.forEach((branch, i) => {
                if (!branch.outlet_code.trim()) {
                    branchErrors[`branches.${i}.outlet_code`] = t("setup.required");
                } else if (outletCodes.has(branch.outlet_code.trim())) {
                    branchErrors[`branches.${i}.outlet_code`] = t("setup.duplicateCode");
                } else {
                    outletCodes.add(branch.outlet_code.trim());
                }
                if (!branch.outlet_name.trim()) {
                    branchErrors[`branches.${i}.outlet_name`] = t("setup.required");
                }
                if (!branch.warehouse_code.trim()) {
                    branchErrors[`branches.${i}.warehouse_code`] = t("setup.required");
                } else if (warehouseCodes.has(branch.warehouse_code.trim())) {
                    branchErrors[`branches.${i}.warehouse_code`] = t("setup.duplicateCode");
                } else {
                    warehouseCodes.add(branch.warehouse_code.trim());
                }
                if (!branch.warehouse_name.trim()) {
                    branchErrors[`branches.${i}.warehouse_name`] = t("setup.required");
                }
            });
            Object.assign(nextErrors, branchErrors);
        }
        if (Object.keys(nextErrors).length) {
            setError(nextErrors);
            return false;
        }
        return true;
    };

    const next = (event) => {
        event?.preventDefault();
        event?.stopPropagation();
        if (validateStep()) setStep((currentStep) => currentStep + 1);
    };

    const submit = () => {
        if (!validateStep()) return;

        transform((formData) => ({
            ...formData,
            categories: formData.categories.map((category) => {
                const prefix = `__setup:${formData.business_type}:`;

                return category.startsWith(prefix)
                    ? t(`setup.categoryOptions.${formData.business_type}.${category.slice(prefix.length)}`)
                    : category;
            }),
        }));
        post(route("setup.store"), { forceFormData: true });
    };
```

3. **الخطوة C.3 — تتبع الدوال في `Wizard.jsx` أثناء تفاعل المستخدم:**
   * **عند `step === 0` (بيانات المتجر):** يكتب المستخدم اسم المتجر والعنوان والهاتف والبريد، فتستدعي الحقول `setData('store_name', ...)` إلخ. عند الضغط على زر التالي، تُستدعى `next()` -> تستدعي `validateStep()` -> تتأكد من أن `data.store_name.trim()` غير فارغ -> تزيد `step` إلى `1`.
   * **عند `step === 1` (نوع النشاط التجاري):** يضغط المستخدم على أحد الأزرار الستة (`food`, `retail`, إلخ) فينفذ `setData('business_type', type.key)`. عند الضغط على التالي، تتحقق `validateStep()` من وجود `data.business_type` -> تزيد `step` إلى `2`.
   * **عند `step === 2` (التصنيفات):**
     * يتم إيجاد النشاط المختار عبر `const selectedType = businessTypes?.find((b) => b.key === data.business_type)`.
     * عند الضغط على تصنيف مقترح (مثلاً `food`)، تستدعي `categoryValue('food')` فترجع `'__setup:food:food'`، ثم تستدعي `toggleCategory('__setup:food:food')` لإضافته أو حذفه من مصفوفة `data.categories`.
     * عند كتابة تصنيف مخصص والضغط على إضافة، تستدعي `addCustomCategory()` التي تضيف النص مباشرة إلى `data.categories`.
     * عند الضغط على التالي، تتأكد `validateStep()` أن `data.categories.length > 0` -> تزيد `step` إلى `3`.
   * **عند `step === 3` (الفروع والمستودعات):**
     * كل تعديل في حقل فرع يستدعي `updateBranch(index, field, value)`.
     * زر إضافة فرع يستدعي `addBranch()`، وزر الحذف يستدعي `removeBranch(index)`.
     * عند الضغط على التالي، تفحص `validateStep()` كل فرع في `data.branches` وتتأكد من تعبئة `outlet_code`, `outlet_name`, `warehouse_code`, `warehouse_name` وعدم تكرار الأكواد عبر `outletCodes` و `warehouseCodes` (`Set`) -> تزيد `step` إلى `4`.
   * **عند `step === 4` (حساب المدير والمستودع المركزي):**
     * يدخل المستخدم `user_name`, `user_email`, `password`، ويعدل إن أراد `warehouse_code` (الافتراضي `PUSAT`) و `warehouse_name` (الافتراضي `Gudang Pusat`).
     * يضغط زر الإنهاء (`setup.finish`) الذي يستدعي الدالة `submit()`:
       1. تنفذ `validateStep()`.
       2. تنفذ `transform(...)` لتحويل كل تصنيف يبدأ بـ `__setup:{business_type}:` إلى اسمه المترجم النهائي عبر `t(...)`.
       3. تنفذ `post(route("setup.store"), { forceFormData: true })` فترسل طلب `POST /setup` إلى السيرفر.

---

### المرحلة (D): السيرفر يستقبل `POST /setup` وينفذ `SetupController::store()`

1. **الخطوة D.1 — المرور على الـ Middleware (`routes/web.php` السطر 74 و 76):**
   * يمر الطلب أولاً على `EnsureNotInstalled::handle()` -> يتحقق أن `Setting::getBool('app_setup_completed', false)` هو `false` فيسمح بالمرور.
   * يمر ثانياً على `throttle:10,1` (`ThrottleRequests`) للتأكد من عدم تجاوز 10 محاولات في الدقيقة.
   * يدخل إلى `SetupController::store(Request $request)` (`app/Http/Controllers/SetupController.php` السطر 41).

2. **الخطوة D.2 — تنفيذ `$request->validate([...])` (الأسطر 43–97):**
   * يتحقق من صحة كافة الحقول المرسلة:
     * `store_name`: نص مطلوب بحد أقصى 255 حرفاً.
     * `business_type`: يجب أن يكون ضمن مفاتيح `self::BUSINESS_TYPES`.
     * `categories`: مصفوفة تحتوي على عنصر واحد على الأقل.
     * `user_email`: بريد إلكتروني غير موجود في جدول `users` (`unique:users,email`).
     * `password`: 8 أحرف على الأقل.
     * `warehouse_id`: إن أُرسل، يجب أن يكون موجوداً في جدول `warehouses` وأن يكون كوده في قاعدة البيانات هو `'PUSAT'` حصراً:
       ```php
       if ($value && ! Warehouse::whereKey($value)->where('code', 'PUSAT')->exists()) {
           $fail('Wizard setup hanya dapat memakai gudang PUSAT.');
       }
       ```
     * `warehouse_code`: مطلوب وغير مكرر في جدول `warehouses` (مع استثناء `warehouse_id` الحالي عند التحديث).
     * `branches`: مصفوفة من 1 إلى 10 فروع، لكل فرع `outlet_code` فريد في جدول `outlets` و `warehouse_code` فريد في جدول `warehouses`.
   * إذا نجح التحقق، تُحفظ البيانات المنظفة في المتغير `$validated`.

3. **الخطوة D.3 — الدخول في `DB::transaction(function () use ($validated) { ... })` (السطر 99):**
   يفتح Laravel معاملة قاعدة بيانات (`BEGIN TRANSACTION`) وينفذ العمليات التالية بالتسلسل:

   * **العملية 1 (الأسطر 100–106): إنشاء حساب المدير العام**
     1. `Hash::make($validated['password'])`: يشفر كلمة المرور.
     2. `User::create([...])`: ينفذ `INSERT INTO users (name, email, password, created_at, updated_at) VALUES (...)` ويرجع كائن `$user`.
     3. `$user->assignRole('super-admin')`: يبحث في جدول `roles` عن `super-admin` ويدرج في جدول `model_has_roles` ارتباط المستخدم بهذا الدور.
     4. `$user->markEmailAsVerified()`: ينفذ `UPDATE users SET email_verified_at = NOW() WHERE id = $user->id`.

   * **العملية 2 (الأسطر 108–116): تجهيز المنفذ المركزي `PUSAT` في جدول `outlets`**
     1. يستدعي `Outlet::firstOrCreate(['code' => 'PUSAT'], [...])`: يبحث في جدول `outlets` عن `code = 'PUSAT'` (الذي أنشأه الـ Seeder، أو ينشئه إن لم يكن موجوداً).
     2. يستدعي `$pusat->update(['is_active' => true, 'is_sales_enabled' => false])` لضمان أنه مفعل وغير مخصص للبيع المباشر.

   * **العملية 3 (الأسطر 118–137): تحديث أو إنشاء المستودع المركزي في جدول `warehouses` وحفظه في `settings`**
     * **الحالة الأولى `if (! empty($validated['warehouse_id']))`** (وهي الحالة الافتراضية عند تشغيل الـ Seeder):
       1. ينفذ `Warehouse::where('id', $validated['warehouse_id'])->update([...])` لتحديث كود واسم المستودع المركزي وربطه بـ `'outlet_id' => $pusat->id` وضبط `'type' => 'main'`.
       2. يستدعي `Setting::set('setup_warehouse_id', $validated['warehouse_id'])` لتحديث المفتاح في جدول `settings`.
     * **الحالة الثانية `else`** (لو لم يكن هناك مستودع مسبق):
       1. ينفذ `$warehouse = Warehouse::create([...])` في جدول `warehouses` مع `'type' => 'main'` و `'sort_order' => 0`.
       2. يستدعي `Setting::set('setup_warehouse_id', $warehouse->id)`.

   * **العملية 4 (الأسطر 139–141): ربط المدير بالمنفذ المركزي في جدول `user_outlets`**
     ```php
     $user->outlets()->syncWithoutDetaching([
         $pusat->id => ['is_default' => true],
     ]);
     ```
     * يستدعي علاقة `User::outlets()` وينفذ `INSERT` (أو `UPDATE`) في جدول `user_outlets` لربط `$user->id` بـ `$pusat->id` مع `is_default = 1`.

   * **العملية 5 (الأسطر 143–165): حلقة إنشاء الفروع ومستودعاتها وربطها بالمدير**
     لكل فرع `$branch` في `$validated['branches']` (بدءاً من الفهرس `$i = 0`):
     1. **`Outlet::create([...])`**: ينفذ `INSERT INTO outlets` مع تحويل الكود لأحرف كبيرة `strtoupper($branch['outlet_code'])`، وضبط `is_active = true` و `is_sales_enabled = true`، ويرجع `$branchOutlet`.
     2. **`Warehouse::create([...])`**: ينفذ `INSERT INTO warehouses` مربوطاً بالفرع الجديد (`'outlet_id' => $branchOutlet->id`)، مع `code = strtoupper($branch['warehouse_code'])`، ونوع `'type' => 'branch'`، وترتيب `'sort_order' => $i + 1`.
     3. **`$user->outlets()->syncWithoutDetaching([$branchOutlet->id => ['is_default' => false]])`**: يدرج سجلاً في جدول `user_outlets` يربط المدير بالفرع الجديد مع `is_default = 0`.

   * **العملية 6 (الأسطر 167–169): إنشاء التصنيفات في جدول `categories`**
     ```php
     foreach (array_unique($validated['categories']) as $name) {
         Category::create(['name' => $name]);
     }
     ```
     * يزيل الأسماء المكررة عبر `array_unique`، ولكل اسم يستدعي `Category::create(['name' => $name])` فينفذ `INSERT INTO categories (name, created_at, updated_at) VALUES (...)`.

   * **العملية 7 (الأسطر 171–181): حفظ إعدادات المتجر وقفل التثبيت في جدول `settings`**
     1. يدور في حلقة `foreach` على المفاتيح الخمسة (`store_name`, `store_address`, `store_phone`, `store_email`, `store_business_type`) ويستدعي لكل منها `Setting::set($key, $value)` التي تنفذ `Setting::updateOrCreate(['key' => $key, 'outlet_id' => null], ['value' => $value, ...])`.
     2. ينفذ السطر 181:
        ```php
        Setting::set('app_setup_completed', true, 'Penanda wizard setup awal sudah diselesaikan');
        ```
        الذي يكتب `key = 'app_setup_completed'`, `value = 1` في جدول `settings`.
     3. تنتهي دالة الـ Closure الخاصة بـ `DB::transaction` فينفذ Laravel أمر **`COMMIT`** لحفظ كل التغييرات نهائياً في قاعدة البيانات.

4. **الخطوة D.4 — حفظ شعار المتجر إن وُجد (الأسطر 184–187):**
   ```php
   if ($request->file('store_logo')) {
       $path = $request->file('store_logo')->store('store', 'public');
       Setting::set('store_logo', $path, 'Logo toko');
   }
   ```
   * إذا رفع المستخدم صورة، تُخزن في `storage/app/public/store` ويُحفظ مسارها في جدول `settings` تحت المفتاح `store_logo`.

5. **الخطوة D.5 — إعادة التوجيه النهائية إلى صفحة تسجيل الدخول (السطر 189):**
   ```php
   return redirect()->route('login')->with('success', __('setup.completed'));
   ```
   * يضع الكنترولر رسالة النجاح في الـ Session تحت المفتاح `'success'`، ويرجع استجابة `302 Redirect` إلى الراوت `login` (`/login`).

---

### المرحلة (E): ما بعد التثبيت — الدخول إلى `/login` وتسجيل الدخول لأول مرة

1. **عند طلب `GET /login` (`routes/auth.php` السطر 19):**
   * يستدعي `AuthenticatedSessionController::create()` (`app/Http/Controllers/Auth/AuthenticatedSessionController.php` السطر 25):
     ```php
     public function create(): Response
     {
         return Inertia::render('Auth/Login', [
             'canResetPassword' => Route::has('password.request'),
             'canRegister' => config('security.auth.public_registration'),
             'status' => session('status'),
             'botGuard' => BotGuard::payload(),
         ]);
     }
     ```
   * يعرض صفحة تسجيل الدخول `resources/js/Pages/Auth/Login.jsx`.

2. **عند إرسال بيانات الدخول `POST /login` (`routes/auth.php` السطر 22):**
   * يستدعي `AuthenticatedSessionController::store(LoginRequest $request)` (السطر 38):
     1. `$request->authenticate()`: يتحقق من البريد وكلمة المرور في جدول `users` ويسجل دخول المستخدم في الجلسة.
     2. `$request->session()->regenerate()`: يجدد معرف الجلسة ويحفظ وقت بدء الجلسة في `security.session_started_at`.
     3. `$this->auditLogService->log(...)`: يسجل حدث `auth.login_succeeded` في جدول `audit_logs`.
     4. يحدد الراوت الافتراضي بعد الدخول بفحص مصفوفة الصلاحيات `$routePriority` (السطر 57):
        ```php
        $routePriority = [
            'transactions-access' => 'transactions.index',
            'receivables-access' => 'receivables.index',
            'payables-access' => 'payables.index',
            'customers-access' => 'customers.index',
            'suppliers-access' => 'suppliers.index',
            'reports-access' => 'reports.sales.index',
            'dashboard-access' => 'dashboard',
        ];
        ```
        وبما أن المدير الذي أنشأناه في التثبيت يحمل دور `super-admin`، فإن `$user->can('transactions-access')` يستدعي `User::hasPermissionTo()` -> يستدعي `$this->isSuperAdmin()` -> يرجع `true` فوراً!
     5. يتم توجيه المدير مباشرة إلى النظام بكامل الصلاحيات وجميع الفروع والمستودعات والتصنيفات التي أنشأها في معالج التثبيت.

3. **حماية مسارات التثبيت بعد إتمام العملية:**
   * لو حاول أي شخص الدخول إلى `GET /setup` أو `POST /setup` بعد هذه اللحظة، سيعترضه `EnsureNotInstalled::handle()`، وعند استدعاء `Setting::getBool('app_setup_completed', false)` سترجع `true`، فيتم طرده فوراً عبر `return redirect()->route('login')`.
   * ولو دخل أي زائر إلى الصفحة الرئيسية `GET /`، فإن الشرط `if (! Setting::getBool('app_setup_completed', false))` في `routes/web.php` سيكون `false`، فيعرض له النظام صفحة الهبوط التعريفية `Inertia::render('Welcome', [...])` (`resources/js/Pages/Welcome.jsx`).
