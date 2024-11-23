@extends('layouts.dashboard')
@section('title', 'Roles')
@section('content')
<div class="row">
  <div class="col-12">
  <div class="card">
        <div class="card-header">
          @can('Add Role')
          <a href="{{ url('auth/roles/create') }}" class="btn btn-info">Create Role</a>
          @endcan
        </div><!-- /.box-header -->
        <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($roles as $role)
                <tr>
                  <td>{{ $role->name }}</td>
                  <td>
                    <form action="{{ url('auth/roles/' . $role->id) }}" method="POST">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        @can('Edit Role')
                        <a href="{{ url('auth/roles/' . $role->id . '/edit') }}" class="btn btn-info btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        @endcan
                        @can('Delete Role')
                        <button type="submit" class="btn btn-danger btn-xs delete" title="Delete">
                            &nbsp;<i class="fa fa-trash"></i>&nbsp;
                        </button>
                        @endcan
                    </form>
                </td>

                </tr>
              @endforeach
            </tbody>
          </table>
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </div><!-- /.col -->
  </div><!-- /.row -->

@stop

@push('scripts')
<script>
$('.delete').on('click', function(e) {
         var form =  $(this).closest("form");
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
      'Role will be deleted.',
      'success'
    ).then((willDelete) => {
           if (willDelete) {
             form.submit();
           }
         })
  }
})

});
</script>
@endpush
