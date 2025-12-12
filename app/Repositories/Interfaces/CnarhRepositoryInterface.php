<?php

namespace App\Repositories\Interfaces;

interface CnarhRepositoryInterface
{
    public function deleteAll(): void;

    public function insertBatch(array $data): void;

    public function getAllWithCoordinates();

    public function getByCnarh(string $intCdCnarh40);
}
