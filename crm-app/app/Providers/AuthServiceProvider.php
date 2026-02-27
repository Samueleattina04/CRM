<?php
namespace App\Providers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Customer::class => CustomerPolicy::class,
        Activity::class => ActivityPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void {
        $this->registerPolicies();
    }
}
