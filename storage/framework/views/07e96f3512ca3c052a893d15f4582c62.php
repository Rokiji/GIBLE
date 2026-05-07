<?php $__env->startSection('content'); ?>
  <?php
    $opsEmail = 'agullanaaaronjoshua@gmail.com';
    $isOpsViewer = strcasecmp((string) (auth()->user()->email ?? ''), $opsEmail) === 0;
  ?>

  <?php if(session('status')): ?>
    <div class="alert alert-info gible-reveal mb-3" role="alert">
      <i class="bi bi-info-circle-fill me-2"></i><?php echo e(session('status')); ?>

    </div>
  <?php endif; ?>

  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Stay on schedule</span>
    <h1>Study planner</h1>
    <p class="gible-lead">Add a subject and time. When it is due, GIBLE sends you a reminder in Discord.</p>
  </header>

  <?php if($isOpsViewer): ?>
    <div class="alert alert-info border-0 shadow-sm gible-reveal mb-4" role="status">
      <div class="d-flex gap-3 align-items-start">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
        <div class="small mb-0">
          <strong>Automation:</strong> with XAMPP, keep MySQL running and start
          <code>php artisan schedule:work</code> in another terminal so due reminders dispatch every minute-or use the Dashboard
          <a href="<?php echo e(route('dashboard')); ?>#demo-notifications">demo buttons</a> to fire Discord immediately during presentations.
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card gible-card shadow-sm border-0 gible-reveal gible-reveal-delay-1">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-plus-circle text-success"></i> New reminder
        </div>
        <div class="card-body p-4">
          <form method="post" action="<?php echo e(route('planner')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <input type="text" name="subject" class="form-control" placeholder="Data structures" required />
            </div>
            <div class="mb-4">
              <label class="form-label">Remind at</label>
              <input type="datetime-local" name="remind_at" class="form-control" required />
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-bell me-2"></i>Save reminder</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card gible-card shadow-sm border-0 gible-reveal gible-reveal-delay-2">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-list-ul text-warning"></i> Your reminders
        </div>
        <ul class="list-group list-group-flush">
          <?php $__empty_1 = true; $__currentLoopData = $reminders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span class="fw-medium"><?php echo e($r->subject); ?></span>
              <span class="text-secondary small"><?php echo e($r->remind_at->format('D, M j · g:i A')); ?></span>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="list-group-item text-secondary">No reminders scheduled.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\agull\OneDrive\Documents\Visual Studio\GIBLE\resources\views/planner.blade.php ENDPATH**/ ?>