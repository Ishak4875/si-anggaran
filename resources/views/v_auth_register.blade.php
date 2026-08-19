@extends('layout.v_auth_layout')
@section('title', 'Register')
@section('bodyClass', 'register-page')

@section('content')
<div class="register-box">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <span class="link-dark text-center d-block">
                <h1 class="mb-0"><b>PROGRESSO</b></h1>
            </span>
        </div>
        <div class="card-body register-card-body">
            <p class="register-box-msg">Daftar akun baru</p>

            <form method="POST" action="{{ route('register.attempt') }}">
                @csrf
                <div class="input-group mb-1">
                    <div class="form-floating">
                        <input id="registerFullName" name="name" type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="" required autofocus />
                        <label for="registerFullName">Nama Lengkap</label>
                    </div>
                    <div class="input-group-text">
                        <span class="bi bi-person"></span>
                    </div>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="input-group mb-1">
                    <div class="form-floating">
                        <input id="registerEmail" name="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="" required />
                        <label for="registerEmail">Email</label>
                    </div>
                    <div class="input-group-text">
                        <span class="bi bi-envelope"></span>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="input-group mb-1">
                    <div class="form-floating">
                        <input id="registerPassword" name="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="" required />
                        <label for="registerPassword">Password</label>
                    </div>
                    <div class="input-group-text">
                        <span class="bi bi-lock-fill"></span>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="input-group mb-2">
                    <div class="form-floating">
                        <input id="registerPasswordConfirm" name="password_confirmation" type="password"
                               class="form-control" placeholder="" required />
                        <label for="registerPasswordConfirm">Konfirmasi Password</label>
                    </div>
                    <div class="input-group-text">
                        <span class="bi bi-lock-fill"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3">
                <a href="{{ route('login') }}" class="link-primary text-center"> Sudah punya akun? Masuk </a>
            </p>
        </div>
        <!-- /.register-card-body -->
    </div>
</div>
<!-- /.register-box -->
@endsection
