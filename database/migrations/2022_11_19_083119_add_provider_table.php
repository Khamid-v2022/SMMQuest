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
        //
        Schema::table('providers', function (Blueprint $table) {
            $table->tinyInteger('is_activated')->nullable()->default(0);
            $table->unsignedBigInteger('request_by')->nullable();
            $table->timestamp('activated_at')->nullable();
            
            $table->foreign('request_by')
                ->references('id')
                ->on('users')
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
        //
    }
};
