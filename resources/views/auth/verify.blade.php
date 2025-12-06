@extends('layouts.app')

@section('content')
    <style>
        .verify-card {
            background: #1c1c1c;
            border: 1px solid #333;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .verify-icon-box {
            width: 80px;
            height: 80px;
            background: rgba(27, 124, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 2px solid #1b7c00;
        }

        .verify-icon {
            font-size: 2.5rem;
            color: #00ff7f;
        }
    </style>

    <div class="container py-5">
        <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
            <div class="col-md-6 col-lg-5">
                <div class="card verify-card text-white text-center p-4">

                    {{-- Icon Email --}}
                    <div class="verify-icon-box animate__animated animate__bounceIn">
                        <i class="bi bi-envelope-check-fill verify-icon"></i>
                    </div>

                    <div class="card-body px-0">
                        <h3 class="fw-bold mb-3">{{ __('Xác thực Email') }}</h3>

                        <p class="text-secondary mb-4">
                            {{ __('Cảm ơn bạn đã đăng ký! Trước khi bắt đầu, vui lòng kiểm tra email của bạn và bấm vào liên kết để xác thực tài khoản.') }}
                        </p>

                        {{-- Thông báo gửi lại thành công --}}
                        @if (session('resent'))
                            <div class="alert alert-success d-flex align-items-center mb-4" role="alert"
                                style="background: rgba(25, 135, 84, 0.2); border-color: #198754; color: #75b798;">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div>
                                    {{ __('Một liên kết xác thực mới đã được gửi đến email của bạn.') }}
                                </div>
                            </div>
                        @endif

                        {{-- Nút gửi lại --}}
                        <div class="d-grid gap-2">
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold"
                                    style="background: linear-gradient(to right, #1b7c00, #28a745); border: none;">
                                    <i class="bi bi-send-fill me-2"></i>{{ __('Gửi lại email xác thực') }}
                                </button>
                            </form>

                            {{-- Nút Logout (để user có thể thoát ra đăng nhập acc khác nếu muốn) --}}
                            {{-- <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline mt-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill py-2 border-0">
                                    {{ __('Đăng xuất') }}
                                </button>
                            </form> --}}
                        </div>

                        <p class="text-secondary small mt-4 mb-0">
                            {{ __('Nếu bạn không nhận được email, hãy kiểm tra mục Spam hoặc bấm nút gửi lại.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
