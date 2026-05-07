@extends('layouts.app')

@section('content')
  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Smart topic finder</span>
    <h1>Topic search</h1>
    <p class="gible-lead">Search once to get a quick Wikipedia explanation, helpful YouTube videos, and next-topic suggestions based on your learning activity.</p>
  </header>

  <div class="gible-search-shell gible-reveal gible-reveal-delay-1 mb-4">
    <div class="input-group input-group-lg">
      <span class="input-group-text border-0 bg-transparent text-secondary"><i class="bi bi-search"></i></span>
      <input id="topicInput" class="form-control form-control-lg border-0 shadow-none" maxlength="300" autocomplete="off" autocapitalize="sentences" enterkeyhint="search" aria-label="Topic to search" placeholder="Try “binary search tree”, “relational database”, …" />
      <button id="searchBtn" class="btn btn-primary m-1" type="button" aria-label="Run topic search"><i class="bi bi-lightning-charge-fill me-1" aria-hidden="true"></i>Search</button>
    </div>
  </div>

  <div id="alertBox" class="mb-4"></div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card gible-card shadow-sm border-0 h-100 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-book-half text-info"></i> Wikipedia summary
        </div>
        <div class="card-body" id="wikiBox">
          <p class="text-secondary mb-0"><i class="bi bi-arrow-up-circle me-1 opacity-50"></i>Results appear here after you search.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card gible-card shadow-sm border-0 h-100 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-youtube text-danger"></i> Educational videos
        </div>
        <div class="card-body" id="ytBox">
          <p class="text-secondary mb-0"><i class="bi bi-play-btn me-1 opacity-50"></i>YouTube results load here.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="card gible-card shadow-sm border-0 mt-4 gible-reveal gible-reveal-delay-3">
    <div class="card-header bg-white d-flex align-items-center gap-2">
      <i class="bi bi-stars gible-icon-violet"></i> Recommended next topics
    </div>
    <div class="card-body" id="recBox">
      <p class="text-secondary mb-0">Suggestions are based on your recent activity, your current topic, and related Wikipedia pages.</p>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    window.__lastTopic = '';
    window.__lastExtract = '';
    const alertBox = document.getElementById('alertBox');
    const wikiBox = document.getElementById('wikiBox');
    const ytBox = document.getElementById('ytBox');
    const recBox = document.getElementById('recBox');
    function escapeHtml(s) {
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }
    function safeHttpsUrl(u) {
      return typeof u === 'string' && /^https:\/\//i.test(u) ? u : '';
    }
    function showAlert(type, msg) {
      alertBox.innerHTML = msg ? '<div class="alert alert-' + type + '">' + escapeHtml(msg) + '</div>' : '';
    }
    let searchBusy = false;
    async function runTopicSearch() {
      if (searchBusy) return;
      const btn = document.getElementById('searchBtn');
      const topic = document.getElementById('topicInput').value.trim();
      if (!topic) return showAlert('warning', 'Enter a topic.');
      searchBusy = true;
      btn.disabled = true;
      try {
        showAlert('', '');
        wikiBox.innerHTML = '<p class="text-secondary mb-0"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Fetching Wikipedia…</p>';
        ytBox.innerHTML = '<p class="text-secondary mb-0"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Fetching YouTube…</p>';
        const res = await fetch('/api/search?topic=' + encodeURIComponent(topic), { headers: { Accept: 'application/json' } });
        const data = await res.json();
        window.__lastTopic = data.topic || topic;
        window.__lastExtract = data.wikipedia && data.wikipedia.extract ? data.wikipedia.extract : '';
        try {
          sessionStorage.setItem('gible_lastTopic', window.__lastTopic);
          sessionStorage.setItem('gible_lastExtract', window.__lastExtract);
        } catch (e) {}
        if (data.errors && data.errors.length) {
          showAlert('warning', data.errors.join(' '));
        }
        if (data.wikipedia && data.wikipedia.extract) {
          const thumb = safeHttpsUrl(data.wikipedia.thumbnail);
          const wikiRead = safeHttpsUrl(data.wikipedia.contentUrls);
          const img = thumb
            ? '<img src="' + escapeHtml(thumb) + '" class="img-fluid rounded-3 mb-3 shadow" alt=""/>'
            : '';
          wikiBox.innerHTML =
            '<div class="gible-wiki-body">' +
            img +
            '<h2 class="h5">' +
            escapeHtml(data.wikipedia.title || '') +
            '</h2><p class="mb-2 text-secondary small">' +
            escapeHtml(data.wikipedia.description || '') +
            '</p><p class="mb-3">' +
            escapeHtml(data.wikipedia.extract || '') +
            '</p>' +
            (wikiRead
              ? '<a class="btn btn-outline-secondary btn-sm" href="' + escapeHtml(wikiRead) + '" target="_blank" rel="noopener"><i class="bi bi-book-half me-1"></i>Read on Wikipedia</a>'
              : '') +
            '</div>';
        } else {
          wikiBox.innerHTML = '<p class="text-secondary mb-0">No summary available. Refine your topic or check spelling.</p>';
        }
        const vids = data.youtube && data.youtube.videos ? data.youtube.videos : [];
        if (!vids.length) {
          var ytFallback =
            'No educational videos matched this topic. Your search still works—the summary panel may still have useful context.';
          var ytConfigured =
            data.youtube && typeof data.youtube.error === 'string' && data.youtube.error.length > 0 && data.youtube.error.length < 200;
          ytBox.innerHTML =
            '<p class="text-secondary mb-0">' + escapeHtml(ytConfigured ? data.youtube.error : ytFallback) + '</p>';
        } else {
          ytBox.innerHTML = vids
            .map(function (v) {
              var tid = v && v.id ? String(v.id).replace(/[^A-Za-z0-9_\-]/g, '') : '';
              var tn = safeHttpsUrl(v && v.thumbnail);
              var poster =
                '<div class="rounded-3 me-3 gible-yt-thumb-placeholder shadow-sm" role="presentation"></div>';
              if (tid && tn) {
                poster =
                  '<img class="rounded-3 me-3 flex-shrink-0 shadow-sm gible-yt-thumb" src="' +
                  escapeHtml(tn) +
                  '" alt=""/>';
              }
              if (!tid) return '';
              return (
                '<div class="d-flex mb-3 p-2 rounded-3 gible-yt-row align-items-start">' +
                poster +
                '<div class="min-w-0 gible-yt-meta"><div class="fw-semibold gible-yt-title">' +
                escapeHtml(v.title) +
                '</div><div class="small text-secondary gible-yt-channel">' +
                escapeHtml(v.channelTitle) +
                '</div><a class="btn btn-sm btn-outline-primary mt-2" target="_blank" rel="noopener" href="https://www.youtube.com/watch?v=' +
                tid +
                '"><i class="bi bi-play-fill me-1"></i>Watch</a></div></div>'
              );
            })
            .join('');
        }
        const recs = data.recommendations || [];
        recBox.innerHTML = recs.length
          ? '<ul class="mb-0 ps-3">' + recs.map(function (r) { return '<li class="mb-1">' + escapeHtml(r) + '</li>'; }).join('') + '</ul>'
          : '<p class="text-secondary mb-0">No recommendations.</p>';
      } catch (e) {
        showAlert('danger', 'Search could not be completed. Try again in a moment.');
        wikiBox.innerHTML = '<p class="text-secondary mb-0">Results could not be loaded.</p>';
        ytBox.innerHTML = '<p class="text-secondary mb-0"></p>';
        recBox.innerHTML = '<p class="text-secondary mb-0">No recommendations.</p>';
      } finally {
        searchBusy = false;
        btn.disabled = false;
      }
    }
    document.getElementById('searchBtn').addEventListener('click', runTopicSearch);
    document.getElementById('topicInput').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        runTopicSearch();
      }
    });
  </script>
@endpush
