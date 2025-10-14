<?php

namespace App\Services;

use App\Repositories\Interfaces\JobStatusInterface as InterfacesJobStatusInterface;
use App\Repositories\JobStatusInterface;

class JobStatusService
{
    private $repository;

    public function __construct(InterfacesJobStatusInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    } 

    public function store(array $data)
    { 
        try {
            return $this->repository->store($data);    
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            dd('Failed to store data: ' . $e->getMessage());

            // Optionally, you can rethrow the exception if you want it to propagate
            throw $e;

            // Or return a default value or response
            // return false; // Uncomment this line if you want to return a default value
        }
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function getByStatus(int $job, int $status)
    {
        return $this->repository->getByStatus($job, $status);
    }

    public function getByDatetimeReadingAndJob($date, $job)
    {
        return $this->repository->getByDatetimeReadingAndJob($date, $job);
    }
}
