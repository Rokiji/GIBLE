@extends('layouts.app')

@section('content')
  @php
    $opsEmail = 'agullanaaaronjoshua@gmail.com';
    $isOpsViewer = strcasecmp((string) ($user->email ?? ''), $opsEmail) === 0;
    $demoTip = session('demo_tip_status');
  @endphp

  @if ($isOpsViewer && is_array($demoTip))
    <div class="alert alert-{{ ($demoTip['ok'] ?? false) ? 'success' : 'warning' }} gible-reveal mb-3" role="alert">
      <i class="bi bi-chat-dots-fill me-2"></i>{{ $demoTip['detail'] ?? 'Tip request finished.' }}
    </div>
  @endif
  @if ($isOpsViewer && session('demo_reminder_status'))
    <div class="alert alert-info gible-reveal mb-3" role="alert">
      <i class="bi bi-bell-fill me-2"></i>{{ session('demo_reminder_status') }}
    </div>
  @endif

  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Your workspace</span>
    <h1>{{ session('just_registered') ? 'Welcome' : 'Welcome back' }}, {{ $user->name }}</h1>
    <p class="gible-lead">See your recent searches, quiz progress, reminders, and suggested topics in one place.</p>
  </header>

  <div class="gible-stat-grid gible-reveal gible-reveal-delay-1 mb-4">
    <div class="gible-stat">
      <div class="gible-stat-icon text-info"><i class="bi bi-journal-text"></i></div>
      <div class="gible-stat-value">{{ $user->searches_count }}</div>
      <div class="gible-stat-label">Searches saved</div>
    </div>
    <div class="gible-stat">
      <div class="gible-stat-icon text-warning"><i class="bi bi-alarm"></i></div>
      <div class="gible-stat-value">{{ $user->reminders_count }}</div>
      <div class="gible-stat-label">Reminders</div>
    </div>
    <div class="gible-stat">
      <div class="gible-stat-icon text-success"><i class="bi bi-trophy"></i></div>
      <div class="gible-stat-value">{{ $user->quiz_results_count }}</div>
      <div class="gible-stat-label">Quizzes taken</div>
    </div>
    <div class="gible-stat">
      <div class="gible-stat-icon gible-icon-violet"><i class="bi bi-layers-half"></i></div>
      <div class="gible-stat-value">{{ $user->flash_decks_count }}</div>
      <div class="gible-stat-label">Flashcard decks</div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-clock-history text-info"></i> Recently searched topics
        </div>
        <ul class="list-group list-group-flush">
          @forelse ($recentSearches as $s)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium">{{ $s->topic }}</span>
              <small class="text-secondary">{{ $s->created_at->format('M j · H:i') }}</small>
            </li>
          @empty
            <li class="list-group-item text-secondary">No searches yet. Try <a href="{{ route('search') }}">Topic Search</a>.</li>
          @endforelse
        </ul>
      </div>

      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-calendar2-check text-warning"></i> Upcoming study sessions
        </div>
        <ul class="list-group list-group-flush">
          @forelse ($reminders as $r)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium">{{ $r->subject }}</span>
              <small class="text-secondary">{{ $r->remind_at->format('D, M j · g:i A') }}</small>
            </li>
          @empty
            <li class="list-group-item text-secondary">No reminders. Use the <a href="{{ route('planner') }}">Study Planner</a>.</li>
          @endforelse
        </ul>
      </div>

      <div class="card gible-card shadow-sm border-0 gible-reveal gible-reveal-delay-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-stars gible-icon-violet"></i> Recommended learning materials
        </div>
        <div class="card-body">
          <p class="text-secondary small mb-4">These picks are based on what you have been studying, plus related Wikipedia pages and YouTube videos.</p>
          @foreach ($recommendedMaterials as $mat)
            <div class="gible-rec-tile mb-3">
              <div class="gible-rec-title">{{ $mat['topic'] }}</div>
              @if (! empty($mat['wikipedia']['extract']))
                <p class="small mb-3 gible-text-canvas">{{ \Illuminate\Support\Str::limit(strip_tags($mat['wikipedia']['extract']), 280) }}</p>
                @if (! empty($mat['wikipedia']['contentUrls']))
                  <a href="{{ $mat['wikipedia']['contentUrls'] }}" class="btn btn-outline-secondary btn-sm mb-2" target="_blank" rel="noopener"><i class="bi bi-book-half me-1"></i>Open article</a>
                @endif
              @else
                <p class="small text-secondary mb-2">Summary unavailable for this suggestion.</p>
              @endif
              @if (! empty($mat['youtube_video']['id']))
                <div class="d-flex align-items-center gap-3 flex-wrap mt-2">
                  @if (! empty($mat['youtube_video']['thumbnail']))
                    <img src="{{ $mat['youtube_video']['thumbnail'] }}" alt="" class="rounded-3 shadow" width="128" height="72" style="object-fit: cover" />
                  @endif
                  <div class="flex-grow-1 gible-minw-12">
                    <div class="small fw-semibold mb-1 gible-video-title">{{ $mat['youtube_video']['title'] }}</div>
                    <a class="btn btn-sm btn-outline-primary" href="https://www.youtube.com/watch?v={{ $mat['youtube_video']['id'] }}" target="_blank" rel="noopener"><i class="bi bi-play-circle me-1"></i>Watch</a>
                  </div>
                </div>
              @endif
              @foreach ($mat['errors'] ?? [] as $err)
                <div class="small mt-2 gible-rec-hint"><i class="bi bi-exclamation-triangle me-1"></i>{{ $err }}</div>
              @endforeach
            </div>
          @endforeach
          @if (! empty($extraRecommendations))
            <p class="fw-semibold small mb-2 mt-4 gible-extra-topics-heading"><i class="bi bi-lightning-charge me-1 text-info"></i>More topics to explore</p>
            <ul class="mb-0 ps-3 gible-text-soft">
              @foreach ($extraRecommendations as $t)
                <li class="mb-1">{{ $t }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      @if ($latestTip)
        <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-1">
          <div class="card-header bg-white d-flex align-items-center gap-2">
            <i class="bi bi-lightbulb text-info"></i> Latest learning tip
          </div>
          <div class="card-body">
            <p class="small mb-2">{{ $latestTip->meta['tip'] ?? 'Tip unavailable.' }}</p>
            @if (!empty($latestTip->meta['username']))
              <p class="small text-secondary mb-0">Also sent to Discord for <strong>{{ $latestTip->meta['username'] }}</strong>.</p>
            @endif
          </div>
        </div>
      @endif
      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-graph-up-arrow text-success"></i> Quiz scores
        </div>
        <ul class="list-group list-group-flush">
          @forelse ($quizResults as $q)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="text-truncate me-2">{{ $q->topic }}</span>
              <span class="badge bg-primary">{{ $q->score }}/{{ $q->total }}</span>
            </li>
          @empty
            <li class="list-group-item text-secondary">No quiz records yet. Start one on the Quiz page.</li>
          @endforelse
        </ul>
      </div>

      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-bar-chart-line text-info"></i> Progress insights
        </div>
        <div class="card-body">
          <div class="small text-secondary mb-2">Last 7 days activity</div>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-info-subtle text-info-emphasis">Searches: {{ $activityTrend['searches'] }}</span>
            <span class="badge bg-success-subtle text-success-emphasis">Quizzes: {{ $activityTrend['quizzes'] }}</span>
            <span class="badge bg-primary-subtle text-primary-emphasis">Decks: {{ $activityTrend['decks'] }}</span>
          </div>
          <p class="mb-3"><span class="fw-semibold">Average quiz accuracy:</span> {{ $avgQuizPercent }}%</p>
          <div class="small text-secondary mb-2">Topics to reinforce</div>
          @if (! empty($lowScoreTopics))
            <ul class="mb-0 ps-3">
              @foreach ($lowScoreTopics as $row)
                <li class="mb-1">{{ $row['topic'] }} ({{ $row['ratio'] }}%)</li>
              @endforeach
            </ul>
          @else
            <p class="small text-secondary mb-0">Take a few quizzes to see which topics need more practice.</p>
          @endif
        </div>
      </div>

      @if ($isOpsViewer)
        <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
          <div class="card-header bg-white d-flex align-items-center gap-2">
            <i class="bi bi-send-check text-warning"></i> Notification reliability
          </div>
          <div class="card-body">
            <p class="mb-2">Webhook deliveries (30d): <strong>{{ $webhookHealth['delivered'] }}</strong> / {{ $webhookHealth['total'] }}</p>
            <p class="small text-secondary mb-0">Discord sends now include retries and delivery logging for auditability.</p>
          </div>
        </div>
      @endif

      @if ($isOpsViewer)
        <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-3" id="demo-notifications">
          <div class="card-header bg-white d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-plugin text-info"></i> Demo · Discord without waiting for cron
          </div>
          <div class="card-body">
            <p class="small text-secondary mb-3">
              For defenses or demos: trigger webhook notifications immediately. Background automation still uses
              <code class="small">php artisan schedule:work</code> or server cron (<code class="small">schedule:run</code>).
            </p>
            <p class="small mb-3"><span class="fw-semibold">Due reminders pending:</span> <span class="badge bg-warning text-dark">{{ $dueRemindersCount }}</span></p>
            <div class="d-flex flex-column gap-2">
              <form method="post" action="{{ route('dashboard.demo.tip-discord') }}" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm py-2">
                  <i class="bi bi-lightbulb me-2"></i>Send a learning tip to Discord now
                </button>
              </form>
              <form method="post" action="{{ route('dashboard.demo.run-reminders') }}" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm py-2">
                  <i class="bi bi-bell me-2"></i>Process due study reminders now
                </button>
              </form>
            </div>
          </div>
        </div>
      @endif

      <div class="card gible-card shadow-sm border-0 gible-reveal gible-reveal-delay-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-collection gible-icon-violet"></i> Saved flashcard decks
        </div>
        <ul class="list-group list-group-flush">
          @forelse ($decks as $d)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium">{{ $d->topic }}</span>
              <small class="text-secondary">{{ $d->created_at->format('M j, Y') }}</small>
            </li>
          @empty
            <li class="list-group-item text-secondary">Generate decks from <a href="{{ route('flashcards') }}">Flashcards</a>.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
@endsection
