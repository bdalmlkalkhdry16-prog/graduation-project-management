@php
    $map = [
        'active' => ['نشط', 'success'],
        'dropped' => ['منسحب', 'danger'],
        'completed' => ['مكتمل', 'secondary'],
    ];
    [$label, $color] = $map[$status] ?? [$status, 'light text-dark'];
@endphp
<span class="badge bg-{{ $color }}">{{ $label }}</span>
