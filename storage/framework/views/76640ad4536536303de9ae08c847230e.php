<?php $__env->startSection('content'); ?>
  <?php
    $opsEmail = 'agullanaaaronjoshua@gmail.com';
    $isOpsViewer = strcasecmp((string) ($user->email ?? ''), $opsEmail) === 0;
    $demoTip = session('demo_tip_status');
  ?>

  <?php if($isOpsViewer && is_array($demoTip)): ?>
    <div class="alert alert-<?php echo e(($demoTip['ok'] ?? false) ? 'success' : 'warning'); ?> gible-reveal mb-3" role="alert">
      <i class="bi bi-chat-dots-fill me-2"></i><?php echo e($demoTip['detail'] ?? 'Tip request finished.'); ?>

    </div>
  <?php endif; ?>
  <?php if($isOpsViewer && session('demo_reminder_status')): ?>
    <div class="alert alert-info gible-reveal mb-3" role="alert">
      <i class="bi bi-bell-fill me-2"></i><?php echo e(session('demo_reminder_status')); ?>

    </div>
  <?php endif; ?>

  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Your workspace</span>
    <h1><?php echo e(session('just_registered') ? 'Welcome' : 'Welcome back'); ?>, <?php echo e($user->name); ?></h1>
    <p class="gible-lead">See your recent searches, quiz progress, reminders, and suggested topics in one place.</p>
  </header>

  <div class="gible-stat-grid gible-reveal gible-reveal-delay-1 mb-4">
    <div class="gible-stat">
      <div class="gible-stat-icon text-info"><i class="bi bi-journal-text"></i></div>
      <div class="gible-stat-value"><?php echo e($user->searches_count); ?></div>
      <div class="gible-stat-label">Searches saved</div>
    </div>
    <div class="gible-stat">
      <div class="gible-stat-icon text-warning"><i class="bi bi-alarm"></i></div>
      <div class="gible-stat-value"><?php echo e($user->reminders_count); ?></div>
      <div class="gible-stat-label">Reminders</div>
    </div>
    <div class="gible-stat">
      <div class="gible-stat-icon text-success"><i class="bi bi-trophy"></i></div>
      <div class="gible-stat-value"><?php echo e($user->quiz_results_count); ?></div>
      <div class="gible-stat-label">Quizzes taken</div>
    </div>
    <div class="gible-stat">
      <div class="gible-stat-icon gible-icon-violet"><i class="bi bi-layers-half"></i></div>
      <div class="gible-stat-value"><?php echo e($user->flash_decks_count); ?></div>
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
          <?php $__empty_1 = true; $__currentLoopData = $recentSearches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium"><?php echo e($s->topic); ?></span>
              <small class="text-secondary"><?php echo e($s->created_at->format('M j · H:i')); ?></small>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="list-group-item text-secondary">No searches yet. Try <a href="<?php echo e(route('search')); ?>">Topic Search</a>.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-calendar2-check text-warning"></i> Upcoming study sessions
        </div>
        <ul class="list-group list-group-flush">
          <?php $__empty_1 = true; $__currentLoopData = $reminders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium"><?php echo e($r->subject); ?></span>
              <small class="text-secondary"><?php echo e($r->remind_at->format('D, M j · g:i A')); ?></small>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="list-group-item text-secondary">No reminders. Use the <a href="<?php echo e(route('planner')); ?>">Study Planner</a>.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="card gible-card shadow-sm border-0 gible-reveal gible-reveal-delay-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-stars gible-icon-violet"></i> Recommended learning materials
        </div>
        <div class="card-body">
          <p class="text-secondary small mb-4">These picks are based on what you have been studying, plus related Wikipedia pages and YouTube videos.</p>
          <?php $__currentLoopData = $recommendedMaterials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="gible-rec-tile mb-3">
              <div class="gible-rec-title"><?php echo e($mat['topic']); ?></div>
              <?php if(! empty($mat['wikipedia']['extract'])): ?>
                <p class="small mb-3 gible-text-canvas"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($mat['wikipedia']['extract']), 280)); ?></p>
                <?php if(! empty($mat['wikipedia']['contentUrls'])): ?>
                  <a href="<?php echo e($mat['wikipedia']['contentUrls']); ?>" class="btn btn-outline-secondary btn-sm mb-2" target="_blank" rel="noopener"><i class="bi bi-book-half me-1"></i>Open article</a>
                <?php endif; ?>
              <?php else: ?>
                <p class="small text-secondary mb-2">Summary unavailable for this suggestion.</p>
              <?php endif; ?>
              <?php if(! empty($mat['youtube_video']['id'])): ?>
                <div class="d-flex align-items-center gap-3 flex-wrap mt-2">
                  <?php if(! empty($mat['youtube_video']['thumbnail'])): ?>
                    <img src="<?php echo e($mat['youtube_video']['thumbnail']); ?>" alt="" class="rounded-3 shadow" width="128" height="72" style="object-fit: cover" />
                  <?php endif; ?>
                  <div class="flex-grow-1 gible-minw-12">
                    <div class="small fw-semibold mb-1 gible-video-title"><?php echo e($mat['youtube_video']['title']); ?></div>
                    <a class="btn btn-sm btn-outline-primary" href="https://www.youtube.com/watch?v=<?php echo e($mat['youtube_video']['id']); ?>" target="_blank" rel="noopener"><i class="bi bi-play-circle me-1"></i>Watch</a>
                  </div>
                </div>
              <?php endif; ?>
              <?php $__currentLoopData = $mat['errors'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="small mt-2 gible-rec-hint"><i class="bi bi-exclamation-triangle me-1"></i><?php echo e($err); ?></div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php if(! empty($extraRecommendations)): ?>
            <p class="fw-semibold small mb-2 mt-4 gible-extra-topics-heading"><i class="bi bi-lightning-charge me-1 text-info"></i>More topics to explore</p>
            <ul class="mb-0 ps-3 gible-text-soft">
              <?php $__currentLoopData = $extraRecommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="mb-1"><?php echo e($t); ?></li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <?php if($latestTip): ?>
        <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-1">
          <div class="card-header bg-white d-flex align-items-center gap-2">
            <i class="bi bi-lightbulb text-info"></i> Latest learning tip
          </div>
          <div class="card-body">
            <p class="small mb-2"><?php echo e($latestTip->meta['tip'] ?? 'Tip unavailable.'); ?></p>
            <?php if(!empty($latestTip->meta['username'])): ?>
              <p class="small text-secondary mb-0">Also sent to Discord for <strong><?php echo e($latestTip->meta['username']); ?></strong>.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-graph-up-arrow text-success"></i> Quiz scores
        </div>
        <ul class="list-group list-group-flush">
          <?php $__empty_1 = true; $__currentLoopData = $quizResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="text-truncate me-2"><?php echo e($q->topic); ?></span>
              <span class="badge bg-primary"><?php echo e($q->score); ?>/<?php echo e($q->total); ?></span>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="list-group-item text-secondary">No quiz records yet. Start one on the Quiz page.</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-bar-chart-line text-info"></i> Progress insights
        </div>
        <div class="card-body">
          <div class="small text-secondary mb-2">Last 7 days activity</div>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-info-subtle text-info-emphasis">Searches: <?php echo e($activityTrend['searches']); ?></span>
            <span class="badge bg-success-subtle text-success-emphasis">Quizzes: <?php echo e($activityTrend['quizzes']); ?></span>
            <span class="badge bg-primary-subtle text-primary-emphasis">Decks: <?php echo e($activityTrend['decks']); ?></span>
          </div>
          <p class="mb-3"><span class="fw-semibold">Average quiz accuracy:</span> <?php echo e($avgQuizPercent); ?>%</p>
          <div class="small text-secondary mb-2">Topics to reinforce</div>
          <?php if(! empty($lowScoreTopics)): ?>
            <ul class="mb-0 ps-3">
              <?php $__currentLoopData = $lowScoreTopics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="mb-1"><?php echo e($row['topic']); ?> (<?php echo e($row['ratio']); ?>%)</li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <p class="small text-secondary mb-0">Take a few quizzes to see which topics need more practice.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php if($isOpsViewer): ?>
        <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-2">
          <div class="card-header bg-white d-flex align-items-center gap-2">
            <i class="bi bi-send-check text-warning"></i> Notification reliability
          </div>
          <div class="card-body">
            <p class="mb-2">Webhook deliveries (30d): <strong><?php echo e($webhookHealth['delivered']); ?></strong> / <?php echo e($webhookHealth['total']); ?></p>
            <p class="small text-secondary mb-0">Discord sends now include retries and delivery logging for auditability.</p>
          </div>
        </div>
      <?php endif; ?>

      <?php if($isOpsViewer): ?>
        <div class="card gible-card shadow-sm border-0 mb-4 gible-reveal gible-reveal-delay-3" id="demo-notifications">
          <div class="card-header bg-white d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-plugin text-info"></i> Demo · Discord without waiting for cron
          </div>
          <div class="card-body">
            <p class="small text-secondary mb-3">
              For defenses or demos: trigger webhook notifications immediately. Background automation still uses
              <code class="small">php artisan schedule:work</code> or server cron (<code class="small">schedule:run</code>).
            </p>
            <p class="small mb-3"><span class="fw-semibold">Due reminders pending:</span> <span class="badge bg-warning text-dark"><?php echo e($dueRemindersCount); ?></span></p>
            <div class="d-flex flex-column gap-2">
              <form method="post" action="<?php echo e(route('dashboard.demo.tip-discord')); ?>" class="d-grid">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-primary btn-sm py-2">
                  <i class="bi bi-lightbulb me-2"></i>Send a learning tip to Discord now
                </button>
              </form>
              <form method="post" action="<?php echo e(route('dashboard.demo.run-reminders')); ?>" class="d-grid">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-warning btn-sm py-2">
                  <i class="bi bi-bell me-2"></i>Process due study reminders now
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="card gible-card shadow-sm border-0 gible-reveal gible-reveal-delay-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-collection gible-icon-violet"></i> Saved flashcard decks
        </div>
        <ul class="list-group list-group-flush">
          <?php $__empty_1 = true; $__currentLoopData = $decks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium"><?php echo e($d->topic); ?></span>
              <small class="text-secondary"><?php echo e($d->created_at->format('M j, Y')); ?></small>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="list-group-item text-secondary">Generate decks from <a href="<?php echo e(route('flashcards')); ?>">Flashcards</a>.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\agull\OneDrive\Documents\Visual Studio\GIBLE\resources\views/dashboard.blade.php ENDPATH**/ ?>