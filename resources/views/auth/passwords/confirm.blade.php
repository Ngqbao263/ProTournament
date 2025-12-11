@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100 bg-dark">
        <div class="card shadow-lg border-0 p-4"
            style="max-width: 420px; width: 100%; background: linear-gradient(145deg, #141414, #1e1e1e); border-radius: 18px;">

            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-shield-lock-fill text-danger" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold text-white mb-1">Xác nhận mật khẩu</h3>
                <p class="text-secondary small">Vui lòng xác nhận mật khẩu trước khi tiếp tục.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-4">
                    <label for="password" class="form-label text-white">Mật khẩu hiện tại</label>
                    <input id="password" type="password"
                        class="form-control bg-dark text-white border-secondary rounded-3 @error('password') is-invalid @enderror"
                        name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu của bạn">

                    @error('password')
                        <div class="invalid-feedback">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-danger fw-semibold py-2"
                        style="background: linear-gradient(90deg, #dc3545, #b02a37); border: none; border-radius: 30px;">
                        <i class="bi bi-check-lg me-1"></i> Xác Nhận
                    </button>
                </div>

                @if (Route::has('password.request'))
                    <div class="text-center">
                        <a class="text-info text-decoration-none small hover-effect" href="{{ route('password.request') }}">
                            <i class="bi bi-question-circle me-1"></i> Quên mật khẩu?
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
