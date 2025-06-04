<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('event_categories', function (Blueprint $table) {
            // Periksa apakah kolom deleted_at belum ada
            if (!Schema::hasColumn('event_categories', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('event_categories', function (Blueprint $table) {
            // Periksa apakah kolom deleted_at ada sebelum menghapusnya
            if (Schema::hasColumn('event_categories', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};