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
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
            $table->string('cpf_cnpj')->nullable()->change();
            $table->string('phone')->nullable()->change();
            // $table->string('function')->nullable()->after('cpf_cnpj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
            $table->string('cpf_cnpj')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->dropColumn('function');
        });
    }
};
