<nav class="main-header navbar-expand navbar-white navbar-light">

    <ul class="navbar-nav">
@if (request()->is('maps'))
<a href="{{ url('/') }}" class="" >
<span class=""> <img src="{{ asset('/img/logo-imis.png') }}" alt="Municipality Logo" id="map-logo"
    style=" line-height: .8;
    margin-right: 0.5rem; margin-top:8px; max-height:33px; width:70px"></span>
</a>
@endif
@if (request()->is('maps'))
<li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#" role="button" onclick="hideImage()">
        <i class="fas fa-bars"></i>
    </a>
</li>
@else
<li class="nav-item">
    <a class="nav-link" data-widget="pushmenu" href="#" role="button" onclick="toggleElements()">
        <i class="fas fa-bars"></i>
    </a>
</li>
@endif
<li class="nav-item ml-auto">

<a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
<i class="fas fa-th-large"></i>
</a>
</li>
<!--         User Account Menu
  <div class="dropdown">
         Menu Toggle Button
       <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
          <i class="fa fa-user"></i>
           hidden-xs hides the username on small devices so only the image appears.
          <span class="hidden-xs">{{ Auth::user()->name }}</span>
        </button>
        <div class="dropdown-menu">
          <a href="{{ route('logout.perform') }}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa-solid fa-arrow-right-from-bracket" style="font-size: 24px;vertical-align: middle;"></i>
           <h5>Sign Out </h5>
          </a>
          <form id="logout-form" action="{{ route('logout.perform') }}" method="POST" style="display: none;">
              {{ csrf_field() }}
          </form>

        </div>
    </div>-->
</ul>
</nav>
<script>
    function hideImage() {

    var logo = document.getElementById('map-logo');

    if (logo.style.display === 'none') {
        logo.style.display = 'inline';

    } else {
        logo.style.display = 'none';
        helloText.style.display = 'inline';
    }
}
        </script>
