<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScannerLoginToken extends Model
{
    protected $fillable = ['token', 'expires_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public static function createForToday(): self
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = now()->endOfDay();

        return static::create([
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    public static function findByToken(string $token): ?self
    {
        $model = static::query()->where('token', $token)->first();

        return $model && $model->isValid() ? $model : null;
    }
}
