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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->integer('service')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->float('rate', 10, 3)->nullable();
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->tinyInteger('dripfeed')->nullable();
            $table->tinyInteger('refill')->nullable();
            $table->tinyInteger('cancel')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

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
        Schema::dropIfExists('services');
    }
};
