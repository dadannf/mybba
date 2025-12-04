<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Baca file SQL
        $sqlFile = database_path('backups/dbsekolah.sql');
        
        if (!file_exists($sqlFile)) {
            throw new Exception("File SQL tidak ditemukan: {$sqlFile}");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Eksekusi SQL
        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS pembayaran');
        DB::statement('DROP TABLE IF EXISTS informasi');
        DB::statement('DROP TABLE IF EXISTS keuangan');
        DB::statement('DROP TABLE IF EXISTS siswa');
        DB::statement('DROP TABLE IF EXISTS users');
    }
};
