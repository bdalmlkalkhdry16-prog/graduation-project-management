@extends('layouts.app')

@section('title', $data['title'])

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ $data['title'] }}</h4>
            <button onclick="window.print()" class="btn btn-secondary">طباعة</button>
        </div>
        <div class="card-body">
            @if(isset($data['stats']))
                @include('admin.reports.partials.statistics')
            @elseif(isset($data['projects']))
                @include('admin.reports.partials.projects')
            @elseif(isset($data['students']))
                @include('admin.reports.partials.students')
            @elseif(isset($data['supervisors']))
                @include('admin.reports.partials.supervisors')
            @endif
        </div>
    </div>
</div>
@endsection