<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--Extend the main layout--}}
@extends('layouts.dashboard')
{{--Add sections for the main layout--}}
@section('title', 'Add Application')
{{--Add sections for the index layout--}}

{{--Include the layout inside the main content section--}}
@section('content')
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    {!! Form::open(['url' => route('application.store'), 'class' => 'form-horizontal', 'id' => 'create_application_form']) !!}
    @include('layouts.partial-form',["submitButtonText" => 'Save',"cardForm"=>true])
    {!! Form::close() !!}
@endsection

@push('scripts')
    <script>
        function autoFillDetails() {
            $(document).ready(function() {
                if ($("input[name='autofill']:checked").val() === 'on') {
                    $("input[name='applicant_name']").val($("input[name=customer_name]").val());
                    $("#applicant_gender").val($("#customer_gender").val());
                    $("input[name='applicant_contact']").val($("input[name=customer_contact]").val());

                } else {
                    $("input[name='applicant_name']").val('');
                    $("#applicant_gender").val('');
                    $("input[name='applicant_contact']").val('');
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
                    displayAjaxLoader();
                    $.ajax(
                        {
                            url: "{{ route('application.get-building-details') }}",
                            data: {
                                "bin" : $('#bin').val()
                            },
                            success: function (res) {
                                if (res.status === true){
                                    let containments = '';
                                    res.containments.forEach(function (containment) {
                                        containments+=containment.id + ' ';
                                    })
                                    $('#customer_name').val(res.customer_name).attr('disabled',true);
                                    if(res.customer_gender == "Male"){
                                        var cgender = "Male";
                                    } else if(res.customer_gender == "Female") {
                                        var cgender = "Female";
                                    } else if(res.customer_gender == "Others") {
                                        var cgender = "Others";
                                    }
                                   
                                    $('#customer_gender').val(cgender).attr('disabled',true);;
                                    $('#customer_contact').val(res.customer_contact).attr('disabled',true);;
                                    $('#containment_code').val(containments).attr('disabled',true);;
                                    $('#household_served').val(res.household_served).attr('disabled',true);;
                                    $('#population_served').val(res.population_served).attr('disabled',true);;
                                    $('#toilet_count').val(res.toilet_count).attr('disabled',true);;
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
                                removeAjaxLoader();
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
                    )
                }
            }
        }

        $(document).ready(function() {
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
            $('#bin').prepend('<option selected=""></option>').select2({
                ajax: {
                    url:"{{ route('building.get-house-numbers-containments') }}",
                    data: function (params) {
                        return {
                            search: params.term,
                            road_code: $('#road_code').val(),
                            page: params.page || 1
                        };
                    },
                },
                placeholder: 'House Number / BIN',
                allowClear: true,
                closeOnSelect: true,
                width: '100%'
            });
            $('#road_code').prepend('<option selected=""></option>').select2({
                ajax: {
                    url:"{{ route('roadlines.get-road-names') }}",
                    data: function (params) {
                        return {
                            search: params.term,
                            house_number: $('#house_number').val(),
                            page: params.page || 1
                        };
                    },
                },
                placeholder: 'Street Name / Street Code',
                allowClear: true,
                closeOnSelect: true,
                width: '100%'
            });

            if ('{{ old('address') }}'!==''){
                $('#address').select2().val('{{ old('address') }}').trigger('change');
                onAddressChange();
            }

            $('#bin').on('change',onAddressChange);

            $('#create_application_form').on('submit',function (e) {
                $("input[value='Add']").attr('disabled',true);
            })
        });
    </script>
@endpush

