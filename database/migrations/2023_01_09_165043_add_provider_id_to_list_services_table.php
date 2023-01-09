<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('list_services', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('provider_id');

            $table->foreign('provider_id')
                    ->references('id')
                    ->on('providers')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('list_services', function (Blueprint $table) {
            //
        });
    }
};
