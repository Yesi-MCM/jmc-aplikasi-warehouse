<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incoming_goods_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_goods_id')->constrained('incoming_goods')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->integer('quantity');
            $table->string('unit_name'); // e.g. "buah", "Unit"
            $table->date('expiry_date')->nullable();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rack_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('tier')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_goods_details');
    }
};
