<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BelanjaStoreRequest;
use App\Http\Requests\Admin\BelanjaUpdateRequest;
use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Services\BelanjaManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BelanjaManagementController extends Controller
{
    public function __construct(private readonly BelanjaManagementService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Belanja::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;
        $status = (string) $request->query('status', 'all');
        $baucarId = (int) $request->query('baucar_id', 0);

        $query = Belanja::query()
            ->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
            ->notDeleted()
            ->with(['akaun:id,nama_akaun', 'kategoriBelanja:id,nama_kategori', 'baucar:id,no_baucar'])
            ->latest('tarikh')
            ->latest('id');

        if ($status === 'draft') {
            $query->draft();
        } elseif ($status === 'submitted') {
            $query->where('status', 'LULUS');
        }

        if ($baucarId > 0) {
            $query->forBaucar($baucarId);
        }

        $belanja = $query->paginate(15)->withQueryString();

        $baseStats = Belanja::query()
            ->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
            ->notDeleted();

        $stats = [
            'total' => (clone $baseStats)->count(),
            'draft' => (clone $baseStats)->draft()->count(),
            'submitted' => (clone $baseStats)->where('status', 'LULUS')->count(),
            'linked_baucar' => (clone $baseStats)->whereNotNull('id_baucar')->count(),
        ];

        $baucarOptions = BaucarBayaran::query()
            ->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
            ->orderByDesc('tarikh')
            ->orderBy('no_baucar')
            ->get(['id', 'no_baucar']);

        return view('admin.belanja.index', [
            'belanja' => $belanja,
            'stats' => $stats,
            'status' => $status,
            'baucarId' => $baucarId,
            'baucarOptions' => $baucarOptions,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Belanja::class);

        return view('admin.belanja.create', $this->formData($request));
    }

    public function store(BelanjaStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Belanja::class);

        $data = $request->validated();

        if ($request->hasFile('bukti_fail')) {
            $data['bukti_fail'] = $request->file('bukti_fail')->store('belanja-bukti', 'public');
        } elseif ($request->hasFile('bukti_fail_camera')) {
            $data['bukti_fail'] = $request->file('bukti_fail_camera')->store('belanja-bukti', 'public');
        }

        $belanja = $this->service->create($request->user(), $data);

        return redirect()
            ->route('admin.belanja.edit', $belanja)
            ->with('status', 'Rekod belanja berjaya disimpan.');
    }

    public function edit(Request $request, Belanja $belanja): View
    {
        $this->authorize('update', $belanja);

        return view('admin.belanja.edit', $this->formData($request, $belanja) + [
            'belanjaRecord' => $belanja,
        ]);
    }

    public function update(BelanjaUpdateRequest $request, Belanja $belanja): RedirectResponse
    {
        $this->authorize('update', $belanja);

        $data = $request->validated();

        if ($request->boolean('remove_bukti_fail') && $belanja->bukti_fail) {
            Storage::disk('public')->delete($belanja->bukti_fail);
            $data['bukti_fail'] = null;
        } elseif ($request->hasFile('bukti_fail')) {
            if ($belanja->bukti_fail) {
                Storage::disk('public')->delete($belanja->bukti_fail);
            }
            $data['bukti_fail'] = $request->file('bukti_fail')->store('belanja-bukti', 'public');
        } elseif ($request->hasFile('bukti_fail_camera')) {
            if ($belanja->bukti_fail) {
                Storage::disk('public')->delete($belanja->bukti_fail);
            }
            $data['bukti_fail'] = $request->file('bukti_fail_camera')->store('belanja-bukti', 'public');
        } else {
            $data['bukti_fail'] = $belanja->bukti_fail;
        }

        $this->service->update($belanja, $request->user(), $data);

        return redirect()
            ->route('admin.belanja.edit', $belanja)
            ->with('status', 'Rekod belanja berjaya dikemaskini.');
    }

    public function destroy(Request $request, Belanja $belanja): RedirectResponse
    {
        $this->authorize('delete', $belanja);

        $this->service->softDelete($belanja, $request->user());

        return redirect()
            ->route('admin.belanja.index')
            ->with('status', 'Rekod belanja dipindahkan keluar daripada senarai aktif.');
    }

    public function deleteAttachment(Belanja $belanja): JsonResponse
    {
        $this->authorize('update', $belanja);

        if ($belanja->bukti_fail) {
            Storage::disk('public')->delete($belanja->bukti_fail);
            $belanja->update(['bukti_fail' => null]);
        }

        return response()->json(['success' => true], 200);
    }

    private function formData(Request $request, ?Belanja $belanja = null): array
    {
        $selectedMasjidId = (int) old('id_masjid', $belanja?->id_masjid ?? $request->user()->id_masjid);
        $masjidScope = $request->user()->peranan === 'superadmin' ? null : $request->user()->id_masjid;

        $scope = fn($builder) => $builder
            ->when($selectedMasjidId > 0, fn($query) => $query->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn($query) => $query->byMasjid($masjidScope));

        return [
            'masjidOptions' => $request->user()->peranan === 'superadmin'
                ? Masjid::query()->orderBy('nama')->get(['id', 'nama'])
                : Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']),
            'akaunOptions' => Akaun::query()->tap($scope)->aktif()->orderBy('nama_akaun')->get(['id', 'nama_akaun']),
            'kategoriOptions' => KategoriBelanja::query()->tap($scope)->aktif()->orderBy('nama_kategori')->get(['id', 'nama_kategori']),
            'baucarOptions' => BaucarBayaran::query()->tap($scope)->orderByDesc('tarikh')->orderBy('no_baucar')->get(['id', 'no_baucar']),
        ];
    }
}
