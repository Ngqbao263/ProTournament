@extends('layouts.app')

@section('content')
    <style>
        .ck-editor__editable_inline {
            min-height: 200px;
            background-color: #212529 !important;
            color: #fff !important;
        }

        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-color: #6c757d !important;
        }

        .ck.ck-toolbar {
            background-color: #343a40 !important;
            border-color: #6c757d !important;
        }

        .ck.ck-button {
            color: #fff !important;
        }

        .ck.ck-button:hover {
            background-color: #495057 !important;
        }

        .ck-list__item .ck-button {
            color: #000 !important;
        }
    </style>

    <div class="tournament-create py-5">
        <div class="container col-md-8 col-lg-6 bg-dark text-white rounded-4 shadow-lg p-4">
            <h3 class="text-center fw-bold mb-4 text-uppercase">Tạo giải đấu mới</h3>

            <form method="POST" action="{{ route('tournaments.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Tên giải đấu --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Tên giải đấu</label>
                    <input type="text" name="name" class="form-control bg-dark text-white border-secondary"
                        value="{{ old('name') }}" placeholder="Ví dụ: Giải bóng đá mở rộng...">
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Thumbnail --}}
                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Ảnh thumbnail</label>
                    <input type="file" name="thumbnail" id="thumbnail-input"
                        class="form-control bg-dark text-white border-secondary" accept="image/*">

                    <div class="mt-3 text-center d-none" id="preview-container">
                        <p class="small text-muted mb-1">Xem trước:</p>
                        <img id="preview-img" src="#" alt="Thumbnail Preview" class="img-fluid rounded shadow"
                            style="max-height: 200px; object-fit: cover;">
                    </div>

                    @error('thumbnail')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Thể loại --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Thể loại</label>
                        <select name="category" id="category" class="form-select bg-dark text-white border-secondary"
                            required>
                            <option value="">-- Chọn thể loại --</option>
                            <option value="sport" {{ old('category') == 'sport' ? 'selected' : '' }}>Thể thao</option>
                            <option value="e-sport" {{ old('category') == 'e-sport' ? 'selected' : '' }}>E-Sport</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">Chế độ thi đấu</label>
                        <select name="mode" class="form-select bg-dark text-white border-secondary">
                            <option value="individual">Cá nhân</option>
                            <option value="team">Đồng đội</option>
                        </select>
                    </div>
                </div>

                {{-- Bộ môn thi đấu --}}
                <div class="mb-3">
                    <label for="game_name" class="form-label">Bộ môn thi đấu</label>
                    <select name="game_name" id="game_name" class="form-select bg-dark text-white border-secondary"
                        required>
                        <option value="">-- Chọn bộ môn --</option>
                    </select>
                </div>

                {{-- Ngày bắt đầu giải --}}
                <div class="mb-3">
                    <label for="start_date" class="form-label">Ngày bắt đầu</label>
                    <input type="date" name="start_date" id="start_date"
                        class="form-control bg-white text-black border-secondary" value="{{ old('start_date') }}">
                </div>

                {{-- Mô tả --}}
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea name="description" id="editor" class="form-control text-dark" rows="5">{{ old('description') }}</textarea>
                </div>

                {{-- Thể thức --}}
                <div class="mb-3">
                    <label for="type" class="form-label">Thể thức thi đấu</label>
                    <select name="type" class="form-select bg-dark text-white border-secondary">
                        <option value="">-- Chọn thể thức --</option>
                        <option value="single_elimination" {{ old('type') == 'single_elimination' ? 'selected' : '' }}>
                            Loại trực tiếp
                        </option>
                        <option value="double_elimination" {{ old('type') == 'double_elimination' ? 'selected' : '' }}>
                            Nhánh thắng nhánh thua
                        </option>
                        <option value="round_robin" {{ old('type') == 'round_robin' ? 'selected' : '' }} disabled>
                            Vòng tròn (đang phát triển)
                        </option>
                        <option value="group_stage" {{ old('type') == 'group_stage' ? 'selected' : '' }} disabled>
                            Chia bảng đấu (đang phát triển)
                        </option>
                    </select>
                    @error('type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Số lượng --}}
                <div class="mb-4">
                    <label for="max_player" class="form-label">Số lượng người chơi (đội) tham gia tối đa</label>
                    <input type="number" name="max_player" class="form-control bg-dark text-white border-secondary"
                        value="{{ old('max_player') }}">
                    @error('max_player')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nút Submit --}}
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-semibold py-2">
                    <i class="bi bi-trophy-fill me-2"></i>Tạo giải đấu
                </button>
            </form>
        </div>
    </div>

    {{-- Nhúng CKEditor 5 từ CDN --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        // 1. Xử lý Rich Text Editor (CKEditor)
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                placeholder: 'Nhập mô tả chi tiết về giải đấu...'
            })
            .catch(error => {
                console.error(error);
            });

        // 2. Xử lý Xem trước ảnh (Image Preview)
        const thumbnailInput = document.getElementById('thumbnail-input');
        const previewContainer = document.getElementById('preview-container');
        const previewImg = document.getElementById('preview-img');

        thumbnailInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none'); // Hiện khung ảnh
                }
                reader.readAsDataURL(file);
            } else {
                previewImg.src = '#';
                previewContainer.classList.add('d-none'); // Ẩn nếu không có ảnh
            }
        });

        // 3. Xử lý chọn Bộ môn
        const sportGames = ["Bóng đá", "Pickelball", "Bóng rổ", "Cầu lông", "Bóng chuyền"];
        const eSportGames = ["Liên Minh Huyền Thoại", "Liên Quân Mobile", "Valorant", "CS2", "Tốc Chiến", "Dota 2"];

        const categorySelect = document.getElementById('category');
        const gameSelect = document.getElementById('game_name');

        // Giá trị cũ từ Laravel (nếu validate fail)
        const oldGameName = "{{ old('game_name') }}";

        function updateGameOptions(selectedCategory) {
            gameSelect.innerHTML = '<option value="">-- Chọn bộ môn --</option>';
            let games = [];
            if (selectedCategory === 'sport') games = sportGames;
            if (selectedCategory === 'e-sport') games = eSportGames;

            games.forEach(game => {
                const option = document.createElement('option');
                option.value = game;
                option.textContent = game;
                // Nếu game này trùng với old value thì select nó
                if (game === oldGameName) option.selected = true;
                gameSelect.appendChild(option);
            });
        }

        categorySelect.addEventListener('change', function() {
            updateGameOptions(this.value);
        });

        // Chạy 1 lần khi load trang để phòng trường hợp validate fail mà mất danh sách game
        if (categorySelect.value) {
            updateGameOptions(categorySelect.value);
        }
    </script>
@endsection
