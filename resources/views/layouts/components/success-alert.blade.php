{{--
A component for success alert/toast
--}}
@if(!empty($success))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-check"></i> Success!</h5>
        {{ $success }}
    </div>
@endif
