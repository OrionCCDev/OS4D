<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================-->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('DAssets/assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Dashboard - Analytics | {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="Dashboard Analytics for {{ config('app.name', 'Laravel') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('DAssets/') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <!-- Custom Gradient CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom-gradient.css') }}" />
    <link rel="stylesheet" href="{{ asset('DAssets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('DAssets/assets/css/task-files.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('DAssets/assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('DAssets/assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu sidebar-gradient">
          <div class="app-brand demo">
            <a href="{{ url('/') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img width="120px" src="{{ asset('DAssets/logo-blue.webp') }}" alt="" srcset="">
              </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            @if(Auth::user()->isManager() || Auth::user()->isSubAdmin())
            <!-- Dashboard - Manager and Sub-Admin -->
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>
            @endif

            @if(Auth::user()->role === 'admin')
            <!-- Users - Admin only -->
            <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
              <a href="{{ route('admin.users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Users">Users</div>
              </a>
            </li>
            @endif

            @if(Auth::user()->isManager() || Auth::user()->isSubAdmin())
            <!-- Projects - Manager and Sub-Admin -->
            <li class="menu-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
              <a href="{{ route('projects.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder"></i>
                <div data-i18n="Projects">Projects</div>
              </a>
            </li>
            <!-- Reports - Manager and Sub-Admin -->
            <li class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
              <a href="{{ route('reports.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div data-i18n="Reports">Reports</div>
              </a>
            </li>
            <!-- Folders - Manager only -->
            {{--  <li class="menu-item {{ request()->routeIs('folders.*') ? 'active' : '' }}">
              <a href="{{ route('folders.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder-open"></i>
                <div data-i18n="Folders">Folders</div>
              </a>v5
            </li>  --}}
            <!-- Contractors - Manager only -->
            <li class="menu-item {{ request()->routeIs('contractors.*') ? 'active' : '' }}">
              <a href="{{ route('contractors.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-briefcase"></i>
                <div data-i18n="Contractors">Contractors</div>
              </a>
            </li>
            {{--  <!-- Email Templates - Manager only -->
            <li class="menu-item {{ request()->routeIs('email-templates.*') ? 'active' : '' }}">
              <a href="{{ route('email-templates.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-mail-send"></i>
                <div data-i18n="Email Templates">Email Templates</div>
              </a>
            </li>
            <!-- External Stakeholders - Manager only -->
            <li class="menu-item {{ request()->routeIs('external-stakeholders.*') ? 'active' : '' }}">
              <a href="{{ route('external-stakeholders.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="External Stakeholders">External Stakeholders</div>
              </a>
            </li>  --}}
            @endif

            <!-- Tasks - Available to all users (with restrictions) -->
            <li class="menu-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
              <a href="{{ route('tasks.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-task"></i>
                <div data-i18n="Tasks">My Tasks</div>
              </a>
            </li>

            <!-- Send Email - Available to all users -->
            <li class="menu-item {{ request()->routeIs('emails.send*') ? 'active' : '' }}">
              <a href="{{ route('emails.send-form') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div data-i18n="Send Email">Send Email</div>
              </a>
            </li>

            {{--  <!-- Email Notifications - Available to all users -->
            <li class="menu-item {{ request()->routeIs('email-notifications.*') ? 'active' : '' }}">
              <a href="{{ route('email-notifications.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div data-i18n="Email Notifications">Email Notifications</div>
                <span class="badge rounded-pill bg-danger ms-auto nav-bell-count" id="nav-bell-count" style="display: none;">0</span>
              </a>
            </li>

            <!-- Live Email Monitoring - Available to all users -->
            <li class="menu-item {{ request()->routeIs('live-monitoring.*') ? 'active' : '' }}">
              <a href="{{ route('live-monitoring.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-radar"></i>
                <div data-i18n="Live Email Monitoring">Live Email Monitoring</div>
                <span class="badge rounded-pill bg-success ms-auto" id="live-monitoring-badge" style="display: none;">LIVE</span>
              </a>
            </li>  --}}

            {{--  <!-- Email Tracker - Available to all users -->
            <li class="menu-item {{ request()->routeIs('email-tracker.*') ? 'active' : '' }}">
              <a href="{{ route('email-tracker.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                <div data-i18n="Email Tracker">Email Tracker</div>
                <span class="badge rounded-pill bg-info ms-auto">engineering@orion-contracting.com</span>
              </a>
            </li>

            <!-- Email Monitoring - Available to all users -->
            <li class="menu-item {{ request()->routeIs('email-monitoring.*') ? 'active' : '' }}">
              <a href="{{ route('email-monitoring.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div data-i18n="Email Monitoring">Email Monitoring</div>
              </a>
            </li>  --}}

            @if(Auth::user()->isManager())
            <!-- All Emails - Managers only (Designers Inbox) -->
            <li class="menu-item {{ request()->routeIs('emails.all') ? 'active' : '' }}">
              <a href="{{ route('emails.all') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope-open"></i>
                <div data-i18n="Designers Inbox">Designers Inbox</div>
                {{--  <span class="badge rounded-pill bg-primary ms-auto">engineering@orion-contracting.com</span>  --}}
              </a>
            </li>
            @endif
{{--
            <!-- Layouts -->
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Layouts</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="layouts-without-menu.html" class="menu-link">
                    <div data-i18n="Without menu">Without menu</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="layouts-without-navbar.html" class="menu-link">
                    <div data-i18n="Without navbar">Without navbar</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="layouts-container.html" class="menu-link">
                    <div data-i18n="Container">Container</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="layouts-fluid.html" class="menu-link">
                    <div data-i18n="Fluid">Fluid</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="layouts-blank.html" class="menu-link">
                    <div data-i18n="Blank">Blank</div>
                  </a>
                </li>
              </ul>
            </li>

            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Pages</span>
            </li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div data-i18n="Account Settings">Account Settings</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="pages-account-settings-account.html" class="menu-link">
                    <div data-i18n="Account">Account</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="pages-account-settings-notifications.html" class="menu-link">
                    <div data-i18n="Notifications">Notifications</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="pages-account-settings-connections.html" class="menu-link">
                    <div data-i18n="Connections">Connections</div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
                <div data-i18n="Authentications">Authentications</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="auth-login-basic.html" class="menu-link" target="_blank">
                    <div data-i18n="Basic">Login</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="auth-register-basic.html" class="menu-link" target="_blank">
                    <div data-i18n="Basic">Register</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="auth-forgot-password-basic.html" class="menu-link" target="_blank">
                    <div data-i18n="Basic">Forgot Password</div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                <div data-i18n="Misc">Misc</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="pages-misc-error.html" class="menu-link">
                    <div data-i18n="Error">Error</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="pages-misc-under-maintenance.html" class="menu-link">
                    <div data-i18n="Under Maintenance">Under Maintenance</div>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Components -->
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Components</span></li>
            <!-- Cards -->
            <li class="menu-item">
              <a href="cards-basic.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Basic">Cards</div>
              </a>
            </li>
            <!-- User interface -->
            <li class="menu-item">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-box"></i>
                <div data-i18n="User interface">User interface</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="ui-accordion.html" class="menu-link">
                    <div data-i18n="Accordion">Accordion</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-alerts.html" class="menu-link">
                    <div data-i18n="Alerts">Alerts</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-badges.html" class="menu-link">
                    <div data-i18n="Badges">Badges</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-buttons.html" class="menu-link">
                    <div data-i18n="Buttons">Buttons</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-carousel.html" class="menu-link">
                    <div data-i18n="Carousel">Carousel</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-collapse.html" class="menu-link">
                    <div data-i18n="Collapse">Collapse</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-dropdowns.html" class="menu-link">
                    <div data-i18n="Dropdowns">Dropdowns</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-footer.html" class="menu-link">
                    <div data-i18n="Footer">Footer</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-list-groups.html" class="menu-link">
                    <div data-i18n="List Groups">List groups</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-modals.html" class="menu-link">
                    <div data-i18n="Modals">Modals</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-navbar.html" class="menu-link">
                    <div data-i18n="Navbar">Navbar</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-offcanvas.html" class="menu-link">
                    <div data-i18n="Offcanvas">Offcanvas</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-pagination-breadcrumbs.html" class="menu-link">
                    <div data-i18n="Pagination &amp; Breadcrumbs">Pagination &amp; Breadcrumbs</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-progress.html" class="menu-link">
                    <div data-i18n="Progress">Progress</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-spinners.html" class="menu-link">
                    <div data-i18n="Spinners">Spinners</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-tabs-pills.html" class="menu-link">
                    <div data-i18n="Tabs &amp; Pills">Tabs &amp; Pills</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-toasts.html" class="menu-link">
                    <div data-i18n="Toasts">Toasts</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-tooltips-popovers.html" class="menu-link">
                    <div data-i18n="Tooltips & Popovers">Tooltips &amp; popovers</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="ui-typography.html" class="menu-link">
                    <div data-i18n="Typography">Typography</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Extended components -->
            <li class="menu-item">
              <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-copy"></i>
                <div data-i18n="Extended UI">Extended UI</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="extended-ui-perfect-scrollbar.html" class="menu-link">
                    <div data-i18n="Perfect Scrollbar">Perfect scrollbar</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="extended-ui-text-divider.html" class="menu-link">
                    <div data-i18n="Text Divider">Text Divider</div>
                  </a>
                </li>
              </ul>
            </li>

            <li class="menu-item">
              <a href="icons-boxicons.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-crown"></i>
                <div data-i18n="Boxicons">Boxicons</div>
              </a>
            </li>

            <!-- Forms & Tables -->
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Forms &amp; Tables</span></li>
            <!-- Forms -->
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Form Elements">Form Elements</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="forms-basic-inputs.html" class="menu-link">
                    <div data-i18n="Basic Inputs">Basic Inputs</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="forms-input-groups.html" class="menu-link">
                    <div data-i18n="Input groups">Input groups</div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Form Layouts">Form Layouts</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="form-layouts-vertical.html" class="menu-link">
                    <div data-i18n="Vertical Form">Vertical Form</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="form-layouts-horizontal.html" class="menu-link">
                    <div data-i18n="Horizontal Form">Horizontal Form</div>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Tables -->
            <li class="menu-item">
              <a href="tables-basic.html" class="menu-link">
                <i class="menu-icon tf-icons bx bx-table"></i>
                <div data-i18n="Tables">Tables</div>
              </a>
            </li>
            <!-- Misc -->
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Misc</span></li>
            <li class="menu-item">
              <a
                href="https://github.com/themeselection/sneat-html-admin-template-free/issues"
                target="_blank"
                class="menu-link"
              >
                <i class="menu-icon tf-icons bx bx-support"></i>
                <div data-i18n="Support">Support</div>
              </a>
            </li>
            <li class="menu-item">
              <a
                href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/"
                target="_blank"
                class="menu-link"
              >
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Documentation">Documentation</div>
              </a>
            </li>  --}}
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container navbar navbar-expand-xl navbar-detached align-items-center navbar-gradient"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4 text-white" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm text-white"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0 text-white"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none text-white"
                    placeholder="Search..."
                    aria-label="Search..."
                    style="background: transparent; color: white;"
                  />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Email Notifications - Separate Icon -->
                {{--  <li class="nav-item dropdown me-3">
                  <a class="nav-link dropdown-toggle hide-arrow position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-envelope fs-4"></i>
                    <span class="badge rounded-pill bg-warning position-absolute" style="top: 0; right: -4px;" id="nav-email-notification-count">0</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-0 email-notification-popup" style="min-width: 380px; max-width: 400px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border: 1px solid #e5e7eb;">
                      <!-- Email Header -->
                    <div class="notification-header d-flex align-items-center justify-content-between p-3 border-bottom" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 12px 12px 0 0;">
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2" style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                          <i class="bx bx-envelope" style="font-size: 16px;"></i>
                        </div>
                        <div>
                          <h6 class="mb-0 fw-semibold">Email Notifications</h6>
                          <small class="opacity-75">Email replies & updates</small>
                        </div>
                      </div>
                      <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-light" type="button" id="nav-mark-all-email-read" style="border-radius: 6px; padding: 4px 8px; font-size: 12px;">Mark all</button>
                      </div>
                    </div>

                    <!-- Email Messages Area -->
                    <div class="notification-messages" style="max-height: 400px; overflow-y: auto; background: #f8f9fa;" id="nav-email-notification-list">
                      <div class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading email notifications...
                      </div>
                    </div>

                    <!-- Email Footer -->
                    <div class="notification-footer p-3 border-top" style="background: white; border-radius: 0 0 12px 12px;">
                      <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('email-notifications.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-list-ul me-1"></i>View All
                        </a>
                        <a href="{{ route('email-monitoring.index') }}" class="btn btn-outline-info btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-envelope me-1"></i>Monitor
                        </a>
                        @if(config('app.debug'))
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="test-email-notification-btn" style="border-radius: 8px;">
                          <i class="bx bx-test-tube me-1"></i>Test
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </li>  --}}

                <!-- Email Notifications - All Users -->
                <li class="nav-item dropdown me-3">
                  <a class="nav-link dropdown-toggle hide-arrow position-relative text-white" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-envelope fs-4 text-white"></i>
                    <span class="badge rounded-pill bg-primary position-absolute" style="top: 0; right: -4px;" id="nav-email-notification-count">0</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-0 email-notification-popup" style="min-width: 380px; max-width: 400px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border: 1px solid #e5e7eb;">
                    <!-- Email Header -->
                    <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 12px 12px 0 0;">
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center me-2">
                          <i class="bx bx-envelope" style="font-size: 16px;"></i>
                        </div>
                        <div>
                          <h6 class="mb-0 fw-semibold">Email Notifications</h6>
                          <small class="opacity-75">Email replies & updates</small>
                        </div>
                      </div>
                      <button class="btn btn-sm btn-outline-light" type="button" id="nav-mark-all-email-read" style="border-radius: 6px; padding: 4px 8px; font-size: 12px;">Mark all</button>
                    </div>

                    <!-- Email Messages Area -->
                    <div class="notification-messages" style="max-height: 400px; overflow-y: auto; background: #f8f9fa;" id="nav-email-notification-list">
                      <div class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading email notifications...
                      </div>
                    </div>

                    <!-- Email Footer -->
                    <div class="p-3 border-top" style="background: white; border-radius: 0 0 12px 12px;">
                      <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('notifications.emails') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-list-ul me-1"></i>View All
                        </a>
                        @if(Auth::user()->isManager())
                        <a href="{{ route('emails.all') }}" class="btn btn-outline-info btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-envelope-open me-1"></i>Designers Inbox
                        </a>
                        @endif
                        @if(config('app.debug'))
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="test-email-notification-btn" style="border-radius: 8px;">
                          <i class="bx bx-test-tube me-1"></i>Test
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </li>

                <!-- Task Notifications - All Users -->
                <li class="nav-item dropdown me-3">
                  <a class="nav-link dropdown-toggle hide-arrow position-relative text-white" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-task fs-4 text-white"></i>
                    <span class="badge rounded-pill bg-success position-absolute" style="top: 0; right: -4px;" id="nav-task-notification-count">0</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-0 task-notification-popup" style="min-width: 380px; max-width: 400px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border: 1px solid #e5e7eb;">
                    <!-- Task Header -->
                    <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 12px 12px 0 0;">
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center me-2">
                          <i class="bx bx-task" style="font-size: 16px;"></i>
                        </div>
                        <div>
                          <h6 class="mb-0 fw-semibold">Task Notifications</h6>
                          <small class="opacity-75">Task updates & assignments</small>
                        </div>
                      </div>
                      <button class="btn btn-sm btn-outline-light" type="button" id="nav-mark-all-task-read" style="border-radius: 6px; padding: 4px 8px; font-size: 12px;">Mark all</button>
                    </div>

                    <!-- Task Messages Area -->
                    <div class="notification-messages" style="max-height: 400px; overflow-y: auto; background: #f8f9fa;" id="nav-task-notification-list">
                      <div class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading task notifications...
                      </div>
                    </div>

                    <!-- Task Footer -->
                    <div class="p-3 border-top" style="background: white; border-radius: 0 0 12px 12px;">
                      <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('notifications.tasks') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-list-ul me-1"></i>View All
                        </a>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-success btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-task me-1"></i>My Tasks
                        </a>
                        @if(config('app.debug'))
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="test-task-notification-btn" style="border-radius: 8px;">
                          <i class="bx bx-test-tube me-1"></i>Test
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </li>

                <!-- User Ranking Display -->
                @if(Auth::user()->isRegularUser())
                @php
                    $reportService = new \App\Services\ReportService();
                    $userRanking = $reportService->getUserRankings(Auth::user()->id, 'overall');
                @endphp
       <li class="nav-item me-3 me-xl-1">
         <div class="nav-link d-flex align-items-center bg-gradient rounded" style="cursor: default; padding: 0.5rem 0.75rem;">
           <div class="d-flex align-items-center">
             <div class="avatar avatar-sm me-2">
               <div class="avatar-initial rounded bg-white text-secondary">
                 @if($userRanking['user_ranking']['rank'] == 1)
                   <i class="bx bx-trophy" style="font-size: 12px;"></i>
                 @elseif($userRanking['user_ranking']['rank'] == 2)
                   <i class="bx bx-medal" style="font-size: 12px;"></i>
                 @elseif($userRanking['user_ranking']['rank'] == 3)
                   <i class="bx bx-award" style="font-size: 12px;"></i>
                 @else
                   <i class="bx bx-user" style="font-size: 12px;"></i>
                 @endif
               </div>
             </div>
             <div class="d-flex flex-column">
               <span class="fw-semibold text-white" style="font-size: 12px; line-height: 1;">
                 #{{ $userRanking['user_ranking']['rank'] }}
               </span>
               <small class="text-white-50" style="font-size: 10px; line-height: 1;">Overall</small>
             </div>
           </div>
         </div>
       </li>
                @endif

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow text-white" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="nav-profile-avatar">
                      <img src="{{ asset('uploads/users/' . (Auth::user()->img ?: 'default.png')) }}"
                           alt="{{ Auth::user()->name }}" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="nav-profile-avatar">
                              <img src="{{ asset('uploads/users/' . (Auth::user()->img ?: 'default.png')) }}"
                                   alt="{{ Auth::user()->name }}" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                            <small class="text-muted">{{ Auth::user()->role }}</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    {{--  <li>
                      <a class="dropdown-item" href="#">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <span class="d-flex align-items-center align-middle">
                          <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                          <span class="flex-grow-1 align-middle">Billing</span>
                          <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                        </span>
                      </a>
                    </li>  --}}
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('logout') }}"
                         onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bx bx-power-off me-2"></i>
                        <span class="align-middle">Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- / Navbar -->

          <!-- Bottom Right Chat Popup for Regular Users - Always Visible -->
          @if(Auth::user()->isRegularUser())
          <div id="bottom-chat-popup" class="bottom-chat-widget" style="position: fixed; bottom: 0; right: 0; z-index: 1050; width: 350px; height: 500px; background: white; border-radius: 16px 16px 0 0; box-shadow: 0 -8px 32px rgba(0,0,0,0.12); border: 1px solid #e5e7eb; border-bottom: none; overflow: hidden; transform: translateY(420px); transition: all 0.3s ease;">
            <!-- Chat Header - Always Visible -->
            <div class="chat-header" onclick="toggleBottomChat()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; border-radius: 16px 16px 0 0;">
              <div class="d-flex align-items-center">
                <div class="chat-avatar" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                  <i class="bx bx-message-dots" style="font-size: 20px;"></i>
                </div>
                <div>
                  <h6 class="mb-0 fw-semibold">Notifications</h6>
                  <small class="opacity-75">Live updates for you</small>
                </div>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <span class="chat-badge" id="bottom-chat-badge" style="background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; display: none;">0</span>
                <button class="btn btn-sm btn-outline-light" type="button" id="bottom-mark-all-read" style="border-radius: 6px; padding: 4px 8px; font-size: 12px;" onclick="event.stopPropagation();">Mark all</button>
                <button class="btn btn-sm btn-outline-light" type="button" onclick="toggleBottomChat(); event.stopPropagation();" style="border-radius: 6px; padding: 4px 8px;">
                  <i class="bx bx-chevron-up" id="bottom-chat-toggle-icon" style="font-size: 14px;"></i>
                </button>
              </div>
            </div>

            <!-- Chat Messages - Collapsible -->
            <div class="chat-messages" id="bottom-chat-messages" style="height: 380px; overflow-y: auto; background: #f8f9fa; padding: 0;">
              <div class="p-4 text-center text-muted">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                Loading notifications...
              </div>
            </div>

            <!-- Chat Footer -->
            <div class="chat-footer" style="background: white; padding: 12px 16px; border-top: 1px solid #e5e7eb;">
              <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px;">
                  <i class="bx bx-list-ul me-1"></i>View All
                </a>
                @if(config('app.debug'))
                <button class="btn btn-outline-secondary btn-sm" type="button" id="bottom-test-notification-btn" style="border-radius: 8px;">
                  <i class="bx bx-test-tube me-1"></i>Test
                </button>
                @endif
              </div>
            </div>
          </div>
          @endif

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <script>
              // CRITICAL: Define handleNotificationClick function immediately to prevent ReferenceError
              window.handleNotificationClick = window.handleNotificationClick || function(notificationId, viewUrl) {
                console.log('Global handleNotificationClick called with:', notificationId, viewUrl);

                // Mark as read using unified notification system
                fetch(`{{ url('notifications') }}/${notificationId}/mark-read`, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                  }
                }).then(() => {
                  console.log('Notification marked as read:', notificationId);
                  // Refresh notification counts if functions are available
                  if (typeof fetchEmailCount === 'function') fetchEmailCount();
                  if (typeof fetchTaskCount === 'function') fetchTaskCount();
                  if (typeof fetchBottomNotifications === 'function') fetchBottomNotifications();
                  if (typeof fetchDesignersCount === 'function') fetchDesignersCount();
                  if (typeof fetchDesignersNotifications === 'function') fetchDesignersNotifications();
                  if (typeof fetchEmailNotifications === 'function') fetchEmailNotifications();
                  if (typeof fetchTaskNotifications === 'function') fetchTaskNotifications();
                }).catch(error => {
                  console.error('Error marking notification as read:', error);
                });

                // Navigate to URL if provided
                if (viewUrl && viewUrl.trim() !== '' && viewUrl !== '#') {
                  console.log('Navigating to:', viewUrl);
                  window.location.href = viewUrl;
                } else {
                  console.log('No valid URL provided for navigation');
                }
              };
            </script>
            <script>
              (function(){
                // Email notification elements
                const emailCountEl = document.getElementById('nav-email-notification-count');
                const emailListEl = document.getElementById('nav-email-notification-list');
                const emailMarkAllBtn = document.getElementById('nav-mark-all-email-read');

                // Task notification elements
                const taskCountEl = document.getElementById('nav-task-notification-count');
                const taskListEl = document.getElementById('nav-task-notification-list');
                const taskMarkAllBtn = document.getElementById('nav-mark-all-task-read');

                if(!emailCountEl || !emailListEl || !taskCountEl || !taskListEl) return;

                // Store previous counts to detect new notifications
                let previousEmailCount = 0;
                let previousTaskCount = 0;


                // Global function for playing notification sound
                window.playNotificationSound = function() {
                  try {
                    const audio = new Audio('{{ asset("uploads/mail-noti.wav") }}');
                    audio.volume = 0.7; // Set volume to 70%
                    audio.play().then(() => {
                      console.log('Mail notification sound played');
                    }).catch(e => {
                      console.log('Mail notification sound play failed:', e);
                      // Fallback to beep sound if audio file fails
                      playFallbackSound();
                    });
                  } catch (e) {
                    console.log('Mail notification sound creation failed:', e);
                    // Fallback to beep sound
                    playFallbackSound();
                  }
                };

                // Function for playing task notification sound
                window.playTaskNotificationSound = function() {
                  try {
                    const audio = new Audio('{{ asset("uploads/gun.mp3") }}');
                    audio.volume = 0.8; // Set volume to 80%
                    audio.play().then(() => {
                      console.log('Task notification sound played');
                    }).catch(e => {
                      console.log('Task notification sound play failed:', e);
                      // Fallback to mail sound if task sound fails
                      playNotificationSound();
                    });
                  } catch (e) {
                    console.log('Task notification sound creation failed:', e);
                    // Fallback to mail sound
                    playNotificationSound();
                  }
                };

                // Fallback beep sound
                function playFallbackSound() {
                  try {
                    // Create audio context for better browser compatibility
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();

                    // Create a simple beep sound using oscillator
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    // Configure the beep sound
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime); // 800Hz frequency
                    oscillator.type = 'sine';

                    // Set volume (0.0 to 1.0)
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);

                    // Play the beep
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2); // 200ms duration

                    console.log('Fallback notification sound played');
                  } catch (e) {
                    console.log('Fallback notification sound creation failed:', e);
                  }
                };

                // Fetch email notification count
                async function fetchEmailCount(){
                  try {
                    const r = await fetch('{{ route('notifications.unread-count') }}', { credentials: 'same-origin' });
                    const d = await r.json();
                    const currentCount = d.success ? d.counts.email : 0;

                    // Debug logging
                    console.log('Email notification count fetch result:', { success: d.success, emailCount: d.counts.email, currentCount });

                    // Play sound if count increased (new notification)
                    if (currentCount > previousEmailCount && previousEmailCount > 0) {
                      playNotificationSound();
                    }

                    emailCountEl.textContent = currentCount;
                    emailCountEl.style.display = currentCount > 0 ? 'inline-block' : 'none';

                    previousEmailCount = currentCount;
                  } catch (e) {
                    console.error('Error fetching email notification count:', e);
                    // Reset counter on error
                    emailCountEl.textContent = '0';
                    emailCountEl.style.display = 'none';
                  }
                }

                // Fetch task notification count
                async function fetchTaskCount(){
                  try {
                    const r = await fetch('{{ route('notifications.unread-count') }}', { credentials: 'same-origin' });
                    const d = await r.json();
                    const currentCount = d.success ? d.counts.task : 0;

                    // Debug logging
                    console.log('Task notification count fetch result:', { success: d.success, taskCount: d.counts.task, currentCount });

                    // Play task sound if count increased (new task notification)
                    if (currentCount > previousTaskCount && previousTaskCount > 0) {
                      playTaskNotificationSound();
                    }

                    taskCountEl.textContent = currentCount;
                    taskCountEl.style.display = currentCount > 0 ? 'inline-block' : 'none';

                    previousTaskCount = currentCount;
                  } catch (e) {
                    console.error('Error fetching task notification count:', e);
                    // Reset counter on error
                    taskCountEl.textContent = '0';
                    taskCountEl.style.display = 'none';
                  }
                }

                // Fetch email notifications
                async function fetchEmailNotifications(){
                  try {
                    const r = await fetch('{{ route('notifications.emails') }}', { credentials: 'same-origin' });

                    if (!r.ok) {
                      throw new Error(`HTTP error! status: ${r.status}`);
                    }

                    const data = await r.json();
                    const list = data.success ? data.notifications : [];

                    if(!Array.isArray(list) || list.length === 0){
                      emailListEl.innerHTML = `
                        <div class="p-4 text-center text-muted">
                          <div class="mb-3">
                            <i class="bx bx-envelope" style="font-size: 3rem; color: #d1d5db;"></i>
                          </div>
                          <h6 class="text-muted mb-2">No email notifications</h6>
                          <small class="text-muted">You're all caught up!</small>
                        </div>`;
                      return;
                    }
                    emailListEl.innerHTML = list.map(function(n){
                      const title = n.title || 'Email Notification';
                      const message = n.message || '';
                      const timeAgo = getTimeAgo(n.created_at);
                      const viewUrl = n.email_id ? `{{ route('emails.show', ':id') }}`.replace(':id', n.email_id) : '';
                      const typeIcon = 'bx-envelope';
                      const typeColor = '#3b82f6';

                      return `
                        <div class="notification-message p-3 border-bottom" style="transition: all 0.2s ease; cursor: pointer; background: ${n.is_read ? '#f8f9fa' : '#e3f2fd'}; border-left: 3px solid ${n.is_read ? '#e0e0e0' : '#2196f3'};" onclick="handleEmailNotificationClick(${n.id}, '${viewUrl}')">
                          <div class="d-flex align-items-start gap-3">
                            <div class="notification-avatar" style="width: 40px; height: 40px; background: ${typeColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              <i class="bx ${typeIcon}" style="color: white; font-size: 18px;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                              <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="mb-0 fw-semibold text-dark" style="font-size: 14px;">
                                  <span class="badge bg-primary me-2" style="font-size: 10px;">email</span>
                                  ${title}
                                  ${!n.is_read ? '<span class="badge bg-danger ms-2" style="font-size: 8px;">NEW</span>' : ''}
                                </h6>
                                <small class="text-muted" style="font-size: 11px;">${timeAgo}</small>
                              </div>
                              <p class="mb-2 text-muted" style="font-size: 13px; line-height: 1.4; margin: 0;">${message}</p>
                              ${viewUrl ? `
                                <span class="badge bg-primary" style="font-size: 10px; padding: 2px 6px;">
                                  <i class="bx bx-link-external me-1"></i>Click to view email
                                </span>
                              ` : ''}
                            </div>
                          </div>
                        </div>`;
                    }).join('');
                  } catch (e) {
                    emailListEl.innerHTML = `
                      <div class="p-4 text-center text-muted">
                        <div class="mb-3">
                          <i class="bx bx-error-circle" style="font-size: 3rem; color: #f56565;"></i>
                        </div>
                        <h6 class="text-muted mb-2">Failed to load</h6>
                        <small class="text-muted">Please try again later</small>
                      </div>`;
                  }
                }

                // Fetch task notifications
                async function fetchTaskNotifications(){
                  try {
                    const r = await fetch('{{ route('notifications.tasks') }}', { credentials: 'same-origin' });

                    if (!r.ok) {
                      throw new Error(`HTTP error! status: ${r.status}`);
                    }

                    const data = await r.json();
                    const list = data.success ? data.notifications : [];

                    if(!Array.isArray(list) || list.length === 0){
                      taskListEl.innerHTML = `
                        <div class="p-4 text-center text-muted">
                          <div class="mb-3">
                            <i class="bx bx-task" style="font-size: 3rem; color: #d1d5db;"></i>
                          </div>
                          <h6 class="text-muted mb-2">No task notifications</h6>
                          <small class="text-muted">You're all caught up!</small>
                        </div>`;
                      return;
                    }
                    taskListEl.innerHTML = list.map(function(n){
                      const title = n.title || 'Task Notification';
                      const message = n.message || '';
                      const timeAgo = getTimeAgo(n.created_at);
                      const viewUrl = n.action_url || (n.task_id ? `{{ url('tasks') }}/${n.task_id}` : '');
                      const typeIcon = 'bx-task';
                      const typeColor = '#10b981';

                      return `
                        <div class="notification-message p-3 border-bottom" style="transition: all 0.2s ease; cursor: pointer; background: ${n.is_read ? '#f8f9fa' : '#e3f2fd'}; border-left: 3px solid ${n.is_read ? '#e0e0e0' : '#10b981'};" onclick="handleTaskNotificationClick(${n.id}, '${viewUrl}')">
                          <div class="d-flex align-items-start gap-3">
                            <div class="notification-avatar" style="width: 40px; height: 40px; background: ${typeColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              <i class="bx ${typeIcon}" style="color: white; font-size: 18px;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                              <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="mb-0 fw-semibold text-dark" style="font-size: 14px;">
                                  <span class="badge bg-success me-2" style="font-size: 10px;">task</span>
                                  ${title}
                                  ${!n.is_read ? '<span class="badge bg-danger ms-2" style="font-size: 8px;">NEW</span>' : ''}
                                </h6>
                                <small class="text-muted" style="font-size: 11px;">${timeAgo}</small>
                              </div>
                              <p class="mb-2 text-muted" style="font-size: 13px; line-height: 1.4; margin: 0;">${message}</p>
                              ${viewUrl ? `
                                <span class="badge bg-success" style="font-size: 10px; padding: 2px 6px;">
                                  <i class="bx bx-link-external me-1"></i>Click to view task
                                </span>
                              ` : ''}
                            </div>
                          </div>
                        </div>`;
                    }).join('');
                  } catch (e) {
                    taskListEl.innerHTML = `
                      <div class="p-4 text-center text-muted">
                        <div class="mb-3">
                          <i class="bx bx-error-circle" style="font-size: 3rem; color: #f56565;"></i>
                        </div>
                        <h6 class="text-muted mb-2">Failed to load</h6>
                        <small class="text-muted">Please try again later</small>
                      </div>`;
                  }
                }

                // Global function for time formatting
                window.getTimeAgo = function(dateString) {
                  const now = new Date();
                  const date = new Date(dateString);

                  // Check if date is valid
                  if (isNaN(date.getTime())) {
                    return 'Invalid Date';
                  }

                  const diffInSeconds = Math.floor((now - date) / 1000);

                  if (diffInSeconds < 60) return 'Just now';
                  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
                  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
                  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

                  return date.toLocaleDateString();
                };

                // Alias for formatTimeAgo function (used in email notifications)
                window.formatTimeAgo = window.getTimeAgo;

                // Global function for notification icons
                window.getNotificationIcon = function(type) {
                  const icons = {
                    'reply_received': 'bx-reply',
                    'email_received': 'bx-envelope',
                    'email_opened': 'bx-show',
                    'task_assigned': 'bx-task',
                    'task_status_changed': 'bx-refresh',
                    'task_approved': 'bx-check-circle',
                    'task_rejected': 'bx-x-circle',
                    'project_created': 'bx-folder-plus',
                    'project_updated': 'bx-edit',
                    'project_ending_soon': 'bx-time-five',
                    'project_overdue': 'bx-error-circle',
                    'test': 'bx-test-tube',
                    'info': 'bx-info-circle',
                    'warning': 'bx-error',
                    'success': 'bx-check',
                    'error': 'bx-x'
                  };
                  return icons[type] || 'bx-bell';
                };

                // Global function for notification colors
                window.getNotificationColor = function(type) {
                  const colors = {
                    'reply_received': '#10b981',
                    'email_received': '#3b82f6',
                    'email_opened': '#06b6d4',
                    'task_assigned': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'task_status_changed': 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                    'task_approved': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                    'task_rejected': 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
                    'project_created': 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                    'project_updated': 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                    'project_ending_soon': 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                    'project_overdue': 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
                    'test': 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                    'info': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'warning': 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                    'success': 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                    'error': 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)'
                  };
                  return colors[type] || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                };

                // Global function for marking notifications as read
                window.markAsRead = async function(notificationId) {
                  try {
                    await fetch(`{{ route('email-monitoring.notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                      }
                    });
                    // Refresh all notification areas
                    refreshAllNotifications();
                  } catch (e) {
                    console.error('Failed to mark notification as read:', e);
                  }
                };

                // Global function for handling email notification clicks
                window.handleEmailNotificationClick = async function(notificationId, viewUrl) {
                  try {
                    // Mark email notification as read
                    await fetch(`{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                      },
                      credentials: 'same-origin'
                    });

                    // Refresh email notifications
                    fetchEmailCount();
                    fetchEmailNotifications();

                    // If there's a URL, navigate to it
                    if (viewUrl && viewUrl.trim() !== '') {
                      window.location.href = viewUrl;
                    }
                  } catch (e) {
                    console.error('Failed to handle email notification click:', e);
                    // Still try to navigate if there's a URL
                    if (viewUrl && viewUrl.trim() !== '') {
                      window.location.href = viewUrl;
                    }
                  }
                };

                // Global function for handling task notification clicks
                window.handleTaskNotificationClick = async function(notificationId, viewUrl) {
                  try {
                    // Mark task notification as read
                    await fetch(`{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                      },
                      credentials: 'same-origin'
                    });

                    // Refresh task notifications
                    fetchTaskCount();
                    fetchTaskNotifications();

                    // If there's a URL, navigate to it
                    if (viewUrl && viewUrl.trim() !== '') {
                      window.location.href = viewUrl;
                    }
                  } catch (e) {
                    console.error('Failed to handle task notification click:', e);
                    // Still try to navigate if there's a URL
                    if (viewUrl && viewUrl.trim() !== '') {
                      window.location.href = viewUrl;
                    }
                  }
                };

                // Designers Inbox Notifications
                (function(){
                  const designersCountEl = document.getElementById('nav-designers-inbox-count');
                  const designersListEl = document.getElementById('nav-designers-inbox-list');
                  const designersMarkAllBtn = document.getElementById('nav-mark-all-designers-read');

                  if(!designersCountEl || !designersListEl) return;

                  let designersPreviousCount = 0;

                  async function fetchDesignersCount(){
                    try {
                      // Use unified notification system for all notifications
                      const r = await fetch('{{ route('notifications.unread-count') }}', { credentials: 'same-origin' });
                      const d = await r.json();
                      const currentCount = d.success ? d.counts.total : 0;

                      // Debug logging
                      console.log('Designers inbox count fetch result:', { success: d.success, counts: d.counts, currentCount });

                      // Play sound if count increased (new notification)
                      if (currentCount > designersPreviousCount && designersPreviousCount > 0) {
                        playNotificationSound();
                      }

                      designersCountEl.textContent = currentCount;
                      designersCountEl.style.display = currentCount > 0 ? 'inline-block' : 'none';

                      // Also update the main email notification badge
                      const mainEmailBadge = document.getElementById('nav-email-notification-count');
                      if (mainEmailBadge) {
                        mainEmailBadge.textContent = currentCount;
                        mainEmailBadge.style.display = currentCount > 0 ? 'inline-block' : 'none';
                      }

                      // Update the main notification bell count
                      const mainBellCount = document.getElementById('nav-bell-count');
                      if (mainBellCount) {
                        mainBellCount.textContent = currentCount;
                        mainBellCount.style.display = currentCount > 0 ? 'inline' : 'none';
                      }

                      designersPreviousCount = currentCount;
                    } catch (e) {
                      console.error('Error fetching designers count:', e);
                      // Reset counters on error
                      designersCountEl.textContent = '0';
                      designersCountEl.style.display = 'none';
                      const mainEmailBadge = document.getElementById('nav-email-notification-count');
                      if (mainEmailBadge) {
                        mainEmailBadge.textContent = '0';
                        mainEmailBadge.style.display = 'none';
                      }
                      const mainBellCount = document.getElementById('nav-bell-count');
                      if (mainBellCount) {
                        mainBellCount.textContent = '0';
                        mainBellCount.style.display = 'none';
                      }
                    }
                  }

                  async function fetchDesignersNotifications(){
                    try {
                      // Use unified notification system for all notifications
                      const r = await fetch('{{ route('notifications.index') }}', { credentials: 'same-origin' });
                      const d = await r.json();
                      const list = d.success ? d.notifications : [];

                      if(!Array.isArray(list) || list.length === 0){
                        designersListEl.innerHTML = `
                          <div class="p-4 text-center text-muted">
                            <div class="mb-3">
                              <i class="bx bx-envelope-open" style="font-size: 3rem; color: #d1d5db;"></i>
                            </div>
                            <h6 class="text-muted mb-2">No new emails</h6>
                            <small class="text-muted">Designers inbox is up to date!</small>
                          </div>`;
                        return;
                      }

                      designersListEl.innerHTML = list.map(function(n){
                        const title = n.title || 'Notification';
                        const message = n.message || '';
                        const timeAgo = getTimeAgo(n.created_at);
                        const viewUrl = n.action_url ||
                                       (n.category === 'task' && n.task_id ? `{{ url('tasks') }}/${n.task_id}` :
                                       n.category === 'email' && n.email_id ? `{{ route('emails.show', ':id') }}`.replace(':id', n.email_id) :
                                       n.category === 'email' && n.task_id ? `{{ url('tasks') }}/${n.task_id}` : '');
                        const typeIcon = n.icon || (n.type === 'engineering_inbox_user_involved' ? 'bx-user-check' : 'bx-bell');
                        const typeColor = n.color === 'danger' ? '#dc3545' :
                                         n.color === 'warning' ? '#ffc107' :
                                         n.color === 'success' ? '#10b981' :
                                         n.color === 'info' ? '#3b82f6' :
                                         n.type === 'engineering_inbox_user_involved' ? '#8b5cf6' : '#6c757d';

                        // Check if notification requires action
                        const requiresAction = n.requires_action || false;

                        // Enhanced styling for actionable notifications
                        const actionableBg = requiresAction ? (n.is_read ? '#fff3cd' : '#fff3cd') : (n.is_read ? '#f8f9fa' : '#e3f2fd');
                        const actionableBorder = requiresAction ? '#ff9800' : (n.is_read ? '#e0e0e0' : '#2196f3');
                        const actionableBorderWidth = requiresAction ? '4px' : '3px';
                        const actionableAnimation = requiresAction && !n.is_read ? 'animation: pulse-border 2s infinite;' : '';

                        return `
                          <div class="notification-message p-3 border-bottom" style="transition: all 0.2s ease; cursor: pointer; background: ${actionableBg}; border-left: ${actionableBorderWidth} solid ${actionableBorder}; ${actionableAnimation} position: relative;" onclick="handleNotificationClick(${n.id}, '${viewUrl}')">
                            ${requiresAction ? '<div style="position: absolute; top: 8px; right: 8px;"><span class="badge bg-warning text-dark" style="font-size: 9px; font-weight: 600; padding: 3px 6px; box-shadow: 0 2px 4px rgba(255,152,0,0.3);"><i class="bx bx-bell-ring me-1"></i>ACTION REQUIRED</span></div>' : ''}
                            <div class="d-flex align-items-start gap-3">
                              <div class="notification-avatar" style="width: 40px; height: 40px; background: ${requiresAction ? '#ff9800' : typeColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; ${requiresAction && !n.is_read ? 'box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2); animation: pulse-icon 2s infinite;' : ''}">
                                <i class="bx ${typeIcon}" style="color: white; font-size: 18px;"></i>
                              </div>
                              <div class="flex-grow-1" style="min-width: 0; ${requiresAction ? 'padding-right: 100px;' : ''}">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                  <h6 class="mb-0 fw-semibold ${requiresAction ? 'text-dark' : 'text-dark'}" style="font-size: 14px;">
                                    <span class="badge bg-${n.badge_color || 'primary'} me-2" style="font-size: 10px;">${n.category || 'notification'}</span>
                                    ${title}
                                    ${!n.is_read ? '<span class="badge bg-danger ms-2" style="font-size: 8px;">NEW</span>' : ''}
                                  </h6>
                                  <small class="text-muted" style="font-size: 11px;">${timeAgo}</small>
                                </div>
                                <p class="mb-2 ${requiresAction ? 'text-dark fw-medium' : 'text-muted'}" style="font-size: 13px; line-height: 1.4; margin: 0;">${message}</p>
                                ${n.email ? `
                                  <div class="d-flex align-items-center gap-2 mt-2">
                                    <span class="badge bg-light text-dark" style="font-size: 10px; padding: 2px 6px;">
                                      <i class="bx bx-user me-1"></i>${n.email.from_email}
                                    </span>
                                    <span class="badge bg-light text-dark" style="font-size: 10px; padding: 2px 6px;">
                                      <i class="bx bx-time me-1"></i>${n.email.received_at}
                                    </span>
                                  </div>
                                ` : ''}
                                ${viewUrl ? `
                                  <span class="badge ${requiresAction ? 'bg-warning text-dark' : 'bg-primary'} mt-2" style="font-size: 10px; padding: 2px 6px;">
                                    <i class="bx bx-link-external me-1"></i>Click to ${requiresAction ? 'take action' : 'view email'}
                                  </span>
                                ` : ''}
                              </div>
                            </div>
                          </div>`;
                      }).join('');
                    } catch (e) {
                      designersListEl.innerHTML = `
                        <div class="p-4 text-center text-muted">
                          <div class="mb-3">
                            <i class="bx bx-error-circle" style="font-size: 3rem; color: #f56565;"></i>
                          </div>
                          <h6 class="text-muted mb-2">Failed to load</h6>
                          <small class="text-muted">Please try again later</small>
                        </div>`;
                    }
                  }

                  // Global function for handling notification clicks
                  window.handleNotificationClick = function(notificationId, viewUrl) {
                    console.log('handleNotificationClick called with:', notificationId, viewUrl);

                    // Mark as read using unified notification system
                    fetch(`{{ url('notifications') }}/${notificationId}/mark-read`, {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                      }
                    }).then(() => {
                      console.log('Notification marked as read:', notificationId);
                      // Refresh all notification areas
                      if (typeof fetchDesignersCount === 'function') fetchDesignersCount();
                      if (typeof fetchDesignersNotifications === 'function') fetchDesignersNotifications();
                      if (typeof fetchBottomNotifications === 'function') fetchBottomNotifications();
                      if (typeof fetchEmailCount === 'function') fetchEmailCount();
                      if (typeof fetchEmailNotifications === 'function') fetchEmailNotifications();
                      if (typeof fetchTaskCount === 'function') fetchTaskCount();
                      if (typeof fetchTaskNotifications === 'function') fetchTaskNotifications();
                    }).catch(error => {
                      console.error('Error marking notification as read:', error);
                    });

                    // Navigate to URL if provided
                    if (viewUrl && viewUrl.trim() !== '' && viewUrl !== '#') {
                      console.log('Navigating to:', viewUrl);
                      window.location.href = viewUrl;
                    } else {
                      console.log('No valid URL provided for navigation');
                    }
                  };

                  // Ensure the function is always available globally (fallback)
                  if (typeof window.handleNotificationClick === 'undefined') {
                    window.handleNotificationClick = function(notificationId, viewUrl) {
                      console.log('Fallback handleNotificationClick called with:', notificationId, viewUrl);

                      // Mark as read using unified notification system
                      fetch(`{{ url('notifications') }}/${notificationId}/mark-read`, {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Content-Type': 'application/json',
                        }
                      }).then(() => {
                        console.log('Notification marked as read:', notificationId);
                        // Refresh notification counts if functions are available
                        if (typeof fetchEmailCount === 'function') fetchEmailCount();
                        if (typeof fetchTaskCount === 'function') fetchTaskCount();
                        if (typeof fetchBottomNotifications === 'function') fetchBottomNotifications();
                      }).catch(error => {
                        console.error('Error marking notification as read:', error);
                      });

                      // Navigate to URL if provided
                      if (viewUrl && viewUrl.trim() !== '' && viewUrl !== '#') {
                        console.log('Navigating to:', viewUrl);
                        window.location.href = viewUrl;
                      } else {
                        console.log('No valid URL provided for navigation');
                      }
                    };
                  }

                  // Mark all designers notifications as read
                  if (designersMarkAllBtn) {
                    designersMarkAllBtn.addEventListener('click', function() {
                      fetch('{{ route("notifications.mark-all-read") }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Content-Type': 'application/json',
                        }
                      }).then(() => {
                        fetchDesignersCount();
                        fetchDesignersNotifications();
                      });
                    });
                  }

                  // Load notifications when dropdown is shown
                  const designersDropdown = document.querySelector('[data-bs-toggle="dropdown"]');
                  if (designersDropdown) {
                    designersDropdown.addEventListener('shown.bs.dropdown', function() {
                      fetchDesignersNotifications();
                    });
                  }

                  // Initial load
                  fetchDesignersCount();

                  // Auto-refresh every 10 seconds
                  setInterval(fetchDesignersCount, 10000);

                  // Global function to refresh notification count
                  window.refreshNotificationCount = function() {
                    fetchDesignersCount();
                  };
                })();

                // Global function to force reset all notification counters to 0
                window.resetAllNotificationCounters = function(){
                  console.log('Resetting all notification counters to 0');

                  // Reset main notification counter
                  if (countEl) {
                    countEl.textContent = '0';
                    countEl.style.display = 'none';
                  }

                  // Reset designers inbox counter
                  if (designersCountEl) {
                    designersCountEl.textContent = '0';
                    designersCountEl.style.display = 'none';
                  }

                  // Reset sidebar bell count
                  const navBellCount = document.getElementById('nav-bell-count');
                  if (navBellCount) {
                    navBellCount.textContent = '0';
                    navBellCount.style.display = 'none';
                  }

                  // Reset main email notification badge
                  const mainEmailBadge = document.getElementById('nav-email-notification-count');
                  if (mainEmailBadge) {
                    mainEmailBadge.textContent = '0';
                    mainEmailBadge.style.display = 'none';
                  }

                  // Reset previous counts
                  previousCount = 0;
                  designersPreviousCount = 0;

                  console.log('All notification counters reset to 0');
                };

                // Global refresh function that updates both notification areas
                window.refreshAllNotifications = function(){
                  fetchEmailCount();
                  fetchEmailNotifications();
                  fetchTaskCount();
                  fetchTaskNotifications();
                  // Also refresh bottom chat if it exists
                  if (typeof refreshBottomChat === 'function') {
                    refreshBottomChat();
                  }
                };


                // Email notification icon function
                function getEmailNotificationIcon(type) {
                  const icons = {
                    'reply_received': 'bx-reply',
                    'email_received': 'bx-envelope',
                    'email_opened': 'bx-show',
                    'email_sent': 'bx-send',
                    'default': 'bx-envelope'
                  };
                  return icons[type] || icons.default;
                }

                // Email notification color function
                function getEmailNotificationColor(type) {
                  const colors = {
                    'reply_received': '#28a745',
                    'email_received': '#007bff',
                    'email_opened': '#17a2b8',
                    'email_sent': '#6c757d',
                    'default': '#6c757d'
                  };
                  return colors[type] || colors.default;
                }

                // Handle email notification click
                window.handleEmailNotificationClick = async function(notificationId, viewUrl) {
                  try {
                    // Mark email notification as read using unified notifications route
                    await fetch(`{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                      },
                      credentials: 'same-origin'
                    });

                    // Refresh all notification areas
                    refreshAllNotifications();

                    // Navigate to the URL if provided
                    if (viewUrl && viewUrl.trim() !== '' && viewUrl !== '#') {
                      window.location.href = viewUrl;
                    }
                  } catch (e) {
                    console.error('Failed to handle email notification click:', e);
                    // Still try to navigate if there's a URL
                    if (viewUrl && viewUrl.trim() !== '' && viewUrl !== '#') {
                      window.location.href = viewUrl;
                    }
                  }
                };

                // Event listeners for mark all buttons
                if (emailMarkAllBtn) {
                  emailMarkAllBtn.addEventListener('click', async function() {
                    try {
                      await fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                      });
                      refreshAllNotifications();
                    } catch (e) {
                      console.error('Failed to mark all email notifications as read:', e);
                    }
                  });
                }

                if (taskMarkAllBtn) {
                  taskMarkAllBtn.addEventListener('click', async function() {
                    try {
                      await fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                      });
                      refreshAllNotifications();
                    } catch (e) {
                      console.error('Failed to mark all task notifications as read:', e);
                    }
                  });
                }

                // Load notifications when dropdowns are shown
                const emailDropdown = document.querySelector('[data-bs-toggle="dropdown"]');
                if (emailDropdown) {
                  emailDropdown.addEventListener('shown.bs.dropdown', function() {
                    fetchEmailNotifications();
                  });
                }

                const taskDropdown = document.querySelectorAll('[data-bs-toggle="dropdown"]')[1];
                if (taskDropdown) {
                  taskDropdown.addEventListener('shown.bs.dropdown', function() {
                    fetchTaskNotifications();
                  });
                }

                // Poll every 5s for faster updates - refresh both areas
                refreshAllNotifications();
                setInterval(refreshAllNotifications, 5000);

                // Refresh notifications when page becomes visible (user switches back to tab)
                document.addEventListener("visibilitychange", function() {
                  if (!document.hidden) {
                    refreshAllNotifications();
                  }
                });

                // Refresh notifications when window gains focus
                window.addEventListener("focus", function() {
                  refreshAllNotifications();
                });

                // Refresh notifications when user comes back online
                window.addEventListener("online", function() {
                  refreshAllNotifications();
                });

                // Test notification button (debug mode only)
                const testBtn = document.getElementById('test-notification-btn');
                if (testBtn) {
                  testBtn.addEventListener('click', async function() {
                    try {
                      await fetch('{{ route('test.notification') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                      });
                      // Refresh all notification areas to show the new one
                      setTimeout(refreshAllNotifications, 500);
                    } catch (e) {
                      console.error('Failed to create test notification:', e);
                    }
                  });
                }

                // Test email notification button (debug mode only)
                const testEmailBtn = document.getElementById('test-email-notification-btn');
                if (testEmailBtn) {
                  testEmailBtn.addEventListener('click', async function() {
                    try {
                      await fetch('{{ route('create-notification') }}', {
                        method: 'GET',
                        credentials: 'same-origin'
                      });
                      // Refresh all notification areas to show the new one
                      setTimeout(refreshAllNotifications, 500);
                    } catch (e) {
                      console.error('Failed to create test email notification:', e);
                    }
                  });
                }

                const markAllBtn = document.getElementById('bottom-mark-all-read');
                if(markAllBtn){
                  markAllBtn.addEventListener('click', async function(){
                    try{
                      await fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                      });
                      refreshAllNotifications();
                    } catch(e){
                      console.error('Failed to mark all notifications as read:', e);
                    }
                  });
                }

                // Mark all email notifications as read
                const markAllEmailBtn = document.getElementById('nav-mark-all-email-read');
                if(markAllEmailBtn){
                  markAllEmailBtn.addEventListener('click', async function(){
                    try{
                      await fetch('{{ route('auto-emails.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                      });
                      refreshAllNotifications();
                    } catch(e){
                      console.error('Failed to mark all email notifications as read:', e);
                    }
                  });
                }
              })();

              // Bottom Chat Popup for Regular Users - Always Visible
              @if(Auth::user()->isRegularUser())
              (function(){
                const bottomChatPopup = document.getElementById('bottom-chat-popup');
                const bottomChatMessages = document.getElementById('bottom-chat-messages');
                const bottomChatBadge = document.getElementById('bottom-chat-badge');
                const bottomMarkAllBtn = document.getElementById('bottom-mark-all-read');
                const bottomTestBtn = document.getElementById('bottom-test-notification-btn');
                const bottomChatToggleIcon = document.getElementById('bottom-chat-toggle-icon');

                let isBottomChatOpen = false;
                let previousBottomCount = 0;

                // Toggle bottom chat (slide up/down)
                window.toggleBottomChat = function() {
                  if (isBottomChatOpen) {
                    closeBottomChat();
                  } else {
                    openBottomChat();
                  }
                };

                function openBottomChat() {
                  bottomChatPopup.style.transform = 'translateY(0)';
                  bottomChatToggleIcon.className = 'bx bx-chevron-down';
                  isBottomChatOpen = true;
                  fetchBottomNotifications();
                }

                function closeBottomChat() {
                  bottomChatPopup.style.transform = 'translateY(420px)';
                  bottomChatToggleIcon.className = 'bx bx-chevron-up';
                  isBottomChatOpen = false;
                }

                // Fetch notifications for bottom chat
                window.fetchBottomNotifications = async function() {
                  try {
                    // Use new unified notification system
                    const r = await fetch('{{ route('notifications.index') }}', { credentials: 'same-origin' });

                    if (!r.ok) {
                      throw new Error(`HTTP error! status: ${r.status}`);
                    }

                    const data = await r.json();
                    const list = data.success ? data.notifications : [];

                    if (!Array.isArray(list) || list.length === 0) {
                      bottomChatMessages.innerHTML = `
                        <div class="p-4 text-center text-muted">
                          <div class="mb-3">
                            <i class="bx bx-message-dots" style="font-size: 3rem; color: #d1d5db;"></i>
                          </div>
                          <h6 class="text-muted mb-2">No new notifications</h6>
                          <small class="text-muted">You're all caught up!</small>
                        </div>`;
                      return;
                    }

                    bottomChatMessages.innerHTML = list.map(function(n) {
                      const title = n.title || 'Notification';
                      const message = n.message || '';
                      const timeAgo = getTimeAgo(n.created_at);
                      const viewUrl = n.action_url ||
                                     (n.category === 'task' && n.task_id ? `{{ url('tasks') }}/${n.task_id}` :
                                     n.category === 'email' && n.email_id ? `{{ route('emails.show', ':id') }}`.replace(':id', n.email_id) :
                                     n.category === 'email' && n.task_id ? `{{ url('tasks') }}/${n.task_id}` : '');
                      const typeIcon = n.icon || (n.type === 'engineering_inbox_user_involved' ? 'bx-user-check' : 'bx-bell');
                      const typeColor = n.color === 'danger' ? '#dc3545' :
                                       n.color === 'warning' ? '#ffc107' :
                                       n.color === 'success' ? '#198754' :
                                       n.color === 'info' ? '#0dcaf0' :
                                       n.type === 'engineering_inbox_user_involved' ? '#8b5cf6' : '#6c757d';

                      // Check if notification requires action
                      const requiresAction = n.requires_action || false;

                      // Enhanced styling for actionable notifications
                      const actionableBg = requiresAction ? (n.is_read ? '#fff3cd' : '#fff3cd') : (n.is_read ? '#ffffff' : '#e3f2fd');
                      const actionableBorder = requiresAction ? '#ff9800' : (n.is_read ? '#e0e0e0' : '#2196f3');
                      const actionableBorderWidth = requiresAction ? '4px' : '3px';
                      const actionableAnimation = requiresAction && !n.is_read ? 'animation: pulse-border 2s infinite;' : '';

                      return `
                        <div class="chat-message p-3 border-bottom" style="transition: all 0.2s ease; cursor: pointer; background: ${actionableBg}; margin: 4px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: ${actionableBorderWidth} solid ${actionableBorder}; ${actionableAnimation} position: relative;" onclick="handleNotificationClick(${n.id}, '${viewUrl}')">
                          ${requiresAction ? '<div style="position: absolute; top: 6px; right: 6px;"><span class="badge bg-warning text-dark" style="font-size: 8px; font-weight: 600; padding: 2px 5px; box-shadow: 0 2px 4px rgba(255,152,0,0.3);"><i class="bx bx-bell-ring" style="font-size: 8px;"></i> ACTION</span></div>' : ''}
                          <div class="d-flex align-items-start gap-3">
                            <div class="notification-avatar" style="width: 36px; height: 36px; background: ${requiresAction ? '#ff9800' : typeColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; ${requiresAction && !n.is_read ? 'box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2); animation: pulse-icon 2s infinite;' : ''}">
                              <i class="bx ${typeIcon}" style="color: white; font-size: 16px;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0; ${requiresAction ? 'padding-right: 60px;' : ''}">
                              <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="mb-0 fw-semibold text-dark" style="font-size: 13px;">
                                  <span class="badge bg-${n.badge_color} me-2" style="font-size: 9px;">${n.category}</span>
                                  ${title}
                                  ${!n.is_read ? '<span class="badge bg-danger ms-2" style="font-size: 7px;">NEW</span>' : ''}
                                </h6>
                                <small class="text-muted" style="font-size: 10px;">${timeAgo}</small>
                              </div>
                              <p class="mb-2 ${requiresAction ? 'text-dark fw-medium' : 'text-muted'}" style="font-size: 12px; line-height: 1.4; margin: 0;">${message}</p>
                              ${viewUrl ? `
                                <span class="badge ${requiresAction ? 'bg-warning text-dark' : 'bg-primary'}" style="font-size: 9px; padding: 1px 4px;">
                                  <i class="bx bx-link-external me-1"></i>Click to ${requiresAction ? 'take action' : 'view ' + n.category}
                                </span>
                              ` : ''}
                            </div>
                          </div>
                        </div>`;
                    }).join('');
                  } catch (e) {
                    bottomChatMessages.innerHTML = `
                      <div class="p-4 text-center text-muted">
                        <div class="mb-3">
                          <i class="bx bx-error-circle" style="font-size: 3rem; color: #f56565;"></i>
                        </div>
                        <h6 class="text-muted mb-2">Failed to load</h6>
                        <small class="text-muted">Please try again later</small>
                      </div>`;
                  }
                };

                // Fetch notification count for bottom chat
                async function fetchBottomCount() {
                  try {
                    // Use new unified notification system
                    const r = await fetch('{{ route('notifications.unread-count') }}', { credentials: 'same-origin' });
                    const d = await r.json();
                    const currentCount = d.success ? d.counts.total : 0;
                    const taskCount = d.success ? d.counts.task : 0;
                    const emailCount = d.success ? d.counts.email : 0;

                    // Auto-open chat if new notification arrives
                    if (currentCount > previousBottomCount && previousBottomCount >= 0) {
                      // Play appropriate sound based on notification type
                      if (taskCount > 0) {
                        playTaskNotificationSound();
                      } else {
                        playNotificationSound();
                      }

                      // Auto-open chat when new notification arrives
                      if (!isBottomChatOpen) {
                        openBottomChat();
                        // Auto-close after 10 seconds if user doesn't interact
                        setTimeout(() => {
                          if (isBottomChatOpen) {
                            closeBottomChat();
                          }
  }, 10000);
                      }
                      // Also refresh the dropdown notifications
                      if (typeof refreshAllNotifications === 'function') {
                        refreshAllNotifications();
                      }
                    }

                    if (currentCount > 0) {
                      bottomChatBadge.textContent = currentCount;
                      bottomChatBadge.style.display = 'flex';
                    } else {
                      bottomChatBadge.style.display = 'none';
                    }
                    previousBottomCount = currentCount;
                  } catch (e) {
                    // silent
                  }
                }


                // Mark all as read for bottom chat
                if (bottomMarkAllBtn) {
                  bottomMarkAllBtn.addEventListener('click', async function() {
                    try {
                      await fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                          'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                      });
                      // Refresh both notification areas
                      if (typeof refreshAllNotifications === 'function') {
                        refreshAllNotifications();
                      } else {
                        refreshBottomChat();
                      }
                    } catch (e) {
                      console.error('Failed to mark all as read:', e);
                    }
                  });
                }

                // Test notification for bottom chat
                if (bottomTestBtn) {
                  bottomTestBtn.addEventListener('click', async function() {
                    try {
                      await fetch('{{ route('test.notification') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                      });
                      // Refresh both notification areas
                      setTimeout(function() {
                        if (typeof refreshAllNotifications === 'function') {
                          refreshAllNotifications();
                        } else {
                          refreshBottomChat();
                        }
                      }, 500);
                    } catch (e) {
                      console.error('Failed to create test notification:', e);
                    }
                  });
                }

                // Global function for refreshing bottom chat
                window.refreshBottomChat = function() {
                  fetchBottomCount();
                  if (isBottomChatOpen) {
                    fetchBottomNotifications();
                  }
                };

                // Initialize bottom chat
                fetchBottomCount();

                // Poll every 20s - refresh both areas
                setInterval(function() {
                  if (typeof refreshAllNotifications === 'function') {
                    refreshAllNotifications();
                  } else {
                    refreshBottomChat();
                  }
                }, 20000);

                // Show bottom chat popup initially (always visible)
                setTimeout(() => {
                  bottomChatPopup.style.display = 'block';
                  // Auto-open if there are notifications
                  if (previousBottomCount > 0) {
                    openBottomChat();
                  }
                }, 1000);
              })();
              @endif
            </script>

            <style>
              /* Notification Chat Popup Styles */
              .notification-chat-popup {
                animation: slideInDown 0.3s ease-out;
              }

              @keyframes slideInDown {
                from {
                  opacity: 0;
                  transform: translateY(-10px);
                }
                to {
                  opacity: 1;
                  transform: translateY(0);
                }
              }

              .notification-messages::-webkit-scrollbar {
                width: 6px;
              }

              .notification-messages::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
              }

              .notification-messages::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
              }

              .notification-messages::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
              }

              .notification-message {
                transition: all 0.2s ease;
                border-left: 3px solid transparent;
              }

              .notification-message:hover {
                background-color: #f1f3f4 !important;
                border-left-color: #667eea;
                transform: translateX(2px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
              }

              .chat-message:hover {
                transform: translateX(2px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
              }

              .notification-avatar {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border: 2px solid rgba(255,255,255,0.8);
              }

              .notification-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
              }

              /* Custom scrollbar for notification messages */
              .notification-messages {
                scrollbar-width: thin;
                scrollbar-color: #c1c1c1 #f1f1f1;
              }

              /* Animation for new notifications */
              .notification-message.new-notification {
                animation: pulse 0.5s ease-in-out;
              }

              @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.02); }
                100% { transform: scale(1); }
              }

              /* Badge animation */
              #nav-notification-count {
                animation: bounce 0.6s ease-in-out;
              }

              @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-3px); }
                60% { transform: translateY(-2px); }
              }

              /* Actionable notification pulse animations */
              @keyframes pulse-border {
                0%, 100% { border-left-color: #ff9800; }
                50% { border-left-color: #ff5722; }
              }

              @keyframes pulse-icon {
                0%, 100% {
                  box-shadow: 0 0 0 4px rgba(255, 152, 0, 0.2);
                  transform: scale(1);
                }
                50% {
                  box-shadow: 0 0 0 8px rgba(255, 152, 0, 0.1);
                  transform: scale(1.05);
                }
              }

              /* Bottom Chat Widget Styles - Always Visible */
              .bottom-chat-widget {
                font-family: 'Public Sans', sans-serif;
                border-radius: 16px 16px 0 0 !important;
                border-bottom: none !important;
                box-shadow: 0 -8px 32px rgba(0,0,0,0.12) !important;
              }

              .chat-header {
                cursor: pointer;
                transition: all 0.2s ease;
              }

              .chat-header:hover {
                background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
              }

              .chat-toggle-btn {
                animation: pulse 2s infinite;
              }

              .chat-toggle-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
              }

              .chat-window {
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
              }

              .chat-messages::-webkit-scrollbar {
                width: 4px;
              }

              .chat-messages::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 2px;
              }

              .chat-messages::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 2px;
              }

              .chat-messages::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
              }

              .chat-message {
                transition: all 0.2s ease;
                border-left: 3px solid transparent;
              }

              .chat-message:hover {
                background-color: #f8f9fa !important;
                border-left-color: #667eea;
                transform: translateX(2px);
              }

              .chat-badge {
                animation: bounce 0.6s ease-in-out;
              }

              .chat-avatar {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border: 2px solid rgba(255,255,255,0.8);
              }

              .chat-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
              }

              /* Mobile responsiveness for bottom chat - Always Visible */
              @media (max-width: 768px) {
                .bottom-chat-widget {
                  width: 300px !important;
                  height: 400px !important;
                }

                .chat-messages {
                  height: 300px !important;
                }
              }

              @media (max-width: 480px) {
                .bottom-chat-widget {
                  width: 280px !important;
                  height: 350px !important;
                }

                .chat-messages {
                  height: 250px !important;
                }
              }

              @media (max-width: 360px) {
                .bottom-chat-widget {
                  width: 100% !important;
                  right: 0 !important;
                  left: 0 !important;
                  height: 300px !important;
                }

                .chat-messages {
                  height: 200px !important;
                }
              }

              /* Animation for chat window */
              @keyframes slideInUp {
                from {
                  opacity: 0;
                  transform: translateY(20px);
                }
                to {
                  opacity: 1;
                  transform: translateY(0);
                }
              }

              .chat-window.show {
                animation: slideInUp 0.3s ease-out;
              }

              /* Responsive Navigation Profile Image */
              .nav-profile-avatar {
                position: relative;
                display: inline-block;
                width: 40px;
                height: 40px;
                padding: 0;
                margin: 0;
                overflow: hidden;
                border-radius: 50%;
              }

              .nav-profile-avatar img {
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                object-position: center !important;
                transition: all 0.3s ease;
                border: 2px solid transparent;
                display: block;
                max-width: 40px !important;
                max-height: 40px !important;
                min-width: 40px !important;
                min-height: 40px !important;
              }

              .nav-profile-avatar:hover img {
                transform: scale(1.05);
                border-color: #696cff;
                box-shadow: 0 2px 8px rgba(105, 108, 255, 0.3);
              }

              /* Responsive sizing for different screen sizes */
              @media (max-width: 768px) {
                .nav-profile-avatar {
                  width: 35px;
                  height: 35px;
                  padding: 0;
                  margin: 0;
                  overflow: hidden;
                  border-radius: 50%;
                }
                .nav-profile-avatar img {
                  width: 35px !important;
                  height: 35px !important;
                  max-width: 35px !important;
                  max-height: 35px !important;
                  min-width: 35px !important;
                  min-height: 35px !important;
                }
              }

              @media (max-width: 576px) {
                .nav-profile-avatar {
                  width: 32px;
                  height: 32px;
                  padding: 0;
                  margin: 0;
                  overflow: hidden;
                  border-radius: 50%;
                }
                .nav-profile-avatar img {
                  width: 32px !important;
                  height: 32px !important;
                  max-width: 32px !important;
                  max-height: 32px !important;
                  min-width: 32px !important;
                  min-height: 32px !important;
                }
              }

              @media (max-width: 480px) {
                .nav-profile-avatar {
                  width: 30px;
                  height: 30px;
                  padding: 0;
                  margin: 0;
                  overflow: hidden;
                  border-radius: 50%;
                }
                .nav-profile-avatar img {
                  width: 30px !important;
                  height: 30px !important;
                  max-width: 30px !important;
                  max-height: 30px !important;
                  min-width: 30px !important;
                  min-height: 30px !important;
                }
              }

              /* Fix for very small screens */
              @media (max-width: 360px) {
                .nav-profile-avatar {
                  width: 28px;
                  height: 28px;
                  padding: 0;
                  margin: 0;
                  overflow: hidden;
                  border-radius: 50%;
                }
                .nav-profile-avatar img {
                  width: 28px !important;
                  height: 28px !important;
                  max-width: 28px !important;
                  max-height: 28px !important;
                  min-width: 28px !important;
                  min-height: 28px !important;
                }
              }
            </style>

