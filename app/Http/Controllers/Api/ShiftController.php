<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloseShiftRequest;
use App\Http\Requests\OpenShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function open(OpenShiftRequest $request): ShiftResource
    {
        $shift = $this->shiftService->open($request->user(), $request->opening_balance, $request->notes);

        return new ShiftResource($shift->load('user'));
    }

    public function close(CloseShiftRequest $request): ShiftResource
    {
        $shift = $this->shiftService->close($request->user(), $request->closing_balance, $request->notes);

        return new ShiftResource($shift->load('user'));
    }

    public function current(Request $request): JsonResponse
    {
        $shift = $request->user()->activeShift()?->load('user');

        return response()->json(['data' => $shift ? new ShiftResource($shift) : null]);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $shifts = $request->user()
            ->shifts()
            ->with('user')
            ->latest('opened_at')
            ->paginate($request->integer('per_page', 15));

        return ShiftResource::collection($shifts);
    }
}
