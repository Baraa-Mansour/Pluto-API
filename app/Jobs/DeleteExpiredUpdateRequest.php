<?php

namespace App\Jobs;

use App\Services\UpdateRequestService;

class DeleteExpiredUpdateRequest
{
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(UpdateRequestService $updateRequestService): void
    {
        $updateRequestService->deleteExpiredUpdateRequests();
    }
}
