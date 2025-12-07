<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApartmentsRequest\ApartmentFilterRequest;
use App\Http\Resources\ApartmentResource\ApartmentScrollResource;
use App\Http\Resources\ApartmentResource\ApartmentDetailResource;
use App\Services\ApartmentService;
use Illuminate\Support\Facades\Log;

class ApartmentController extends Controller
{
    private $apartmentService;

    public function __construct(ApartmentService $apartmentService)
    {
        $this->apartmentService = $apartmentService;
    }

    public function getApartmentsWithFilters(ApartmentFilterRequest $request)
    {
        Log::info('ApartmentController getApartmentsWithFilters called');
        $validated = $request->validated();

        $apartments = $this->apartmentService->getAllApartmentsWithFilters($validated);
        $apartments_resource = ApartmentScrollResource::collection($apartments);

        return response()->json([
            'data' => $apartments_resource,
        ]);
    }
    public function getApartmentById(int $apartmentId)
    {
        Log::info('ApartmentController getApartmentById called for apartmentId: ' . $apartmentId);

        $apartment = $this->apartmentService->getApartmentById($apartmentId);
        $apartment_resource = new ApartmentDetailResource($apartment);

        return response()->json([
            'data' => $apartment_resource,
        ]);
    }
}
