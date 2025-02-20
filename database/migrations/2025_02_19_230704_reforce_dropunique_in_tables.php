<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->hasUniqueIndex('clients', 'email')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        }
    
        if ($this->hasUniqueIndex('clients', 'cpf_cnpj')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropUnique(['cpf_cnpj']);
            });
        }
    
        if ($this->hasUniqueIndex('users', 'cpf_cnpj')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['cpf_cnpj']);
            });
        }
    }
    
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->unique()->change();
            $table->string('cpf_cnpj')->unique()->change();
        });
    
        Schema::table('users', function (Blueprint $table) {            
            $table->string('cpf_cnpj')->unique()->change();
        });
    }
        
    private function hasUniqueIndex(string $table, string $column): bool
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = ? AND index_name = ?
        ", [$database, $table, $column]);
    
        return !empty($result) && $result[0]->count > 0;
    }
};
