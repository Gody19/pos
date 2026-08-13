<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // cash | card | mobile | qr
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->string('status')->default('completed'); // pending | completed | failed | refunded
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['sale_id']);
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
