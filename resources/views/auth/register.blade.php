@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
        <div class="card shadow-lg border-0 p-4"
            style="max-width: 420px; width: 100%; background: linear-gradient(145deg, #141414, #1e1e1e); border-radius: 18px;">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-white mb-2">Đăng ký tài khoản</h2>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label text-white">Tên</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="form-control bg-dark text-white border-secondary rounded-3 @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label text-white">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="form-control bg-dark text-white border-secondary rounded-3 @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label text-white">Mật khẩu</label>
                    <input id="password" type="password" name="password" required
                        class="form-control bg-dark text-white border-secondary rounded-3 @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password-confirm" class="form-label text-white">Nhập lại mật khẩu</label>
                    <input id="password-confirm" type="password" name="password_confirmation" required
                        class="form-control bg-dark text-white border-secondary rounded-3">
                </div>

                <!-- Submit -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn fw-semibold py-2"
                        style="background: linear-gradient(90deg, #00c851, #007e33); border: none; border-radius: 30px; color: #fff;">
                        <i class="bi bi-person-plus me-1"></i> Đăng ký
                    </button>
                </div>

                <!-- Login link -->
                <div class="text-center">
                    <span class="text-light">Đã có tài khoản?</span>
                    <a href="{{ route('login') }}" class="text-info text-decoration-none ms-1">Đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
@endsection
