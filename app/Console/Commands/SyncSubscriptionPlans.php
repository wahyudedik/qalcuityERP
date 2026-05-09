<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;

class SyncSubscriptionPlans extends Command
{
    protected $signature = 'plans:sync {--force : Overwrite existing plans}';

    protected $description = 'Sync subscription plans from defaultPlans() to database';

    public function handle(): int
    {
        $plans = SubscriptionPlan::defaultPlans();
        $synced = 0;

        foreach ($plans as $plan) {
            if ($this->option('force')) {
                SubscriptionPlan::updateOrCreate(
                    ['slug' => $plan['slug']],
                    $plan + ['is_active' => true]
                );
                $synced++;
            } else {
                $existing = SubscriptionPlan::where('slug', $plan['slug'])->first();
                if (! $existing) {
                    SubscriptionPlan::create($plan + ['is_active' => true]);
                    $synced++;
                    $this->line("Created: {$plan['name']}");
                } else {
                    $this->line("Skipped (exists): {$plan['name']}");
                }
            }
        }

        $this->info("Synced {$synced} plans.");

        return self::SUCCESS;
    }
}
