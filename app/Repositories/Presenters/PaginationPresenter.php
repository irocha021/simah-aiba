<?php

namespace App\Repositories\Presenters;

use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class PaginationPresenter implements IPagination
{
    public $paginator;
    public $meta;

    public function __construct(LengthAwarePaginator $paginator)
    {
        $this->paginator = $paginator;
        $this->meta = new stdClass();
        $this->meta->total = $paginator->total();
        $this->meta->per_page = $paginator->perPage();
        $this->meta->current_page = $paginator->currentPage();
        $this->meta->first_page = 1;
        $this->meta->last_page = $paginator->lastPage();
        $this->meta->from = $paginator->firstItem();
        $this->meta->to = $paginator->lastItem();
    }

    public function items(): array
    {
        return $this->paginator->items();
    }

    public function total(): int
    {
        return $this->paginator->total() ?? 0;
    }

    public function lastPage(): int
    {
        return $this->paginator->lastPage() ?? 0;
    }

    public function firstPage(): int
    {
        return $this->paginator->firstItem() ?? 0;
    }

    public function currentPage(): int
    {
        return $this->paginator->currentPage() ?? 0;
    }

    public function perPage(): int
    {
        return $this->paginator->perPage() ?? 0;
    }

    public function to(): int
    {
        return $this->paginator->firstItem() ?? 0;
    }

    public function from(): int
    {
        return $this->paginator->lastItem() ?? 0;
    }

    public function hasPages(): bool
    {
        return $this->paginator->hasPages();
    }

    public function hasMorePages(): bool
    {
        return $this->paginator->hasMorePages();
    }

    public function nextPageUrl(): string|null
    {
        return $this->paginator->nextPageUrl();
    }
}