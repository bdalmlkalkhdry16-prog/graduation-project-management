@php
    $color = [
        'draft' => 'secondary',
        'submitted' => 'warning text-dark',
        'approved' => 'info text-dark',
        'published' => 'success',
        'rejected' => 'danger',
    ][$status] ?? 'light text-dark';
@endphp
<span class="badge bg-{{ $color }}">{{ \App\Models\Exam::statusLabel($status) }}</span>
