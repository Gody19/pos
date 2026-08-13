<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    public const TYPE_EARNED = 'earned';

    public const TYPE_REDEEMED = 'redeemed';

    public const TYPE_ADJUSTED = 'adjusted';

    protected $fillable = ['customer_id', 'sale_id', 'points', 'type', 'notes'];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
