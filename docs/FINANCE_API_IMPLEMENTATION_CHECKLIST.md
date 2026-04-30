# Finance API Implementation Checklist

Version: 1.0.0
Last Updated: April 29, 2026
Purpose: Convert existing finance web modules into REST API endpoints for mobile app integration.

---

## 1. Current State

- Existing API routes in routes/api.php currently cover: Auth, Users, Masjids, Notifications.
- Finance modules are implemented in web routes/controllers, not yet in API routes.
- Validation rules already exist in FormRequest classes under app/Http/Requests/Admin.

---

## 2. API Conventions (Recommended)

- Base URL: /api
- Auth: auth:sanctum
- Tenant scope: derive id_masjid from authenticated user unless Superadmin
- Pagination response format:

```json
{
  "data": [],
  "pagination": {
    "total": 0,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1,
    "from": null,
    "to": null
  }
}
```

- Validation errors: HTTP 422 with message + errors object
- Authorization errors: HTTP 403
- Not found: HTTP 404

---

## 3. Route Group Blueprint

Add inside routes/api.php under auth:sanctum middleware:

```php
Route::prefix('finance')->group(function () {
    // akaun
    // hasil
    // belanja
    // pindahan-akaun
    // sumber-hasil
    // kategori-belanja
    // tabung-khas
    // program-masjid
    // running-no
    // reports
});
```

---

## 4. Controller Mapping Plan

Recommended API controllers:

- App\Http\Controllers\Api\Finance\AkaunController
- App\Http\Controllers\Api\Finance\HasilController
- App\Http\Controllers\Api\Finance\BelanjaController
- App\Http\Controllers\Api\Finance\PindahanAkaunController
- App\Http\Controllers\Api\Finance\SumberHasilController
- App\Http\Controllers\Api\Finance\KategoriBelanjaController
- App\Http\Controllers\Api\Finance\TabungKhasController
- App\Http\Controllers\Api\Finance\ProgramMasjidController
- App\Http\Controllers\Api\Finance\RunningNoController
- App\Http\Controllers\Api\Finance\ReportsController

Source web controllers for business logic reference:

- Admin/AkaunManagementController
- Admin/HasilManagementController
- Admin/BelanjaManagementController
- Admin/PindahanAkaunManagementController
- Admin/SumberHasilManagementController
- Admin/KategoriBelanjaManagementController
- Admin/TabungKhasManagementController
- Admin/ProgramMasjidManagementController
- Admin/RunningNoManagementController
- Laporan* controllers

---

## 5. Endpoint-to-Schema Mapping

## 5.1 Accounts (Akaun)

Permissions: akaun.view, akaun.create, akaun.update, akaun.delete

| Method | Endpoint | Controller Method | Request Schema |
|---|---|---|---|
| GET | /finance/akaun | AkaunController@index | Query: search, status_aktif, per_page, page |
| POST | /finance/akaun | AkaunController@store | nama_akaun(required), jenis(required: tunai/bank), no_akaun(required_if bank), nama_bank(required_if bank), status_aktif(boolean) |
| GET | /finance/akaun/{akaun} | AkaunController@show | - |
| PATCH | /finance/akaun/{akaun} | AkaunController@update | same as store |
| DELETE | /finance/akaun/{akaun} | AkaunController@destroy | - |

Response item fields:
- id, id_masjid, nama_akaun, jenis, no_akaun, nama_bank, status_aktif, created_at, updated_at

---

## 5.2 Income (Hasil)

Permissions: hasil.view, hasil.create, hasil.update, hasil.delete

| Method | Endpoint | Controller Method | Request Schema |
|---|---|---|---|
| GET | /finance/hasil | HasilController@index | Query: search, tarikh_mula, tarikh_tamat, id_akaun, id_sumber_hasil, per_page, page |
| POST | /finance/hasil | HasilController@store | tarikh(required date), amaun(required numeric), id_akaun(required), id_sumber_hasil(required), id_tabung_khas(nullable), is_jumaat(boolean), catatan(nullable) |
| GET | /finance/hasil/{hasil} | HasilController@show | - |
| PATCH | /finance/hasil/{hasil} | HasilController@update | same as store |
| DELETE | /finance/hasil/{hasil} | HasilController@destroy | - |
| GET | /finance/hasil/{hasil}/receipt | HasilController@receipt | - |

Response item fields (recommended):
- id, tarikh, no_resit, id_akaun, id_sumber_hasil, id_tabung_khas, amaun_tunai, amaun_online, jumlah, jenis_jumaat, catatan, created_by

Note:
- Existing FormRequest uses amaun, while model stores split fields (amaun_tunai/amaun_online/jumlah). Define API transform rule clearly.

---

## 5.3 Expenses (Belanja)

Permissions: belanja.view, belanja.create, belanja.update, belanja.delete, finance.approve

| Method | Endpoint | Controller Method | Request Schema |
|---|---|---|---|
| GET | /finance/belanja | BelanjaController@index | Query: search, status, tarikh_mula, tarikh_tamat, id_kategori_belanja, per_page, page |
| POST | /finance/belanja | BelanjaController@store | tarikh(required), amaun(required), id_akaun(required), id_kategori_belanja(required), id_baucar(nullable), submit_action(draft/submitted), penerima(nullable), catatan(nullable), bukti_fail(file optional) |
| GET | /finance/belanja/{belanja} | BelanjaController@show | - |
| PATCH | /finance/belanja/{belanja} | BelanjaController@update | same as store + remove_bukti_fail(boolean) |
| DELETE | /finance/belanja/{belanja} | BelanjaController@destroy | - |
| PATCH | /finance/belanja/{belanja}/approve | BelanjaController@approve | body optional note/status, server sets dilulus_oleh + tarikh_lulus |

Response item fields:
- id, tarikh, id_akaun, id_kategori_belanja, amaun, id_tabung_khas, id_program, penerima, catatan, bukti_fail, status, id_baucar, dilulus_oleh, tarikh_lulus

---

## 5.4 Account Transfers (Pindahan Akaun)

Permissions: pindahan_akaun.view, pindahan_akaun.create, pindahan_akaun.update, pindahan_akaun.delete

| Method | Endpoint | Controller Method | Request Schema |
|---|---|---|---|
| GET | /finance/pindahan-akaun | PindahanAkaunController@index | Query: tarikh_mula, tarikh_tamat, per_page, page |
| POST | /finance/pindahan-akaun | PindahanAkaunController@store | tarikh(required), dari_akaun_id(required), ke_akaun_id(required,different), amaun(required), catatan(nullable) |
| GET | /finance/pindahan-akaun/{pindahanAkaun} | PindahanAkaunController@show | - |
| PATCH | /finance/pindahan-akaun/{pindahanAkaun} | PindahanAkaunController@update | same as store |
| DELETE | /finance/pindahan-akaun/{pindahanAkaun} | PindahanAkaunController@destroy | - |

Response item fields:
- id, tarikh, dari_akaun_id, ke_akaun_id, amaun, catatan, created_by

---

## 5.5 Master Data APIs

## 5.5.1 Sumber Hasil
Permissions: sumber_hasil.view/create/update/delete

| Method | Endpoint | Request Schema |
|---|---|---|
| GET | /finance/sumber-hasil | Query: search, aktif, per_page, page |
| POST | /finance/sumber-hasil | kod(required,max20), nama_sumber(required,max150), jenis(required,max50), aktif(boolean) |
| GET | /finance/sumber-hasil/{sumberHasil} | - |
| PATCH | /finance/sumber-hasil/{sumberHasil} | same as POST |
| DELETE | /finance/sumber-hasil/{sumberHasil} | - |
| PATCH | /finance/sumber-hasil/{sumberHasil}/status | aktif(boolean) |

## 5.5.2 Kategori Belanja
Permissions: kategori_belanja.view/create/update/delete

| Method | Endpoint | Request Schema |
|---|---|---|
| GET | /finance/kategori-belanja | Query: search, aktif, per_page, page |
| POST | /finance/kategori-belanja | kod(required,max20), nama_kategori(required,max150), aktif(boolean) |
| GET | /finance/kategori-belanja/{kategoriBelanja} | - |
| PATCH | /finance/kategori-belanja/{kategoriBelanja} | same as POST |
| DELETE | /finance/kategori-belanja/{kategoriBelanja} | - |
| PATCH | /finance/kategori-belanja/{kategoriBelanja}/status | aktif(boolean) |

## 5.5.3 Tabung Khas
Permissions: tabung_khas.view/create/update/delete

| Method | Endpoint | Request Schema |
|---|---|---|
| GET | /finance/tabung-khas | Query: search, aktif, per_page, page |
| POST | /finance/tabung-khas | nama_tabung(required,max150), aktif(boolean) |
| GET | /finance/tabung-khas/{tabungKhas} | - |
| PATCH | /finance/tabung-khas/{tabungKhas} | same as POST |
| DELETE | /finance/tabung-khas/{tabungKhas} | - |
| PATCH | /finance/tabung-khas/{tabungKhas}/status | aktif(boolean) |

## 5.5.4 Program Masjid
Permissions: program_masjid.view/create/update/delete

| Method | Endpoint | Request Schema |
|---|---|---|
| GET | /finance/program-masjid | Query: search, aktif, per_page, page |
| POST | /finance/program-masjid | nama_program(required,max150), aktif(boolean) |
| GET | /finance/program-masjid/{programMasjid} | - |
| PATCH | /finance/program-masjid/{programMasjid} | same as POST |
| DELETE | /finance/program-masjid/{programMasjid} | - |
| PATCH | /finance/program-masjid/{programMasjid}/status | aktif(boolean) |

---

## 5.6 Running Number API

Permissions: running_no.view, running_no.generate, running_no.update

| Method | Endpoint | Controller Method | Request Schema |
|---|---|---|---|
| GET | /finance/running-no | RunningNoController@index | Query: prefix, tahun, bulan, per_page, page |
| POST | /finance/running-no/generate | RunningNoController@generate | prefix(required alnum), tahun(required 2000-2100), bulan(required 1-12), id_masjid(nullable) |
| PATCH | /finance/running-no/{idMasjid}/{prefix}/{tahun}/{bulan} | RunningNoController@update | last_no(required integer >= 0) |

Response item fields:
- id_masjid, prefix, tahun, bulan, last_no

---

## 5.7 Reports API

Permissions: reports.view

| Method | Endpoint | Source Web Controller | Query Schema |
|---|---|---|---|
| GET | /finance/reports/buku-tunai | LaporanBukuTunaiController@generate | akaun_id(required), tarikh_mula, tarikh_tamat, baki_awal, id_masjid(superadmin) |
| GET | /finance/reports/jumaat | LaporanJumaatController@index | tahun, bulan, jenis_paparan, id_masjid(superadmin) |
| GET | /finance/reports/derma | LaporanDermaController@index | tarikh_dari, tarikh_hingga, jenis_paparan, id_masjid(superadmin) |
| GET | /finance/reports/belanja | LaporanBelanjaController@index | tarikh_dari, tarikh_hingga, jenis_paparan, kategori_id, akaun_id, status, id_masjid(superadmin) |
| GET | /finance/reports/penyata | LaporanPenyataController@index | jenis_penyata, tahun, bulan, id_masjid(superadmin) |
| GET | /finance/reports/tabung | LaporanTabungController@index | tarikh_dari, tarikh_hingga, id_masjid(superadmin) |

---

## 6. Resources/Transformers Checklist

Create API resources under app/Http/Resources/Api/Finance:

- AkaunResource
- HasilResource
- BelanjaResource
- PindahanAkaunResource
- SumberHasilResource
- KategoriBelanjaResource
- TabungKhasResource
- ProgramMasjidResource
- RunningNoResource

Checklist:
- [ ] Ensure fields are camelCase or snake_case consistently (follow existing API, currently snake_case)
- [ ] Include relation labels (example: akaun_name, kategori_name) where useful for mobile UI
- [ ] Hide internal fields not needed on mobile

---

## 7. Implementation Task Checklist

## Phase A: Scaffolding
- [x] Create finance API route group in routes/api.php
- [x] Create Api/Finance controllers
- [x] Add permission middleware per endpoint

## Phase B: CRUD Endpoints
- [x] Implement Akaun API (5 endpoints)
- [x] Implement Hasil API (6 endpoints)
- [x] Implement Belanja API (6 endpoints)
- [x] Implement Pindahan Akaun API (5 endpoints)
- [x] Implement Master Data APIs (24 endpoints incl. status toggles)
- [x] Implement Running No API (3 endpoints)

## Phase C: Reports
- [x] Implement 6 JSON report endpoints
- [ ] Implement optional export endpoints (PDF/Excel)

## Phase D: Validation and Security
- [ ] Reuse existing FormRequest rules where possible
- [ ] Enforce tenant scoping for non-Superadmin users
- [ ] Add policy checks for each resource

## Phase E: Testing
- [ ] Feature tests for each endpoint success path
- [ ] Validation failure tests (422)
- [ ] Unauthorized/forbidden tests (401/403)
- [ ] Cross-tenant isolation tests

## Phase F: Documentation
- [ ] Update API_DOCUMENTATION.md with finance section
- [x] Update API_QUICK_REFERENCE.md status from Planned to Implemented when ready
- [ ] Update Thunder Client collection with finance requests

---

## 8. Open Decisions (Confirm Before Build)

- [ ] Final endpoint prefix: /finance/* vs flat (/akaun, /hasil, etc.)
- [ ] Hasil payload style: single amaun vs split amaun_tunai + amaun_online
- [ ] Belanja approve workflow states: draft/submitted/approved/rejected final list
- [ ] Whether export endpoints are required in mobile API phase 1
- [ ] Whether receipt endpoint returns JSON only or downloadable PDF
