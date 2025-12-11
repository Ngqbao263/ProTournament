@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
        <div class="card shadow-lg border-0 p-4"
            style="max-width: 420px; width: 100%; background: linear-gradient(145deg, #141414, #1e1e1e); border-radius: 18px;">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-white mb-2">Đăng nhập</h2>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label text-white">Email</label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
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

                <!-- Remember Me -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-light" for="remember">Ghi nhớ đăng nhập</label>
                </div>

                <!-- Submit -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success fw-semibold py-2"
                        style="background: linear-gradient(90deg, #00c851, #007e33); border: none; border-radius: 30px;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                    </button>
                </div>

                <!-- Forgot password -->
                @if (Route::has('password.request'))
                    <div class="text-center">
                        <a class="text-info text-decoration-none" href="{{ route('password.request') }}">
                            <i class="bi bi-question-circle me-1"></i>Quên mật khẩu?
                        </a>
                    </div>
                @endif

                <div class="text-center">
                    <span class="text-white">Chưa có tài khoản?</span>
                    <a class="text-info text-decoration-none" href="{{ route('register') }}">
                        Đăng ký
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
