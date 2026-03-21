<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: ubah enum untuk tambah nilai kasir dan gudang
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','manager','staff','kasir','gudang') NOT NULL DEFAULT 'staff'");
    }

    public function down(): void
    {
        // Hapus user dengan role baru sebelum rollback enum
        DB::statement("UPDATE users SET role = 'staff' WHERE role IN ('kasir','gudang')");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','manager','staff') NOT NULL DEFAULT 'staff'");
    }
};
