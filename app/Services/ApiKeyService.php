<?php

namespace App\Services;

use App\Mail\ApiKeyMail;
use App\Repositories\Interfaces\ApiKeyRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function __construct(
        protected ApiKeyRepositoryInterface $apiKeyRepository
    ) {}

    public function issueKey(string $name, string $email): void
    {
        $existing = $this->apiKeyRepository->findByEmail($email);

        if ($existing) {
            $this->apiKeyRepository->deactivate($existing->id);
        }

        $plainKey = Str::random(64);

        $this->apiKeyRepository->create([
            'name'     => $name,
            'email'    => $email,
            'key_hash' => hash('sha256', $plainKey),
        ]);

        Mail::to($email)->send(new ApiKeyMail($name, $plainKey));
    }
}
