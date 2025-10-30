<!DOCTYPE html>
<html
  lang="en"
  class="light-style customizer-hide"
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

    <title>Login - {{ config('app.name', 'Orion Designers') }}</title>

    <meta name="description" content="Login to {{ config('app.name', 'Orion Designers') }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('DAssets/logo-blue.webp') }}" />

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

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('DAssets/assets/vendor/css/pages/page-auth.css') }}" />
    <!-- Helpers -->
    <script src="{{ asset('DAssets/assets/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('DAssets/assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Content -->

    <style>
      /* Parallax background container */
      .auth-parallax {
        position: fixed;
        inset: 0;
        overflow: hidden;
        z-index: 0;
        pointer-events: none;
        background: radial-gradient(1200px 600px at 10% 10%, rgba(25,118,210,0.12), transparent 60%),
                    radial-gradient(1000px 500px at 90% 20%, rgba(76,175,80,0.10), transparent 60%),
                    linear-gradient(180deg, rgba(3,169,244,0.06), rgba(3,169,244,0.00));
      }
      /* Animated gradient backdrop under parallax */
      .auth-gradient {
        position: fixed;
        inset: 0;
        z-index: -1;
        background: linear-gradient(120deg, #0d47a1, #1976d2 35%, #26c6da 60%, #7e57c2);
        background-size: 200% 200%;
        animation: authGradientMove 18s ease-in-out infinite;
      }
      @keyframes authGradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }
      .auth-parallax .layer {
        position: absolute;
        will-change: transform;
        transition: transform 0.12s ease-out;
        filter: drop-shadow(0 10px 18px rgba(0,0,0,0.12));
        opacity: 0.9;
      }
      .auth-parallax .bubble {
        border-radius: 9999px;
        background: linear-gradient(135deg, rgba(33,150,243,0.28), rgba(144,202,249,0.18));
        backdrop-filter: blur(2px);
        animation: floatY 9s ease-in-out infinite;
      }
      .auth-parallax .bubble.b1 { width: 260px; height: 260px; top: 8%; left: -60px; animation-delay: 0s; }
      .auth-parallax .bubble.b2 { width: 180px; height: 180px; bottom: 12%; right: -40px; animation-delay: 1.2s; }
      .auth-parallax .bubble.b3 { width: 120px; height: 120px; top: 70%; left: 12%; animation-delay: 0.6s; }
      .auth-parallax .bubble.b4 { width: 220px; height: 220px; top: 22%; right: 18%; animation-delay: .9s; }
      .auth-parallax .bubble.b5 { width: 90px; height: 90px; bottom: 28%; left: 24%; animation-delay: 1.8s; }
      .auth-parallax .bubble.b6 { width: 140px; height: 140px; top: 55%; right: 6%; animation-delay: .3s; }
      .auth-parallax .orb {
        position: absolute;
        width: 480px; height: 480px;
        border-radius: 50%;
        background: radial-gradient(closest-side, rgba(126,87,194,0.16), rgba(126,87,194,0));
        filter: blur(8px) saturate(120%);
        mix-blend-mode: screen;
        will-change: transform;
      }
      .auth-parallax .orb.o1 { top: -120px; left: 35%; }
      .auth-parallax .orb.o2 { bottom: -160px; left: -120px; width: 560px; height: 560px; background: radial-gradient(closest-side, rgba(38,198,218,0.16), rgba(38,198,218,0)); }
      .auth-parallax .logo-float {
        top: 15%;
        right: 8%;
        width: 140px;
        opacity: 0.85;
        animation: floatY 7.5s ease-in-out infinite;
        pointer-events: auto;
      }
      .auth-parallax .logo-float img { width: 100%; height: auto; display: block; }
      @keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }

      /* Elevate the login card above parallax */
      .authentication-wrapper { position: relative; z-index: 1; }

      /* Entry animations */
      .auth-fade-in { opacity: 0; transform: translateY(12px) scale(0.98); animation: authEnter .6s cubic-bezier(.2,.6,.2,1) .15s forwards; }
      .auth-stagger-1 { animation-delay: .2s; }
      .auth-stagger-2 { animation-delay: .3s; }
      .auth-stagger-3 { animation-delay: .4s; }
      @keyframes authEnter { to { opacity: 1; transform: translateY(0) scale(1); } }

      /* Subtle hover on submit */
      .btn.auth-cta { transform: translateZ(0); transition: transform .15s ease, box-shadow .15s ease; }
      .btn.auth-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(25,118,210,0.22); }

      /* Glassmorphism + tilt card */
      .card.auth-modern {
        position: relative;
        background: rgba(255,255,255,0.72);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.35);
        box-shadow: 0 18px 50px rgba(13,71,161,0.20), 0 2px 8px rgba(0,0,0,0.04);
        transform-style: preserve-3d;
        transition: transform .12s ease-out, box-shadow .2s ease;
      }
      .card.auth-modern:hover { box-shadow: 0 24px 60px rgba(25,118,210,0.28), 0 6px 16px rgba(0,0,0,0.08); }
      .card-shine {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: radial-gradient(320px 220px at var(--mx, 50%) var(--my, 50%), rgba(255,255,255,0.38), transparent 60%);
        mix-blend-mode: soft-light;
        opacity: .6;
        border-radius: inherit;
        transform: translateZ(40px);
      }

      /* Inputs focus glow */
      .form-control:focus {
        box-shadow: 0 0 0 .25rem rgba(25,118,210,0.20);
        transform: translateY(-1px);
        transition: transform .15s ease, box-shadow .2s ease;
      }

      /* Respect reduced motion */
      @media (prefers-reduced-motion: reduce) {
        .auth-parallax .layer, .auth-fade-in, .btn.auth-cta, .bubble, .auth-gradient { animation: none !important; transition: none !important; }
      }
      /* Interactive logo hover */
      .logo-interactive { transition: transform .18s ease, filter .18s ease; transform-style: preserve-3d; }
      .logo-hovering { filter: drop-shadow(0 10px 20px rgba(25,118,210,0.25)); }
    </style>

    <div class="auth-gradient" aria-hidden="true"></div>
    <div class="auth-parallax" aria-hidden="true">
      <div class="layer orb o1" data-depth="0.02" data-speed="0.3" data-ampx="20" data-ampy="12"></div>
      <div class="layer orb o2" data-depth="0.03" data-speed="0.25" data-ampx="26" data-ampy="16"></div>
      <div class="layer bubble b1" data-depth="0.05" data-speed="0.45" data-ampx="18" data-ampy="10"></div>
      <div class="layer bubble b2" data-depth="0.08" data-speed="0.55" data-ampx="22" data-ampy="12"></div>
      <div class="layer bubble b3" data-depth="0.12" data-speed="0.65" data-ampx="26" data-ampy="14"></div>
      <div class="layer bubble b4" data-depth="0.09" data-speed="0.5" data-ampx="18" data-ampy="10"></div>
      <div class="layer bubble b5" data-depth="0.14" data-speed="0.75" data-ampx="30" data-ampy="16"></div>
      <div class="layer bubble b6" data-depth="0.06" data-speed="0.35" data-ampx="16" data-ampy="10"></div>
      <div class="layer logo-float" data-depth="0.10" data-speed="0.4" data-ampx="14" data-ampy="10">
        <img class="logo-interactive" src="{{ asset('DAssets/logo-blue.webp') }}" alt="Logo floating layer" />
      </div>
    </div>

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Login -->
          <div class="card auth-fade-in auth-modern" id="authCard">
            <div class="card-shine" aria-hidden="true"></div>
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center auth-fade-in auth-stagger-1">
                <a href="{{ url('/') }}" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                <img class="logo-interactive" width="120px" src="{{ asset('DAssets/logo-blue.webp') }}" alt="">
                  </span>

                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-2 auth-fade-in auth-stagger-2">Welcome to Orion Designers 👋</h4>
              <p class="mb-4 auth-fade-in auth-stagger-3">Please sign-in to your account and start the adventure</p>

              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <form id="formAuthentication" class="mb-3 auth-fade-in auth-stagger-3" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input
                    type="text"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email or username"
                    autofocus
                    required
                  />
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3 form-password-toggle">
                  {{--  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                    <a href="{{ route('password.request') }}">
                      <small>Forgot Password?</small>
                    </a>
                  </div>  --}}
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control @error('password') is-invalid @enderror"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password"
                      required
                    />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                </div>
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100 auth-cta" type="submit">
                    <i class="bx bx-log-in me-1"></i>Sign in
                  </button>
                </div>
              </form>

              {{--  <p class="text-center">
                <span>New on our platform?</span>
                <a href="{{ route('register') }}">
                  <span>Create an account</span>
                </a>
              </p>  --}}
            </div>
          </div>
          <!-- /Login -->
        </div>
      </div>
    </div>

    <!-- / Content -->


    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('DAssets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('DAssets/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('DAssets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('DAssets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('DAssets/assets/vendor/js/menu.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('DAssets/assets/js/main.js') }}"></script>

    <!-- Page JS -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script>
      (function() {
        var root = document.querySelector('.auth-parallax');
        if (!root) return;
        var layers = [].slice.call(root.querySelectorAll('.layer'));
        var clamp = function(v, min, max) { return Math.max(min, Math.min(max, v)); };
        var size = { w: window.innerWidth || 0, h: window.innerHeight || 0 };
        var center = { x: size.w / 2, y: size.h / 2 };
        var mouse = { x: 0, y: 0 };
        var target = { x: 0, y: 0 };
        var t = 0, rafId = null;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function onMove(e) {
          target.x = (e.clientX || center.x) - center.x;
          target.y = (e.clientY || center.y) - center.y;
        }

        function onResize() {
          size.w = window.innerWidth || 0;
          size.h = window.innerHeight || 0;
          center.x = size.w / 2; center.y = size.h / 2;
        }

        function loop() {
          rafId = requestAnimationFrame(loop);
          // ease mouse toward target for smoother parallax
          mouse.x += (target.x - mouse.x) * 0.08;
          mouse.y += (target.y - mouse.y) * 0.08;
          t += 0.008;
          layers.forEach(function(el) {
            var depth = parseFloat(el.getAttribute('data-depth') || '0.05');
            var speed = parseFloat(el.getAttribute('data-speed') || '0.4');
            var ampx = parseFloat(el.getAttribute('data-ampx') || '18');
            var ampy = parseFloat(el.getAttribute('data-ampy') || '10');
            var autoX = reduceMotion ? 0 : Math.sin(t * speed) * ampx;
            var autoY = reduceMotion ? 0 : Math.cos(t * (speed * 0.85)) * ampy;
            var tx = clamp((-mouse.x * depth) + autoX, -80, 80);
            var ty = clamp((-mouse.y * depth) + autoY, -60, 60);
            el.style.transform = 'translate3d(' + tx.toFixed(2) + 'px,' + ty.toFixed(2) + 'px,0)';
          });
        }

        window.addEventListener('mousemove', onMove, { passive: true });
        window.addEventListener('resize', onResize);
        // Initial center and start loop
        onResize();
        onMove({ clientX: center.x, clientY: center.y });
        rafId = requestAnimationFrame(loop);
      })();
    </script>
    <script>
      // Card tilt + shine
      (function() {
        var card = document.getElementById('authCard');
        if (!card) return;
        var rafId = null;
        var state = { rx: 0, ry: 0, tx: 0, ty: 0, mx: 0, my: 0 };

        function handle(e) {
          var rect = card.getBoundingClientRect();
          var cx = rect.left + rect.width / 2;
          var cy = rect.top + rect.height / 2;
          var dx = (e.clientX - cx) / rect.width;
          var dy = (e.clientY - cy) / rect.height;
          state.ry = dx * 10; // rotateY
          state.rx = -dy * 10; // rotateX
          state.mx = e.clientX - rect.left;
          state.my = e.clientY - rect.top;
          schedule();
        }

        function reset() {
          state.rx = 0; state.ry = 0;
          schedule();
        }

        function schedule() {
          if (rafId) return;
          rafId = requestAnimationFrame(apply);
        }

        function apply() {
          rafId = null;
          card.style.transform = 'perspective(900px) rotateX(' + state.rx.toFixed(2) + 'deg) rotateY(' + state.ry.toFixed(2) + 'deg)';
          card.style.setProperty('--mx', state.mx + 'px');
          card.style.setProperty('--my', state.my + 'px');
        }

        card.addEventListener('mousemove', handle, { passive: true });
        card.addEventListener('mouseleave', reset);
      })();
    </script>
    <script>
      // Logo hover motion (both card logo and floating logo)
      (function() {
        var logos = [].slice.call(document.querySelectorAll('.logo-interactive'));
        if (!logos.length) return;
        logos.forEach(function(img) {
          var rafId = null;
          var state = { tx: 0, ty: 0, rx: 0, ry: 0, sc: 1 };
          function onMove(e) {
            var r = img.getBoundingClientRect();
            var cx = r.left + r.width / 2;
            var cy = r.top + r.height / 2;
            var dx = (e.clientX - cx) / r.width;
            var dy = (e.clientY - cy) / r.height;
            state.tx = dx * 10; // translate X
            state.ty = dy * 10; // translate Y
            state.ry = dx * 8;  // rotate Y
            state.rx = -dy * 8; // rotate X
            state.sc = 1.04;
            schedule();
          }
          function onEnter() { img.classList.add('logo-hovering'); }
          function onLeave() { state.tx = state.ty = state.rx = state.ry = 0; state.sc = 1; img.classList.remove('logo-hovering'); schedule(); }
          function schedule() { if (!rafId) rafId = requestAnimationFrame(apply); }
          function apply() {
            rafId = null;
            img.style.transform = 'perspective(600px) translate(' + state.tx.toFixed(1) + 'px,' + state.ty.toFixed(1) + 'px) rotateX(' + state.rx.toFixed(1) + 'deg) rotateY(' + state.ry.toFixed(1) + 'deg) scale(' + state.sc.toFixed(2) + ')';
          }
          img.addEventListener('mousemove', onMove, { passive: true });
          img.addEventListener('mouseenter', onEnter);
          img.addEventListener('mouseleave', onLeave);
        });
      })();
    </script>
  </body>
</html>
