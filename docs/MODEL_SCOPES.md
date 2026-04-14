# Model Scopes and Query Examples

The following examples use local scopes added to your domain models.

## User

- Active users in one masjid:
    - `User::query()->active()->byMasjid($idMasjid)->get();`
- All admins:
    - `User::query()->role('admin')->get();`

## Akaun

- Active cash accounts for a masjid:
    - `Akaun::query()->byMasjid($idMasjid)->aktif()->tunai()->get();`
- Accounts by type:
    - `Akaun::query()->byMasjid($idMasjid)->jenis('bank')->get();`

## SumberHasil and KategoriBelanja

- Active income sources:
    - `SumberHasil::query()->byMasjid($idMasjid)->aktif()->get();`
- Active expense categories:
    - `KategoriBelanja::query()->byMasjid($idMasjid)->aktif()->get();`

## TabungKhas and ProgramMasjid

- Active special funds:
    - `TabungKhas::query()->byMasjid($idMasjid)->aktif()->get();`
- Active mosque programs:
    - `ProgramMasjid::query()->byMasjid($idMasjid)->aktif()->get();`

## Hasil

- Income for period and account:
    - `Hasil::query()->byMasjid($idMasjid)->betweenDates($from, $to)->byAkaun($idAkaun)->get();`
- Friday collections only:
    - `Hasil::query()->byMasjid($idMasjid)->jumaat()->betweenDates($from, $to)->get();`

## Belanja

- Draft expense list:
    - `Belanja::query()->byMasjid($idMasjid)->notDeleted()->draft()->betweenDates($from, $to)->get();`
- Expense items for one voucher:
    - `Belanja::query()->byMasjid($idMasjid)->notDeleted()->forBaucar($idBaucar)->get();`

## BaucarBayaran

- Pending vouchers:
    - `BaucarBayaran::query()->byMasjid($idMasjid)->draft()->get();`
- Approved vouchers by period:
    - `BaucarBayaran::query()->byMasjid($idMasjid)->approved()->betweenDates($from, $to)->get();`

## LogAktiviti

- Audit trail by type and date:
    - `LogAktiviti::query()->byMasjid($idMasjid)->jenis('LOGIN_OK')->betweenCreatedAt($fromDateTime, $toDateTime)->get();`

## PindahanAkaun

- Transfers touching one account in period:
    - `PindahanAkaun::query()->byMasjid($idMasjid)->forAkaun($idAkaun)->betweenDates($from, $to)->get();`

## RunningNo

- Fetch current running number row:
    - `RunningNo::query()->forPeriod($idMasjid, 'RMT', $tahun, $bulan)->first();`
