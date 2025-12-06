@extends('adminlte::page')

@section('title', 'Quản lý Giải đấu')

@section('content_header')
    <h1></h1>
@stop

@section('content')

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 10%;">Ảnh</th>
                <th style="width: 50%;">Tên giải đấu</th>
                <th style="width: 10%;">Trạng thái</th>
                <th style="width: 10%;">Ngày bắt đầu</th>
                <th style="width: 20%;">Hành động</th>
            </tr>
        </thead>
        <tbody class="">
            @forelse ($tournaments as $tournament)
                <tr>
                    <td style="width: 120px;">
                        <img src="{{ Str::startsWith($tournament->thumbnail, 'home/')
                            ? asset($tournament->thumbnail)
                            : asset('storage/' . $tournament->thumbnail) }}"
                            alt="{{ $tournament->name }}" style="width: 100px; height: 60px; object-fit: cover;">
                    </td>
                    <td class="fw-bold">{{ $tournament->name }}</td>
                    <td>
                        @php
                            $statusColor = match ($tournament->status) {
                                'open' => 'primary',
                                'ongoing' => 'warning',
                                'finished' => 'success',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $statusColor }}">
                            {{ ucfirst($tournament->status) }}
                        </span>
                    </td>
                    <td class="fw-bold">{{ $tournament->start_date }}</td>
                    <td>
                        <button class="btn btn-xs btn-primary editTournamentBtn" data-toggle="modal"
                            data-target="#editTournamentModal" data-id="{{ $tournament->id }}"
                            data-name="{{ $tournament->name }}" data-status="{{ $tournament->status }}">
                            Xem
                        </button>
                        <button class="btn btn-xs btn-danger" data-toggle="modal"
                            data-target="#deleteTournamentModal{{ $tournament->id }}">
                            Xóa
                        </button>

                        {{-- Modal Xóa --}}
                        <div class="modal fade" id="deleteTournamentModal{{ $tournament->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Xác nhận xóa</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        Bạn có chắc muốn xóa <strong>{{ $tournament->name }}</strong> không?
                                    </div>
                                    <div class="modal-footer">
                                        <form action="{{ route('admin.tournaments.destroy', $tournament->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Hủy</button>
                                            <button type="button" class="btn btn-danger confirmDeleteBtn"
                                                data-id="{{ $tournament->id }}">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-muted">Chưa có giải đấu nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $tournaments->links() }}

    {{-- @include('admin.tournaments.create')
    @include('admin.tournaments.edit') --}}
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.editTournamentBtn').on('click', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');

                $('#editTournamentName').val(name);
                $('#editTournamentStatus').val(status);

                let url = "{{ url('admin/tournaments') }}/" + id;
                $('#editTournamentForm').attr('action', url);
            });
        });

        $(document).ready(function() {
            // Nút Xem
            $('.editTournamentBtn').on('click', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');

                $('#editTournamentName').val(name);
                $('#editTournamentStatus').val(status);

                let url = "{{ url('admin/tournaments') }}/" + id;
                $('#editTournamentForm').attr('action', url);
            });

            // Nút Xóa trong modal
            $('.confirmDeleteBtn').on('click', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: '/admin/tournaments/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Xóa hàng khỏi bảng mà không reload
                        $('button[data-target="#deleteTournamentModal' + id + '"]').closest(
                            'tr').remove();
                        $('#deleteTournamentModal' + id).modal('hide');
                        // FIX: xóa lớp mờ và khôi phục cuộn
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('overflow', 'auto');
                        toastr.success('Đã xóa giải đấu thành công!');
                    },
                    error: function(xhr) {
                        toastr.error('Có lỗi xảy ra khi xóa!');
                        console.log(xhr.responseText);
                    }
                });
            });
        });
    </script>
@stop
