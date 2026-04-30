<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Services\LogAktivitiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BaucarController extends Controller
{
    public function __construct(private readonly LogAktivitiService $log) {}
    public function show(Request $request, int $belanja_id): View
    {
        $belanja = $this->resolveBelanja($request, $belanja_id);
        $baucarNo = $this->resolveBaucarNo($belanja);

        $verifyUrl = route('baucar.show', ['belanja_id' => $belanja->id]);
        $qrCode = QrCode::format('svg')->size(160)->margin(1)->generate($verifyUrl);

        $signatureImages = [
            'bendahari' => $this->resolveSignatureImageData($belanja->bendahariLulusOleh?->signature_path),
            'pengerusi' => $this->resolveSignatureImageData($belanja->pengerusiLulusOleh?->signature_path),
        ];

        return view('baucar.show', [
            'belanja'  => $belanja,
            'baucarNo' => $baucarNo,
            'qrCode'   => $qrCode,
            'verifyUrl' => $verifyUrl,
            'signatureImages' => $signatureImages,
        ]);
    }

    public function exportPdf(Request $request, int $belanja_id): Response
    {
        $belanja = $this->resolveBelanja($request, $belanja_id);
        $baucarNo = $this->resolveBaucarNo($belanja);

        $verifyUrl = route('baucar.show', ['belanja_id' => $belanja->id]);
        $qrCode = QrCode::format('svg')->size(160)->margin(1)->generate($verifyUrl);

        $signatureImages = [
            'bendahari' => $this->resolveSignatureImageData($belanja->bendahariLulusOleh?->signature_path),
            'pengerusi' => $this->resolveSignatureImageData($belanja->pengerusiLulusOleh?->signature_path),
        ];

        $pdf = Pdf::loadView('baucar.pdf', [
            'belanja'   => $belanja,
            'baucarNo'  => $baucarNo,
            'qrCode'    => $qrCode,
            'verifyUrl' => $verifyUrl,
            'signatureImages' => $signatureImages,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Baucar-' . $baucarNo . '.pdf');
    }

    public function approve(Request $request, int $belanja_id): \Illuminate\Http\RedirectResponse
    {
        $belanja = $this->resolveBelanja($request, $belanja_id);
        $actor = $request->user();

        if ($belanja->is_baucar_locked) {
            return redirect()->route('baucar.show', ['belanja_id' => $belanja->id])
                ->with('status_error', 'Baucar telah dikunci dan tidak boleh diubah.');
        }

        if (empty($actor->signature_path)) {
            $this->log->record(LogAktivitiService::JENIS_APPROVE, 'Baucar', 'Kelulusan Ditolak - Tiada Tandatangan', [
                'rujukan_id' => $belanja->id,
                'butiran'    => 'Percubaan meluluskan baucar ditolak kerana pengguna tiada tandatangan digital.',
            ], $request);

            return redirect()->route('baucar.show', ['belanja_id' => $belanja->id])
                ->with('status_error', 'Sila muat naik tandatangan digital pada profil sebelum meluluskan baucar.');
        }

        if ((int) $belanja->approval_step === 0) {
            $this->authorize('approveBendahari', $belanja);

            $belanja->update([
                'status'                => 'DRAF',
                'approval_step'         => 1,
                'bendahari_lulus_oleh'  => $actor->id,
                'bendahari_lulus_pada'  => now(),
                'bendahari_signature'   => $this->generateDigitalSignature($belanja, $actor->id, 'bendahari'),
                'dilulus_oleh'          => null,
                'tarikh_lulus'          => null,
                'is_baucar_locked'      => false,
                'locked_at'             => null,
                'locked_by'             => null,
                'ditolak_oleh'          => null,
                'tarikh_tolak'          => null,
                'catatan_tolak'         => null,
            ]);

            $this->log->record(LogAktivitiService::JENIS_APPROVE, 'Baucar', 'Lulus Bendahari', [
                'rujukan_id' => $belanja->id,
                'butiran'    => 'Baucar ' . ($belanja->no_baucar ?: '#' . $belanja->id) . ' telah disemak dan diluluskan oleh Bendahari.',
            ], $request);

            return redirect()->route('baucar.show', ['belanja_id' => $belanja->id])
                ->with('status', 'Semakan Bendahari selesai. Baucar dihantar untuk kelulusan Pengerusi.');
        }

        $this->authorize('approvePengerusi', $belanja);

        $belanja->update([
            'status'                => 'LULUS',
            'approval_step'         => 2,
            'pengerusi_lulus_oleh'  => $actor->id,
            'pengerusi_lulus_pada'  => now(),
            'pengerusi_signature'   => $this->generateDigitalSignature($belanja, $actor->id, 'pengerusi'),
            'dilulus_oleh'          => $actor->id,
            'tarikh_lulus'          => now(),
            'is_baucar_locked'      => true,
            'locked_at'             => now(),
            'locked_by'             => $actor->id,
            'ditolak_oleh'          => null,
            'tarikh_tolak'          => null,
            'catatan_tolak'         => null,
        ]);

        $this->log->record(LogAktivitiService::JENIS_APPROVE, 'Baucar', 'Lulus Pengerusi', [
            'rujukan_id' => $belanja->id,
            'butiran'    => 'Baucar ' . ($belanja->no_baucar ?: '#' . $belanja->id) . ' telah diluluskan oleh Pengerusi dan dikunci.',
        ], $request);

        return redirect()->route('baucar.show', ['belanja_id' => $belanja->id])
            ->with('status', 'Kelulusan Pengerusi selesai. Baucar rasmi dikunci.');
    }

    public function reject(Request $request, int $belanja_id): \Illuminate\Http\RedirectResponse
    {
        $belanja = $this->resolveBelanja($request, $belanja_id);
        $this->authorize('reject', $belanja);

        if ($belanja->is_baucar_locked) {
            return redirect()->route('baucar.show', ['belanja_id' => $belanja->id])
                ->with('status_error', 'Baucar telah dikunci dan tidak boleh ditolak.');
        }

        $request->validate([
            'catatan_tolak' => ['required', 'string', 'max:500'],
        ]);

        $belanja->update([
            'status'        => 'DRAF',
            'approval_step' => 0,
            'ditolak_oleh'  => $request->user()->id,
            'tarikh_tolak'  => now(),
            'catatan_tolak' => $request->input('catatan_tolak'),
            'dilulus_oleh'  => null,
            'tarikh_lulus'  => null,
            'bendahari_lulus_oleh' => null,
            'bendahari_lulus_pada' => null,
            'bendahari_signature'  => null,
            'pengerusi_lulus_oleh' => null,
            'pengerusi_lulus_pada' => null,
            'pengerusi_signature'  => null,
            'is_baucar_locked'     => false,
            'locked_at'            => null,
            'locked_by'            => null,
        ]);

        return redirect()->route('baucar.show', ['belanja_id' => $belanja->id])
            ->with('status_error', 'Baucar telah ditolak.');
    }

    private function generateDigitalSignature(Belanja $belanja, int $approverId, string $stage): string
    {
        $payload = implode('|', [
            $belanja->id,
            $belanja->no_baucar ?: 'pending',
            $approverId,
            $stage,
            now()->format('YmdHis'),
            Str::random(6),
        ]);

        return strtoupper(substr(hash_hmac('sha256', $payload, (string) config('app.key')), 0, 24));
    }

    private function resolveBelanja(Request $request, int $belanja_id): Belanja
    {
        $actor = $request->user();

        $query = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->with([
                'masjid:id,nama,alamat,daerah,negeri',
                'akaun:id,nama_akaun',
                'kategoriBelanja:id,nama_kategori',
                'createdBy:id,name',
                'dilulusOleh:id,name',
                'ditolakOleh:id,name',
                'bendahariLulusOleh:id,name,signature_path',
                'pengerusiLulusOleh:id,name,signature_path',
                'lockedBy:id,name',
            ])
            ->whereKey($belanja_id);

        if ($actor->peranan !== 'superadmin') {
            abort_if(empty($actor->id_masjid), 403, 'Unauthorized');
            $query->byMasjid((int) $actor->id_masjid);
        }

        return $query->firstOrFail();
    }

    private function resolveBaucarNo(Belanja $belanja): string
    {
        if ($belanja->no_baucar) {
            return $belanja->no_baucar;
        }

        $year = optional($belanja->tarikh)->format('Y') ?? now()->year;

        return 'BV-' . $year . '-' . str_pad((string) $belanja->id, 6, '0', STR_PAD_LEFT);
    }

    private function resolveSignatureImageData(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $raw = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }
}
