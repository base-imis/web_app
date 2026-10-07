@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="row">
    <div class="col-12">
    <div class="card">
        <div class="card-header">
          <a href="{{ action('Fsm\ContainmentController@index') }}" class="btn btn-info">Back to List</a>
        </div><!-- /.card-header -->
        <div class="card-body">
          <table id="data-table" class="table table-bordered table-striped" width="100%">
            <thead>
              <tr>
                <th>BIN </th>
                <th>Tax Code/Holding ID</th>
                <th>Structure Type</th>
                <th>Estimated Area of the Building  (m&#178;)</th>
                <th>Functional Use of Building </th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @if(!($buildings->isEmpty()))
                @foreach($buildings as $building)
                <tr>
                  <td>{{ $building->bin }}</td>
                  <td>{{ $building->tax_code }}</td>
                  <td>{{ $building->StructureType->type }}</td>
                  <td>{{ $building->estimated_area }}</td>
                  <td>{{ $building->functionalUse->name }}</td>
                  <td>
                  {!! Form::open(['method' => 'DELETE', 'action' => ['Fsm\ContainmentController@deleteBuilding', $containment->id, $building->bin]]) !!}

                    @can('Delete Building from Containment')
                    <button
                    title="Delete Connection of Containment From Building"
                    type="submit"
                    class="btn btn-danger btn-xs delete">
                    &nbsp;<i class="fa fa-trash"></i>&nbsp;
                  </button>


                    @endcan
                  {!! Form::close() !!}
                  </td> 
                </tr>
                @endforeach
              @else
                <tr>
                  <td valign="top" colspan="6">No Matching records found</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div><!-- /.card-body -->
      </div><!-- /.card -->
    </div><!-- /.col -->
  </div><!-- /.row -->

@stop

@push('scripts')
<script>
 $('.delete').on('click', function(e) {

         var form = $(this).closest("form");
                    event.preventDefault();
                    deleteAction(form || this);
     });
</script>
@endpush
