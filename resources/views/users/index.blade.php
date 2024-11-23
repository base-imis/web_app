<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', 'Users')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    @can('Add User')
                        <a href="{{ action('Auth\UserController@create') }}" class="btn btn-info">Create User</a>
                        <a href="{{ action('Auth\UserController@export') }}" class="btn btn-info">Export to CSV</a>
                    @endcan



                    {{--
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            --}}
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap table-striped">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>User Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @if (!$user->hasRole('Super Admin'))
                                    <tr>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>
                                            <?php
                                            $user_roles = [];
                                            foreach ($user->roles as $role) {
                                                $user_roles[] = $role->name;
                                            }
                                            echo implode(', ', $user_roles);

                                            ?>
                                        </td>
                                        <td>{{ $user->user_type }}</td>
                                        <td>{{ get_user_status_description($user->status) }}</td>
                                        <td>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id]]) !!}
                                            @can('Edit User')
                                                <a title="Edit" href="{{ action('Auth\UserController@edit', [$user->id]) }}"
                                                    class="btn btn-info btn-xs"><i class="fa fa-edit"></i></a>
                                            @endcan
                                            @can('View User')
                                                <a title="Detail"href="{{ action('Auth\UserController@show', [$user->id]) }}"
                                                    class="btn btn-info btn-xs"><i class="fa fa-list"></i></a>
                                            @endcan
                                            @can('View User')
                                                <a title="History"
                                                    href="{{ action('Auth\UserController@getLoginActivity', [$user->id]) }}"
                                                    class="btn btn-info btn-xs"><i class="fa-solid fa-right-to-bracket"></i></a>
                                            @endcan
                                            @can('Delete User')
                                                <button title="Delete" type="submit"
                                                    class="btn btn-danger btn-xs delete">&nbsp;<i
                                                        class="fa fa-trash"></i>&nbsp;</button>
                                            @endcan
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $('.delete').on('click', function(e) {
            var form = $(this).closest("form");
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        'Deleted!',
                        'User will be deleted.',
                        'success'
                    ).then((willDelete) => {
                        if (willDelete) {
                            form.submit();
                        }
                    })
                }
            })

        });


        $("#export").on("click", function(e) {

            e.preventDefault();

            window.location.href = "{!! url('users/users/export?searchData=') !!}" + searchData +

        });
    </script>
@endpush
