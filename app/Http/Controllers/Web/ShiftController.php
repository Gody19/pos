<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloseShiftRequest;
use App\Http\Requests\OpenShiftRequest;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function showOpenForm(): View
    {
        $openShift = auth()->user()->activeShift();

        return view('shifts.open', ['openShift' => $openShift]);
    }

    public function open(OpenShiftRequest $request): RedirectResponse
    {
        $shift = $this->shiftService->open(auth()->user(), (float) $request->opening_balance, $request->notes);

        return redirect()
            ->route('pos.index')
            ->with('success', 'Shift opened successfully. You can now process sales.');
    }

    public function showCloseForm(): View
    {
        $shift = auth()->user()->activeShift();

        if ($shift === null) {
            return view('shifts.close', ['shift' => null, 'history' => auth()->user()->shifts()->latest('opened_at')->limit(5)->get()]);
        }

        $shift->load('sales');

        return view('shifts.close', ['shift' => $shift]);
    }

    public function close(CloseShiftRequest $request): RedirectResponse
    {
        $shift = $this->shiftService->close(auth()->user(), (float) $request->closing_balance, $request->notes);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Shift closed successfully. Expected balance was '.number_format($shift->expected_balance, 2).'.');
    }

    public function history(Request $request): View
    {
        $shifts = auth()->user()
            ->shifts()
            ->with('user')
            ->latest('opened_at')
            ->paginate(15)
            ->withQueryString();

        return view('shifts.history', ['shifts' => $shifts]);
    }
}