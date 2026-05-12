<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>TimeBee — <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d0f;
            --surface: #16161a;
            --surface2: #1e1e24;
            --border: #2a2a32;
            --accent: #f5c842;
            --accent2: #ff6b35;
            --text: #e8e8f0;
            --muted: #6b6b80;
            --success: #2dd4a0;
            --danger: #ff4757;
            --sidebar-w: 240px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }
        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo .logo-mark {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 22px;
            color: var(--accent);
            letter-spacing: -0.5px;
        }
        .sidebar-logo .logo-mark span { color: var(--accent2); }
        .sidebar-logo .org-name {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
            padding: 12px 8px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active { background: rgba(245,200,66,0.12); color: var(--accent); }
        .nav-item .icon { width: 18px; height: 18px; opacity: 0.8; }
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }
        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            background: var(--surface2);
        }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 13px;
            color: #0d0d0f;
            flex-shrink: 0;
        }
        .user-info .user-name { font-size: 13px; font-weight: 500; color: var(--text); }
        .user-info .user-role { font-size: 11px; color: var(--muted); }
        .logout-btn {
            display: block;
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 12px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            text-decoration: none;
            transition: all 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .logout-btn:hover { border-color: var(--danger); color: var(--danger); }
        /* ── Main ── */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            height: 60px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .page-title {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: var(--text);
        }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .content { padding: 28px; flex: 1; }
        /* ── Components ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }
        .card-sm { padding: 18px 20px; }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }
        .stat-card.yellow::before { background: var(--accent); }
        .stat-card.orange::before { background: var(--accent2); }
        .stat-card.green::before { background: var(--success); }
        .stat-card.red::before { background: var(--danger); }
        .stat-label { font-size: 11px; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
        .stat-value { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 28px; color: var(--text); }
        .stat-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .grid { display: grid; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 16px; }
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            border: none;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.15s;
        }
        .btn-primary { background: var(--accent); color: #0d0d0f; }
        .btn-primary:hover { background: #f0c020; }
        .btn-danger { background: rgba(255,71,87,0.15); color: var(--danger); border: 1px solid rgba(255,71,87,0.3); }
        .btn-danger:hover { background: rgba(255,71,87,0.25); }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { color: var(--text); border-color: var(--text); }
        .btn-success { background: rgba(45,212,160,0.15); color: var(--success); border: 1px solid rgba(45,212,160,0.3); }
        .btn-success:hover { background: rgba(45,212,160,0.25); }
        /* Table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }
        tbody td { padding: 14px 16px; border-bottom: 1px solid rgba(42,42,50,0.5); font-size: 14px; }
        tbody tr:hover td { background: rgba(255,255,255,0.02); }
        tbody tr:last-child td { border-bottom: none; }
        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-green { background: rgba(45,212,160,0.12); color: var(--success); }
        .badge-yellow { background: rgba(245,200,66,0.12); color: var(--accent); }
        .badge-red { background: rgba(255,71,87,0.12); color: var(--danger); }
        .badge-gray { background: rgba(107,107,128,0.15); color: var(--muted); }
        .badge-orange { background: rgba(255,107,53,0.12); color: var(--accent2); }
        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 8px; }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--accent); }
        .form-select option { background: var(--surface2); }
        /* Alert */
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; }
        .alert-error { background: rgba(255,71,87,0.1); border: 1px solid rgba(255,71,87,0.25); color: #ff8090; }
        .alert-success { background: rgba(45,212,160,0.1); border: 1px solid rgba(45,212,160,0.25); color: var(--success); }
        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            width: 480px;
            max-width: 95vw;
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: none; } }
        .modal-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 18px; margin-bottom: 20px; }
        .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }
        /* Section header */
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .section-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 16px; }
        /* Spinner */
        .spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.2); border-top-color: var(--accent); border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* Timer */
        .timer-display {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 42px;
            color: var(--accent);
            letter-spacing: -2px;
        }
        /* Toast */
        #toast {
            position: fixed;
            bottom: 24px; right: 24px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 13px;
            z-index: 9999;
            display: none;
            animation: slideUp 0.2s ease;
        }
        #toast.show { display: block; }
        #toast.success { border-color: rgba(45,212,160,0.4); color: var(--success); }
        #toast.error { border-color: rgba(255,71,87,0.4); color: var(--danger); }
        @keyframes slideUp { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: none; } }
        /* Global API loading line (parallel requests use ref-count) */
        #apiLoadingLine {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            z-index: 10001;
            pointer-events: none;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.15s ease;
        }
        #apiLoadingLine.is-active {
            opacity: 1;
        }
        #apiLoadingLine::before {
            content: '';
            display: block;
            height: 100%;
            width: 35%;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            animation: apiLoadingSweep 0.9s ease-in-out infinite;
        }
        @keyframes apiLoadingSweep {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(400%); }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

<div id="apiLoadingLine" aria-hidden="true"></div>


<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-mark">Time<span>Bee</span> 🐝</div>
        <div class="org-name"><?php echo e(auth()->user()?->organization?->name ?? 'Organization'); ?></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="<?php echo e(route('dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="<?php echo e(route('timelogs.index')); ?>" class="nav-item <?php echo e(request()->routeIs('timelogs.*') ? 'active' : ''); ?>">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
            Time Logs
        </a>
        <a href="<?php echo e(route('projects.index')); ?>" class="nav-item <?php echo e(request()->routeIs('projects.*') ? 'active' : ''); ?>">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7h18M3 12h18M3 17h12"/></svg>
            Projects
        </a>
        <div class="nav-section-label">Team</div>
        <?php if(auth()->user()?->role === 'admin'): ?>
        <a href="<?php echo e(route('users.index')); ?>" class="nav-item <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Users
        </a>
        <?php endif; ?>
        <a href="<?php echo e(route('ai.report')); ?>" class="nav-item <?php echo e(request()->routeIs('ai.*') ? 'active' : ''); ?>">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/></svg>
            AI Report
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?php echo e(strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2))); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo e(auth()->user()?->name ?? 'User'); ?></div>
                <div class="user-role"><?php echo e(ucfirst(auth()->user()?->role ?? 'member')); ?></div>
            </div>
        </div>
        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="logout-btn">Sign out</button>
        </form>
    </div>
</aside>


<div class="main-wrap">
    <div class="topbar">
        <div class="page-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></div>
        <div class="topbar-actions">
            <?php echo $__env->yieldContent('topbar-actions'); ?>
        </div>
    </div>
    <div class="content">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</div>

<div id="toast"></div>

<script>
// Store JWT from session (JSON-encode so tokens are never broken by Blade/HTML escaping)
window.API_TOKEN = <?php echo json_encode(session('jwt_token', ''), 512) ?>;

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show ' + type;
    setTimeout(() => t.className = '', 3000);
}

function escapeHtml(text) {
    if (text == null || text === '') return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

let __apiInflight = 0;
function __apiLoadingEnter() {
    __apiInflight++;
    const line = document.getElementById('apiLoadingLine');
    if (line) line.classList.add('is-active');
}
function __apiLoadingLeave() {
    __apiInflight = Math.max(0, __apiInflight - 1);
    if (__apiInflight === 0) {
        const line = document.getElementById('apiLoadingLine');
        if (line) line.classList.remove('is-active');
    }
}

async function apiCall(method, url, data = null) {
    __apiLoadingEnter();
    try {
        const opts = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + window.API_TOKEN,
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        };
        if (data) opts.body = JSON.stringify(data);
        const res = await fetch(url, opts);
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            return { success: false, message: 'Unexpected response from server.' };
        }
        return await res.json();
    } catch (e) {
        return { success: false, message: e.message || 'Network error' };
    } finally {
        __apiLoadingLeave();
    }
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>