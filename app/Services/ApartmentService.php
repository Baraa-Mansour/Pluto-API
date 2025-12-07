<?php

namespace App\Services;

use App\Repositories\ApartmentRepo;
use Illuminate\Support\Facades\Log;

class ApartmentService
{
    private $apartmentRepository;

    public function __construct(ApartmentRepo $apartmentRepository)
    {
        $this->apartmentRepository = $apartmentRepository;
    }

    public function getAllApartmentsWithFilters(array $filters)
    {
        Log::info('ApartmentService getAllApartmentsWithFilters called');
        return $this->apartmentRepository->getAllApartmentsWithFilters($filters);
    }

    public function getApartmentById(int $apartmentId)
    {
        Log::info('ApartmentService getApartmentById called for apartmentId: ' . $apartmentId);
        return $this->apartmentRepository->getApartmentById($apartmentId);
    }
}
