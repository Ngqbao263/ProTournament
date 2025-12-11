@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
        <div class="card shadow-lg border-0 p-4"
            style="max-width: 420px; width: 100%; background: linear-gradient(145deg, #141414, #1e1e1e); border-radius: 18px;">

            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-envelope-exclamation-fill text-warning" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold text-white mb-1">Quên mật khẩu?</h3>
                <p class="text-secondary small">Nhập email để nhận liên kết đặt lại mật khẩu</p>
            </div>

            <div class="card-body p-0">
                @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center border-0 mb-4" role="alert"
                        style="background-color: rgba(25, 135, 84, 0.2); color: #75b798; border-radius: 10px;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <small>{{ session('status') }}</small>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label text-white">Địa chỉ Email</label>
                        <input id="email" type="email"
                            class="form-control bg-dark text-white border-secondary rounded-3 @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                            placeholder="name@example.com">

                        @error('email')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-success fw-semibold py-2 text-white"
                            style="background: linear-gradient(90deg, #00c851, #007e33); border: none; border-radius: 30px;">
                            <i class="bi bi-send-fill me-1"></i> Gửi Liên Kết
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
    </div>
@endsection
