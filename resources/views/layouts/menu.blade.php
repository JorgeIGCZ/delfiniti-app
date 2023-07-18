@if(auth()->user()->can('SeccionReservaciones.index') || auth()->user()->can('SeccionTienda.index') || auth()->user()->can('SeccionFotoVideo.index'))
  
    <ul class="nav">
      @if(session('modulo') == 'reservaciones')
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

        @can('Checkin.index')
          <li class="nav-item {{url()->current() == url('checkin') ? 'active' : ''}}">
            <a href="{{ url('checkin') }}" class="nav-link"><i class="typcn typcn-tick-outline"></i> Check-in</a>
          </li>
        @endcan

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

        @can('FotoVideoVentas.create')
          @role('Recepcion')
            <li class="nav-item {{url()->current() == url('fotovideoventas/create') ? 'active' : ''}}">
              <a href="{{ url('fotovideoventas/create') }}" class="nav-link {{url()->current() == url('fotovideoventas/create') ? 'active' : ''}}"><i class="typcn typcn-credit-card"></i> Nueva venta</a>
            </li>
          @endrole
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
      @endif

      @if(session('modulo') == 'tienda')
        @can('Reportes.index')
          <li class="nav-item {{url()->current() == url('reportes') ? 'active' : ''}}">
            <a href="#!" class="nav-link with-sub"><i class="typcn typcn-chart-area-outline"></i> Reportes</a>
            {{-- <div class="az-menu-sub">
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
            </div> --}}
          </li>
        @endcan
      
        @can('TiendaVentas.create')
          <li class="nav-item {{url()->current() == url('ventas/create') ? 'active' : ''}}">
            <a href="{{ url('ventas/create') }}" class="nav-link {{url()->current() == url('ventas/create') ? 'active' : ''}}"><i class="typcn typcn-credit-card"></i> Nueva venta</a>
          </li>
        @endcan

        @can('FotoVideoVentas.create')
          @role('Tienda')
            <li class="nav-item {{url()->current() == url('fotovideoventas/create') ? 'active' : ''}}">
              <a href="{{ url('fotovideoventas/create') }}" class="nav-link {{url()->current() == url('fotovideoventas/create') ? 'active' : ''}}"><i class="typcn typcn-credit-card"></i> Nueva venta (foto y video)</a>
            </li>
          @endrole
        @endcan

        @can('TiendaVentas.index')
          <li class="nav-item {{url()->current() == url('ventas') ? 'active' : ''}}">
            <a href="{{ url('ventas') }}" class="nav-link {{url()->current() == url('ventas') ? 'active' : ''}}"><i class="typcn typcn-contacts"></i> Ver ventas</a>
          </li>
        @endcan

        @can('TiendaPedidos.index')
          <li class="nav-item {{url()->current() == url('pedidos') ? 'active' : ''}}">
            <a href="#!" class="nav-link with-sub"><i class="typcn typcn-chart-area-outline"></i> Pedidos</a>
            <div class="az-menu-sub">
              <nav class="nav">
                @can('TiendaPedidos.index')
                  <a href="{{ url('/pedidos') }}" class="nav-link {{url()->current() == url('pedidos') ? 'active' : ''}}">Pedidos</a>
                @endcan
                @can('TiendaPedidos.create')
                  <a href="{{ url('/pedidos/create') }}" class="nav-link {{url()->current() == url('pedidos/create') ? 'active' : ''}}">Nuevo pedido</a>
                @endcan
                @can('TiendaProductosValidacion.index')
                    <a href="{{ url('/pedidos/validate') }}" class="nav-link {{url()->current() == url('/pedidos/validate') ? 'active' : ''}}">Autorizar pedidos</a>
                @endcan
              </nav>
            </div>
          </li>
        @endcan

        @can('TiendaComisiones.index')
          <li class="nav-item {{url()->current() == url('tiendacomisiones') ? 'active' : ''}}">
            <a href="{{ url('tiendacomisiones') }}" class="nav-link"><i class="typcn typcn-group-outline"></i> Comisiones</a>
          </li>
        @endcan
      @endif

      @if(session('modulo') == 'fotovideo')
       @can('Reportes.index')
          <li class="nav-item {{url()->current() == url('reportes') ? 'active' : ''}}">
            <a href="#!" class="nav-link with-sub"><i class="typcn typcn-chart-area-outline"></i> Reportes</a>
            {{-- <div class="az-menu-sub">
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
            </div> --}}
          </li>
        @endcan
      
        @can('FotoVideoVentas.create')
          <li class="nav-item {{url()->current() == url('fotovideoventas/create') ? 'active' : ''}}">
            <a href="{{ url('fotovideoventas/create') }}" class="nav-link {{url()->current() == url('fotovideoventas/create') ? 'active' : ''}}"><i class="typcn typcn-credit-card"></i> Nueva venta</a>
          </li>
        @endcan

        @can('FotoVideoVentas.index')
          <li class="nav-item {{url()->current() == url('fotovideoventas') ? 'active' : ''}}">
            <a href="{{ url('fotovideoventas') }}" class="nav-link {{url()->current() == url('fotovideoventas') ? 'active' : ''}}"><i class="typcn typcn-contacts"></i> Ver ventas</a>
          </li>
        @endcan 

        @can('FotoVideoComisiones.index')
          <li class="nav-item {{url()->current() == url('fotovideocomisiones') ? 'active' : ''}}">
            <a href="{{ url('fotovideocomisiones') }}" class="nav-link"><i class="typcn typcn-group-outline"></i> Comisiones</a>
          </li>
        @endcan
      @endif
      
      @can('Configuracion')
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
                  @can('Directivos.index')
                    <a href="{{ url('/directivos') }}" class="nav-link {{url()->current() == url('directivos') ? 'active' : ''}}">Directivos</a>
                  @endcan
                  
                  @if(session('modulo') == 'tienda')
                    {{-- @can('TipoCambio.index') --}}
                      <a href="{{ url('/impuestos') }}" class="nav-link {{url()->current() == url('impuestos') ? 'active' : ''}}">Impuestos productos</a>
                    {{-- @endcans --}}
                  @endif
                </nav>
              </div>
              <div>
                <nav class="nav">
                  <span>Catálogos</span>
                    @if(session('modulo') == 'reservaciones')
                      @can('Actividades.index')
                        <a href="{{ url('/actividades') }}" class="nav-link {{url()->current() == url('actividades') ? 'active' : ''}}">Actividades</a>
                      @endcan
                      @can('Alojamientos.index')
                        <a href="{{ url('/alojamientos') }}" class="nav-link {{url()->current() == url('alojamientos') ? 'active' : ''}}">Alojamientos</a>
                      @endcan
                      @can('Cerradores.index')
                        <!--a href="{{ url('/cerradores') }}" class="nav-link {{url()->current() == url('cerradores') ? 'active' : ''}}">Cerradores</a-->
                      @endcan
                      @can('Comisionista.index')
                        <a href="{{ url('/comisionistas') }}" class="nav-link {{url()->current() == url('comisionistas') ? 'active' : ''}}">Comisionistas</a>
                      @endcan
                      @can('CanalesVenta.index')
                        <a href="{{ url('/canalesventa') }}" class="nav-link {{url()->current() == url('canalesventa') ? 'active' : ''}}">Canales de venta</a>
                      @endcan
                      @can('CodigosDescuento.index')
                        <a href="{{ url('/descuentocodigos') }}" class="nav-link {{url()->current() == url('descuentocodigos') ? 'active' : ''}}">Códigos descuento</a>
                      @endcan
                    @endif

                    @if(session('modulo') == 'tienda')
                      @can('TiendaProductos.index')
                        <a href="{{ url('/productos') }}" class="nav-link {{url()->current() == url('productos') ? 'active' : ''}}">Productos</a>
                      @endcan

                      @can('TiendaProveedores.index')
                        <a href="{{ url('/proveedores') }}" class="nav-link {{url()->current() == url('proveedores') ? 'active' : ''}}">Proveedores</a>
                      @endcan
                    @endif

                    @if(session('modulo') == 'fotovideo')
                      @can('FotoVideoProductos.index')
                        <a href="{{ url('/fotovideoproductos') }}" class="nav-link {{url()->current() == url('fotovideoproductos') ? 'active' : ''}}">Productos</a>
                      @endcan
                      @can('FotoVideoComisionistas.index')
                        <a href="{{ url('/fotovideocomisionistas') }}" class="nav-link {{url()->current() == url('fotovideocomisionistas') ? 'active' : ''}}">Fotógrafos</a>
                      @endcan
                    @endif
                </nav>
              </div>
            </div><!-- container -->
          </div>
        </li>
      @endcan
    </ul>
@endif