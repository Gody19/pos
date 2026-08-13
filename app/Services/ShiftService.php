<?php

namespace App\Services;

use App\Exceptions\NoActiveShiftException;
use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\User;

class ShiftService
{
    public function open(User $user, float $openingBalance, ?string $notes = null): Shift
    {
        if ($user->hasOpenShift()) {
            throw new NoActiveShiftException('A shift is already open for this cashier.');
        }

        $shift = Shift::create([
            'user_id' => $user->id,
            'opening_balance' => $openingBalance,
            'opened_at' => now(),
            'status' => Shift::STATUS_OPEN,
            'notes' => $notes,
        ]);

        AuditLog::record('shift_opened', $shift, ['opening_balance' => $openingBalance]);

        return $shift;
    }

    public function close(User $user, float $closingBalance, ?string $notes = null): Shift
    {
        $shift = $user->activeShift();

        if ($shift === null) {
            throw new NoActiveShiftException('No open shift to close.');
        }

        $shift->update([
            'closing_balance' => $closingBalance,
            'expected_balance' => round($shift->opening_balance + $shift->totalSales(), 2),
            'closed_at' => now(),
            'status' => Shift::STATUS_CLOSED,
            'notes' => $notes,
        ]);

        AuditLog::record('shift_closed', $shift, [
            'closing_balance' => $closingBalance,
            'expected_balance' => $shift->expected_balance,
        ]);

        return $shift;
    }

    public function refreshTotals(Shift $shift): void
    {
        $totals = $shift->sales()->where('payment_status', 'completed')->get();

        $shift->update([
            'cash_sales' => $totals->where('payment_method', 'cash')->sum('total'),
            'card_sales' => $totals->where('payment_method', 'card')->sum('total'),
            'mobile_sales' => $totals->where('payment_method', 'mobile')->sum('total'),
            'qr_sales' => $totals->where('payment_method', 'qr')->sum('total'),
            'sales_count' => $totals->count(),
        ]);
    }
}
