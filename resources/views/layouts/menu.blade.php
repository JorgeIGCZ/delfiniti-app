<ul class="nav">
    <li class="nav-item {{url()->current() == url('reportes') ? 'active' : ''}} show">
      <a href="{{ url('reportes') }}" class="nav-link"><i class="typcn typcn-chart-area-outline"></i> Reportes</a>
    </li>
    <li class="nav-item {{url()->current() == url('disponibilidad') ? 'active' : ''}}">
      <a href="{{ url('disponibilidad') }}" class="nav-link"><i class="typcn  typcn typcn-ticket"></i> Disponibilidad</a>
    </li>
     <li class="nav-item">
      <a href="#" class="nav-link with-sub"><i class="typcn typcn-credit-card"></i> Reservaciones</a>
      <div class="az-menu-sub">
        <nav class="nav">
          <a href="{{ url('reservaciones/create') }}" class="nav-link {{url()->current() == url('reservaciones/create') ? 'active' : ''}}">Nueva reservación</a>
          <a href="{{ url('reservaciones') }}" class="nav-link {{url()->current() == url('reservaciones') ? 'active' : ''}}">Ver reservaciones</a>
        </nav>
      </div><!-- az-menu-sub -->
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link with-sub"><i class="typcn typcn-cog"></i> Configuración</a>
      <div class="az-menu-sub az-menu-sub-mega">
        <div class="container">
          <div>
            <nav class="nav">
              <span>General</span>
              <a href="{{ url('/usuarios') }}" class="nav-link {{url()->current() == url('usuarios') ? 'active' : ''}}">Usuarios</a>
              <a href="{{ url('/roles') }}" class="nav-link {{url()->current() == url('roles') ? 'active' : ''}}">Roles</a>
              <a href="{{ url('/tiposcambio') }}" class="nav-link {{url()->current() == url('tiposcambio') ? 'active' : ''}}">Tipos de cambio</a>
            </nav>
          </div>
          <div>
            <nav class="nav">
              <span>Catálogos</span>
              <a href="{{ url('/actividades') }}" class="nav-link {{url()->current() == url('actividades') ? 'active' : ''}}">Actividades</a>
              <a href="{{ url('/alojamientos') }}" class="nav-link {{url()->current() == url('alojamientos') ? 'active' : ''}}">Alojamientos</a>
              <a href="{{ url('/comisionistas') }}" class="nav-link {{url()->current() == url('comisionistas') ? 'active' : ''}}">Comisionistas</a>
              <a href="{{ url('/comisionistatipos') }}" class="nav-link {{url()->current() == url('comisionistatipos') ? 'active' : ''}}">Tipos de Comisionista</a>
            </nav>
          </div>
        </div><!-- container -->
      </div>
    </li>
  </ul>