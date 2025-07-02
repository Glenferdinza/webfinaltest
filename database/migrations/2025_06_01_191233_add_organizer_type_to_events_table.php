<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // First, check if organizer_type column exists and is of wrong type
            if (Schema::hasColumn('events', 'organizer_type')) {
                // If it exists, we need to drop foreign key first, then change the column type
                try {
                    // Drop the foreign key constraint first
                    $table->dropForeign(['organizer_type']);
                } catch (Exception $e) {
                    // Foreign key might not exist, that's okay
                }
                
                // Change the column type from integer to string
                $table->string('organizer_type')->default('individual')->change();
            } else {
                // If column doesn't exist, create it as string
                $table->string('organizer_type')->default('individual')->after('additional_info');
            }
            
            // Add organizer_name if it doesn't exist
            if (!Schema::hasColumn('events', 'organizer_name')) {
                $table->string('organizer_name')->nullable()->after('organizer_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if foreign key exists before trying to drop it
        $foreignKeyExists = false;
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'events' 
                AND COLUMN_NAME = 'organizer_type' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            $foreignKeyExists = !empty($foreignKeys);
        } catch (Exception $e) {
            // Error checking, assume it doesn't exist
        }
        
        Schema::table('events', function (Blueprint $table) use ($foreignKeyExists) {
            // Only drop foreign key if it actually exists
            if ($foreignKeyExists) {
                try {
                    $table->dropForeign(['organizer_type']);
                } catch (Exception $e) {
                    // Continue if failed
                }
            }
            
            // Drop columns
            if (Schema::hasColumn('events', 'organizer_type')) {
                $table->dropColumn('organizer_type');
            }
            if (Schema::hasColumn('events', 'organizer_name')) {
                $table->dropColumn('organizer_name');
            }
        });
    }
};