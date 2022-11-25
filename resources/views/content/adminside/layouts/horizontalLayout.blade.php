@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@php
    $configData = Helper::appClasses();
@endphp

<!-- @extends('layouts/commonMaster' ) -->
<!DOCTYPE html>

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}" class="{{ $configData['style'] }}-style {{ $navbarFixed ?? '' }} {{ $menuFixed ?? '' }} {{ $menuCollapsed ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}" dir="{{ $configData['textDirection'] }}" data-theme="{{ $configData['theme'] }}" data-assets-path="{{ asset('/assets') . '/' }}" data-base-url="{{url('/')}}" data-framework="laravel" data-template="{{ $configData['layout'] . '-menu-' . $configData['theme'] . '-' . $configData['style'] }}">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

        <title>@yield('title') | Admin Panel</title>
        
        <!-- laravel CRUD token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

        <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />

        <!-- sweetalert -->
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />

        <!-- Include Styles -->
        @include('layouts/sections/styles')

        <!-- Include Scripts for customizer, helper, analytics, config -->
        @include('layouts/sections/scriptsIncludes')
    </head>

    @php

        $menuHorizontal = true;

        /* Display elements */
        $isNavbar = ($isNavbar ?? true);
        $isMenu = ($isMenu ?? true);
        $isFlex = ($isFlex ?? false);
        $isFooter = ($isFooter ?? true);
        $customizerHidden = ($customizerHidden ?? '');
        $pricingModal = ($pricingModal ?? false);

        /* HTML Classes */
        $menuFixed = (isset($configData['menuFixed']) ? $configData['menuFixed'] : '');
        $navbarFixed = (isset($configData['navbarFixed']) ? $configData['navbarFixed'] : '');
        $footerFixed = (isset($configData['footerFixed']) ? $configData['footerFixed'] : '');
        $menuCollapsed = (isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '');

        /* Content classes */
        $container = ($container ?? 'container-xxl');
        $containerNav = ($containerNav ?? 'container-xxl');

    @endphp

    <body>
        <!-- Layout Content -->
        <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
            <div class="layout-container">

                <!-- BEGIN: Navbar-->
                <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="{{$containerNav}}">
               
                        <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
                            <a href="{{url('/')}}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    @include('_partials.macros',["width"=>25,"withbg"=>'#696cff'])
                                </span>
                                <span class="app-brand-text demo menu-text fw-bolder">{{config('variables.templateName')}}</span>
                            </a>
                        </div>

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
                            <div class="navbar-nav align-items-center">
                                <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                                    <i class='bx bx-sm'></i>
                                </a>
                            </div>
                            <!--/ Style Switcher -->
                            <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <!-- User -->
                                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                    @if (Auth::check())
                                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class='bx bx-power-off me-2'></i>
                                            <span class="align-middle">Logout</span>
                                        </a>
                                        <form method="GET" id="logout-form" action="{{ route('logout') }}">
                                            @csrf
                                        </form>
                                    @endif
                                </li>
                                <!--/ User -->
                            </ul>
                        </div>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- END: Navbar-->

                <!-- Layout page -->
                <div class="layout-page">

                    <!-- Content wrapper -->
                    <div class="content-wrapper">

                        <!-- Horizontal Menu -->
                        <aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0">
                            <div class="{{$containerNav}} d-flex h-100">
                                <ul class="menu-inner">
                                    <li class="menu-item">
                                        <a href="/admin/" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                                            <div>Dashboard</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/provider-management" class="menu-link">
                                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                                            <div>Providers</div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </aside>
                        <!--/ Horizontal Menu -->
                      
                        <div class="{{$container}} flex-grow-1 container-p-y">
                            @yield('content')
                        </div>
                        <!-- / Content -->

                        <div class="content-backdrop fade"></div>
                    </div>
                    <!--/ Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>
            <!-- / Layout Container -->

            @if ($isMenu)
                <!-- Overlay -->
                <div class="layout-overlay layout-menu-toggle"></div>
            @endif
            <!-- Drag Target Area To SlideIn Menu On Small Screens -->
            <div class="drag-target"></div>
        </div>
        <!--/ Layout Content -->

        <!-- Include Scripts -->
        @include('layouts/sections/scripts')
    </body>

</html>
