<?php
// app/Services/DcpFailureCodeService.php

namespace App\Services;

use App\Repositories\Interfaces\DcpFailureCodeRepositoryInterface;
use App\Models\DcpFailureCode;
use Illuminate\Support\Collection;

class DcpFailureCodeService
{
    protected DcpFailureCodeRepositoryInterface $failureCodeRepository;

    public function __construct(DcpFailureCodeRepositoryInterface $failureCodeRepository)
    {
        $this->failureCodeRepository = $failureCodeRepository;
    }

    public function getAllFailureCodes(): Collection
    {
        return $this->failureCodeRepository->all();
    }

    public function getFailureCode(int $id): ?DcpFailureCode
    {
        return $this->failureCodeRepository->find($id);
    }

    public function getFailureCodeByCode(string $code): ?DcpFailureCode
    {
        return $this->failureCodeRepository->findByCode($code);
    }

    public function createFailureCode(array $data): DcpFailureCode
    {
        return $this->failureCodeRepository->create($data);
    }

    public function updateFailureCode(int $id, array $data): bool
    {
        return $this->failureCodeRepository->update($id, $data);
    }

    public function getFailureDescription(string $code): ?string
    {
        $failureCode = $this->failureCodeRepository->findByCode($code);
        return $failureCode ? $failureCode->description : null;
    }

    public function isSuccessCode(string $code): bool
    {
        return $code === 'G';
    }
}