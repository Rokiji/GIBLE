

<?php $__env->startSection('content'); ?>
  <header class="gible-page-hero gible-reveal mb-4">
    <span class="gible-kicker">Your account</span>
    <h1>Profile</h1>
    <p class="gible-lead">Update your name, email, and password. Password fields stay empty unless you want to change them.</p>
  </header>

  <?php if(session('status')): ?>
    <div class="alert alert-success gible-reveal mb-4" role="status"><?php echo e(session('status')); ?></div>
  <?php endif; ?>

  <div class="row justify-content-center gible-reveal gible-reveal-delay-1">
    <div class="col-lg-8">
      <div class="card gible-card shadow-sm border-0">
        <div class="card-header bg-white d-flex align-items-center gap-2">
          <i class="bi bi-person-gear text-info"></i> Edit profile
        </div>
        <div class="card-body p-4">
          <?php if($errors->any()): ?>
            <div class="alert alert-danger mb-4">
              <ul class="mb-0 ps-3 small">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><?php echo e($err); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </ul>
            </div>
          <?php endif; ?>
          <form method="post" action="<?php echo e(route('profile.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="mb-3">
              <label class="form-label" for="profileName">Name</label>
              <input id="profileName" type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="form-control" required maxlength="255" autocomplete="name" />
            </div>
            <div class="mb-4">
              <label class="form-label" for="profileEmail">Email</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-envelope"></i></span>
                <input id="profileEmail" type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="form-control border-secondary-subtle" required autocomplete="email" />
              </div>
            </div>

            <hr class="border-secondary opacity-25 my-4" />
            <p class="small text-secondary mb-3">Leave the fields below blank to keep your current password.</p>

            <div class="mb-3">
              <label class="form-label" for="profileCurrentPassword">Current password</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-shield-lock"></i></span>
                <input id="profileCurrentPassword" type="password" name="current_password" class="form-control border-secondary-subtle" autocomplete="current-password" />
                <button type="button" class="btn btn-outline-secondary gible-password-toggle px-3" data-password-toggle-for="profileCurrentPassword" aria-label="Show password">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="profileNewPassword">New password</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-key"></i></span>
                <input id="profileNewPassword" type="password" name="password" class="form-control border-secondary-subtle" autocomplete="new-password" minlength="8" />
                <button type="button" class="btn btn-outline-secondary gible-password-toggle px-3" data-password-toggle-for="profileNewPassword" aria-label="Show password">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label" for="profileNewPasswordConfirm">Confirm new password</label>
              <div class="input-group">
                <span class="input-group-text border-secondary-subtle bg-transparent text-secondary"><i class="bi bi-key-fill"></i></span>
                <input id="profileNewPasswordConfirm" type="password" name="password_confirmation" class="form-control border-secondary-subtle" autocomplete="new-password" minlength="8" />
                <button type="button" class="btn btn-outline-secondary gible-password-toggle px-3" data-password-toggle-for="profileNewPasswordConfirm" aria-label="Show password">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Save changes</button>
              <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\agull\OneDrive\Documents\Visual Studio\GIBLE\resources\views/profile/edit.blade.php ENDPATH**/ ?>