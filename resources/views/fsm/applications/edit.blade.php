<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--
An Edit Layout for all forms
--}}
{{--Extend the main layout--}}
@extends('layouts.dashboard')
{{--Add sections for the main layout--}}
@section('title', $page_title)
{{--Add sections for the index layout--}}

{{--Include the layout inside the main content section--}}
@section('content')
    <div class="card card-info">
        @include('layouts.components.error-list')
        @include('layouts.components.success-alert')
        @include('layouts.components.error-alert')
        <div class="card-body">
            {!! Form::open(['url' => $formAction, 'class' => 'form-horizontal','method'=>'PATCH']) !!}
            @include('layouts.partial-form', ['submitButtonText' => 'Save'])
            {!! Form::close() !!}
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function autoFillDetails() {
            $(document).ready(function() {
                if ($("input[name='autofill']:checked").val() === 'on') {
                    $("input[name='applicant_name']").val($("input[name=customer_name]").val());
                    $("#applicant_gender").val($("#customer_gender").val());
                    $("input[name='applicant_contact']").val($("input[name=customer_contact]").val());
//                    $("input[name='applicant_name']").attr('disabled','disabled');
//                    $("#applicant_gender").attr('disabled','disabled');
//                    $("input[name='applicant_contact']").attr('disabled','disabled');
                } else {
                    $("input[name='applicant_name']").val('');
                    $("#applicant_gender").val('');
                    $("input[name='applicant_contact']").val('');
                    $("input[name='applicant_name']").removeAttr('disabled');
                    $("#applicant_gender").removeAttr('disabled');
                    $("input[name='applicant_contact']").removeAttr('disabled');
                }
            });
        }

        function emptyAutoFields() {
            $('#containment_code').val('');
            $('#ward').val('');
            $('#customer_name').val('');
            $('#customer_gender').val('');
            $('#customer_contact').val('');
            $("input[name='applicant_name']").val('');
            $("#applicant_gender").val('');
            $("input[name='applicant_contact']").val('');
            $("input[name='applicant_name']").removeAttr('disabled');
            $("#applicant_gender").removeAttr('disabled');
            $("input[name='applicant_contact']").removeAttr('disabled');
            $("input[name='autofill']").prop('checked', false);
        }

        function onAddressChange() {
            emptyAutoFields();
            if($('#bin').find(":selected").text() === 'Address Not Found'){
                $('#building-if-address').hide();
                $("#building-if-address :input").each(function () {
                    $(this).attr("disabled",true);
                });
                $('#building-if-not-address').show();
                $("#building-if-not-address :input").each(function () {
                    $(this).attr("disabled",false);
                });
                $("input[type='submit']").removeAttr('disabled');
            }else {
                $('#building-if-not-address').hide();
                $("#building-if-not-address :input").each(function () {
                    $(this).attr("disabled",true);
                });
                $('#building-if-address').show();
                $("#building-if-address :input").each(function () {
                    $(this).attr("disabled",false);
                });

                if ($('#bin').val()!=''){
                    $.ajax(
                        {
                            url: "{{ route('application.get-building-details') }}",
                            data: {
                                "house_number" : $('#bin').val()
                            },
                            success: function (res) {
                                if (res.status === true){
                                    let containments = '';
                                    res.containments.forEach(function (containment) {
                                        containments+=containment.id + ' ';
                                    })
                                    $('#customer_name').val(res.customer_name);
                                    if(res.customer_gender == "Male"){
                                        var cgender = "M";
                                    } else if(res.customer_gender == "Female") {
                                        var cgender = "F";
                                    } else if(res.customer_gender == "Others") {
                                        var cgender = "O";
                                    }
                                   
                                    $('#customer_gender').val(cgender);
                                    $('#customer_contact').val(res.customer_contact);
                                    $('#containment_code').val(containments);
                                    $('#ward').val(res.ward);
                                    $("input[type='submit']").removeAttr('disabled');
                                } else if (res.status === false) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: "There is an ongoing application for this address!",
                                    });
                                    emptyAutoFields();
                                    $("input[type='submit']").attr('disabled','disabled');
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: "Error!",
                                    });
                                    emptyAutoFields();
                                }

                            },
                            error: function (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: err.responseJSON.error,
                                });
                                emptyAutoFields();
                                $("input[type='submit']").attr('disabled','disabled');
                            }
                        }
                    );
                    var studentSelect = $('#bin');
                }
            }
        }

        $(document).ready(function() {

            let house_number = '{{ $application->bin }}';
            var option = new Option(house_number, house_number, true, true);
            $('#bin').val(house_number).trigger('change');

            // manually trigger the `select2:select` event
            $('#bin').trigger('select2:select');

            $('#proposed_emptying_date').daterangepicker({
                minDate: moment(),
                singleDatePicker: true,
                autoUpdateInput: false,
                showDropdowns:true,
                autoApply:true,
                drops:"auto"
            });
            $('#proposed_emptying_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            });

            $('#proposed_emptying_date').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            if ('{{ old('address') }}'!==''){
                $('#address').select2().val('{{ old('address') }}').trigger('change');
                onAddressChange();
            }

            $('#bin').on('change',onAddressChange)
        });
    </script>
@endpush
