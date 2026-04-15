<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    protected $fillable = [
        'name',
        'email',
        'key_hash',
        'is_active',
        'last_used_at',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public static function findByPlainKey(string $plainKey): ?static
    {
        $hash = hash('sha256', $plainKey);

        return static::where('key_hash', $hash)
            ->where('is_active', true)
            ->first();
    }
}
