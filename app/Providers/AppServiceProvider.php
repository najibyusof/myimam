<?php

namespace App\Providers;

use App\Models\Masjid;
use App\Models\User;
use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\SumberHasil;
use App\Models\KategoriBelanja;
use App\Models\PindahanAkaun;
use App\Models\ProgramMasjid;
use App\Models\RunningNo;
use App\Models\LogAktiviti;
use App\Models\TabungKhas;
use App\Policies\AkaunPolicy;
use App\Policies\BelanjaPolicy;
use App\Policies\HasilPolicy;
use App\Policies\MasjidPolicy;
use App\Policies\KategoriBelanjaPolicy;
use App\Policies\PindahanAkaunPolicy;
use App\Policies\ProgramMasjidPolicy;
use App\Policies\RunningNoPolicy;
use App\Policies\LogAktivitiPolicy;
use App\Policies\SumberHasilPolicy;
use App\Policies\TabungKhasPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user) {
            return $user->peranan === 'superadmin' ? true : null;
        });

        Gate::policy(Akaun::class, AkaunPolicy::class);
        Gate::policy(Belanja::class, BelanjaPolicy::class);
        Gate::policy(Hasil::class, HasilPolicy::class);
        Gate::policy(SumberHasil::class, SumberHasilPolicy::class);
        Gate::policy(KategoriBelanja::class, KategoriBelanjaPolicy::class);
        Gate::policy(PindahanAkaun::class, PindahanAkaunPolicy::class);
        Gate::policy(ProgramMasjid::class, ProgramMasjidPolicy::class);
        Gate::policy(RunningNo::class, RunningNoPolicy::class);
        Gate::policy(TabungKhas::class, TabungKhasPolicy::class);
        Gate::policy(LogAktiviti::class, LogAktivitiPolicy::class);
        Gate::policy(Masjid::class, MasjidPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
