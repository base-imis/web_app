<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
<div class="form-group row required">
        {!! Form::label('type', 'Toilet Type', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-4">
            {!! Form::select('type', ['Community Toilet' => 'Community Toilet', 'Public Toilet' => 'Public Toilet'], null, [
                'class' => 'form-control type',
                'placeholder' => 'Toilet Type',
            ]) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('name', 'Toilet Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-4">
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Toilet Name']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('ward', 'Ward Number', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-4">
            {!! Form::select('ward', $ward, null, ['class' => 'form-control', 'placeholder' => 'Ward Number']) !!}
        </div>
    </div>

    <div class="form-group row ">
            {!! Form::label('location_name', 'Location', ['class' => 'col-sm-3 control-label ']) !!}
            <div class=col-sm-4>
                {!! Form::text('location_name', null, ['class' => 'form-control', 'placeholder' => 'Location']) !!}
            </div>
        </div>
        <div class="form-group row required" id="bin" >
            {!! Form::label('bin', 'House Number / BIN', ['class' => 'col-sm-3 control-label ']) !!}
            <div class=col-sm-4>
                {!! Form::select('bin', $bin, null, ['class' => 'form-control col-sm-10 bin', 'placeholder' => 'House Number']) !!}
            </div>
        </div>

        <div class="form-group row " id="access_frm_nearest_road">
        {!! Form::label('access_frm_nearest_road', 'Distance from Nearest Road (m)', [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class=col-sm-4>
            {!! Form::number('access_frm_nearest_road', null, [
                'class' => 'form-control access_frm_nearest_road',
                'placeholder' => 'Distance from Nearest Road (m)',
            ]) !!}
        </div>
    </div>

    <div class="form-group row required " id="status">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('status', $operational, null, ['class' => 'form-control chosen-select ', 'placeholder' => 'Status ']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('caretaker_name', 'Caretaker Name', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('caretaker_name', null, ['class' => 'form-control', 'placeholder' => 'Caretaker Name']) !!}
        </div>
    </div>
    <div class="form-group row required" id="caretaker_gender">
        {!! Form::label('caretaker_gender','Caretaker Gender',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('caretaker_gender', ['Male' => 'Male', 'Female' => 'Female' , 'Others' => 'Others'], null, ['class' => 'form-control caretaker_gender', 'placeholder' => 'Caretaker Gender']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('caretaker_contact_number', 'Caretaker Contact ', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::number('caretaker_contact_number', null, [
                'class' => 'form-control',
                'placeholder' => 'Caretaker Contact',
            ]) !!}
        </div>
    </div>

    
    <div class="form-group row required">
        {!! Form::label('owner', 'Owning Institution ', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('owner', ['Community Based Organization' => 'Community Based Organization','Government' => 'Government','Municipality' => 'Municipality','NGO' => 'NGO', 'Private' => 'Private','Public Private Partnership' => 'Public Private Partnership', 'Self Help Group' => 'Self Help Group'], null, [
                'class' => 'form-control' , 'id' => 'owning_institution',
                'placeholder' => ' Owning Institution ',
            ]) !!}
        </div>
    </div>

    <div class="form-group row" id="name_of_owning_institution" style="display:none">
    {!! Form::label('owning_institution_name', 'Name of Owning Institution', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-4">
        {!! Form::text('owning_institution_name', null, ['class' => 'form-control', 'id'=>'name_of_owning_institution', 'placeholder' => 'Name of Owning Institution']) !!}
    </div>
</div>

    <div class="form-group row required">
        {!! Form::label('operator_or_maintainer', 'Operate and Maintained by', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select(
                'operator_or_maintainer',
                ['Community Based Organization' => 'Community Based Organization','Government' => 'Government','Municipality' => 'Municipality','NGO' => 'NGO', 'Private' => 'Private','Public Private Partnership' => 'Public Private Partnership', 'Self Help Group' => 'Self Help Group'],
                null,
                ['class' => 'form-control', 'id' => 'operator_or_maintainer','placeholder' => ' Operate and Maintained by '],
            ) !!}
        </div>
    </div>

    <div class="form-group row " id="name_operator_or_maintainer" style="display:none">
            {!! Form::label('operator_or_maintainer_name', 'Name of Operate and Maintained by', ['class' => 'col-sm-3 control-label ']) !!}
            <div class=col-sm-4>
                {!! Form::text('operator_or_maintainer_name', null, ['class' => 'form-control','id' => 'name_operator_or_maintainer', 'placeholder' => 'Name of Operate and Maintained by']) !!}
            </div>
        </div>

    <div class="form-group row ">
        {!! Form::label('total_no_of_toilets', 'Total Number of Seats ', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('total_no_of_toilets', null, ['class' => 'form-control total_no_of_toilets', 'placeholder' => 'Total Number of Seats']) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('total_no_of_urinals', 'Total Number of Urinals ', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('total_no_of_urinals', null, ['class' => 'form-control total_no_of_urinals', 'placeholder' => 'Total Number of Urinals']) !!}
        </div>
    </div>
    
    <div class="form-group row " id="male_or_female_facility">
        {!! Form::label('male_or_female_facility', 'Separate Facility for Male and Female', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('male_or_female_facility', [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control male_or_female_facility',
                'placeholder' => ' Separate Facility for Male and Female ',
            ]) !!}
        </div>
    </div>
    <div class="form-group row " id="male_seats">
        {!! Form::label('male_seats', 'No. of Seats for Male Users', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('male_seats', null, ['class' => 'form-control male_seats', 'placeholder' => 'No. of Seats for Male Users']) !!}
        </div>
    </div>

    <div class="form-group row " id="female_seats">
        {!! Form::label('female_seats', 'No. of Seats for Female Users', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('female_seats', null, ['class' => 'form-control female_seats', 'placeholder' => 'No. of Seats for Female Users']) !!}
        </div>
    </div>

    <div class="form-group row " id="handicap_facility">
        {!! Form::label('handicap_facility','Separate Facility for People with Disability',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('handicap_facility',  [true => 'Yes', false => 'No'], null, ['class' => 'form-control chosen-select handicap_facility','placeholder' => ' Separate Facility for People with Disability ']) !!}
        </div>
    </div>

    <div class="form-group row " id="pwd_seats">
        {!! Form::label('pwd_seats', 'No. of Seats for People with Disability', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('pwd_seats', null, ['class' => 'form-control pwd_seats', 'placeholder' => 'No. of Seats for People with Disability']) !!}
        </div>
    </div>
  
    <div class="form-group row " id="children_facility">
        {!! Form::label('children_facility','Separate Facility for Children',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('children_facility',  [true => 'Yes', false => 'No'], null, ['class' => 'form-control chosen-select children_facility', 'placeholder' => ' Separate Facility for Children ']) !!}
        </div>
    </div>
   
    <div class="form-group row ">
        {!! Form::label('indicative_sign','Presence of Indicative Sign',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('indicative_sign', [true => 'Yes', false => 'No'], null, ['class' => 'form-control chosen-select', 'placeholder' => ' Presence of Indicative Sign ']) !!}
        </div>
    </div>
  
    <div class="form-group row " id="sanitary_supplies_disposal_facility">
        {!! Form::label('sanitary_supplies_disposal_facility','Sanitary Supplies and Disposal Facilities',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('sanitary_supplies_disposal_facility',  [true => 'Yes', false => 'No'], null, ['class' => 'form-control chosen-select sanitary_supplies_disposal_facility', 'placeholder' => 'Sanitary Supplies and Disposal Facilities']) !!}
        </div>
    </div>

    <div class="form-group row " id="separate_facility_with_universal_design">
        {!! Form::label('separate_facility_with_universal_design','Adherence with Universal Design Principles',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('separate_facility_with_universal_design',  [true => 'Yes', false => 'No'], null, ['class' => 'form-control chosen-select ','placeholder' => ' Adherence with Universal Design Principles ']) !!}
        </div>
    </div>

    <div class="form-group row " id="fee_collected">
        {!! Form::label('fee_collected','Uses Fee Collection',['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('fee_collected', [true => 'Yes', false => 'No'], null, ['class' => 'form-control chosen-select fee_collected', 'placeholder' => ' Uses Fee Collection ']) !!}
        </div>
    </div>

    <div class="form-group row " id="amount_fee_collected" style = 'display:none'>
        {!! Form::label('amount_of_fee_collected', 'Uses Fee Rate  ', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::text('amount_of_fee_collected', null, ['id'=> 'amount_of_fee_collected','class' => 'form-control', 'placeholder' => 'Uses Fee Rate']) !!}
        </div>
    </div>
    
    <div class="form-group row " id="frequency_of_fee_collected" style = 'display:none'>
   
        {!! Form::label('frequency_of_fee_collected', 'Frequency of Fee Collection ', ['class' => 'col-sm-3 control-label']) !!}
        <div class=col-sm-4>
            {!! Form::select('frequency_of_fee_collected', [], null, ['class' => 'form-control ', 'placeholder' => 'Frequency of Fee Collection', 'id' => 'frequency_select']) !!}
        </div>
    </div>  


</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\CtptController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->
@push('scripts')
   
<script>
  @if(isset($ctpt) && $ctpt->exists())
        var selectedFrequency = {!! json_encode($ctpt->frequency_of_fee_collected) !!};
    @endif


    //change in Separate facility for male and female 
    $('.male_or_female_facility').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === '1') {
            $('#male_seats, #female_seats').show();
        }
        else{
            $('#male_seats, #female_seats').hide().val('');
        }
    }).trigger('change');  

    //change in Separate facility for PWD
    $('.handicap_facility').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === '1') {
            $('#pwd_seats').show();   
        }
        else{
            $('#pwd_seats').hide().val('');
        }
    }).trigger('change');

    //change in Separate facility for children
    // $('.children_facility').on('change', function() {
    //     var selectedValue = $(this).val();
    //     if (selectedValue === '1') {
    //             $('#no_of_children_users').show();  
    //             $('.no_of_children_users').show();   

    //         } else {
    //             $('#no_of_children_users').hide().val('');
    //         }
    // }).trigger('change');
    
    $('#type').on('change', function() {
    var selectedValue = $(this).val();
    
    
    // Hide/show fee collected elements based on the selected value
    $('.fee_collected').on('change', function() {
        var value = $(this).val();
        if (value === '0') {
            $('#frequency_of_fee_collected, #amount_fee_collected').hide().val('');
        } 
         if (value === '1' && selectedValue === 'Community Toilet') {
            $('#amount_fee_collected').show();
            $('#frequency_of_fee_collected').show();
           
                $('#frequency_select').html('<option value="">Frequency of Fee Collection</option><option value="Weekly">Weekly</option><option value="Half Monthly">Half Monthly</option><option value="Monthly">Monthly</option><option value="Quartely">Quartely</option><option value="Yearly">Yearly</option>');
                $('#frequency_select').val(selectedFrequency);
         }
              if (value === '1' && selectedValue === 'Public Toilet') {
                $('#amount_fee_collected').show();
            $('#frequency_of_fee_collected').show();
                $('#frequency_select').html('<option value="">Frequency of Fee Collection</option><option value="Per Use">Per Use</option>');
                $('#frequency_select').val(selectedFrequency);
            }
        
    }).trigger('change');

        // Show/hide elements based on selectedValue
        
    
    // When owning institution is other than municipality
    $('#owning_institution').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'Municipality' || selectedValue == '') {
            $('#name_of_owning_institution').hide().val('');
        } 
        else{
            $('#name_of_owning_institution').show();
        }
    }).trigger('change');


    // When operate & maintained by is other than municipality
    $('#operator_or_maintainer').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'Municipality' || selectedValue == '') {
            $('#name_operator_or_maintainer').hide().val('');
        } 
        else{
            $('#name_operator_or_maintainer').show();
        }
    }).trigger('change');

    </script>
@endpush
