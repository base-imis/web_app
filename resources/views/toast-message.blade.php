<script type="text/javascript">

       @if ($message = Session::get('success'))      
        toastr.success('{{ $message }}')
       @endif
       @if ($message = Session::get('error'))      
        toastr.error('{{ $message }}')
       @endif
       @if ($message = Session::get('warning'))      
        toastr.warning({{ $message }})
       @endif
       @if ($message = Session::get('info'))      
        toastr.info({{ $message }})
       @endif

</script>