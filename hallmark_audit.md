# HALLMARK AUDIT REPORT — SiKerja V2

**Aplikasi:** SiKerja V2 — Sistem Informasi Kerjasama Pemerintah Kota Samarinda
**Tech Stack:** Laravel 12, Vue 3, Inertia.js, PrimeVue, MySQL
**Tanggal Audit:** 21 Juli 2026
**Audit Scope:** Code Quality, Security, Testing, Documentation, Performance, UX

---

## Maturity Score

| Aspek | Score | Status |
|-------|-------|--------|
| **Security** | 4/10 | Banyak critical holes |
| **Testing** | 1/10 | Hampir tidak ada automated tests |
| **Documentation** | 4/10 | Internal docs ada, README kosong |
| **Configuration** | 2/10 | Tidak ada Docker, CI/CD |
| **Performance** | 5/10 | Eager loading ada, belum ada caching |
| **Code Quality** | 5/10 | Konsisten tapi banyak DRY violations |
| **UX/Accessibility** | 3/10 | Loading states ada, aria labels minim |

---

## CRITICAL (6 item — harus segera diperbaiki)

| # | Kategori | Lokasi | Masalah | Saran |
|---|----------|--------|---------|-------|
| 1 | Security | `GoogleController.php` | OAuth callback tidak verifikasi token — siapa saja bisa login sebagai user lain | Verifikasi token via Google's tokeninfo endpoint atau library `google/apiclient` |
| 2 | Security | `SSOController.php:52,61,95` | Sensitive credentials (uid, pwd, JWT) di-log dalam plaintext | Hapus logging parameter sensitif. Hanya log event/aksi tanpa payload kredensial |
| 3 | Security | `SSOController.php:40` | Broker secret dikirim via query string — terecord di access logs | Kirim via POST body atau gunakan HMAC signature di header |
| 4 | Security | `SSOController.php:93-107` | JWT signature tidak diverifikasi — bisa forge payload | Gunakan `firebase/php-jwt` untuk verifikasi signature |
| 5 | Testing | `tests/` | 0% test coverage untuk fitur inti (workflow, monev, notifikasi) | Buat minimal 10 feature test untuk flow kritis |
| 6 | Config | Tidak ada | Tidak ada CI/CD pipeline — deploy manual tanpa automated checks | Buat `.github/workflows/ci.yml` dengan lint, test, build |

---

## HIGH (14 item)

| # | Kategori | Lokasi | Masalah | Saran |
|---|----------|--------|---------|-------|
| 7 | Security | `.env` | `APP_DEBUG=true`, `DB_PASSWORD=` kosong, `SESSION_SECURE_COOKIE=false` | Set `APP_DEBUG=false`, set password DB, set `SESSION_SECURE_COOKIE=true` |
| 8 | Security | `PenjadwalanController.php:218` | `destroy()` tidak ada authorization check | Tambahkan middleware authorization atau manual check `abort_unless(Auth::user()->isAdmin(), 403)` |
| 9 | Security | `routes/web.php:40` | Chatbot endpoint tanpa rate limiting — cost attack | Tambahkan `throttle:10,1` middleware |
| 10 | Security | `PermohonanPembahasanController.php:72` | File upload tidak ada validasi `mimes` — bisa upload executable | Tambahkan `mimes:pdf,doc,docx,jpg,jpeg,png` |
| 11 | Error | `PenjadwalanController.php:145` | Silent exception swallowing — catch kosong tanpa logging | Tambahkan `Log::error('WA group send failed', ['error' => $e->getMessage()])` |
| 12 | Error | `MonevController.php:494` | Silent exception swallowing — error WA hilang tanpa jejak | Tambahkan `Log::error()` atau `Log::warning()` dengan context |
| 13 | DRY | 9 controllers | Search query pattern diulang 9 kali | Buat Scope di model: `scopeSearch($query, $search)` atau trait `HasSearchScope` |
| 14 | DRY | 6+ controllers | Ownership check pattern diulang 10+ kali | Buat Policy `PermohonanPolicy` method `view()`, `update()` |
| 15 | Performance | `User.php:122` | `hasRole()` query DB setiap dipanggil | Eager-load roles di auth middleware atau cache dalam request lifecycle |
| 16 | Performance | Migrations | Missing indexes di `status`, `id_pemohon_0`, `created_at` | Buat migration untuk add index |
| 17 | Dependencies | `package.json` | 4 date libraries (moment, moment-timezone, date-fns, luxon) | Pilih satu (`date-fns`), hapus lainnya |
| 18 | Dependencies | `package.json` | 2 chart libraries (chart.js + apexcharts) | Standarisasi ke apexcharts, hapus chart.js |
| 19 | Config | Tidak ada | Tidak ada Docker setup — deploy tidak reproducible | Buat `Dockerfile` + `docker-compose.yml` |
| 20 | Documentation | `README.md` | README masih template default Laravel | Rewrite: nama aplikasi, setup guide, architecture overview |

---

## MEDIUM (21 item)

| # | Kategori | Lokasi | Masalah | Saran |
|---|----------|--------|---------|-------|
| 21 | Validation | `PembahasanController.php:160` | `update()` tidak ada validasi input sama sekali | Tambahkan minimal `$request->validate()` |
| 22 | Validation | `DashboardController.php:39` | Filter dashboard tanpa validasi — raw input ke query | Validasi: `'tahun' => 'nullable\|integer\|min:2020\|max:2030'` |
| 23 | Consistency | Multiple | Pagination size tidak konsisten (10 vs 15) | Standardisasi ke config value |
| 24 | Consistency | Multiple | Prop naming Inertia tidak konsisten (`datas` vs `permohonan`) | Standardisasi nama prop sesuai resource |
| 25 | DRY | `KerjasamaManual` + `Permohonan` | `calculateJangkaWaktu` di-duplicate di 2 controller | Buat helper function atau Value Object |
| 26 | Type Safety | Multiple | Missing PHP type hints di mayoritas controller methods | Tambahkan `string $uuid`, `int $id` |
| 27 | Type Safety | Vue Components | Props tidak ada validasi — semua pakai `Object`/`Array` | Tambahkan prop validation atau TypeScript |
| 28 | Logging | `User.php:209` | Hardcoded excluded emails/IDs di model | Pindahkan ke `config('services.admin_notification_excluded')` |
| 29 | Logging | Multiple | Tidak ada logging untuk status changes di luar observer | Tambahkan activity logging untuk status changes penting |
| 30 | Security | Multiple | Tidak ada Laravel Policy — authorization manual di controller | Buat Policies: `PermohonanPolicy`, `MonevPolicy`, `NotifikasiPolicy` |
| 31 | UX | Vue Components | Aria labels sangat minim | Audit semua interactive elements, tambahkan `aria-label` |
| 32 | UX | Vue Components | Tidak ada keyboard navigation support untuk modals | Tambahkan `@keydown.escape`, `tabindex` management |
| 33 | UX | Vue Components | Tidak ada error boundary di Vue app level | Buat `ErrorBoundary.vue` component |
| 34 | Performance | `DashboardController` | 8-10 query terpisah tanpa caching | Cache: `Cache::remember('dashboard_admin_' . $tahun, 300, ...)` |
| 35 | Performance | `LandingController` | 7+ queries untuk landing page tanpa caching | Cache: `Cache::remember('landing_home', 600, ...)` |
| 36 | Performance | `MonevController:720` | Export load semua data ke memory — risk OOM | Gunakan `cursor()` atau chunking |
| 37 | Config | `.env.example:88` | Phone number hardcoded di `.env.example` | Ganti ke placeholder `08XXXXXXXXXX` |
| 38 | Config | `.env.example:8` | `APP_LOCALE=en` — seharusnya `id` | Ganti ke `APP_LOCALE=id` |
| 39 | Dependencies | `composer.json` | Sanctum di-install tapi tidak terpakai | Pertimbangkan remove untuk reduce attack surface |
| 40 | Documentation | Tidak ada | Tidak ada API documentation | Buat minimal OpenAPI spec untuk public endpoints |
| 41 | Documentation | Tidak ada | Tidak ada deployment guide | Buat `DEPLOYMENT.md` dengan server requirements |

---

## LOW (15 item)

| # | Kategori | Lokasi | Masalah | Saran |
|---|----------|--------|---------|-------|
| 42 | Error | `PenandatangananController.php:147` | Catch block hanya komentar tanpa Log | Tambahkan `Log::error()` |
| 43 | Error | `UserController.php:174` | SSO Search error log dikomentari | Uncomment atau gunakan `Log::warning()` |
| 44 | Validation | `PenjadwalanController.php:159` | Hasil validasi tidak di-assign ke variabel | Assign: `$validated = $request->validate([...])` |
| 45 | Validation | `PenandatangananController.php:100` | File upload max 20MB — risk timeout | Pertimbangkan max 10MB |
| 46 | Consistency | Multiple | Return type tidak konsisten di controller methods | Tambahkan return type declaration |
| 47 | Consistency | `RoleController.php:86` | Props dikirim 2x (`datas` + `roles`) | Pilih satu approach |
| 48 | Consistency | `LaporanController.php:322` | `$request->all()` dikirim ke frontend | Gunakan `$request->only([...])` |
| 49 | DRY | Multiple | `Pemohon::where('id_operator', ...)` pattern berulang | Gunakan `$user->pemohon` relationship |
| 50 | Type Safety | `User.php:122` | `hasRole()` parameter tidak di-type | Gunakan union type: `string\|array $role` |
| 51 | Type Safety | DashboardController | Closure parameter tidak di-type | Tambahkan type hint |
| 52 | Logging | `PermohonanController.php:661` | Upload request logging terlalu verbose | Gunakan `Log::debug()` |
| 53 | Logging | Multiple | Tidak ada correlation ID untuk request tracing | Tambahkan middleware request ID |
| 54 | Security | `LandingController.php:44` | Public API `/api/kotas-all` tanpa rate limiting | Tambahkan rate limiting atau cache |
| 55 | Security | `MenuController.php:129` | `reorder()` tidak ada validasi struktur array | Tambahkan validasi: `'items.*.id' => 'required\|exists:menus,id'` |
| 56 | UX | `DashboardController` | Tidak ada empty state jika data kosong | Tambahkan empty state dengan illustration + CTA |

---

## TESTING AUDIT

| # | Kategori | Lokasi | Masalah | Severity |
|---|----------|--------|---------|----------|
| T1 | Testing | `tests/Feature/Auth/` | Hanya test bawaan Laravel Breeze — tidak ada test fitur inti | Critical |
| T2 | Testing | `tests/Unit/ExampleTest.php` | Unit test kosong — hanya `assertTrue(true)` | High |
| T3 | Testing | `package.json` | Tidak ada JavaScript testing framework (Jest/Vitest) | High |
| T4 | Testing | `database/factories/` | Hanya ada `UserFactory.php` — tidak ada factory model inti | High |
| T5 | Testing | `database/seeders/DatabaseSeeder.php` | Seeder hanya 1 user test | Medium |
| T6 | Testing | `phpunit.xml` | Tidak ada code coverage report | Medium |

---

## DOCUMENTATION AUDIT

| # | Kategori | Lokasi | Status | Severity |
|---|----------|--------|--------|----------|
| D1 | Documentation | `README.md` | Template default Laravel — kosong | Critical |
| D2 | Documentation | `STRUKTUR_MODUL.md` | Cukup lengkap | Good |
| D3 | Documentation | `TESTING_FLOW.md` | Sangat komprehensif (498 baris) | Good |
| D4 | Documentation | `NOTIFIKASI.md` | Sangat lengkap (568 baris) | Good |
| D5 | Documentation | `MIGRATION_PLAN.md` | Beberapa item masih TODO/placeholder | Medium |
| D6 | Documentation | API Documentation | Tidak ada (OpenAPI/Swagger) | High |
| D7 | Documentation | Deployment Guide | Tidak ada | High |
| D8 | Documentation | Inline Code | PHPDoc ada tapi method panjang tanpa penjelasan | Medium |

---

## DEPENDENCY AUDIT

### Duplikasi yang Ditemukan

| Kategori | Packages | Masalah | Saran |
|----------|----------|---------|-------|
| Date Libraries | `moment`, `moment-timezone`, `date-fns`, `luxon` | 4 library untuk hal yang sama | Pilih `date-fns`, hapus lainnya |
| Chart Libraries | `chart.js`, `apexcharts`, `vue3-apexcharts` | 2 chart engine berbeda | Standarisasi ke apexcharts |
| CSS Frameworks | `flowbite`, `flowbite-vue`, `primevue`, `tailwindcss-primeui` | Banyak overlap | Evaluasi, pertimbangkan hapus flowbite-vue |
| Unused | `quill`, `filepond`, `vue-filepond`, `xlsx`, `js-cookie`, `sortablejs` | Tidak ada import ditemukan | Hapus dari dependencies |

---

## REKOMENDASI ARSITEKTUR

### 1. Buat `app/Policies/`
Konsolidasi authorization logic ke Policy classes:
```
app/Policies/PermohonanPolicy.php
app/Policies/MonevPolicy.php
app/Policies/NotifikasiPolicy.php
```

### 2. Buat `app/Http/Requests/`
FormRequest untuk validasi reusable:
```
app/Http/Requests/PermohonanStoreRequest.php
app/Http/Requests/PermohonanUpdateRequest.php
app/Http/Requests/MonevStoreRequest.php
```

### 3. Buat `database/factories/`
Factory untuk semua model utama:
```
database/factories/PermohonanFactory.php
database/factories/PemohonFactory.php
database/factories/KategoriFactory.php
database/factories/MonevFactory.php
database/factories/PenjadwalanFactory.php
database/factories/OpdFactory.php
database/factories/NotifikasiFactory.php
```

### 4. Buat Scope di Model
```php
// Permohonan Model
public function scopeSearch($query, $search) { ... }
public function scopeByStatus($query, $status) { ... }
public function scopeByRole($query, $role) { ... }
```

### 5. Extract Service Layer
```
app/Services/NotificationService.php
app/Services/FileUploadService.php
app/Services/PermissionService.php
```

---

## TOP 5 PRIORITAS KRITIS

| Prioritas | Aksi | Estimasi | Impact |
|-----------|------|----------|--------|
| **1** | **Fix SSO Security** — verifikasi JWT signature, hapus logging credentials, kirim secret via POST | 2-3 hari | Menghilangkan authentication bypass |
| **2** | **Fix Google OAuth** — verifikasi token via Google API, jangan trust payload langsung | 1 hari | Menghilangkan account takeover risk |
| **3** | **Buat CI/CD Pipeline** — GitHub Actions untuk lint, test, build otomatis | 1-2 hari | Mencegah regression saat deploy |
| **4** | **Buat Automated Tests** — minimal 10 feature test untuk flow kritis | 3-5 hari | Mencegah bug masuk ke production |
| **5** | **Buat README + Deployment Docs** — setup guide, Docker config, production checklist | 1-2 hari | Developer baru bisa setup project |

---

## RINGKASAN PERUBAHAN YANG SUDAH DILAKUKAN

Berikut perbaikan yang sudah diterapkan sebelum audit ini:

| Perubahan | File | Status |
|-----------|------|--------|
| Fix authorization bypass `PenjadwalanController::review()` | `PenjadwalanController.php` | Done |
| Register routes `NotifikasiController` (6 routes) | `routes/pemohon.php` | Done |
| Register routes `PermohonanPembahasanController` (6 routes) | `routes/pemohon.php` | Done |
| Fix `Notifikasi.category` di `$fillable` | `Notifikasi.php` | Done |
| DOMPurify untuk v-html (About, Page, FAQSection) | 3 Vue files | Done |
| Fix memory leaks — clearInterval di onUnmounted (4 files) | DiskusiSection, FileDiskusiSection, Navbar, ServerStatus | Done |
| Hapus deadcode: deprecated methods, unused imports | MonevController, PksController, DashboardController, ValidasiController, PembahasanController | Done |
| Hapus file sampah: `LaporanController_new.php`, `Donut.vue.bak`, `ApexCharts/ApexCharts/` | 3 files/dirs | Done |
| Fix hardcoded test URLs (ServerStatus, Page.vue) | 2 Vue files | Done |
| Hapus unused `moment` import | DiskusiSection.vue | Done |
| Install DOMPurify | package.json | Done |
