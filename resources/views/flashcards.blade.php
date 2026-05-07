@extends('layouts.app')

@section('content')
  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Active recall</span>
    <h1>Flashcards</h1>
    <p class="gible-lead">Turn dense summaries into flippable prompts—up to twenty cards per run from your last search, or reopen a saved deck.</p>
  </header>

  <div class="d-flex flex-wrap gap-2 mb-4 gible-reveal gible-reveal-delay-1">
    <button type="button" class="btn btn-primary" id="genCards"><i class="bi bi-plus-lg me-1"></i>Generate from last search</button>
    <button type="button" class="btn btn-outline-secondary" id="loadDeckBtn"><i class="bi bi-bookmark-star me-1"></i>Study latest deck</button>
  </div>

  <div id="fcAlert"></div>

  <div id="flipArea" class="d-none gible-reveal gible-reveal-delay-2">
    <div class="card gible-card gible-flip-card border-0 shadow-sm mb-4">
      <div class="card-body text-center py-5 px-4 gible-flip-surface" id="flipCard" tabindex="0" role="button" aria-label="Flashcard — press Space or tap to flip">
        <div class="text-uppercase small fw-semibold mb-2 gible-flip-label" id="sideLabel">Front</div>
        <div class="fs-5 px-md-5 gible-flip-text" id="cardText"></div>
        <div class="small mt-4 gible-flip-hint"><i class="bi bi-arrow-repeat me-1"></i>Tap or press Space to flip</div>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <button type="button" class="btn btn-outline-primary" id="prevC"><i class="bi bi-chevron-left"></i> Prev</button>
      <span id="idxLabel" class="small fw-semibold text-secondary"></span>
      <button type="button" class="btn btn-outline-primary" id="nextC">Next <i class="bi bi-chevron-right"></i></button>
    </div>
  </div>

  <div class="card gible-card shadow-sm border-0 mt-4">
    <div class="card-header bg-white d-flex align-items-center gap-2">
      <i class="bi bi-collection gible-icon-violet"></i> Saved decks
    </div>
    <ul class="list-group list-group-flush">
      @forelse ($decks as $d)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span class="fw-medium">{{ $d->topic }}</span>
          <button type="button" class="btn btn-sm btn-outline-primary deck-open" data-deck-id="{{ $d->id }}"><i class="bi bi-box-arrow-up-right me-1"></i>Open</button>
        </li>
      @empty
        <li class="list-group-item text-secondary">No decks yet.</li>
      @endforelse
    </ul>
  </div>
@endsection

@push('scripts')
  <script>
    const searchUrl = @json(route('search'));
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let cards = [];
    let idx = 0;
    let showFront = true;
    function renderCard() {
      if (!cards.length) return;
      const c = cards[idx];
      document.getElementById('sideLabel').textContent = showFront ? 'Front' : 'Back';
      document.getElementById('cardText').textContent = showFront ? c.front : c.back;
      document.getElementById('idxLabel').textContent = idx + 1 + ' / ' + cards.length;
    }
    document.getElementById('flipCard').addEventListener('click', function () {
      showFront = !showFront;
      renderCard();
    });
    document.getElementById('flipCard').addEventListener('keydown', function (e) {
      if (e.key === ' ' || e.key === 'Enter') {
        e.preventDefault();
        showFront = !showFront;
        renderCard();
      }
    });
    document.getElementById('prevC').addEventListener('click', function () {
      idx = (idx - 1 + cards.length) % cards.length;
      showFront = true;
      renderCard();
    });
    document.getElementById('nextC').addEventListener('click', function () {
      idx = (idx + 1) % cards.length;
      showFront = true;
      renderCard();
    });
    document.getElementById('genCards').addEventListener('click', async function () {
      document.getElementById('fcAlert').innerHTML = '';
      let extract = '';
      let topic = 'Topic';
      try {
        extract = sessionStorage.getItem('gible_lastExtract') || '';
        topic = sessionStorage.getItem('gible_lastTopic') || 'Topic';
      } catch (e) {}
      if (!extract) {
        document.getElementById('fcAlert').innerHTML =
          '<div class="alert alert-warning">Run <a href="' + searchUrl + '">Topic Search</a> first.</div>';
        return;
      }
      const res = await fetch('/api/flashcards/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ topic: topic, extract: extract }),
      });
      const data = await res.json();
      if (!res.ok) {
        document.getElementById('fcAlert').innerHTML = '<div class="alert alert-danger">' + (data.error || 'Error') + '</div>';
        return;
      }
      cards = data.cards;
      idx = 0;
      showFront = true;
      document.getElementById('flipArea').classList.remove('d-none');
      renderCard();
    });
    document.getElementById('loadDeckBtn').addEventListener('click', async function () {
      document.getElementById('fcAlert').innerHTML = '';
      const btn = document.querySelector('.deck-open');
      if (!btn) {
        document.getElementById('fcAlert').innerHTML = '<div class="alert alert-warning">No saved deck yet.</div>';
        return;
      }
      const id = btn.getAttribute('data-deck-id');
      const res = await fetch('/api/flashcards/deck/' + id, { headers: { Accept: 'application/json' } });
      const data = await res.json();
      if (!res.ok) {
        document.getElementById('fcAlert').innerHTML = '<div class="alert alert-danger">' + (data.error || 'Error') + '</div>';
        return;
      }
      cards = data.cards;
      idx = 0;
      showFront = true;
      document.getElementById('flipArea').classList.remove('d-none');
      renderCard();
    });
    document.querySelectorAll('.deck-open').forEach(function (b) {
      b.addEventListener('click', async function () {
        document.getElementById('fcAlert').innerHTML = '';
        const id = b.getAttribute('data-deck-id');
        const res = await fetch('/api/flashcards/deck/' + id, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!res.ok) return;
        cards = data.cards;
        idx = 0;
        showFront = true;
        document.getElementById('flipArea').classList.remove('d-none');
        renderCard();
      });
    });
  </script>
@endpush
