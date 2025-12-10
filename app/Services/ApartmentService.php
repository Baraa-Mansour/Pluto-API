<?php

namespace App\Services;

use App\Repositories\ApartmentRepo;
use App\Models\Apartment;
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

    public function createApartment(array $data , int $userId)
    {
        Log::info('ApartmentService createApartment called');

        $apartment = $this->getApartmentFromRequest($data , $userId);

        return $this->apartmentRepository->createApartment($apartment);
    }

    private function getApartmentFromRequest(array $data , int $userId)
    {
        $apartment = new Apartment();

        $apartment->user_id = $userId;
        $apartment->description = $data['description'];
        $apartment->city_id = $data['city_id'];
        $apartment->rooms = $data['rooms'];
        $apartment->area = $data['area'];
        $apartment->price = $data['price'];
        $apartment->photo = $data['photo']->store('apartment_images', 'public');
        $apartment->floor = $data['floor'];
        $apartment->is_deleted = false;

        return $apartment;
    }
}
