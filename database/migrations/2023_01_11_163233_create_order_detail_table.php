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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('list_id');
            $table->unsignedBigInteger('service_id');

            $table->double('paid_price', 16, 5)->nullable()->default(0);                    // Price at time of payments
            $table->string('paid_currency')->nullable()->default("USD");                    // Currency at time of payments
            $table->double('conversion_rate', 16, 5)->nullable()->default(1);               // Conversion Rate from base_currency table at time of payments

            $table->integer('order_id')->nullable();                                        
            $table->double('cost', 16, 5)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('link')->nullable();
            $table->text('comments')->nullable();
            $table->text('usernames')->nullable();
            $table->string('username')->nullable();
            $table->text('hashtags')->nullable();
            $table->text('hashtag')->nullable();
            $table->text('media')->nullable();
            $table->integer('answer_number')->nullable();
            $table->text('groups')->nullable();
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->integer('delay')->nullable();
            
            $table->integer('start_count')->nullable()->default(0);
            $table->integer('remains')->nullable();

            $table->integer('in_progress_minute')->nullable();
            $table->integer('completed_minute')->nullable();

            $table->tinyInteger('status')->default(1);
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->foreign('header_id')
                ->references('id')
                ->on('order_headers')
                ->onDelete('cascade');
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
        Schema::dropIfExists('order_details');
    }
};
