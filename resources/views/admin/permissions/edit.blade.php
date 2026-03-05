@extends('layouts.app')

@section('title', 'Lejet e ' . $user->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Lejet e: <strong>{{ $user->name }}</strong></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Lejet</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.permissions.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- Roles --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="ri-shield-user-line me-1"></i> Rolet</h6>
                </div>
                <div class="card-body">
                    @foreach($roles as $role)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox"
                            name="roles[]"
                            value="{{ $role->name }}"
                            id="role_{{ $role->name }}"
                            {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->name }}">
                            <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : 'primary' }}">
                                {{ $role->name }}
                            </span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="ri-key-2-line me-1"></i> Lejet Direkte</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($groupedPermissions as $module => $perms)
                        <div class="col-md-6 mb-4">
                            <h6 class="text-uppercase text-muted border-bottom pb-1 mb-2" style="font-size:11px;">
                                {{ $module }}
                            </h6>
                            @foreach($perms as $permission)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"
                                    id="perm_{{ $permission->id }}"
                                    {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">
            <i class="ri-save-line me-1"></i> Ruaj Ndryshimet
        </button>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kthehu
        </a>
    </div>
</form>
@endsection