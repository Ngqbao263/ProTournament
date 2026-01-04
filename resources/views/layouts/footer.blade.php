{{-- Footer --}}
<footer class="site-footer text-white py-4">
    <div class="container">
        <div class="row align-items-center gy-4">

            {{-- Logo + Mô tả --}}
            <div class="col-md-4 align-items-center justify-content-center justify-content-md-start logo">
                <img src="{{ asset('home/img/logo.png') }}" alt="Logo" class="me-3 logo-img" />
                <div>
                    <small class="text-light opacity-75">Tạo & quản lý giải đấu dễ dàng</small>
                </div>
            </div>

            {{-- Menu --}}
            <div class="col-md-4 text-center">
                <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-3 mb-0 footer-menu">
                    <li><a href="#" class="footer-link">Trang chủ</a></li>
                    <li><a href="#" class="footer-link">Tạo giải đấu</a></li>
                    <li><a href="#" class="footer-link">Tất cả giải đấu</a></li>
                    {{-- <li><a href="#" class="footer-link">Tin tức</a></li> --}}
                </ul>
            </div>

            {{-- Mạng xã hội --}}
            <div class="col-md-4 d-flex justify-content-center justify-content-md-end gap-3">
                <a href="#" class="footer-icon"><i class="bi bi-facebook"></i></a>
                <a href="#" class="footer-icon"><i class="bi bi-youtube"></i></a>
                <a href="#" class="footer-icon"><i class="bi bi-instagram"></i></a>
            </div>
        </div>

        <hr class="border-secondary mt-4 mb-3 opacity-25">

        {{-- Bản quyền --}}
        <div class="text-center small text-light opacity-75">
            <span id="yr"></span> Pro Tournament
        </div>
    </div>
</footer>

<script>
    document.getElementById("yr").textContent = new Date().getFullYear();
</script>
