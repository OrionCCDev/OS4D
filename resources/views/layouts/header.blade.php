<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
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

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
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
            @if(Auth::user()->isManager())
            <!-- Dashboard - Manager only -->
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

            @if(Auth::user()->isManager())
            <!-- Projects - Manager only -->
            <li class="menu-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
              <a href="{{ route('projects.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-folder"></i>
                <div data-i18n="Projects">Projects</div>
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
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Live Notifications - Chat Style -->
                <li class="nav-item dropdown me-3">
                  <a class="nav-link dropdown-toggle hide-arrow position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-message-dots fs-4"></i>
                    <span class="badge rounded-pill bg-danger position-absolute" style="top: 0; right: -4px;" id="nav-notification-count">0</span>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-0 notification-chat-popup" style="min-width: 380px; max-width: 400px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border: 1px solid #e5e7eb;">
                    <!-- Chat Header -->
                    <div class="notification-header d-flex align-items-center justify-content-between p-3 border-bottom" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2" style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                          <i class="bx bx-message-dots" style="font-size: 16px;"></i>
                        </div>
                        <div>
                          <h6 class="mb-0 fw-semibold">Notifications</h6>
                          <small class="opacity-75">Live updates</small>
                        </div>
                      </div>
                      <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-light" type="button" id="nav-mark-all-read" style="border-radius: 6px; padding: 4px 8px; font-size: 12px;">Mark all</button>
                      </div>
                    </div>

                    <!-- Chat Messages Area -->
                    <div class="notification-messages" style="max-height: 400px; overflow-y: auto; background: #f8f9fa;" id="nav-notification-list">
                      <div class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading notifications...
                      </div>
                    </div>

                    <!-- Chat Footer -->
                    <div class="notification-footer p-3 border-top" style="background: white; border-radius: 0 0 12px 12px;">
                      <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px;">
                          <i class="bx bx-list-ul me-1"></i>View All
                        </a>
                        @if(config('app.debug'))
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="test-notification-btn" style="border-radius: 8px;">
                          <i class="bx bx-test-tube me-1"></i>Test
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </li>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="{{ asset('uploads/users/' . Auth::user()->img) }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="{{ asset('uploads/users/' . Auth::user()->img) }}" alt class="w-px-40 h-auto rounded-circle" />
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
                    <li>
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
                    </li>
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
              (function(){
                const countEl = document.getElementById('nav-notification-count');
                const listEl = document.getElementById('nav-notification-list');
                const markAllBtn = document.getElementById('nav-mark-all-read');

                if(!countEl || !listEl) return;

                // Store previous count to detect new notifications
                let previousCount = 0;


                // Global function for playing notification sound (disabled)
                window.playNotificationSound = function() {
                  // Sound functionality removed
                };

                async function fetchCount(){
                  try {
                    const r = await fetch('{{ route('api.notifications.count') }}', { credentials: 'same-origin' });
                    const d = await r.json();
                    const currentCount = d.count ?? 0;

                    // Play sound if count increased (new notification)
                    if (currentCount > previousCount && previousCount > 0) {
                      playNotificationSound();
                      // Also refresh bottom chat if it exists
                      if (typeof refreshBottomChat === 'function') {
                        refreshBottomChat();
                      }
                    }

                    countEl.textContent = currentCount;
                    countEl.style.display = currentCount > 0 ? 'inline-block' : 'none';
                    previousCount = currentCount;
                  } catch (e) {
                    // silent
                  }
                }

                async function fetchUnread(){
                  try {
                    const r = await fetch('{{ route('api.notifications.unread') }}', { credentials: 'same-origin' });

                    if (!r.ok) {
                      throw new Error(`HTTP error! status: ${r.status}`);
                    }

                    const list = await r.json();

                    if(!Array.isArray(list) || list.length === 0){
                      listEl.innerHTML = `
                        <div class="p-4 text-center text-muted">
                          <div class="mb-3">
                            <i class="bx bx-message-dots" style="font-size: 3rem; color: #d1d5db;"></i>
                          </div>
                          <h6 class="text-muted mb-2">No new notifications</h6>
                          <small class="text-muted">You're all caught up!</small>
                        </div>`;
                      return;
                    }
                    listEl.innerHTML = list.map(function(n){
                      const title = (n.title || 'Notification');
                      const message = (n.message || '');
                      const timeAgo = getTimeAgo(n.created_at);
                      const viewUrl = n.data && n.data.task_id ? `{{ url('tasks') }}/${n.data.task_id}` : '';
                      const notificationType = n.type || 'info';
                      const typeIcon = getNotificationIcon(notificationType);
                      const typeColor = getNotificationColor(notificationType);

                      return `
                        <div class="notification-message p-3 border-bottom" style="transition: all 0.2s ease; cursor: pointer;" onclick="handleNotificationClick(${n.id}, '${viewUrl}')">
                          <div class="d-flex align-items-start gap-3">
                            <div class="notification-avatar" style="width: 40px; height: 40px; background: ${typeColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              <i class="bx ${typeIcon}" style="color: white; font-size: 18px;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                              <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="mb-0 fw-semibold text-dark" style="font-size: 14px;">${title}</h6>
                                <small class="text-muted" style="font-size: 11px;">${timeAgo}</small>
                              </div>
                              <p class="mb-2 text-muted" style="font-size: 13px; line-height: 1.4; margin: 0;">${message}</p>
                              ${viewUrl ? `
                                <span class="badge bg-primary" style="font-size: 10px; padding: 2px 6px;">
                                  <i class="bx bx-link-external me-1"></i>Click to view task
                                </span>
                              ` : ''}
                            </div>
                          </div>
                        </div>`;
                    }).join('');
                  } catch (e) {
                    listEl.innerHTML = `
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
                  const diffInSeconds = Math.floor((now - date) / 1000);

                  if (diffInSeconds < 60) return 'Just now';
                  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
                  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
                  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

                  return date.toLocaleDateString();
                };

                // Global function for notification icons
                window.getNotificationIcon = function(type) {
                  const icons = {
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
                    await fetch(`{{ url('notifications') }}/${notificationId}/read`, {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                      },
                      credentials: 'same-origin'
                    });
                    // Refresh all notification areas
                    refreshAllNotifications();
                  } catch (e) {
                    console.error('Failed to mark notification as read:', e);
                  }
                };

                // Global function for handling notification clicks
                window.handleNotificationClick = async function(notificationId, viewUrl) {
                  try {
                    // Mark notification as read first
                    await fetch(`{{ url('notifications') }}/${notificationId}/read`, {
                      method: 'POST',
                      headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                      },
                      credentials: 'same-origin'
                    });

                    // Refresh all notification areas
                    refreshAllNotifications();

                    // If there's a task URL, navigate to it
                    if (viewUrl && viewUrl.trim() !== '') {
                      window.location.href = viewUrl;
                    }
                  } catch (e) {
                    console.error('Failed to handle notification click:', e);
                    // Still try to navigate if there's a URL
                    if (viewUrl && viewUrl.trim() !== '') {
                      window.location.href = viewUrl;
                    }
                  }
                };

                // Global refresh function that updates both notification areas
                window.refreshAllNotifications = function(){
                  fetchCount();
                  fetchUnread();
                  // Also refresh bottom chat if it exists
                  if (typeof refreshBottomChat === 'function') {
                    refreshBottomChat();
                  }
                };

                function refresh(){
                  refreshAllNotifications();
                }


                // Poll every 20s - refresh both areas
                refreshAllNotifications();
                setInterval(refreshAllNotifications, 20000);

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

                if(markAllBtn){
                  markAllBtn.addEventListener('click', async function(){
                    try{
                      await fetch('{{ route('notifications.read-all') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin'
                      });
                      refreshAllNotifications();
                    } catch(e){ /* noop */ }
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
                async function fetchBottomNotifications() {
                  try {
                    const r = await fetch('{{ route('api.notifications.unread') }}', { credentials: 'same-origin' });

                    if (!r.ok) {
                      throw new Error(`HTTP error! status: ${r.status}`);
                    }

                    const list = await r.json();

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
                      const title = (n.title || 'Notification');
                      const message = (n.message || '');
                      const timeAgo = getTimeAgo(n.created_at);
                      const viewUrl = n.data && n.data.task_id ? `{{ url('tasks') }}/${n.data.task_id}` : '';
                      const notificationType = n.type || 'info';
                      const typeIcon = getNotificationIcon(notificationType);
                      const typeColor = getNotificationColor(notificationType);

                      return `
                        <div class="chat-message p-3 border-bottom" style="transition: all 0.2s ease; cursor: pointer; background: white; margin: 4px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onclick="handleNotificationClick(${n.id}, '${viewUrl}')">
                          <div class="d-flex align-items-start gap-3">
                            <div class="notification-avatar" style="width: 36px; height: 36px; background: ${typeColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              <i class="bx ${typeIcon}" style="color: white; font-size: 16px;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                              <div class="d-flex align-items-center justify-content-between mb-1">
                                <h6 class="mb-0 fw-semibold text-dark" style="font-size: 13px;">${title}</h6>
                                <small class="text-muted" style="font-size: 10px;">${timeAgo}</small>
                              </div>
                              <p class="mb-2 text-muted" style="font-size: 12px; line-height: 1.4; margin: 0;">${message}</p>
                              ${viewUrl ? `
                                <span class="badge bg-primary" style="font-size: 9px; padding: 1px 4px;">
                                  <i class="bx bx-link-external me-1"></i>Click to view task
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
                }

                // Fetch notification count for bottom chat
                async function fetchBottomCount() {
                  try {
                    const r = await fetch('{{ route('api.notifications.count') }}', { credentials: 'same-origin' });
                    const d = await r.json();
                    const currentCount = d.count ?? 0;

                    // Auto-open chat if new notification arrives
                    if (currentCount > previousBottomCount && previousBottomCount >= 0) {
                      playNotificationSound();
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
                      await fetch('{{ route('notifications.read-all') }}', {
                        method: 'POST',
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            </style>
