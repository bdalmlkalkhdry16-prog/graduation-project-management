@php
    $map = [
        'active' => ['نشط', 'bg-emerald-100 text-emerald-800'],
        'dropped' => ['منسحب', 'bg-red-100 text-red-800'],
        'completed' => ['مكتمل', 'bg-slate-200 text-slate-700'],
    ];
    [$label, $classes] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-700'];
@endphp
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $classes }}">{{ $label }}</span>
