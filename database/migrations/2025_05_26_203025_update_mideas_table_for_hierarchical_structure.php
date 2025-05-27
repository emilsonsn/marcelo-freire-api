<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
    {
        Schema::table('mideas', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->enum('type', ['folder', 'media'])->default('media')->after('service_id');
            $table->string('media_type')->nullable()->after('type');
            $table->bigInteger('size')->nullable()->after('path');

            $table->foreign('parent_id')->references('id')->on('mideas')->onDelete('cascade');

            $table->string('path')->nullable()->change();
            $table->unsignedBigInteger('service_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('mideas', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'type', 'media_type', 'size']);

            $table->string('path')->nullable(false)->change();
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
