<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>TimeBee — Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d0f;
            --surface: #16161a;
            --border: #2a2a32;
            --accent: #f5c842;
            --accent2: #ff6b35;
            --text: #e8e8f0;
            --muted: #6b6b80;
            --danger: #ff4757;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        /* Left hero panel */
        .hero {
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(245,200,66,0.08) 0%, transparent 70%);
            top: 10%; left: 50%;
            transform: translateX(-50%);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245,200,66,0.1);
            border: 1px solid rgba(245,200,66,0.25);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 32px;
            width: fit-content;
        }
        .hero-title {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 52px;
            line-height: 1.05;
            letter-spacing: -2px;
            margin-bottom: 20px;
        }
        .hero-title span { color: var(--accent); }
        .hero-sub {
            font-size: 16px;
            color: var(--muted);
            line-height: 1.6;
            max-width: 360px;
            margin-bottom: 48px;
        }
        .feature-list { display: flex; flex-direction: column; gap: 14px; }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: var(--muted);
        }
        .feature-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent);
            flex-shrink: 0;
        }
        /* Right form panel */
        .auth-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
        }
        .auth-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 24px;
            color: var(--accent);
            margin-bottom: 40px;
        }
        .auth-logo span { color: var(--accent2); }
        .auth-title {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .auth-sub { font-size: 14px; color: var(--muted); margin-bottom: 36px; }
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .form-input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            color: var(--text);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--accent); }
        .form-input::placeholder { color: var(--muted); }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--accent);
            color: #0d0d0f;
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: #f0c020; }
        .auth-switch {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--muted);
        }
        .auth-switch a { color: var(--accent); text-decoration: none; }
        .error-box {
            background: rgba(255,71,87,0.1);
            border: 1px solid rgba(255,71,87,0.25);
            border-radius: 8px;
            padding: 12px 16px;
            color: #ff8090;
            font-size: 13px;
            margin-bottom: 20px;
        }
        @media(max-width:768px) {
            body { grid-template-columns: 1fr; }
            .hero { display: none; }
            .auth-panel { padding: 40px 24px; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-badge">🐝 Employee Time Tracking</div>
        <h1 class="hero-title">Track time.<br><span>Build more.</span></h1>
        <p class="hero-sub">TimeBee helps your team track hours, monitor productivity, and ship projects on time.</p>
        <div class="feature-list">
            <div class="feature-item"><div class="feature-dot"></div> Real-time time tracking per project</div>
            <div class="feature-item"><div class="feature-dot"></div> Team productivity analytics</div>
            <div class="feature-item"><div class="feature-dot"></div> AI-powered productivity reports</div>
            <div class="feature-item"><div class="feature-dot"></div> Role-based access control</div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-logo">Time<span>Bee</span> 🐝</div>
        <h2 class="auth-title">Welcome back</h2>
        <p class="auth-sub">Sign in to your account to continue</p>

        <?php if(session('error')): ?>
            <div class="error-box"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="error-box"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="you@company.com" value="<?php echo e(old('email')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-submit">Sign in →</button>
        </form>

        <div class="auth-switch">
            Don't have an account? <a href="<?php echo e(route('register')); ?>">Create one</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>