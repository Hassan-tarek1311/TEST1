<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>TimeBee — Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#0d0d0f; --surface:#16161a; --border:#2a2a32; --accent:#f5c842; --accent2:#ff6b35; --text:#e8e8f0; --muted:#6b6b80; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:40px 20px; }
        .register-card { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:40px; width:100%; max-width:520px; }
        .auth-logo { font-family:'Syne',sans-serif; font-weight:800; font-size:22px; color:var(--accent); margin-bottom:28px; }
        .auth-logo span { color:var(--accent2); }
        .auth-title { font-family:'Syne',sans-serif; font-weight:700; font-size:26px; margin-bottom:6px; }
        .auth-sub { font-size:14px; color:var(--muted); margin-bottom:30px; }
        .divider { font-size:11px; font-weight:600; letter-spacing:1px; text-transform:uppercase; color:var(--muted); margin:16px 0 14px; padding-bottom:14px; border-bottom:1px solid var(--border); }
        .form-group { margin-bottom:16px; }
        .form-label { display:block; font-size:11px; font-weight:600; letter-spacing:0.8px; text-transform:uppercase; color:var(--muted); margin-bottom:8px; }
        .form-input { width:100%; background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:11px 14px; color:var(--text); font-size:14px; font-family:'DM Sans',sans-serif; outline:none; transition:border-color 0.15s; }
        .form-input:focus { border-color:var(--accent); }
        .form-input::placeholder { color:var(--muted); }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .btn-submit { width:100%; padding:13px; background:var(--accent); color:#0d0d0f; border:none; border-radius:10px; font-family:'Syne',sans-serif; font-weight:700; font-size:15px; cursor:pointer; margin-top:8px; transition:all 0.15s; }
        .btn-submit:hover { background:#f0c020; }
        .auth-switch { text-align:center; margin-top:20px; font-size:13px; color:var(--muted); }
        .auth-switch a { color:var(--accent); text-decoration:none; }
        .error-box { background:rgba(255,71,87,0.1); border:1px solid rgba(255,71,87,0.25); border-radius:8px; padding:12px 16px; color:#ff8090; font-size:13px; margin-bottom:20px; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="auth-logo">Time<span>Bee</span> 🐝</div>
        <h2 class="auth-title">Create account</h2>
        <p class="auth-sub">Set up your organization and admin account</p>

        <?php if($errors->any()): ?>
            <div class="error-box"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('register.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="divider">Organization</div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="org_name" class="form-input" placeholder="Acme Corp" value="<?php echo e(old('org_name')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company Email</label>
                    <input type="email" name="org_email" class="form-input" placeholder="info@acme.com" value="<?php echo e(old('org_email')); ?>" required>
                </div>
            </div>
            <div class="divider">Admin Account</div>
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" placeholder="John Doe" value="<?php echo e(old('name')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="john@acme.com" value="<?php echo e(old('email')); ?>" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Account →</button>
        </form>
        <div class="auth-switch">Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in</a></div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/auth/register.blade.php ENDPATH**/ ?>