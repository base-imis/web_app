<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
    <div class="form-group required">
        {!! Form::label('name','Full Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Full Name']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('Gender',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('gender', ["M"=>"Male", "F"=>"Female"], null, ['class' => 'form-control', 'placeholder' => '--- Select Gender ---']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('username',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('username',null,['class' => 'form-control', 'placeholder' => 'Username']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('email',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('email',null,['class' => 'form-control', 'placeholder' => 'Email']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('password',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="password" class="form-control" name="password" id="password" placeholder="Password">
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('password_confirmation','Confirm Password',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password">
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('User Type',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,"Service Provider",['class' => 'form-control']) !!}
            {!! Form::text('user_type', "Service Provider", ['hidden' => 'true']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('roles',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 roles-select">
        </div>
    </div>
    {{--    <div class="form-group">
            {!! Form::label('ward',null,['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('ward',null,['class' => 'form-control', 'placeholder' => 'Ward']) !!}
            </div>
        </div>  --}}
    
    <div class="form-group required" id="service_provider">
        {!! Form::label('service_provider_id','Service Provider',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$serviceProviders[Auth::user()->service_provider_id],['class' => 'form-control']) !!}
            {!! Form::text('service_provider_id', Auth::user()->service_provider_id, ['hidden' => 'true']) !!}
        </div>
    </div>
    <div class="form-group" id="help_desk">
        {!! Form::label('help_desk_id','Help Desk',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('help_desk_id', $helpDesks, null, ['class' => 'form-control ', 'placeholder' => '--- Choose help desk ---']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $status, null, ['class' => 'form-control ', 'placeholder' => '--- Status ---']) !!}
        </div>
    </div>
</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Auth\UserController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->

@push('scripts')
    <script type="text/javascript">
        if ($("#help_desk_id").val()==''){
            $("#help_desk").hide();
        }
        $(document).ready(function(){
          $.ajax({
          url: '{!! url("auth/roles/list-roles") !!}',
          type: "GET",
          data: {
                    user_type: 'Service Provider',
                    @if(old("roles"))
                        roles : '<?php echo json_encode(old("roles"));?>'
                    @elseif(isset($user))
                        roles : '<?php echo json_encode($user->roles);?>'
                    @endif
                },
            cache: true,
            success: function(html) {
                $('.roles-select').html(html);
                $('.chosen-select').select2().on('change',function (e){
                    onRoleSelect(e);
            });
            }
          });
            // Roles
            //$('.chosen-select').chosen();
            $('#roles').on("change",function () {
                if ($(this).val().includes("Help Desk")){
                        $("#help_desk").show();
                    }else{
                    $("#help_desk").hide();
                    $("#help_desk_id").val('');
                }
            })
              function onRoleSelect() {
          var selected = $('.chosen-select').select2('data');
          if (['Solid Waste - Transfer Station','Solid Waste - Landfill'].every(role => selected.some(e => e.text === role))){
              $('#transfer_station').show();
              $('#landfill_site').show();
          } else if (selected.filter(function(e) { return e.text === 'Solid Waste - Transfer Station'; }).length > 0) {
              $('#transfer_station').show();
              $('#landfill_site').hide();
          } else if (selected.filter(function(e) { return e.text === 'Solid Waste - Landfill'; }).length > 0) {
              $('#transfer_station').hide();
              $('#landfill_site').show();
          } else {
              $('#transfer_station').hide();
              $('#landfill_site').hide();
          }
      }
        });
    </script>
@endpush