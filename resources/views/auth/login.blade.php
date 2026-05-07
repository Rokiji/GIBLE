@extends('layouts.app')

@section('content')
  <div class="row justify-content-center align-items-center py-lg-4">
    <div class="col-md-6 col-lg-5">
      <div class="text-center mb-4 gible-reveal">
        <span class="gible-kicker">Welcome back</span>
        <h1 class="h2 fw-bold mb-2 gible-auth-title">Sign in to GIBLE</h1>
        <p class="text-secondary mb-0">Your integrated study cockpit.</p>
      </div>
      <div class="card gible-auth-card shadow-sm border-0 gible-reveal gible-reveal-delay-1">
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif
          <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="email" />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="loginPassword">Password</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-key"></i></span>
                <input id="loginPassword" type="password" name="password" class="form-control border-secondary-subtle" required autocomplete="current-password" />
                <button type="button" class="btn btn-outline-secondary gible-password-toggle px-3" data-password-toggle-for="loginPassword" aria-label="Show password">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="mb-4 form-check">
              <input type="checkbox" name="remember" class="form-check-input" id="remember" />
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-2"></i>Log in</button>
          </form>
          <p class="text-secondary small text-center mt-4 mb-0">No account? <a href="{{ route('register') }}">Create one</a></p>
        </div>
      </div>
    </div>
  </div>
@endsection
