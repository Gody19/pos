<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'cash_sales',
        'card_sales',
        'mobile_sales',
        'qr_sales',
        'sales_count',
        'opened_at',
        'closed_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'mobile_sales' => 'decimal:2',
            'qr_sales' => 'decimal:2',
            'sales_count' => 'integer',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function totalSales(): float
    {
        return $this->cash_sales + $this->card_sales + $this->mobile_sales + $this->qr_sales;
    }
}
