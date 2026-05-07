@extends('layouts.app')

@section('content')
  <div class="gible-empty-state gible-reveal">
    <p class="gible-kicker mb-3">Lost in the stack</p>
    <div class="display-1 mb-3">404</div>
    <h1 class="h4 fw-semibold mb-3">This page does not exist</h1>
    <p class="text-secondary col-md-6 mx-auto mb-4">The URL may be mistyped, or the resource was moved. Head back to your dashboard and keep learning.</p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
      <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primary btn-lg"><i class="bi bi-house-door me-2"></i>@auth Dashboard @else Sign in @endauth</a>
      @auth
        <a href="{{ route('search') }}" class="btn btn-outline-secondary btn-lg"><i class="bi bi-search me-2"></i>Topic search</a>
      @endauth
    </div>
  </div>
@endsection
