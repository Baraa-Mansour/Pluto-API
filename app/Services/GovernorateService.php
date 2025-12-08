<?php

namespace App\Services;

use App\Repositories\GovernorateRepo;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Governorate;

class GovernorateService
{
    private GovernorateRepo $governorateRepo;

    public function __construct()
    {
        $this->governorateRepo = new GovernorateRepo();
    }

    public function getAllGovernorates()
    {
        Log::info('governorateService: getAllGovernorates');
        
        return $this->governorateRepo->getAllGovernorates();
    }

    public function getGovernorateCities(int $governorateId)
    {
        Log::info('governorateService: getGovernorateCities ID: ' . $governorateId);

        if (!$this->governorateRepo->isGovernorateExists($governorateId)) 
        {
            throw new ModelNotFoundException(Governorate::class, $governorateId);
        }

        return $this->governorateRepo->getGovernorateCities($governorateId);
    }

}
