@extends('adminlte::page')

@section('title', 'Quản lý User')

@section('content_header')
@stop

@section('content')
    <button class="btn btn-primary mb-3 mt-3" data-toggle="modal" data-target="#addUserModal">
        + Thêm User
    </button>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Tên</th>
                <th>Email</th>
                <th>Role</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-{{ strtolower($user->role) == 'admin' ? 'success' : 'secondary' }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-xs btn-warning editUserBtn" data-toggle="modal" data-target="#editUserModal"
                            data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}"
                            data-role="{{ strtolower($user->role) }}">
                            Sửa
                        </button>
                        <button class="btn btn-xs btn-danger" data-toggle="modal"
                            data-target="#deleteUserModal{{ $user->id }}">
                            Xóa
                        </button>
                        <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Xác nhận xóa</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        Bạn có chắc muốn xóa <strong>{{ $user->name }}</strong> không?
                                    </div>
                                    <div class="modal-footer">
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @include('admin.users.create')
    @include('admin.users.edit')
    {{ $users->links() }}
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.editUserBtn').on('click', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let email = $(this).data('email');
                let role = $(this).data('role');

                // set giá trị vào form
                $('#editName').val(name);
                $('#editEmail').val(email);
                $('#editRole').val(role).change();

                // update action cho form
                let url = "{{ url('admin/users') }}/" + id;
                $('#editUserForm').attr('action', url);
            });
        });
    </script>

    {{-- Nếu có lỗi thì tự mở lại modal thêm user --}}
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var myModal = new bootstrap.Modal(document.getElementById('addUserModal'));
                myModal.show();
            });
        </script>
    @endif
@endsection
