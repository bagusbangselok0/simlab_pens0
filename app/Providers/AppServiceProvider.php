<?php

namespace App\Providers;

use App\Models\LogbookPertemuan;
use App\Models\LogbookPraktikum;
use App\Models\PeminjamanAlat;
use App\Models\PeminjamanLab;
use App\Policies\LogbookPertemuanPolicy;
use App\Policies\LogbookPraktikumPolicy;
use App\Policies\PeminjamanAlatPolicy;
use App\Policies\PeminjamanLabPolicy;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        Carbon::setLocale('id');

        Gate::policy(PeminjamanLab::class, PeminjamanLabPolicy::class);
        Gate::policy(PeminjamanAlat::class, PeminjamanAlatPolicy::class);
        Gate::policy(LogbookPraktikum::class, LogbookPraktikumPolicy::class);
        Gate::policy(LogbookPertemuan::class, LogbookPertemuanPolicy::class);

        RateLimiter::for('public-logbook-sign', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));
            return [
                Limit::perMinute(5)->by('login:' . $email . '|' . $request->ip()),
                Limit::perMinute(15)->by('login-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by('register:' . $request->ip());
        });

        RateLimiter::for('signature-upload', function (Request $request) {
            $key = $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();
            return Limit::perMinute(6)->by('sig-upload:' . $key);
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $actor = $request->user()?->id
                ? 'user:' . $request->user()->id
                : strtolower(trim((string) $request->input('email', ''))) . '|' . $request->ip();
            return [
                Limit::perMinute(5)->by('pw-reset:' . $actor),
                Limit::perMinute(10)->by('pw-reset-ip:' . $request->ip()),
            ];
        });

        RateLimiter::for('excel-import', function (Request $request) {
            $key = $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();
            return Limit::perMinute(10)->by('excel-import:' . $key);
        });

        // Paksa HTTPS jika diakses via Cloudflare / production
        if (app()->environment('production') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }
}
