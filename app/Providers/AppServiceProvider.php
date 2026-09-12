<?php

namespace App\Providers;

use App\Support\ProductionSecurityBaseline;
use Cloudinary\Cloudinary;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Cloudinary::class, function (): Cloudinary {
            $cloudinaryUrl = config('cloudinary.url');

            if (! is_string($cloudinaryUrl) || trim($cloudinaryUrl) === '') {
                throw new RuntimeException(
                    'Cloudinary is not configured. Set CLOUDINARY_URL before using Cloudinary services.'
                );
            }

            return new Cloudinary($cloudinaryUrl);
        });
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(env('API_RATE_LIMIT_PER_MINUTE', 120))
                ->by($request->user()?->id ?: $request->ip());
        });

        // API documentation (Scramble) is public — open source project, docs should be viewable
        // by anyone. Protect via SCRAMBLE_DOCS_TOKEN env if desired (RestrictedDocsAccess).
        \Illuminate\Support\Facades\Gate::define('viewApiDocs', fn () => true);

        $issues = ProductionSecurityBaseline::issues();

        if ($issues !== []) {
            Log::warning('Production security baseline check failed.', [
                'issues' => $issues,
            ]);
        }
    }
}
