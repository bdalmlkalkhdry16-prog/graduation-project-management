<?php

namespace App\Http\Requests;

use App\Models\AcademicSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAcademicSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', AcademicSetting::class);
    }

    public function rules(): array
    {
        return [
            'pass_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'appeal_window_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'scale' => ['nullable', 'array', 'max:15'],
            'scale.*.letter' => ['nullable', 'string', 'max:10'],
            'scale.*.min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'scale.*.points' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /** الصفوف المكتملة فقط (letter + min). */
    public function scale(): array
    {
        return collect($this->input('scale', []))
            ->filter(fn ($r) => filled($r['letter'] ?? null) && is_numeric($r['min'] ?? null))
            ->map(fn ($r) => [
                'letter' => trim($r['letter']),
                'min' => (float) $r['min'],
                'points' => is_numeric($r['points'] ?? null) ? (float) $r['points'] : null,
            ])
            ->values()
            ->all();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $scale = $this->scale();
            $mins = array_column($scale, 'min');
            $letters = array_column($scale, 'letter');

            if (count($mins) !== count(array_unique($mins))) {
                $v->errors()->add('scale', 'لا يجوز تكرار الحد الأدنى في سلم التقديرات.');
            }
            if (count($letters) !== count(array_unique($letters))) {
                $v->errors()->add('scale', 'لا يجوز تكرار رمز التقدير.');
            }
            if ($scale !== [] && min($mins) > 0) {
                $v->errors()->add('scale', 'يجب أن يغطي السلم النسبة من 0 (أضف تقديرًا حده الأدنى 0).');
            }
        });
    }
}
