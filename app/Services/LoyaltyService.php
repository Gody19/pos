<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\Config;

class LoyaltyService
{
    /**
     * Points earned for every currency unit spent, e.g. 1 point per 1,000.
     */
    public function pointsForAmount(float $amount): int
    {
        $rate = max(1, (float) Config::get('pos.loyalty.points_per_currency', 1000));

        return (int) floor($amount / $rate);
    }

    public function earn(Customer $customer, ?int $saleId, float $amount): ?LoyaltyTransaction
    {
        $points = $this->pointsForAmount($amount);

        if ($points <= 0) {
            return null;
        }

        $customer->increment('loyalty_points', $points);

        return LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'sale_id' => $saleId,
            'points' => $points,
            'type' => LoyaltyTransaction::TYPE_EARNED,
            'notes' => 'Points earned from sale',
        ]);
    }

    public function redeem(Customer $customer, ?int $saleId, int $points): LoyaltyTransaction
    {
        if ($points <= 0 || $points > $customer->loyalty_points) {
            throw new \InvalidArgumentException('Not enough loyalty points to redeem.');
        }

        $customer->decrement('loyalty_points', $points);

        return LoyaltyTransaction::create([
            'customer_id' => $customer->id,
            'sale_id' => $saleId,
            'points' => $points,
            'type' => LoyaltyTransaction::TYPE_REDEEMED,
            'notes' => 'Points redeemed against sale',
        ]);
    }
}
