<div class="az-header">
    <div class="container">
      <div class="az-header-left">
        <a href="/" class="az-logo"><img src="{{asset('assets/img/logo.png')}}" style="height: 57px;filter: brightness(0.2);"></a>
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
        <!--div class="az-header-message">
          <a href="app-chat.html"><i class="typcn typcn-messages"></i></a>
        </div--><!-- az-header-message -->
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
            
            <form method="POST" action="{{ route('logout') }}">

              <!--a href="#" class="dropdown-item"><i class="typcn typcn-user-outline"></i> My Profile</a>
              <a href="#" class="dropdown-item"><i class="typcn typcn-edit"></i> Edit Profile</a>
              <a href="#" class="dropdown-item"><i class="typcn typcn-time"></i> Activity Logs</a>
              <a href="#" class="dropdown-item"><i class="typcn typcn-cog-outline"></i> Account Settings</a-->
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
