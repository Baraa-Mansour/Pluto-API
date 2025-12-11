<?php

namespace App\Http\Controllers;

use App\Services\GovernorateService;
use Illuminate\Support\Facades\Log;


class GovernorateController extends Controller
{
    private GovernorateService $governorateService;

    public function __construct(GovernorateService $governorateService)
    {
        $this->governorateService = $governorateService;
    }

    public function getAllGovernorates()
    {
        Log::info('governorateController: getAllGovernorates');
        
        return response()->json([
            'data' => $this->governorateService->getAllGovernorates(),
        ], 200);
    }

    public function getGovernorateCities(int $governorateId)
    {
        Log::info('governorateController: getGovernorateCities ID: ' . $governorateId);

        return response()->json([
            'data' => $this->governorateService->getGovernorateCities($governorateId),
        ], 200);
    }
}
