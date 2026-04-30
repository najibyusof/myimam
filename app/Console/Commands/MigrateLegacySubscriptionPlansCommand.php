<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;

class MigrateLegacySubscriptionPlansCommand extends Command
{
    protected $signature = 'subscriptions:migrate-plans {--dry-run : Preview changes without writing to database}';

    protected $description = 'Migrate and sync active legacy subscription_plans records into plans table.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $legacyPlans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        if ($legacyPlans->isEmpty()) {
            $this->warn('No active records found in subscription_plans.');
            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $unchanged = 0;

        foreach ($legacyPlans as $legacy) {
            $attributes = [
                'name' => $legacy->name,
            ];

            $values = [
                'price' => $legacy->price,
                'duration_days' => max(1, ((int) $legacy->duration_months) * 30),
                'features' => $legacy->features,
            ];

            $existingPlan = Plan::query()->where('name', $legacy->name)->first();

            if ($dryRun) {
                if (!$existingPlan) {
                    $created++;
                    $this->line("[CREATE] {$legacy->name}");
                    continue;
                }

                $wouldChange = ((float) $existingPlan->price !== (float) $values['price'])
                    || ((int) $existingPlan->duration_days !== (int) $values['duration_days'])
                    || (($existingPlan->features ?? null) != ($values['features'] ?? null));

                if ($wouldChange) {
                    $updated++;
                    $this->line("[UPDATE] {$legacy->name}");
                } else {
                    $unchanged++;
                    $this->line("[UNCHANGED] {$legacy->name}");
                }

                continue;
            }

            $plan = Plan::query()->updateOrCreate($attributes, $values);

            if (!$existingPlan) {
                $created++;
                $this->info("Created plan: {$plan->name}");
            } else {
                $wasChanged = ((float) $existingPlan->price !== (float) $plan->price)
                    || ((int) $existingPlan->duration_days !== (int) $plan->duration_days)
                    || (($existingPlan->features ?? null) != ($plan->features ?? null));

                if ($wasChanged) {
                    $updated++;
                    $this->line("Updated plan: {$plan->name}");
                } else {
                    $unchanged++;
                    $this->line("Unchanged plan: {$plan->name}");
                }
            }
        }

        $this->newLine();
        $this->info('Migration complete.');
        $this->line('Created: ' . $created);
        $this->line('Updated: ' . $updated);
        $this->line('Unchanged: ' . $unchanged);
        $this->line('Mode: ' . ($dryRun ? 'dry-run' : 'write'));

        return self::SUCCESS;
    }
}
