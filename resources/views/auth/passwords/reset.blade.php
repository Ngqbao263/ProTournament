@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
        <div class="card shadow-lg border-0 p-4"
            style="max-width: 420px; width: 100%; background: linear-gradient(145deg, #141414, #1e1e1e); border-radius: 18px;">

            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-shield-lock-fill text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold text-white mb-1">Đặt lại mật khẩu</h3>
                <p class="text-secondary small">Nhập mật khẩu mới của bạn</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label text-white">Địa chỉ Email</label>
                    <input id="email" type="email"
                        class="form-control bg-dark text-white border-secondary rounded-3 @error('email') is-invalid @enderror"
                        name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                        placeholder="name@example.com">

                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-white">Mật khẩu mới</label>
                    <input id="password" type="password"
                        class="form-control bg-dark text-white border-secondary rounded-3 @error('password') is-invalid @enderror"
                        name="password" required autocomplete="new-password" placeholder="Tối thiểu 8 ký tự">

                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password-confirm" class="form-label text-white">Xác nhận mật khẩu</label>
                    <input id="password-confirm" type="password"
                        class="form-control bg-dark text-white border-secondary rounded-3" name="password_confirmation"
                        required autocomplete="new-password" placeholder="Nhập lại mật khẩu trên">
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success fw-semibold py-2"
                        style="background: linear-gradient(90deg, #00c851, #007e33); border: none; border-radius: 30px;">
                        <i class="bi bi-arrow-repeat me-1"></i> Đổi Mật Khẩu
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-info text-decoration-none small hover-effect">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại đăng nhập
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
