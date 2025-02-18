<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
                $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone');
                $table->date('check_in');
                $table->date('check_out');
                $table->integer('num_guests');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};
