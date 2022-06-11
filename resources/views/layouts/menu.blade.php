<ul class="nav">
    <li class="nav-item active show">
      <a href="{{ url('reportes') }}" class="nav-link"><i class="typcn typcn-chart-area-outline"></i> Reportes</a>
    </li>
     <li class="nav-item">
      <a href="#" class="nav-link with-sub"><i class="typcn typcn-credit-card"></i> Reservaciones</a>
      <div class="az-menu-sub">
        <nav class="nav">
          <a href="{{ url('disponibilidad') }}" class="nav-link">Disponibilidad</a>
          <a href="{{ url('reservacion') }}" class="nav-link active">Ver reservaciones</a>
          <a href="{{ url('reservacion/create') }}" class="nav-link">Nueva reservación</a>
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
              <a href="{{ url('configuracion/actividades') }}" class="nav-link">Actividades</a>
            </nav>
          </div>
          <div>
            <nav class="nav">
              <span>Catálogos</span>
              <a href="{{ url('configuracion/comisionistas') }}" class="nav-link">Comisionistas</a>
              <a href="{{ url('configuracion/localizaciones') }}" class="nav-link">Localizaciones</a>
            </nav>
          </div>
        </div><!-- container -->
      </div>
    </li>
  </ul>