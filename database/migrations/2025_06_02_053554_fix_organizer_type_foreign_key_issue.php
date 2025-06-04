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
        // Check if foreign key exists before trying to drop it
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'events' 
            AND COLUMN_NAME = 'organizer_type' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        Schema::table('events', function (Blueprint $table) use ($foreignKeys) {
            // Only try to drop foreign key if it actually exists
            if (!empty($foreignKeys)) {
                foreach ($foreignKeys as $fk) {
                    try {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    } catch (Exception $e) {
                        // Continue if failed
                    }
                }
            }
            
            // Change organizer_type from integer to string
            if (Schema::hasColumn('events', 'organizer_type')) {
                // Get column info to check current type
                $columnType = DB::select("
                    SELECT DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'events' 
                    AND COLUMN_NAME = 'organizer_type'
                ")[0] ?? null;
                
                // Only change if it's currently not a string type
                if ($columnType && !in_array(strtolower($columnType->DATA_TYPE), ['varchar', 'char', 'text'])) {
                    $table->string('organizer_type')->default('individual')->change();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revert back to integer if needed (though not recommended)
            if (Schema::hasColumn('events', 'organizer_type')) {
                $table->unsignedBigInteger('organizer_type')->nullable()->change();
            }
        });
    }
};