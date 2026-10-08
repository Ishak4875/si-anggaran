@extends('layout.v_auth_layout')
@section('title', $judul)
@section('bodyClass', 'login-page')

@section('content')
<div class="login-box" style="width:420px;max-width:calc(100% - 32px)">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <h1 class="mb-0"><b>{{ $kode }}</b></h1>
        </div>
        <div class="card-body text-center">
            <h5 class="mb-2">{{ $judul }}</h5>
            <p class="text-secondary mb-4">{{ $pesan }}</p>
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="bi bi-house me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
