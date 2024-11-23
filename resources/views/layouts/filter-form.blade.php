<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--
A Layout for Filter
--}}

<div class="card-body">
    @foreach($formFields as $formFieldGroup)
    <div class="form-group row">

        @foreach($formFieldGroup as $formField)
        {!! Form::label($formField->labelFor,$formField->label,['class' => $formField->labelClass]) !!}

        <div class=" col-md-2">
            @if($formField->inputType === 'text')
            {!! Form::text($formField->inputId,$formField->inputValue,['class' => $formField->inputClass, 'placeholder'
            => $formField->placeholder,'autocomplete'=>$formField->autoComplete]) !!}
            @endif
            @if($formField->inputType === 'number')
            {!! Form::number($formField->inputId,$formField->inputValue,['class' => $formField->inputClass,
            'placeholder' => $formField->placeholder]) !!}
            @endif
            @if($formField->inputType === 'select')
            {!! Form::select($formField->inputId,$formField->selectValues,$formField->selectedValue,['class' =>
            $formField->inputClass, 'placeholder' => $formField->placeholder]) !!}
            @endif
            @if($formField->inputType === 'label')
            {!! Form::label($formField->inputId,$formField->labelValue,['class' => $formField->inputClass]) !!}
            @endif
            @if($formField->inputType === 'multiple-select')
            {!! Form::select($formField->inputId,$formField->selectValues,$formField->selectedValue,['class' =>
            $formField->inputClass,'disabled' => $formField->disabled,'autocomplete'=>$formField->autoComplete]) !!}
            @push('scripts')
            <script>
            $(document).ready(function() {
                $('#{{ $formField->inputId }}').prepend('<option selected=""></option>').append(
                    '<option value="-1">Address Not Found</option>').select2({
                    placeholder: '{{ $formField->placeholder }}',
                    matcher: function(params, data) {
                        if (data.id === "-1") {
                            return data;
                        } else {
                            return $.fn.select2.defaults.defaults.matcher.apply(this, arguments);
                        }
                    },
                    closeOnSelect: true,
                    width: 'select'
                });
            });
            </script>
            @endpush
            @endif

        </div>
        @endforeach
    </div>
    @endforeach
</div>