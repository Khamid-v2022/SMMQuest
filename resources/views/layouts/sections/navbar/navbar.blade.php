@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached' && Auth::check())
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/home')}}" class="app-brand-link gap-2">
          <span class="app-brand-logo demo">
            @include('_partials.macros',["width"=>25,"withbg"=>'#696cff'])
          </span>
          <span class="app-brand-text demo menu-text fw-bolder">{{config('variables.templateName')}}</span>
        </a>
      </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="bx bx-menu bx-sm"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <!-- Style Switcher -->
        <!-- <div class="navbar-nav align-items-center">
          <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
            <i class='bx bx-sm'></i>
          </a>
        </div> -->
        <!--/ Style Switcher -->

        <ul class="navbar-nav flex-row align-items-center ms-auto">
          <!-- Currency -->
          <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" id="selected-currency" href="javascript:void(0);" data-bs-toggle="dropdown">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="USD" data-icon-class="fi-us">
                  <i class="fi fi-us fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">USD</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="EUR" data-icon-class="fi-eu">
                  <i class="fi fi-eu fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">EUR</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="INR" data-icon-class="fi-in">
                  <i class="fi fi-in fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">INR</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="TRY" data-icon-class="fi-tr">
                  <i class="fi fi-tr fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">TRY</span>
                </a>
              </li>


              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="RUB" data-icon-class="fi-ru">
                  <i class="fi fi-ru fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">RUB</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="BRL" data-icon-class="fi-br">
                  <i class="fi fi-br fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">BRL</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="NGN" data-icon-class="fi-ng">
                  <i class="fi fi-ng fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">NGN</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="KRW" data-icon-class="fi-kr">
                  <i class="fi fi-kr fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">KRW</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="THB" data-icon-class="fi-th">
                  <i class="fi fi-th fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">THB</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="SAR" data-icon-class="fi-sa">
                  <i class="fi fi-sa fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">SAR</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="CNY" data-icon-class="fi-cn">
                  <i class="fi fi-cn fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">CNY</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="VND" data-icon-class="fi-vn">
                  <i class="fi fi-vn fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">VND</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="KWD" data-icon-class="fi-kw">
                  <i class="fi fi-kw fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">KWD</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="EGP" data-icon-class="fi-eg">
                  <i class="fi fi-eg fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">EGP</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="PKR" data-icon-class="fi-pk">
                  <i class="fi fi-pk fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">PKR</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item currency-item" href="javascript:void(0);" data-currency="PHP" data-icon-class="fi-ph">
                  <i class="fi fi-ph fis rounded-circle fs-4 me-1"></i>
                  <span class="align-middle">PHP</span>
                </a>
              </li>
            </ul>
          </li>
          <!-- Style Switcher -->
          <li class="nav-item me-2 me-xl-0">
            <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
              <i class='bx bx-sm'></i>
            </a>
          </li>
          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <img src="{{ Auth::user() ? Auth::user()->avatar : asset('custom/img/avatars/default-2.png') }}" alt class="rounded-circle">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="{{ Route::has('profile-show') ? route('profile-show') : 'javascript:void(0);' }}">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online">
                        <img src="{{ Auth::user() ? Auth::user()->avatar : asset('custom/img/avatars/default-2.png') }}" alt class="w-px-40 h-auto rounded-circle">
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <span class="fw-semibold d-block">
                        @if (Auth::check())
                          {{ Auth::user()->email }}
                        @endif
                      </span>
                      <small class="text-muted">
                        @if (Auth::check())
                          {{ Auth::user()->first_name . " " . Auth::user()->last_name }}
                        @endif
                      </small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{ Route::has('profile-show') ? route('profile-show') : 'javascript:void(0);' }}">
                  <i class="bx bx-user me-2"></i>
                  <span class="align-middle">My Profile</span>
                </a>
              </li>
             
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <i class="bx bx-credit-card me-2"></i>
                  <span class="align-middle">Billing</span>
                </a>
              </li>
              
              <li>
                <div class="dropdown-divider"></div>
              </li>
              @if (Auth::check())
              <li>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class='bx bx-power-off me-2'></i>
                  <span class="align-middle">Logout</span>
                </a>
              </li>
              <form method="GET" id="logout-form" action="{{ route('logout') }}">
                @csrf
              </form>
              @endif
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->
