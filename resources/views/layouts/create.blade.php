<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--
A create Layout for all forms
--}}
<div class="card card-info">
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    <div class="card-body">
        {!! Form::open(['url' => $formAction, 'class' => 'form-horizontal']) !!}
        @include('layouts.partial-form', ['submitButtonText' => 'Add'])
        {!! Form::close() !!}
    </div>
</div>

