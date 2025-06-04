<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's check what columns exist
        $existingColumns = Schema::getColumnListing('events');
        
        Schema::table('events', function (Blueprint $table) use ($existingColumns) {
            // Contact information
            if (!in_array('contact_email', $existingColumns)) {
                $table->string('contact_email')->nullable();
            }
            
            if (!in_array('contact_phone', $existingColumns)) {
                $table->string('contact_phone', 20)->nullable();
            }
            
            // Event management
            if (!in_array('max_participants', $existingColumns)) {
                $table->integer('max_participants')->nullable();
            }
            
            // Add capacity column
            if (!in_array('capacity', $existingColumns)) {
                $table->integer('capacity')->default(100);
            }
            
            // REMOVED organizer_type from here - will be handled by separate migration as string
            // This was causing the conflict because we defined it as unsignedBigInteger here
            // but the other migration defines it as string
            
            if (!in_array('registration_fee', $existingColumns)) {
                $table->decimal('registration_fee', 10, 2)->nullable();
            }
            
            if (!in_array('location', $existingColumns)) {
                $table->string('location')->nullable();
            }
            
            if (!in_array('category', $existingColumns)) {
                $table->string('category')->nullable();
            }
            
            // Additional info
            if (!in_array('additional_info', $existingColumns)) {
                $table->text('additional_info')->nullable();
            }
            
            // Image handling
            if (!in_array('image', $existingColumns)) {
                $table->string('image')->nullable();
            }
            
            if (!in_array('image_url', $existingColumns)) {
                $table->string('image_url')->nullable();
            }
            
            // Status fields
            if (!in_array('is_active', $existingColumns)) {
                $table->boolean('is_active')->default(true);
            }
            
            if (!in_array('is_featured', $existingColumns)) {
                $table->boolean('is_featured')->default(false);
            }
        });
        
        // Add user relationships in a separate step to avoid foreign key issues
        Schema::table('events', function (Blueprint $table) use ($existingColumns) {
            if (!in_array('user_id', $existingColumns)) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
            
            if (!in_array('created_by', $existingColumns)) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
        });
        
        // Add foreign keys after columns are created
        Schema::table('events', function (Blueprint $table) use ($existingColumns) {
            // Foreign key for user_id
            if (!in_array('user_id', $existingColumns) && Schema::hasTable('users')) {
                try {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                } catch (Exception $e) {
                    // Foreign key might already exist or users table might not exist
                }
            }
            
            // Foreign key for created_by
            if (!in_array('created_by', $existingColumns) && Schema::hasTable('users')) {
                try {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                } catch (Exception $e) {
                    // Foreign key might already exist or users table might not exist
                }
            }
            
            // REMOVED organizer_type foreign key - since organizer_type is now a string field
            // not a foreign key to users table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop foreign keys first
            try {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['created_by']);
                // Removed organizer_type foreign key drop since we don't create it anymore
            } catch (Exception $e) {
                // Foreign keys might not exist
            }
            
            // Drop columns - removed organizer_type from here since it's handled by another migration
            $columnsToCheck = [
                'contact_email', 'contact_phone', 'max_participants', 'capacity', 
                'registration_fee', 'location', 'category', 
                'additional_info', 'image', 'image_url', 'is_active', 'is_featured', 
                'user_id', 'created_by'
            ];
            
            $existingColumns = Schema::getColumnListing('events');
            $columnsToDrop = array_intersect($columnsToCheck, $existingColumns);
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};