<?php

namespace App\Console\Commands;

use App\Models\AttendanceMachine;
use App\Models\MasterOfficeLocation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateMachineLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:machine-log {sn=SIMULATOR001} {pin=1001}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate ADMS machine handshake and log upload';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sn = $this->argument('sn');
        $pin = $this->argument('pin');
        $location = MasterOfficeLocation::first();

        if (!$location) {
            $this->error("No office location found. Please create one in the dashboard first.");
            return;
        }

        // 1. Ensure machine exists
        $machine = AttendanceMachine::firstOrCreate(
            ['serial_number' => $sn],
            [
                'name' => 'Mesin Simulator',
                'master_office_location_id' => $location->id,
                'status' => 'offline'
            ]
        );

        $this->info("Simulating ADMS for SN: $sn, PIN: $pin");
        
        // Since we are running in the same app, we can either use Http call to ourselves 
        // (if artisan serve is running) or call the controller directly.
        // Let's use Http to test the routes.
        $baseUrl = "http://127.0.0.1:8000/api/iclock/cdata";

        $this->comment("Step 1: Handshake (GET)");
        try {
            $response = Http::get($baseUrl, ['SN' => $sn]);
            $this->line("Response: " . $response->body() . " (" . $response->status() . ")");
        } catch (\Exception $e) {
            $this->error("Failed to connect to $baseUrl. Is 'php artisan serve' running?");
            return;
        }

        $this->comment("Step 2: Upload Logs (POST)");
        $now = now()->format('Y-m-d');
        $logData = "$pin\t$now 08:30:15\t0\t1\t0\t0\n";
        $logData .= "$pin\t$now 17:15:20\t1\t1\t0\t0\n";

        $response = Http::withBody($logData, 'text/plain')
            ->post($baseUrl . "?SN=$sn&table=ATTLOG");

        $this->line("Response: " . $response->body() . " (" . $response->status() . ")");
        
        $this->info("Success! Check the 'Mesin Absensi' and 'Log Mesin Absensi' resources in the Employee Panel.");
    }
}
