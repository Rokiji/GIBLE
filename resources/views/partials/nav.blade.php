<nav class="navbar navbar-expand-lg navbar-dark gible-nav mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 py-2" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
      <img class="gible-brand-logo" src="{{ asset('images/gible-logo.svg') }}" alt="GIBLE logo" width="36" height="36" />
      <span class="gible-brand-text">
        <span class="gible-brand-title">GIBLE</span>
        <span class="gible-brand-tag d-none d-sm-block">Connect. Learn. Achieve.</span>
      </span>
    </a>
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      @guest
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Log in</a>
          </li>
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}"><i class="bi bi-person-plus me-1"></i>Register</a>
          </li>
        </ul>
      @else
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill me-1"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('search') ? 'active' : '' }}" href="{{ route('search') }}"><i class="bi bi-search me-1"></i>Topic Search</a>
          </li>
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('quiz') ? 'active' : '' }}" href="{{ route('quiz') }}"><i class="bi bi-patch-check me-1"></i>Quiz</a>
          </li>
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('flashcards') ? 'active' : '' }}" href="{{ route('flashcards') }}"><i class="bi bi-layers me-1"></i>Flashcards</a>
          </li>
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('planner') ? 'active' : '' }}" href="{{ route('planner') }}"><i class="bi bi-calendar-event me-1"></i>Planner</a>
          </li>
          <li class="nav-item">
            <a class="nav-link gible-nav-link px-3 {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle me-1"></i>Profile</a>
          </li>
        </ul>
        <div class="d-flex align-items-center gap-2 ms-lg-2 mt-3 mt-lg-0">
          <span class="gible-user-chip d-none d-md-inline-flex align-items-center gap-2 small">
            <i class="bi bi-person-circle text-info"></i>
            <span class="text-white-50">{{ auth()->user()->name }}</span>
          </span>
          <form method="post" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn gible-btn-logout btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Log out</button>
          </form>
        </div>
      @endguest
    </div>
  </div>
</nav>
