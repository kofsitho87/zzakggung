<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('option')->nullable();
            $table->integer('qty')->unsigned()->comment('수량');

            $table->string('receiver');
            $table->string('phone_1');
            $table->string('phone_2')->nullable();

            $table->string('zipcode')->comment('우편번호');
            $table->string('address')->comment('주소');
            $table->string('delivery_message')->nullable()->comment('배송메세지');
            $table->integer('delivery_price')->unsigned()->default(2500)->comment('배송비');
            $table->tinyInteger('delivery_status')->default(1)->unsigned()->comment('배송상태');
            $table->string('delivery_code')->nullable()->comment('송장번호');
            $table->tinyInteger('delivery_provider')->nullable()->comment('배송업체');
            $table->integer('minus_price')->unsigned()->default(0)->comment('반품완료시 차감할 비용');
            $table->string('comment')->nullable()->comment('참고사항');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->collation = 'utf8_unicode_ci';
            $table->charset   = 'utf8';
            $table->engine    = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
