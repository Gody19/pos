<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change_due', 12, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending | completed | cancelled | refunded
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'sold_at']);
            $table->index(['shift_id']);
            $table->index(['customer_id']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
