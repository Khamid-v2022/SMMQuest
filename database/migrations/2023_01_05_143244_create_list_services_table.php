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
        Schema::create('list_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('list_id');
            $table->unsignedBigInteger('service_id');
            
            $table->string('order_id')->nullable();
            $table->integer('start_count')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('link')->nullable();
            $table->string('service_type')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->string('comment')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->integer('inprogress_minute')->nullable();
            $table->integer('completed_minute')->nullable();
            $table->timestamps();

            $table->foreign('list_id')
                ->references('id')
                ->on('user_lists')
                ->onDelete('cascade');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
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
        Schema::dropIfExists('list_services');
    }
};
