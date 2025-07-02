<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckEventsTable extends Command
{
    protected $signature = 'events:check-table';
    protected $description = 'Check the current structure of events table';

    public function handle()
    {
        $this->info('Checking events table structure...');
        
        if (!Schema::hasTable('events')) {
            $this->error('Events table does not exist!');
            return;
        }
        
        $columns = Schema::getColumnListing('events');
        
        $this->info('Current columns in events table:');
        foreach ($columns as $column) {
            $this->line("- $column");
        }
        
        $this->info("\nRequired columns for full functionality:");
        $requiredColumns = [
            'id', 'title', 'description', 'start_date', 'end_date',
            'contact_email', 'contact_phone', 'max_participants', 
            'registration_fee', 'location', 'category', 'additional_info',
            'image', 'image_url', 'is_active', 'is_featured',
            'user_id', 'created_by', 'created_at', 'updated_at'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (empty($missingColumns)) {
            $this->success('✓ All required columns are present!');
        } else {
            $this->warn('Missing columns:');
            foreach ($missingColumns as $missing) {
                $this->line("- $missing");
            }
        }
        
        // Show table structure
        $this->info("\nFull table structure:");
        try {
            $tableInfo = DB::select("DESCRIBE events");
            $this->table(
                ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'],
                array_map(function($row) {
                    return (array) $row;
                }, $tableInfo)
            );
        } catch (Exception $e) {
            $this->error("Could not retrieve table structure: " . $e->getMessage());
        }
    }
}