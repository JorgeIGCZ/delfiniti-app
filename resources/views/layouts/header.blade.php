<div class="az-header">
    <div class="container">
      <div class="az-header-left">
        <a href="/" class="az-logo"><span></span>DELFINITI</a>
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
        <a href="#" class="az-header-search-link"><i class="fas fa-search"></i></a>
        <div class="az-header-message">
          <a href="app-chat.html"><i class="typcn typcn-messages"></i></a>
        </div><!-- az-header-message -->
        <div class="dropdown az-header-notification">
          <a href="#" class="new"><i class="typcn typcn-bell"></i></a>
          <div class="dropdown-menu">
            <div class="az-dropdown-header mg-b-20 d-sm-none">
              <a href="#" class="az-header-arrow"><i class="icon ion-md-arrow-back"></i></a>
            </div>
            <h6 class="az-notification-title">Notifications</h6>
            <p class="az-notification-text">You have 2 unread notification</p>
            <div class="az-notification-list">
              <div class="media new">
                <div class="az-img-user"><img src="{{asset('assets/img/faces/face2.jpg')}}" alt=""></div>
                <div class="media-body">
                  <p>Congratulate <strong>Socrates Itumay</strong> for work anniversaries</p>
                  <span>Mar 15 12:32pm</span>
                </div><!-- media-body -->
              </div><!-- media -->
              <div class="media new">
                <div class="az-img-user online"><img src="{{asset('assets/img/faces/face3.jpg')}}" alt=""></div>
                <div class="media-body">
                  <p><strong>{{ Auth::user()->name }}</strong> just created a new blog post</p>
                  <span>Mar 13 04:16am</span>
                </div><!-- media-body -->
              </div><!-- media -->
              <div class="media">
                <div class="az-img-user"><img src="{{asset('assets/img/faces/face4.jpg')}}" alt=""></div> 
                <div class="media-body">
                  <p><strong>Althea Cabardo</strong> just created a new blog post</p>
                  <span>Mar 13 02:56am</span>
                </div><!-- media-body -->
              </div><!-- media -->
              <div class="media">
                <div class="az-img-user"><img src="{{asset('assets/img/faces/face5.jpg')}}" alt=""></div>
                <div class="media-body">
                  <p><strong>Adrian Monino</strong> added new comment on your photo</p>
                  <span>Mar 12 10:40pm</span>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- az-notification-list -->
            <div class="dropdown-footer"><a href="#">View All Notifications</a></div>
          </div><!-- dropdown-menu -->
        </div><!-- az-header-notification -->
        <div class="dropdown az-profile-menu">
          <a href="#" class="az-img-user"><img src="{{asset('assets/img/faces/face1.jpg')}}" alt=""></a>
          <div class="dropdown-menu">
            <div class="az-dropdown-header d-sm-none">
              <a href="#" class="az-header-arrow"><i class="icon ion-md-arrow-back"></i></a>
            </div>
            <div class="az-header-profile">
              <div class="az-img-user">
                <img src="{{asset('assets/img/faces/face1.jpg')}}" alt="">
              </div><!-- az-img-user -->
              <h6>{{ Auth::user()->name }}</h6>
              <span>Premium Member</span>
            </div><!-- az-header-profile -->

            <a href="#" class="dropdown-item"><i class="typcn typcn-user-outline"></i> My Profile</a>
            <a href="#" class="dropdown-item"><i class="typcn typcn-edit"></i> Edit Profile</a>
            <a href="#" class="dropdown-item"><i class="typcn typcn-time"></i> Activity Logs</a>
            <a href="#" class="dropdown-item"><i class="typcn typcn-cog-outline"></i> Account Settings</a>
            
              <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <x-responsive-nav-link :href="route('logout')"
                          onclick="event.preventDefault();
                                      this.closest('form').submit();">
                      <i class="typcn typcn-power-outline"></i> 
                      {{ __('Sign Out') }}
                  </x-responsive-nav-link>
              </form>
          </div><!-- dropdown-menu -->
        </div>
      </div><!-- az-header-right -->
    </div><!-- container -->
  </div><!-- az-header -->
