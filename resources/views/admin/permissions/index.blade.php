@extends('layouts.app')

@section('title', 'Menaxhimi i Lejeve')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Menaxhimi i Lejeve</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Lejet</li>
            </ol>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Përdoruesit & Rolet</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Emri</th>
                        <th>Email</th>
                        <th>Rolet</th>
                        <th>Lejet Direkte</th>
                        <th style="width:100px">Veprime</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                            <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : 'primary' }}">
                                {{ $role->name }}
                            </span>
                            @endforeach
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $user->permissions->count() }} leje direkte
                            </small>
                        </td>
                        <td>
                            <a href="{{ route('admin.permissions.edit', $user) }}" class="btn btn-sm btn-primary">
                                <i class="ri-pencil-line"></i> Modifiko
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection