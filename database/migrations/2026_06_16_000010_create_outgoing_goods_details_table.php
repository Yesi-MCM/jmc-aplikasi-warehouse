<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outgoing_goods_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outgoing_goods_id')->constrained('outgoing_goods')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rack_id')->constrained()->cascadeOnDelete();
            $table->integer('tier');
            $table->decimal('price', 15, 2);
            $table->integer('quantity');
            $table->string('unit_name'); // e.g. "buah", "Unit"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outgoing_goods_details');
    }
};
