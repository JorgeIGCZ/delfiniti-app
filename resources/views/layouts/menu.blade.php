<ul class="nav">
    @can('Reportes.index')
    <li class="nav-item {{url()->current() == url('reportes') ? 'active' : ''}}">
      <a href="#!" class="nav-link with-sub"><i class="typcn typcn-chart-area-outline"></i> Reportes</a>
      <div class="az-menu-sub">
        <nav class="nav">
          @can('Reportes.CorteCaja.index')
          <a href="#!" class="nav-link" data-bs-toggle="modal" id="reporte-corte-caja" data-bs-target="#reportes-modal">Corte de caja</a>
          @endcan
          @can('Reportes.Reservaciones.index')
          <a href="#!" class="nav-link" data-bs-toggle="modal" id="reporte-reservaciones" data-bs-target="#reportes-modal">Reservaciones</a>
          @endcan
          @can('Reportes.Comisiones.index')
          <a href="#!" class="nav-link" data-bs-toggle="modal" id="reporte-comisiones" data-bs-target="#reportes-modal">Comisiones</a>
          @endcan
        </nav>
      </div>
    </li>
    @endcan

    <li class="nav-item {{url()->current() == url('checkin') ? 'active' : ''}}">
      <a href="{{ url('checkin') }}" class="nav-link"><i class="typcn typcn-tick-outline"></i> Check-in</a>
    </li>

    @can('Disponibilidad.index')
    <li class="nav-item {{url()->current() == url('disponibilidad') ? 'active' : ''}}">
      <a href="{{ url('disponibilidad') }}" class="nav-link"><i class="typcn  typcn typcn-ticket"></i> Disponibilidad</a>
    </li>
    @endcan


    @can('Reservaciones.create')
    <li class="nav-item {{url()->current() == url('reservaciones/create') ? 'active' : ''}}">
      <a href="{{ url('reservaciones/create') }}" class="nav-link {{url()->current() == url('reservaciones/create') ? 'active' : ''}}"><i class="typcn typcn-credit-card"></i> Nueva reservación</a>
    </li>
    @endcan

    @can('Reservaciones.index')
    <li class="nav-item {{url()->current() == url('reservaciones') ? 'active' : ''}}">
      <a href="{{ url('reservaciones') }}" class="nav-link {{url()->current() == url('reservaciones') ? 'active' : ''}}"><i class="typcn typcn-contacts"></i> Ver reservaciones</a>
    </li>
    @endcan

    @can('Comisiones.index')
    <li class="nav-item {{url()->current() == url('comisiones') ? 'active' : ''}}">
      <a href="{{ url('comisiones') }}" class="nav-link"><i class="typcn typcn-group-outline"></i> Comisiones</a>
    </li>
    @endcan

    <li class="nav-item">
      <a href="#" class="nav-link with-sub"><i class="typcn typcn-cog"></i> Configuración</a>
      <div class="az-menu-sub az-menu-sub-mega">
        <div class="container">
          <div>
            <nav class="nav">
              <span>General</span>
              @can('Usuarios.index')
              <a href="{{ url('/usuarios') }}" class="nav-link {{url()->current() == url('usuarios') ? 'active' : ''}}">Usuarios</a>
              @endcan
              @can('Usuarios.Roles.index')
              <a href="{{ url('/roles') }}" class="nav-link {{url()->current() == url('roles') ? 'active' : ''}}">Roles</a>
              @endcan
              @can('TipoCambio.index')
              <a href="{{ url('/tiposcambio') }}" class="nav-link {{url()->current() == url('tiposcambio') ? 'active' : ''}}">Tipos de cambio</a>
              @endcan
            </nav>
          </div>
          <div>
            <nav class="nav">
              <span>Catálogos</span>
              @can('Actividades.index')
              <a href="{{ url('/actividades') }}" class="nav-link {{url()->current() == url('actividades') ? 'active' : ''}}">Actividades</a>
              @endcan
              @can('Alojamientos.index')
              <a href="{{ url('/alojamientos') }}" class="nav-link {{url()->current() == url('alojamientos') ? 'active' : ''}}">Alojamientos</a>
              @endcan
              @can('Cerradores.index')
              <!--a href="{{ url('/cerradores') }}" class="nav-link {{url()->current() == url('cerradores') ? 'active' : ''}}">Cerradores</a-->
              @endcan
              @can('Comisiones.index')
              <a href="{{ url('/comisionistas') }}" class="nav-link {{url()->current() == url('comisionistas') ? 'active' : ''}}">Comisionistas</a>
              @endcan
              @can('TiposComisionista.index')
              <a href="{{ url('/canalesventa') }}" class="nav-link {{url()->current() == url('canalesventa') ? 'active' : ''}}">Canales de venta</a>
              @endcan

              @can('CodigosDescuento.index')
              <a href="{{ url('/descuentocodigos') }}" class="nav-link {{url()->current() == url('descuentocodigos') ? 'active' : ''}}">Códigos descuento</a>
              @endcan

            </nav>
          </div>
        </div><!-- container -->
      </div>
    </li>
  </ul>