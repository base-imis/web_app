/*
    Function for delete action
*/
function deleteAction(form) {
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
            form.submit();
        }
    });
}
/*
    Function for filtering DataTable
*/
function filterDataTable(dataTable) {
    $('#filter-form').on('submit', function (e) {
        e.preventDefault();
        dataTable.ajax.reload();
    });
}
/*
    Function for resetting filter and DataTable
*/
function resetDataTable(dataTable) {
    $('#reset-filter').on('click', function (e) {
        e.preventDefault();
        localStorage.clear();
        $('#filter-form')[0].reset();
        $('#filter-form').find('select').each(function (index, el) {
            $(el).trigger("change");
        });
        dataTable.ajax.reload();
    });
}

/*
    Function for displaying ajax loader
*/
function displayAjaxLoader(message) {
    $("#loading-content").text(message);
    $(".loading-overlay")[0].classList.toggle('is-active');
}

function removeAjaxLoader() {
    $("#loading-content").text("");
    $(".loading-overlay")[0].classList.toggle('is-active');
}

/*
    Function for export
*/

$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function exportToCsv(event) {
    event.preventDefault();
    var filterData = $('#filter-form').serializeObject();
    displayAjaxLoader();
    try {
        var a = document.createElement('a');
        a.href = event.target.href + "?" + $.param(filterData);
        a.download = 'report.csv';
        a.click();
        a.remove();
        removeAjaxLoader();
    } catch (e) {
        removeAjaxLoader();
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: "Error!",
        });
    }
}
// function to prevent multiple submits in form
function preventMultipleSubmit() {
    $('.prevent-multiple-submits').attr('disabled', 'true');
};

// function to check if user is to be created or not
function createUser() {
    $('#create_user').on('change', function () {
        if ($('#create_user').is(":checked")) {
            $('#user-password').show();
        }
        else {
            $('#user-password').hide();

        }
    });
}
// building page onload functions for dynamic display according to dropdown

function dynamicBuildingForm() {
    // display/ hide building_associated field
    $('#main_building').on('change', function () {
        if ($("#main_building :selected").text() == "Yes") {
            $('#building_associated').hide();
        } else if ($("#main_building :selected").text() == "No") {

            $('#building_associated').show();
        }
    });




    // show lic name when id_lic field is yes
    $('#lic_status').on('change', function () {
        if ($("#lic_status :selected").text() == "Yes") {
            $('#lic_id').show();
        } else {
            $('#lic_id').hide();
        }
    });


    $('#water-id').on('change', function () {
        if ($("#water-id :selected").text() == "Municipal/Public Water Supply") {
            $('#water-customer-id').show();
            $('#water-pipe-id').show();
        } else {
            $('#water-customer-id').hide();
            $('#water-pipe-id').hide();
        }
    });

    // hide distance from well if well presence is No and sanitaiton technology is containment
    $('#well-presence').on('change', function () {
        if ($("#well-presence :selected").text() == "Yes") {
            $('#distance-from-well').show();
        } else {
            $('#distance-from-well').hide();
        }
    });

    // hides if toilet presence No
    // shows if toilet presence Yes
    $('#toilet-presence').on('change', function () {
        if ($("#toilet-presence :selected").text() == "Yes") {
            $('#toilet-info').show();
            $('#shared-toilet').show();
            $('#toilet-connection').show();
            $('#shared-toilet-popn').show();
            $('#defecation-place').hide();
            $('#ctpt-toilet').hide();

        } else {

            $('#defecation-place').show();
            $('#toilet-info').hide();
            $('#shared-toilet').hide();
            $('#toilet-connection').hide();
            $('#shared-toilet-popn').hide();
            $('#containment-info').hide();
            $('#containment-id').hide();
            $('#drain-code').hide();
            $('#sewer-code').hide();
        }
    });

    //show ctpt field only when defecation-place is Community Toilet
    $('#defecation-place').on('change', function () {
        if ($("#defecation-place :selected").text() == "Community Toilet") {
            $('#ctpt-toilet').show();
        } else {
            $('#ctpt-toilet').hide();
        }
    });



    $('#toilet-connection').on('change', function () {
        if ($("#toilet-connection :selected").text() === "Septic Tank" || $(
            "#toilet-connection :selected").text() === "Pit/ Holding Tank") {
            $('#containment-info').show();
            $('#containment-id').hide();
            $('#drain-code').hide();
            $('#sewer-code').hide();
            $('#vacutug-accessible').show();

        }
        else if ($("#toilet-connection :selected").text() == "Shared Septic Tank") {
            $('#containment-id').show();
            $('#containment-info').hide();
            $('#drain-code').hide();
            $('#sewer-code').hide();
            $('#vacutug-accessible').hide();
        }
        else if ($("#toilet-connection :selected").text() == "Drain Network") {
            $('#drain-code').show();
            $('#containment-id').hide();
            $('#containment-info').hide();
            $('#sewer-code').hide();
            $('#vacutug-accessible').hide();
        }
        else if ($("#toilet-connection :selected").text() == "Sewer Network") {
            $('#drain-code').hide();
            $('#containment-id').hide();
            $('#containment-info').hide();
            $('#sewer-code').show();
            $('#vacutug-accessible').hide();
        }
        else {
            $('#containment-id').hide();
            $('#containment-info').hide();
            $('#drain-code').hide();
            $('#sewer-code').hide();
            $('#vacutug-accessible').hide();

        }
    });

    $('#containment-type').on('change', function () {

        var selectedText = $("#containment-type option:selected").text();
        var showOptions = [
            "Septic Tank connected to Drain Network",
            "Lined Pit connected to Drain Network"
        ];
        if (showOptions.includes(selectedText)) {
            $('#drain-code').show();
        } else {
            $('#drain-code').hide();
        }
    });

    $('#containment-type').on('change', function () {

        var selectedText = $("#containment-type option:selected").text();
        var showOptions = [
            "Septic Tank connected to Sewer Network",
            "Lined Pit connected to Sewer Network"
        ];
        if (showOptions.includes(selectedText)) {
            $('#sewer-code').show();
        } else {
            $('#sewer-code').hide();
        }
    });


    $('#containment-type, #pit-shape').on('change', function () {

        var selectedText = $("#containment-type option:selected").text();
        var showOptions = [
            "Double Pit",
            "Permeable/ Unlined Pit/Holding Tank",
            "Lined Pit connected to a Soak Pit",
            "Lined Pit connected to Water Body",
            "Lined Pit connected to Open Ground",
            "Lined Pit connected to Sewer Network",
            "Lined Pit connected to Drain Network",
            "Lined Pit without Outlet",
            "Lined Pit with Unknown Outlet Connection",
        ];
        if (showOptions.includes(selectedText)) {
            $('#pit-shape').show();
            $('#tank-depth').hide();
            $('#tank-width').hide();
            $('#tank-length').hide();
            $('#septic-tank').hide();
        }
        else {
            $('#tank-length').show();
            $('#septic-tank').show();
            $('#pit-shape').hide();
        }

        // Check if the selected text is in the array of showOptions and if the pit shape is "Cylindrical"
        if (showOptions.includes(selectedText) && ($("#pit-shape :selected").text() ==
            "Cylindrical")) {
            $('#pit-depth').show();
            $('#pit-size').show();
            $('#tank-depth').hide();
            $('#tank-width').hide();
            $('#tank-length').hide();
        } else {
            $('#pit-size').hide();
            $('#pit-depth').hide();
            $('#tank-depth').show();
            $('#tank-width').show();
            $('#tank-length').show();

        }


        if (!showOptions.includes(selectedText) || $("#pit-shape :selected").text() !== "Rectangular") {
            $('#tank-length').show();
        } else {
            $('#tank-length').hide();
        }

        if ($("#pit-shape :selected").text() == "Cylindrical") {
            $('#tank-length').hide();
        } else {
            $('#tank-length').show();
        }
        if (!showOptions.includes(selectedText)) {
            $('#tank-length').show();
        }

    });


    $('#functional_use_id').on('change', function () {
        var html = '<option value="">Use Category  of Building</option>';

        var functional_use = $(this).val();
        if (functional_use) {
            $.each(usecatgs[functional_use], function (key, value) {
                html += '<option value="' + key + '">' + value + '</option>';
            });
            if (functional_use == "1" || functional_use == "15") {
                $('#office-business').hide();
            } else {
                $('#office-business').show();
            }


        }

        $('#use_category_id').html(html);
    });

    //ajax
    $(document).on('ready', function () {

        // searchable dropdown for building_associated_to
        $('#building_associated_to').prepend('<option selected=""></option>').select2({

            ajax: {
                url: "{{ route('building.get-house-numbers-all') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'BIN of Main Building',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });
        $('#road_code').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('roadlines.get-road-names') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'Road Code - Road Name',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });

        $('#watersupply_pipe_code').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('watersupply.get-watersupply-code') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        // ward: $('#ward').val(),
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'Water Supply Pipe Line Code',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });
        $('#sewer_code').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('sewerlines.get-sewer-names') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        // ward: $('#ward').val(),
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'Sewer Code',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });

        $('#build_contain').prepend('<option selected=""></option>').select2({
            ajax: {
                url: "{{ route('building.get-house-numbers-containments') }}",
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
            },
            placeholder: 'BIN of Pre Connected Building',
            allowClear: true,
            closeOnSelect: true,
            width: '85%',
        });




    });

    //show use category only when functional use is filled

        var functionalUseId = $('#functional_use_id').val();
        if (functionalUseId) {
            $('#use-category').show();
        } else {
            $('#use-category').hide();
        }

        $('#functional_use_id').on('change', function() {
            var functionalUseId = $(this).val();
            if (functionalUseId) {
                $('#use-category').show();
            } else {
                $('#use-category').hide();
            }
        });


    // to hide population , family count , total popn  if functional == Community/Public toilets
    $('#functional_use_id').on('change', function () {
        if ($("#functional_use_id :selected").text() == "Community/Public Toilet") {
            $('#population-info').hide();
            $('#family-count').hide();
            $('#male-population').hide();
            $('#female-population').hide();
            $('#other-population').hide();
           $('#male-diff-population').hide();
           $('#female-diff-population').hide();
           $('#other-diff-population').hide();
           $('#shared-toilet').hide();
           $('#shared-toilet-popn').hide();
        } else {
            $('#male-population').show();
            $('#female-population').show();
            $('#other-population').show();
            $('#population-info').show();
            $('#family-count').show();
            $('#male-diff-population').show();
            $('#female-diff-population').show();
            $('#other-diff-population').show();
            $('#shared-toilet').show();
            $('#shared-toilet-popn').show();


        }
    });

    // To set toilet status == 1 and show related fields(also applied in backend part (Building Structure Service))
    $('#functional_use_id').on('change', function () {
        var selectedText = $("#functional_use_id option:selected").text();
        if (selectedText == "Community/Public Toilet") {
            $('#toilet_status').val('1'); // Set toilet_status to 'Yes'
            $('#toilet-info').show();
            // $('#shared-toilet').show();
            $('#toilet-connection').show();
            // $('#shared-toilet-popn').show();
            $('#defecation-place').hide();
            $('#ctpt-toilet').hide();
        }
    });

}

document.addEventListener('DOMContentLoaded', function () {
    var populationFields = document.querySelectorAll(
        'input[name="male_population"], input[name="female_population"], input[name="other_population"]'
    );
    populationFields.forEach(function (field) {
        field.addEventListener('input', function () {
            var malePopulation = parseInt(document.querySelector(
                'input[name="male_population"]').value) || 0;
            var femalePopulation = parseInt(document.querySelector(
                'input[name="female_population"]').value) || 0;
            var otherPopulation = parseInt(document.querySelector(
                'input[name="other_population"]').value) || 0;
            var totalPopulation = malePopulation + femalePopulation + otherPopulation;
            document.querySelector('input[name="population_served"]').value =
                totalPopulation;
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var diffAbledFields = document.querySelectorAll(
        'input[name="diff_abled_male_pop"], input[name="diff_abled_female_pop"], input[name="diff_abled_others_pop"]'
    );

    diffAbledFields.forEach(function (field) {
        field.addEventListener('input', function () {
            var maleDiffAbledPopulation = parseInt(document.querySelector(
                'input[name="diff_abled_male_pop"]').value) || 0;
            var femaleDiffAbledPopulation = parseInt(document.querySelector(
                'input[name="diff_abled_female_pop"]').value) || 0;
            var otherDiffAbledPopulation = parseInt(document.querySelector(
                'input[name="diff_abled_others_pop"]').value) || 0;

            var totalDiffAbledPopulation = maleDiffAbledPopulation +
                femaleDiffAbledPopulation + otherDiffAbledPopulation;

            document.querySelector('input[name="diff_abled_pop"]').value =
                totalDiffAbledPopulation;
        });
    });
});



function onloadDynamicContainmentType() {
    $('#containment-info').show();
    var selectedText = $("#containment-type option:selected").text();
    var showOptions = [
        "Septic Tank connected to Drain Network",
        "Lined Pit connected to Drain Network"
    ];
    if (showOptions.includes(selectedText)) {
        $('#drain-code').show();
    } else {
        $('#drain-code').hide();
    }

    var selectedText = $("#containment-type option:selected").text();
    var showOptions = [
        "Septic Tank connected to Sewer Network",
        "Lined Pit connected to Sewer Network"
    ];
    if (showOptions.includes(selectedText)) {
        $('#sewer-code').show();
    } else {
        $('#sewer-code').hide();
    }


    var selectedText = $("#containment-type option:selected").text();
    var showOptions = [
        "Double Pit",
        "Permeable/ Unlined Pit/Holding Tank",
        "Lined Pit connected to a Soak Pit",
        "Lined Pit connected to Water Body",
        "Lined Pit connected to Open Ground",
        "Lined Pit connected to Sewer Network",
        "Lined Pit connected to Drain Network",
        "Lined Pit without Outlet",
        "Lined Pit with Unknown Outlet Connection",
    ];
    if (showOptions.includes(selectedText)) {
        $('#pit-shape').show();
        $('#tank-depth').hide();
        $('#tank-width').hide();
        $('#tank-length').hide();
        $('#septic-tank').hide();
    }
    else {
        $('#septic-tank').show();
        $('#pit-shape').hide();
    }

    // Check if the selected text is in the array of showOptions and if the pit shape is "Cylindrical"
    if (showOptions.includes(selectedText) && ($("#pit-shape :selected").text() ==
        "Cylindrical")) {
        $('#pit-depth').show();
        $('#pit-size').show();
        $('#tank-depth').hide();
        $('#tank-width').hide();
        $('#tank-length').hide();
    } else {
        $('#pit-size').hide();
        $('#pit-depth').hide();
        $('#tank-depth').show();
        $('#tank-width').show();
        $('#tank-length').show();

    }

    // if functional use is public/community Toilets then hide the fields

}




