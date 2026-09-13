<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('rooms', 'name')],
            'building' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::in(['classroom', 'lab'])],
            'status' => ['required', Rule::in(['available', 'maintenance', 'closed'])],
        ];
    }
}