<ul class="nav">
    <li class="nav-item active show">
      <a href="#" class="nav-link with-sub"><i class="typcn typcn-chart-area-outline"></i> Dashboard</a>
      <div class="az-menu-sub">
        <nav class="nav">
          <a href="dashboard-one.html" class="nav-link active">Web Analytics</a>
          <a href="dashboard-two.html" class="nav-link">Sales Monitoring</a>
          <a href="dashboard-three.html" class="nav-link">Ad Campaign</a>
          <a href="dashboard-four.html" class="nav-link">Event Management</a>
          <a href="dashboard-five.html" class="nav-link">Helpdesk Management</a>
          <a href="dashboard-six.html" class="nav-link">Finance Monitoring</a>
          <a href="dashboard-seven.html" class="nav-link">Cryptocurrency</a>
          <a href="dashboard-eight.html" class="nav-link">Executive / SaaS</a>
          <a href="dashboard-nine.html" class="nav-link">Campaign Monitoring</a>
          <a href="dashboard-ten.html" class="nav-link">Product Management</a> 
        </nav>
      </div><!-- az-menu-sub -->
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
              <a href="{{ url('configuracion/promotores') }}" class="nav-link">Promotores directos</a>
              <a href="{{ url('configuracion/agenciascredito') }}" class="nav-link">Agencias con crédito</a>
              <a href="{{ url('configuracion/agencias') }}" class="nav-link">Agencias sin crédito</a>
              <a href="{{ url('configuracion/localizaciones') }}" class="nav-link">Localizaciones</a>
            </nav>
          </div>
        </div><!-- container -->
      </div>
    </li>
  </ul>