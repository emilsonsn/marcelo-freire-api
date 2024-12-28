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
        Schema::table('clients', function (Blueprint $table) {                        
            $table->string('surname')
                ->nullable()
                ->after('name');

            $table->string('url')
                ->nullable()
                ->after('phone');

            $table->enum('gender', ['Male', 'Female'])
                ->nullable()
                ->after('url');
        });
    }            
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('gender');
            $table->dropColumn('surname');
        });
    }
};
