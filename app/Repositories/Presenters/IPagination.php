<?php

namespace App\Repositories\Presenters;


interface IPagination
{
    /**
     * @return stdClass[]
     */
    public function items(): array;
    public function total(): int;
    public function lastPage(): int;
    public function firstPage(): int;
    public function currentPage(): int;
    public function perPage(): int;
    public function to(): int;
    public function from(): int;
    public function hasPages(): bool;
    public function hasMorePages(): bool;
    public function nextPageUrl(): string|null;
}  