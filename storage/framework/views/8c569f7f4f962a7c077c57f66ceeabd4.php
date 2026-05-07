<?php $__env->startSection('content'); ?>
  <div class="gible-empty-state gible-reveal">
    <p class="gible-kicker mb-3">Lost in the stack</p>
    <div class="display-1 mb-3">404</div>
    <h1 class="h4 fw-semibold mb-3">This page does not exist</h1>
    <p class="text-secondary col-md-6 mx-auto mb-4">The URL may be mistyped, or the resource was moved. Head back to your dashboard and keep learning.</p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
      <a href="<?php echo e(auth()->check() ? route('dashboard') : route('login')); ?>" class="btn btn-primary btn-lg"><i class="bi bi-house-door me-2"></i><?php if(auth()->guard()->check()): ?> Dashboard <?php else: ?> Sign in <?php endif; ?></a>
      <?php if(auth()->guard()->check()): ?>
        <a href="<?php echo e(route('search')); ?>" class="btn btn-outline-secondary btn-lg"><i class="bi bi-search me-2"></i>Topic search</a>
      <?php endif; ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\agull\OneDrive\Documents\Visual Studio\GIBLE\resources\views/errors/404.blade.php ENDPATH**/ ?>