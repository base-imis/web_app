<div style="margin: 5px">
    <!-- Top Section -->
    <div >
    @if(isset($value))
    @if($value == 'yes'  || $value == 'y' || $value == 'Y' || $value == 'YES')
    <i class="fa-regular fa-thumbs-up" style="margin-bottom: 10px; font-size: 40px; color: #42c599d3; margin-right:4px"></i>
    @isset($description)
            <span class="" style="font-size: 15px; color:#008FFB">{{$description}}</span>
        @endisset
        {{-- <span class="" style="font-size: 15px; display: flex; align-items: center; margin-left:60px;color:#008FFB">Yes</span> --}}
    @elseif($value == 'no'|| $value == 'n' || $value == 'N' || $value == 'NO' )
    <i class="fa-regular fa-thumbs-down" style="margin-bottom: 5px; font-size: 40px; color: #c54442d3; margin-right:4px"></i>
    @isset($description)
            <span class="" style="font-size: 15px;color:#008FFB">{{$description}}</span>
        @endisset
        {{-- <span class="" style="font-size:15px; display: flex; align-items: center; margin-left:60px;color:#008FFB">No</span> --}}
    @else
    <i class="fa-regular fa-thumbs-up" style="margin-bottom: 10px; font-size: 40px; color: #64706C; margin-right:4px"></i>

    @isset($description)
        <span class="" style="font-size: 15px;color:#008FFB">{{$description}}</span>
    @endisset
    @endif
@endif
    </div>

</div>
