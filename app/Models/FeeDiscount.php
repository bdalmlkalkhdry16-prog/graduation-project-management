<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeDiscount extends Model
{
    protected $fillable = ['student_fee_id', 'type', 'value', 'amount', 'reason', 'created_by', 'revoked_at', 'revoked_by', 'revoke_reason'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'amount' => 'decimal:2', 'revoked_at' => 'datetime'];
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}
