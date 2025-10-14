<?php

namespace App\Repositories;

use App\Models\JobStatus;
use App\Repositories\Interfaces\JobStatusInterface;
use Illuminate\Support\Facades\Log;

class JobStatusRepository implements JobStatusInterface
{
    private $model;

    public function __construct(JobStatus $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->paginate(100);
    }

    public function store($data) : JobStatus
    {    
        try {
            $deviceReadingDailyAverageStatus = new JobStatus([
                'job'               => $data['job'],
                'datetime_reading'  => $data['datetime_reading'],
                'status'            => $data['status']
            ]);

            // Salvando o modelo no banco de dados
            $deviceReadingDailyAverageStatus->save();

            return $deviceReadingDailyAverageStatus;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function update($id, $data)
    {
        $deviceReadingDailyAverageStatus = $this->model->findOrFail($id);
        $deviceReadingDailyAverageStatus = $deviceReadingDailyAverageStatus->update($data);

        return $deviceReadingDailyAverageStatus;
    }

    public function getByStatus(int $job, int $status)
    {
        return $this->model
                        ->where('job', $job)
                        ->where('status', $status)
                        ->get();
    }

    public function getByDatetimeReadingAndJob($date, $job)
    {
        return $this->model->where('datetime_reading', $date)
                            ->where('job', $job)
                            ->first();
    }
}
