<?php

namespace UniSharp\Sms;

use Illuminate\Support\ServiceProvider;

/**
 * Class SettingServiceProvider.
 */
class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Sms', Sms::class);
    }
}
