@extends('index')

@section('title', 'Dashboard')

@section('header')
<h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
@endsection

@section('content')
<div class="py-4">
    <div class="card">
        <div class="card-body">
            {{ __("You're logged in!") }}
        </div>
    </div>
</div>
@endsection