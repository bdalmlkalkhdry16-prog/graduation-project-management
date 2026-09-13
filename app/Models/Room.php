<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    const TYPE_CLASSROOM = 'classroom';
    const TYPE_LAB = 'lab';

    const STATUS_AVAILABLE = 'available';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'building',
        'capacity',
        'type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_CLASSROOM => 'قاعة',
            self::TYPE_LAB => 'معمل',
            default => $type,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_AVAILABLE => 'متاحة',
            self::STATUS_MAINTENANCE => 'تحت الصيانة',
            self::STATUS_CLOSED => 'مغلقة',
            default => $status,
        };
    }
}