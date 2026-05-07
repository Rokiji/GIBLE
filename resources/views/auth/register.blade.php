@extends('layouts.app')

@section('content')
  <div class="row justify-content-center py-lg-3">
    <div class="col-md-7 col-lg-6">
      <div class="text-center mb-4 gible-reveal">
        <span class="gible-kicker">Join GIBLE</span>
        <h1 class="h2 fw-bold mb-2 gible-auth-title">Create your account</h1>
        <p class="text-secondary mb-0">Search, quiz, plan, and get recommendations in one place.</p>
      </div>
      <div class="card gible-auth-card shadow-sm border-0 gible-reveal gible-reveal-delay-1">
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <form method="post" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus autocomplete="name" />
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email" />
            </div>
            <div class="mb-3">
              <label class="form-label" for="registerPassword">Password</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-key"></i></span>
                <input id="registerPassword" type="password" name="password" class="form-control border-secondary-subtle" required minlength="8" autocomplete="new-password" />
                <button type="button" class="btn btn-outline-secondary gible-password-toggle px-3" data-password-toggle-for="registerPassword" aria-label="Show password">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label" for="registerPasswordConfirm">Confirm password</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-key-fill"></i></span>
                <input id="registerPasswordConfirm" type="password" name="password_confirmation" class="form-control border-secondary-subtle" required minlength="8" autocomplete="new-password" />
                <button type="button" class="btn btn-outline-secondary gible-password-toggle px-3" data-password-toggle-for="registerPasswordConfirm" aria-label="Show password">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-person-plus me-2"></i>Create account</button>
          </form>
          <p class="text-secondary small text-center mt-4 mb-0">Already registered? <a href="{{ route('login') }}">Log in</a></p>
        </div>
      </div>
    </div>
  </div>
@endsection
