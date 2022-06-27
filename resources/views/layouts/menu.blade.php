<ul class="nav">
    <li class="nav-item active show">
      <a href="{{ url('reportes') }}" class="nav-link"><i class="typcn typcn-chart-area-outline"></i> Reportes</a>
    </li>
    <li class="nav-item">
      <a href="{{ url('disponibilidad') }}" class="nav-link"><i class="typcn  typcn typcn-ticket"></i> Disponibilidad</a>
    </li>
     <li class="nav-item">
      <a href="#" class="nav-link with-sub"><i class="typcn typcn-credit-card"></i> Reservaciones</a>
      <div class="az-menu-sub">
        <nav class="nav">
          <a href="{{ url('reservaciones/create') }}" class="nav-link">Nueva reservación</a>
          <a href="{{ url('reservaciones') }}" class="nav-link active">Ver reservaciones</a>
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
              <a href="{{ url('/tiposCambio') }}" class="nav-link">Tipos de cambio</a>
            </nav>
          </div>
          <div>
            <nav class="nav">
              <span>Catálogos</span>
              <a href="{{ url('/actividades') }}" class="nav-link">Actividades</a>
              <a href="{{ url('/comisionistas') }}" class="nav-link">Comisionistas</a>
              <a href="{{ url('/localizaciones') }}" class="nav-link">Alojamientos</a>
            </nav>
          </div>
        </div><!-- container -->
      </div>
    </li>
  </ul>