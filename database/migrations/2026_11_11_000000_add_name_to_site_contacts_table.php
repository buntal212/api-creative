<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah nama kontak tanpa menghapus nomor kontak yang sudah tersimpan.
     */
    public function up(): void
    {
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->after('id');
        });

        DB::table('site_contacts')->whereNull('name')->update([
            'name' => 'Kontak Creative',
        ]);
    }

    public function down(): void
    {
        Schema::table('site_contacts', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
