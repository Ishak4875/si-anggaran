@extends('layout.v_auth_layout')
@section('title', 'Login')
@section('bodyClass', 'login-page')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <span class="link-dark text-center d-block">
                <h1 class="mb-0"><b>PROGRESSO</b></h1>
            </span>
        </div>
        <div class="card-body login-card-body">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="input-group mb-1">
                    <div class="form-floating">
                        <input id="loginEmail" name="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="" required autofocus />
                        <label for="loginEmail">Email</label>
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
                        <input id="loginPassword" name="password" type="password" class="form-control"
                               placeholder="" required />
                        <label for="loginPassword">Password</label>
                    </div>
                    <div class="input-group-text">
                        <span class="bi bi-lock-fill"></span>
                    </div>
                </div>
                <!--begin::Row-->
                <div class="row">
                    <div class="col-8 d-inline-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="remember" id="flexCheckDefault" />
                            <label class="form-check-label" for="flexCheckDefault"> Ingat Saya </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Masuk</button>
                        </div>
                    </div>
                    <!-- /.col -->
                </div>
                <!--end::Row-->
            </form>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->
@endsection
