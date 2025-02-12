<?php

namespace App\Providers;

use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
    * Register any application services.
    */
    public function register(): void
    {

    }

    /**
    * Bootstrap any application services.
    */
    private $maxAttempts = 3;
    private $decayMinutes = 1;

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);


        Event::listen('Illuminate\Auth\Events\Login', function ($event) {
            Log::info('Login attempt by ' . request()->ip() . ' for ' . $event->user->{Fortify::username()});
        });

        Event::listen('Illuminate\Auth\Events\Registered', function ($event) {
            Log::info('New user registered: ' . $event->user->{Fortify::username()} . ' from ' . request()->ip());
        });

        Event::listen('Illuminate\Auth\Events\Logout', function ($event) {
            Log::info('User ' . Auth::user()->{Fortify::username()} . ' logged out from ' . request()->ip());
        });


        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            if(Cache::has($throttleKey . '_block')){
                // return redirect()->route('homepage')->with('error', "Too many requests. Please try again in $this->decayMinutes minutes.");
                return response()->json(['error' => 'Too many requests. Please try again in ' . $this->decayMinutes . ' minutes.'], 429);
            }

            if (Cache::has($throttleKey)) {
                $attempts = Cache::increment($throttleKey);
                if ($attempts > $this->maxAttempts) {

                    Cache::put($throttleKey . '_block', true, $this->decayMinutes * 60);
                    Log::warning("IP  $throttleKey has been blocked for $this->decayMinutes minute(s) due to too many attempts.");
                    // return redirect()->route('homepage')->with('error', "Too many requests. Please try again in $this->decayMinutes minutes.");
                    return response()->json(['error' => "IP  $throttleKey has been blocked for $this->decayMinutes minute(s) due to too many attempts."], 429);
                }
            } else {
                Cache::put($throttleKey, 1, $this->decayMinutes * 60);
            }
            return Limit::perMinute($this->maxAttempts)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });


        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });
    }
}
