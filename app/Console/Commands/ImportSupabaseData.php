<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ActiveBreak;
use App\Models\BreakHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportSupabaseData extends Command
{
    protected $signature = 'import:supabase';
    protected $description = 'Import data from Supabase REST API into local SQLite';

    private string $supabaseUrl = 'https://pnleinprsvaijfkvxfyi.supabase.co';
    private string $apikey = 'sb_publishable_eaj3dAdz6t5e1FOjT5ySZA_jwVNt3_c';

    public function handle(): int
    {
        $this->info('Fetching data from Supabase...');

        $employees = $this->fetchData('/rest/v1/employees');
        if (!$employees) {
            $this->error('Failed to fetch employees');
            return 1;
        }
        $this->info('Fetched ' . count($employees) . ' employees');

        $activeBreaks = $this->fetchData('/rest/v1/active_breaks');
        $this->info('Fetched ' . count($activeBreaks) . ' active breaks');

        $breakHistory = $this->fetchData('/rest/v1/break_history?order=started_at.desc&limit=1000');
        $this->info('Fetched ' . count($breakHistory) . ' break history records');

        DB::transaction(function () use ($employees, $activeBreaks, $breakHistory) {
            $this->importUsers($employees);
            $this->importActiveBreaks($activeBreaks);
            $this->importBreakHistory($breakHistory);
        });

        $this->info('Import completed successfully!');
        return 0;
    }

    private function fetchData(string $endpoint): ?array
    {
        $ch = curl_init($this->supabaseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->apikey,
                'Authorization: Bearer ' . $this->apikey,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return json_decode($response, true);
    }

    private function importUsers(array $employees): void
    {
        $bar = $this->output->createProgressBar(count($employees));
        $bar->start();

        $emailToId = [];

        foreach ($employees as $emp) {
            $user = User::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['full_name'],
                    'password' => Hash::make($emp['password'] ?? 'password123'),
                    'role' => $emp['role'] ?? 'agent',
                    'department' => $emp['department'] ?? null,
                    'tl_email' => $emp['tl_email'] ?? null,
                    'email_verified_at' => now(),
                ]
            );

            $emailToId[$emp['email']] = $user->id;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported ' . count($employees) . ' users');

        file_put_contents(storage_path('app/email_to_id.json'), json_encode($emailToId));
    }

    private function importActiveBreaks(array $activeBreaks): void
    {
        $emailToId = json_decode(file_get_contents(storage_path('app/email_to_id.json')), true);

        $bar = $this->output->createProgressBar(count($activeBreaks));
        $bar->start();

        foreach ($activeBreaks as $break) {
            $userId = $emailToId[$break['employee_email']] ?? null;
            if (!$userId) {
                $bar->advance();
                continue;
            }

            ActiveBreak::create([
                'id' => Str::uuid()->toString(),
                'break_id' => $break['break_id'],
                'user_id' => $userId,
                'user_name' => $break['employee_name'],
                'user_email' => $break['employee_email'],
                'department' => $break['department'] ?? null,
                'tl_email' => $break['tl_email'] ?? null,
                'break_type' => $break['break_type'],
                'break_category' => $break['break_category'] ?? 'break',
                'break_label' => $break['break_label'],
                'allowed_minutes' => $break['allowed_minutes'],
                'started_at' => $break['started_at'],
                'expected_end_at' => $break['expected_end_at'],
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported ' . count($activeBreaks) . ' active breaks');
    }

    private function importBreakHistory(array $history): void
    {
        $emailToId = json_decode(file_get_contents(storage_path('app/email_to_id.json')), true);

        $bar = $this->output->createProgressBar(count($history));
        $bar->start();

        $imported = 0;
        foreach ($history as $record) {
            $userId = $emailToId[$record['employee_email']] ?? null;
            if (!$userId) {
                $bar->advance();
                continue;
            }

            BreakHistory::create([
                'id' => Str::uuid()->toString(),
                'break_id' => $record['break_id'],
                'user_id' => $userId,
                'user_name' => $record['employee_name'],
                'user_email' => $record['employee_email'],
                'department' => $record['department'] ?? null,
                'tl_email' => $record['tl_email'] ?? null,
                'break_type' => $record['break_type'],
                'break_category' => $record['break_category'] ?? 'break',
                'break_label' => $record['break_label'],
                'allowed_minutes' => $record['allowed_minutes'],
                'started_at' => $record['started_at'],
                'ended_at' => $record['ended_at'],
                'duration_minutes' => $record['duration_minutes'] ?? 0,
                'duration_seconds' => $record['duration_seconds'] ?? 0,
                'over_minutes' => $record['over_minutes'] ?? 0,
            ]);
            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported {$imported} break history records");
    }
}
