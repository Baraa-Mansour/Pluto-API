<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApartmentResource\ApartmentScrollResource;
use App\Http\Resources\ApartmentResource\ApartmentDetailResource;
use App\Models\Apartment;
use App\Services\ApartmentService;
use App\services\RateService;
use DomainException;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ApartmentRequest\ApartmentCreateRequest;
use App\Http\Requests\ApartmentRequest\ApartmentFilterRequest;
use App\Http\Requests\ApartmentRequest\ApartmentRateRequest;


class ApartmentController extends Controller
{
    private ApartmentService $apartmentService;
    private RateService $rateService;

    public function __construct(ApartmentService $apartmentService, RateService $rateService)
    {
        $this->apartmentService = $apartmentService;
        $this->rateService = $rateService;
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
    public function createApartment(ApartmentCreateRequest $request)
    {
        Log::info('ApartmentController createApartment called');

        $validated = $request->validated();

        $this->apartmentService->createApartment($validated ,$request->user()->id);
        
        return response()->json([
            'message' => 'Apartment created successfully',
        ]);
    }
    public function rateApartment(ApartmentRateRequest $request, int $apartmentId)
    {
        Log::error('ApartmentController rateApartment called');
        Apartment::findOrFail($apartmentId);
        
        try
        {
            $validated = $request->validated();

            $isRated = $this->rateService->rateApartment($request->user()->id,$apartmentId,$validated['rate']);

            return response()->json([
                'message' => 'Apartment rated successfully'
            ], 200);
        }
        catch(DomainException $ex)
        {
            Log::error('ApartmentController rateApartment failed');

            return response()->json([
                'message' => $ex->getMessage()
            ], 400);
        }
    }
}
