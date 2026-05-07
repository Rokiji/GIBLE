<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <meta name="description" content="<?php echo e($metaDescription ?? 'GIBLE — Wikipedia, quizzes, flashcards, and study planning in one calm workspace.'); ?>" />
    <meta name="theme-color" content="#070b14" />
    <?php
      $documentTitle = trim((string) ($title ?? ''));
      if ($documentTitle === '' || strcasecmp($documentTitle, 'GIBLE') === 0) {
          $fullTitle = 'GIBLE';
      } else {
          $fullTitle = $documentTitle.' · GIBLE';
      }
    ?>
    <title><?php echo e($fullTitle); ?></title>
    <link rel="icon" type="image/svg+xml" href="/images/gible-logo.svg" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/app.css" />
  </head>
  <body class="gible-body">
    <div class="gible-bg" aria-hidden="true"></div>
    <div id="gibleLoadingOverlay" class="gible-loading-overlay" aria-live="polite">
      <div class="gible-loading-card">
        <img src="/images/gible-logo.svg" alt="" class="gible-loading-logo" width="84" height="84" decoding="async" />
        <p class="gible-loading-title mb-2">GIBLE</p>
        <p class="gible-loading-text mb-3">Preparing your learning workspace...</p>
        <div class="gible-loading-dots" aria-hidden="true">
          <span></span><span></span><span></span>
        </div>
      </div>
    </div>
    <a href="#gible-main" class="gible-skip-link">Skip to main content</a>
    <?php echo $__env->make('partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main id="gible-main" class="container pb-4 pb-md-5 gible-main" tabindex="-1"><?php echo $__env->yieldContent('content'); ?></main>
    <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script>
      document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-password-toggle-for]');
        if (!btn || btn.disabled) return;
        e.preventDefault();
        var id = btn.getAttribute('data-password-toggle-for');
        var input = id ? document.getElementById(id) : null;
        if (!input || (input.type !== 'password' && input.type !== 'text')) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        var icon = btn.querySelector('i');
        if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
      });
    </script>
    <script>
      (function () {
        const hideOverlay = () => {
          const overlay = document.getElementById('gibleLoadingOverlay');
          if (!overlay) return;
          overlay.classList.add('is-hidden');
          window.setTimeout(() => overlay.remove(), 460);
        };
        if (document.readyState === 'complete') {
          hideOverlay();
        } else {
          window.addEventListener('load', hideOverlay, { once: true });
        }
      })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
<?php /**PATH C:\Users\agull\OneDrive\Documents\Visual Studio\GIBLE\resources\views/layouts/app.blade.php ENDPATH**/ ?>