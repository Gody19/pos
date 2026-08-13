<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['product_id']);
            $table->index(['reorder_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
