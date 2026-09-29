<?php
namespace App\Http\Controllers;

use App\Actions\RecordUsageAction;
use App\DTOs\UsageEventData;
use App\Http\Requests\RecordUsageRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class UsageController extends Controller
{
    public function store(RecordUsageRequest $request, RecordUsageAction $action): JsonResponse
    {
        $data = $request->validated();
        $event = $action->execute(new UsageEventData(
            (int) $data['merchant_id'],
            (int) $data['customer_id'],
            $data['event_key'],
            CarbonImmutable::parse($data['occurred_at']),
            (int) $data['units'],
            $data['type'] ?? 'api',
            $data['metadata'] ?? null,
        ));

        return response()->json([
            'success' => true,
            'duplicate' => ! $event->wasRecentlyCreated,
            'event_id' => $event->id,
        ], $event->wasRecentlyCreated ? 201 : 200);
    }
}
