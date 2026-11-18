@php
    $classes = [
        'draft' => 'bg-slate-200 text-slate-700',
        'submitted' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-sky-100 text-sky-800',
        'published' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-red-100 text-red-800',
    ][$status] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $classes }}">{{ \App\Models\Exam::statusLabel($status) }}</span>
