<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Payment;

class MigrateAdminEarnings extends Command
{
    protected $signature = 'admin:migrate-earnings';
    protected $description = 'Move existing service fees into admin wallet';

    public function handle()
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->error('Admin not found');
            return;
        }

        // Total service fees
        $totalServiceFee = Payment::sum('service_fee');

        if ($totalServiceFee <= 0) {
            $this->warn('No earnings to migrate');
            return;
        }

        // Prevent duplicate migration
        if ($admin->admin_wallet > 0) {
            $this->warn('Admin wallet already has balance. Migration skipped.');
            return;
        }

        // Move funds
        $admin->admin_wallet = $totalServiceFee;
        $admin->save();

        $this->info("✅ Migration complete. Admin wallet funded with ₦{$totalServiceFee}");
    }
}