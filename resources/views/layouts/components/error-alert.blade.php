{{--
A component for error alert/toast
--}}
@if(!empty($error))
    <div class="alert alert-error alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-exclamation-circle"></i> Success!</h5>
        {{ $error }}
    </div>
@endif
