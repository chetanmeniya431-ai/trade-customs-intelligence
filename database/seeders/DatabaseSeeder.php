<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Services\DocumentCheckService;
use App\Services\SignalsEngineService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            ClientsSeeder::class,
            UsersSeeder::class,
            DocumentRequirementsSeeder::class,
            SignalsSeeder::class,
            TariffDocumentsSeeder::class,
            ShipmentsSeeder::class,
            HsLookupsSeeder::class,
        ]);

        // Completeness findings depend on the full checklist + upload state
        // that ShipmentsSeeder just created, so run it after all shipments exist.
        $checkService = app(DocumentCheckService::class);
        Shipment::all()->each(fn (Shipment $s) => $checkService->checkCompleteness($s));

        // Fires the 10 seeded signals against the now-complete demo dataset —
        // the same code path routes/console.php schedules hourly in production.
        $fired = app(SignalsEngineService::class)->runAll();

        $this->command?->info("Seeding complete. Signals engine fired {$fired} signal event(s).");
    }
}
