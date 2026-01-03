<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- boostrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- CSS --}}
    <link href="{{ asset('home/style.css') }}" rel="stylesheet">

    {{-- Bracket bảng đấu --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css">

    <!-- Bootstrap icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div>
        @include('layouts.header')

        <main>
            @yield('content')
        </main>

        @include('layouts.footer')
    </div>

    {{-- MODAL THÔNG BÁO CHUNG CHO TOÀN WEBSITE --}}
    {{-- Chỉ render code này nếu có session trả về từ Controller --}}
    @if (session('success') || session('error') || session('warning'))
        @php
            $msgType = session('error') ? 'danger' : (session('warning') ? 'warning' : 'success');
            $message = session('error') ?? (session('warning') ?? session('success'));
            $title = session('error') ? 'Đã có lỗi xảy ra' : (session('warning') ? 'Cảnh báo' : 'Thành công');

            $iconClass = match ($msgType) {
                'danger' => 'bi-x-circle-fill',
                'warning' => 'bi-exclamation-triangle-fill',
                'success' => 'bi-check-circle-fill',
            };

            $textColor = match ($msgType) {
                'danger' => 'text-danger',
                'warning' => 'text-warning',
                'success' => 'text-success',
            };
        @endphp

        <div class="modal fade" id="globalNotificationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-white border-secondary shadow-lg">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold {{ $textColor }}">
                            {{ $title }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body text-center py-4">
                        {{-- Icon to ở giữa --}}
                        {{-- <div class="mb-3">
                            <i class="bi {{ $iconClass }} {{ $textColor }}" style="font-size: 3.5rem;"></i>
                        </div> --}}

                        {{-- Nội dung thông báo --}}
                        <p class="fs-5 mb-0">{{ $message }}</p>
                    </div>

                    <div class="modal-footer border-secondary justify-content-center">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Script tự động kích hoạt Modal khi trang load xong --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('globalNotificationModal'));
                myModal.show();
            });
        </script>
    @endif

    {{-- Modal xác nhận --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary shadow-lg">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-danger fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body mt-3">
                    <p class="fs-5 text-center">Bạn có chắc chắn muốn xóa không?</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bracket bảng đấu --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js"></script>


    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> --}}

</body>

</html>
