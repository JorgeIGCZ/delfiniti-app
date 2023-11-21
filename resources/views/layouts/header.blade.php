<div class="az-header">
    <div class="container">
      <div class="az-header-left">
        <a href="/" class="az-logo"><img src="{{asset('assets/img/logo.png')}}" style="height: 57px;"></a>
        <a href="#" id="azMenuShow" class="az-header-menu-icon d-lg-none"><span></span></a>
      </div><!-- az-header-left -->
      <div class="az-header-menu">
        <div class="az-header-menu-header">
          <a href="/" class="az-logo"><span></span> DELFINITI</a>
          <a href="#" class="close">&times;</a>
        </div><!-- az-header-menu-header -->
        @include('layouts.menu')
      </div><!-- az-header-menu -->
      <div class="az-header-right">
        <!--a href="#" class="az-header-search-link"><i class="fas fa-search"></i></a-->
        <div class="az-header-message">
          <a href="/dashboard" class="nav-link">
            <b style="font-size: initial;color: #2aa2b8;">
              @switch(session('modulo'))
                @case('reservaciones')
                    Reservaciones
                  @break
                @case('tienda')
                  Tienda
                  @break
                @case('fotovideo')
                  Foto y video
                  @break
              @endswitch
            </b>
          </a>
        </div>
        <!--div class="dropdown az-header-notification">
          <a href="#" class="new"><i class="typcn typcn-bell"></i></a>
          <div class="dropdown-menu">
            <div class="az-dropdown-header mg-b-20 d-sm-none">
              <a href="#" class="az-header-arrow"><i class="icon ion-md-arrow-back"></i></a>
            </div>
            <h6 class="az-notification-title">Notificaciones</h6>
            <p class="az-notification-text">You have 2 unread notification</p>
            <div class="az-notification-list">
              <div class="media new">
                <div class="media-body">
                  <p><strong>Sandra Gomez</strong> requiere aprobacion de codigo de descuento <strong>Cortesia</strong> para cliente <strong>Maria Luz</strong></p>
                  <button class="btn btn-outline-danger btn-block mb-2">Aceptar</button>
                  <span>Mar 15 12:32pm</span>
                </div>
              </div>
              
            </div>
            <div class="dropdown-footer"><a href="#">Ver todas las notificaciones</a></div>
          </div>
        </div--><!-- az-header-notification -->
        <div class="dropdown az-profile-menu">
          <a href="#" class="nav-link">
            <i class="typcn typcn-user-outline"></i> {{Auth::user()->name}} 
          </a>
          <div class="dropdown-menu">
            <div class="az-dropdown-header d-sm-none">
              <a href="#" class="az-header-arrow"><i class="icon ion-md-arrow-back"></i></a>
            </div>
            @can('SeccionReservaciones.index')
              <a href="/switchModule/reservaciones" class="dropdown-item  {{(session('modulo') == 'reservaciones') ? 'selected' : ''}}"><i class="typcn typcn-user-outline"></i> Reservaciones</a>  
            @endcan

            @can('TiendaVentas.index')
              <a href="/switchModule/tienda" class="dropdown-item {{(session('modulo') == 'tienda') ? 'selected' : ''}}"><i class="typcn typcn-edit"></i> Tienda</a>
            @endcan

            @can('SeccionFotoVideo.index')
              <a href="/switchModule/fotovideo" class="dropdown-item {{(session('modulo') == 'tienda') ? 'selected' : ''}}"><i class="typcn typcn-time"></i> Foto y video</a>
            @endcan
            {{-- <a href="#" class="dropdown-item"><i class="typcn typcn-cog-outline"></i> Account Settings</a> --}}
            <form method="POST" action="{{ route('logout') }}">

              @csrf
              <x-responsive-nav-link :href="route('logout')"
                      onclick="event.preventDefault();
                                  this.closest('form').submit();">
                  <i class="typcn typcn-power-outline"></i> 
                  {{ __('Salir') }}
              </x-responsive-nav-link>
            </form>
          </div><!-- dropdown-menu -->
        </div>
      </div><!-- az-header-right -->
    </div><!-- container -->
  </div><!-- az-header -->
