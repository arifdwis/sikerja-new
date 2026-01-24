# 📚 Struktur Modul SiKerja V2

> Dokumentasi lengkap Backend (Laravel) dan Frontend (Vue.js/Inertia)

---

## 🗂️ BACKEND (Laravel)

### 📁 Controllers

```
app/Http/Controllers/
│
├── Controller.php                     # Base controller
│
├── 🏠 MENU UTAMA
│   ├── DashboardController.php        # Dashboard
│   ├── PermohonanController.php       # Permohonan Kerjasama (CRUD + Workflow)
│   ├── NotifikasiController.php       # Notifikasi
│   └── ProfileController.php          # Profile User
│
├── 📦 Master/                         # MASTER DATA
│   ├── KategoriController.php         # Kategori Kerjasama
│   └── InstansiController.php         # Data Instansi/Pemohon
│
├── ⚙️ Settings/                       # PENGATURAN (Admin Only)
│   ├── UserController.php             # Manajemen User
│   ├── RoleController.php             # Manajemen Role
│   ├── PermissionController.php       # Manajemen Permission
│   ├── MenuController.php             # Manajemen Menu Sidebar
│   └── LogActivityController.php      # Log Aktivitas
│
└── 🔐 Auth/                           # AUTHENTICATION
    ├── AuthenticatedSessionController.php
    ├── RegisteredUserController.php
    └── SSOController.php              # SSO Samarinda
```

### 📁 Models

```
app/Models/
│
├── 👤 USER & AUTH
│   ├── User.php                       # Model User
│   ├── Role.php                       # Model Role
│   └── Permission.php                 # Model Permission
│
├── 📋 PERMOHONAN
│   ├── Permohonan.php                 # Model Permohonan Kerjasama
│   ├── PermohonanFile.php             # File Lampiran
│   ├── PermohonanHistori.php          # Histori/Log Permohonan
│   └── PermohonanPembahasan.php       # Data Pembahasan
│
├── 📦 MASTER DATA
│   ├── Kategori.php                   # Kategori Kerjasama
│   ├── Provinsi.php                   # Provinsi (master_provinces)
│   └── Kota.php                       # Kota (master_cities)
│
└── 🔧 SYSTEM
    ├── Menu.php                       # Menu Sidebar
    └── Notifikasi.php                 # Notifikasi
```

### 📁 Routes (web.php)

```php
// 🏠 MENU UTAMA
Route::get('/dashboard', ...);

// 📋 PERMOHONAN WORKFLOW
Route::resource('permohonan', PermohonanController::class);
Route::post('/permohonan/{uuid}/status', ...);
Route::get('/validasi', ...);           // Filter: Menunggu Validasi
Route::get('/pembahasan', ...);         // Filter: Sedang Dibahas
Route::get('/persetujuan', ...);        // Filter: Menunggu Persetujuan

// 📦 MASTER DATA
Route::prefix('master')->group(function () {
    Route::resource('kategori', Master\KategoriController::class);
    Route::resource('instansi', Master\InstansiController::class);
});

// ⚙️ SETTINGS
Route::prefix('settings')->group(function () {
    Route::resource('users', Settings\UserController::class);
    Route::resource('roles', Settings\RoleController::class);
    Route::resource('permissions', Settings\PermissionController::class);
    Route::resource('menu', Settings\MenuController::class);
    Route::resource('log-activity', Settings\LogActivityController::class);
});
```

---

## 🎨 FRONTEND (Vue.js + Inertia)

### 📁 Pages (Views)

```
resources/js/Pages/
│
├── 🏠 MENU UTAMA
│   ├── Dashboard.vue                  # Halaman Dashboard
│   └── Welcome.vue                    # Landing Page
│
├── 📋 Permohonan/                     # PERMOHONAN WORKFLOW
│   ├── Index.vue                      # Daftar Permohonan
│   ├── Create.vue                     # Form Buat Permohonan
│   ├── Show.vue                       # Detail Permohonan
│   └── Edit.vue                       # Edit Permohonan
│
├── ✅ Validasi/                       # VALIDASI (TKKSD)
│   └── Index.vue                      # Daftar Permohonan utk Validasi
│
├── 💬 Pembahasan/                     # PEMBAHASAN (TKKSD)
│   └── Index.vue                      # Daftar Permohonan utk Pembahasan
│
├── 📝 Persetujuan/                    # PERSETUJUAN (Pimpinan)
│   └── Index.vue                      # Daftar utk Persetujuan
│
├── 📅 Penjadwalan/                    # PENJADWALAN
│   └── Index.vue
│
├── 📜 Riwayat/                        # RIWAYAT
│   └── Index.vue
│
├── 📊 Laporan/                        # LAPORAN
│   └── Index.vue
│
├── 📦 Master/                         # MASTER DATA
│   ├── Kategori/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Edit.vue
│   └── Instansi/
│       ├── Index.vue
│       ├── Create.vue
│       └── Edit.vue
│
├── ⚙️ Settings/                       # PENGATURAN
│   ├── Users/
│   │   ├── Index.vue
│   │   ├── Create.vue
│   │   └── Edit.vue
│   ├── Roles/
│   │   ├── Index.vue
│   │   └── Permission.vue
│   ├── Permissions/
│   │   └── Index.vue
│   ├── Menus/
│   │   └── Index.vue
│   └── LogActivity/
│       └── Index.vue
│
├── 👤 Profile/                        # PROFILE
│   └── Edit.vue
│
└── 🔐 Auth/                           # AUTHENTICATION
    ├── Login.vue
    ├── Register.vue
    └── ForgotPassword.vue
```

### 📁 Components

```
resources/js/Components/
│
├── 🧩 UI Components
│   ├── ApplicationLogo.vue
│   ├── Breadcrumb.vue
│   ├── Modal.vue
│   ├── DataTable.vue
│   └── ...
│
└── 📁 Layouts
    └── Partials/
        ├── Sidebar.vue                # Menu Sidebar
        ├── Header.vue                 # Header/Navbar
        └── Footer.vue
```

### 📁 Layouts

```
resources/js/Layouts/
│
├── AuthenticatedLayout.vue            # Layout untuk user login
├── GuestLayout.vue                    # Layout untuk guest
└── Partials/
    ├── Sidebar.vue
    ├── Header.vue
    └── Footer.vue
```

---

## 🔐 PERMISSIONS

### Menu Utama
| Permission | Deskripsi | Superadmin | Admin | TKKSD | Pemohon |
|------------|-----------|:----------:|:-----:|:-----:|:-------:|
| `dashboard` | Akses Dashboard | ✅ | ✅ | ✅ | ✅ |
| `permohonan.index` | Lihat Permohonan | ✅ | ✅ | ✅ | ✅ |
| `permohonan.create` | Buat Permohonan | ✅ | ✅ | ❌ | ✅ |
| `permohonan.show` | Detail Permohonan | ✅ | ✅ | ✅ | ✅ |
| `permohonan.edit` | Edit Permohonan | ✅ | ✅ | ❌ | ✅* |
| `permohonan.destroy` | Hapus Permohonan | ✅ | ✅ | ❌ | ✅* |
| `permohonan.status` | Ubah Status | ✅ | ✅ | ✅ | ❌ |
| `permohonan.menu.validasi` | Menu Validasi | ✅ | ✅ | ✅ | ❌ |
| `permohonan.menu.pembahasan` | Menu Pembahasan | ✅ | ✅ | ✅ | ❌ |
| `permohonan.menu.persetujuan` | Menu Persetujuan | ✅ | ✅ | ❌ | ❌ |

> *Pemohon hanya bisa edit/hapus permohonan miliknya sendiri

### Master Data
| Permission | Deskripsi | Superadmin | Admin | TKKSD | Pemohon |
|------------|-----------|:----------:|:-----:|:-----:|:-------:|
| `master.kategori.index` | Lihat Kategori | ✅ | ✅ | ❌ | ❌ |
| `master.kategori.create` | Buat Kategori | ✅ | ✅ | ❌ | ❌ |
| `master.kategori.edit` | Edit Kategori | ✅ | ✅ | ❌ | ❌ |
| `master.kategori.destroy` | Hapus Kategori | ✅ | ✅ | ❌ | ❌ |

### Settings
| Permission | Deskripsi | Superadmin | Admin | TKKSD | Pemohon |
|------------|-----------|:----------:|:-----:|:-----:|:-------:|
| `settings.users.*` | Manajemen User | ✅ | ✅ | ❌ | ❌ |
| `settings.roles.*` | Manajemen Role | ✅ | ✅ | ❌ | ❌ |
| `settings.permissions.*` | Manajemen Permission | ✅ | ✅ | ❌ | ❌ |
| `settings.menu.*` | Manajemen Menu | ✅ | ✅ | ❌ | ❌ |
| `settings.log-activity.*` | Log Aktivitas | ✅ | ✅ | ❌ | ❌ |

---

## 📝 NAMING CONVENTION

### Backend (Laravel)
| Tipe | Pattern | Contoh |
|------|---------|--------|
| Controller | `{Module}{Name}Controller.php` | `UserController.php`, `KategoriController.php` |
| Model | `{Name}.php` (Singular) | `User.php`, `Permohonan.php` |
| Table | `{names}` (Plural, snake_case) | `users`, `permohonan` |
| Route Name | `{module}.{action}` | `settings.users.index`, `permohonan.create` |
| Permission | `{module}.{action}` | `settings.users.create`, `permohonan.status` |

### Frontend (Vue.js)
| Tipe | Pattern | Contoh |
|------|---------|--------|
| Page | `{Module}/{Action}.vue` | `Permohonan/Index.vue`, `Settings/Users/Create.vue` |
| Component | `{Name}.vue` (PascalCase) | `DataTable.vue`, `Modal.vue` |
| Layout | `{Name}Layout.vue` | `AuthenticatedLayout.vue` |

---

## 🔄 WORKFLOW PERMOHONAN

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   PEMOHON   │    │    TKKSD    │    │    TKKSD    │    │  PIMPINAN   │
│   Membuat   │───▶│   Validasi  │───▶│  Pembahasan │───▶│ Persetujuan │
│  Permohonan │    │             │    │             │    │             │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
     │                   │                  │                  │
     ▼                   ▼                  ▼                  ▼
 STATUS: 0           STATUS: 1          STATUS: 2          STATUS: 4
 (Permohonan)        (Disposisi)        (Pembahasan)       (Selesai)
```

**Status Values:**
- `0` = Permohonan Baru
- `1` = Disposisi/Validasi
- `2` = Pembahasan
- `4` = Selesai/Disetujui
- `9` = Ditolak

---

## 📂 DATABASE TABLES

### Core Tables
| Table | Deskripsi |
|-------|-----------|
| `users` | Data user |
| `roles` | Data role |
| `permissions` | Data permission |
| `role_users` | Pivot role-user |
| `role_permissions` | Pivot role-permission |

### Permohonan Tables
| Table | Deskripsi |
|-------|-----------|
| `permohonan` | Data permohonan kerjasama |
| `permohonan_file` | File lampiran permohonan |
| `permohonan_histori` | Histori/log permohonan |
| `permohonan_pembahasan` | Data pembahasan |

### Master Tables
| Table | Deskripsi |
|-------|-----------|
| `kategori` | Kategori kerjasama |
| `master_provinces` | Data provinsi |
| `master_cities` | Data kota |

### System Tables
| Table | Deskripsi |
|-------|-----------|
| `menus` | Menu sidebar |
| `notifikasis` | Notifikasi user |
| `activity_log` | Log aktivitas (Spatie) |

---

## ✅ CHECKLIST IMPLEMENTASI

### Backend (Controllers + Middleware)
- [x] DashboardController
- [x] PermohonanController (+ middleware `can:`)
- [x] ValidasiController (+ middleware `can:`)
- [x] PembahasanController (+ middleware `can:`)
- [x] PersetujuanController (+ middleware `can:`)
- [x] PenjadwalanController (+ middleware `can:`)
- [x] RiwayatController (+ middleware `can:`)
- [x] ProfileController (+ updateCorporate untuk Pemohon)
- [x] UserController (+ middleware `can:`)
- [x] RoleController (+ middleware `can:`)
- [x] PermissionController (+ middleware `can:`)
- [x] MenuController (+ middleware `can:`)
- [x] LogActivityController (+ middleware `can:`)
- [x] Master/KategoriController (+ middleware `can:`)
- [x] Master/PemohonController (+ middleware `can:`)

### Frontend (Vue Pages)
- [x] Dashboard.vue
- [x] Permohonan/Index.vue
- [x] Permohonan/Create.vue
- [x] Permohonan/Show.vue
- [x] Validasi/Index.vue ✨ NEW
- [x] Pembahasan/Index.vue ✨ NEW
- [x] Persetujuan/Index.vue ✨ NEW
- [x] Penjadwalan/Index.vue ✨ NEW
- [x] Riwayat/Index.vue ✨ NEW
- [x] Profile/Edit.vue (+ Corporate Form untuk Pemohon) ✨ UPDATED
- [x] Profile/Partials/UpdateCorporateForm.vue ✨ NEW
- [x] Settings/Users/Index.vue
- [x] Settings/Users/Create.vue
- [x] Settings/Roles/Index.vue
- [x] Settings/Roles/Permission.vue
- [x] Settings/Permissions/Index.vue
- [x] Settings/Menus/Index.vue
- [x] Master/Kategori/Index.vue
- [x] Master/Pemohon/Index.vue ✨ NEW

### Permissions & Seeder
- [x] PermissionsSeeder (Settings + Permohonan + Master + Penjadwalan + Riwayat + Profile)
- [x] RolePermissionsSeeder (Auto-assign ke roles)

### Database & Models
- [x] User model dengan hasPermission()
- [x] Permohonan model dengan relationship pemohon()
- [x] Pemohon model dengan field Corporate
- [x] Gate definitions di AppServiceProvider
- [x] Spatie middleware dihapus (pakai custom Nue roles)

### Role-based Access (Tested ✅)
- [x] Superadmin - Full access
- [x] Administrator - Full Settings + Permohonan + Master
- [x] TKKSD - Validasi + Pembahasan + Penjadwalan
- [x] Pemohon - Create Permohonan + Profile + Riwayat

---

## 📋 CARA MENAMBAH MODUL BARU

### 1. Buat Model
```bash
php artisan make:model NamaModel -m
```

### 2. Buat Controller
```php
// app/Http/Controllers/Master/NamaController.php

class NamaController extends Controller implements HasMiddleware
{
    // Pattern: constructor + share + static middleware
}
```

### 3. Tambah Routes
```php
// routes/web.php
Route::resource('nama', NamaController::class);
```

### 4. Tambah Permissions
```php
// database/seeders/PermissionsSeeder.php
'master.nama.index',
'master.nama.create',
// ...
```

### 5. Buat Vue Component
```
resources/js/Pages/Master/Nama/Index.vue
```

### 6. Run Seeder
```bash
php artisan db:seed --class=PermissionsSeeder
php artisan db:seed --class=RolePermissionsSeeder
```
