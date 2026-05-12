@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
.stat-value.stat-pending {
    min-height: 36px;
    width: 72px;
    max-width: 100%;
    border-radius: 8px;
    background: linear-gradient(90deg, var(--surface2) 20%, var(--border) 45%, var(--surface2) 70%);
    background-size: 200% 100%;
    animation: dashSkel 1.1s ease-in-out infinite;
    color: transparent !important;
}
@keyframes dashSkel {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
.dash-skel-row {
    height: 48px;
    margin-bottom: 10px;
    border-radius: 8px;
    background: linear-gradient(90deg, var(--surface2) 20%, var(--border) 45%, var(--surface2) 70%);
    background-size: 200% 100%;
    animation: dashSkel 1.1s ease-in-out infinite;
}
.dash-skel-table td {
    padding: 16px !important;
}
.dash-skel-bar {
    height: 14px;
    border-radius: 4px;
    max-width: 180px;
    background: linear-gradient(90deg, var(--surface2) 20%, var(--border) 45%, var(--surface2) 70%);
    background-size: 200% 100%;
    animation: dashSkel 1.1s ease-in-out infinite;
}
</style>
@endpush

@section('topbar-actions')
    <button class="btn btn-primary" onclick="openModal('startTimerModal')">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Start Timer
    </button>
@endsection

@section('content')

{{-- Stats Grid --}}
<div class="grid grid-4" style="margin-bottom:24px">
    <div class="stat-card yellow">
        <div class="stat-label">Today's Hours</div>
        <div class="stat-value stat-pending" id="todayHours"></div>
        <div class="stat-sub">Tracked today</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">This Week</div>
        <div class="stat-value stat-pending" id="weekHours"></div>
        <div class="stat-sub">Total hours</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Active Projects</div>
        <div class="stat-value stat-pending" id="activeProjects"></div>
        <div class="stat-sub">In progress</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Team Members</div>
        <div class="stat-value stat-pending" id="teamCount"></div>
        <div class="stat-sub">Organization</div>
    </div>
</div>

<div class="grid grid-2" style="gap:20px">
    {{-- Active Timer Card --}}
    <div class="card" id="timerCard">
        <div class="section-header">
            <div class="section-title">⏱ Active Timer</div>
            <div id="timerStatus"></div>
        </div>
        <div id="noActiveTimer" style="text-align:center; padding:20px 0; color:var(--muted); font-size:14px;">
            No active timer. Start tracking your time!
        </div>
        <div id="activeTimerDisplay" style="display:none">
            <div class="timer-display" id="timerClock">00:00:00</div>
            <div style="margin-top:8px; font-size:14px; color:var(--muted)" id="timerProject">—</div>
            <div style="margin-top:16px">
                <button class="btn btn-danger" onclick="stopActiveTimer()">
                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16"/></svg>
                    Stop Timer
                </button>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card">
        <div class="section-header">
            <div class="section-title">Recent Logs</div>
            <a href="{{ route('timelogs.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none;">View all →</a>
        </div>
        <div id="recentLogs">
            <div class="dash-skel-row"></div>
            <div class="dash-skel-row"></div>
            <div class="dash-skel-row"></div>
        </div>
    </div>
</div>

{{-- Projects Overview --}}
<div class="card" style="margin-top:20px">
    <div class="section-header">
        <div class="section-title">Projects Overview</div>
        <a href="{{ route('projects.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none;">Manage →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Status</th>
                    <th>Your Hours</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="projectsTable">
                <tr class="dash-skel-table"><td colspan="4"><div class="dash-skel-bar" style="max-width:220px"></div></td></tr>
                <tr class="dash-skel-table"><td colspan="4"><div class="dash-skel-bar" style="max-width:160px"></div></td></tr>
                <tr class="dash-skel-table"><td colspan="4"><div class="dash-skel-bar" style="max-width:200px"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Start Timer Modal --}}
<div class="modal-overlay" id="startTimerModal">
    <div class="modal">
        <div class="modal-title">▶ Start Time Tracking</div>
        <div class="form-group">
            <label class="form-label">Select Project</label>
            <select class="form-select" id="timerProjectSelect">
                <option value="">Loading projects...</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Notes (optional)</label>
            <textarea class="form-textarea" id="timerNotes" rows="2" placeholder="What are you working on?"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('startTimerModal')">Cancel</button>
            <button class="btn btn-primary" onclick="startTimer()">Start Timer</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let activeLogId = null;
let timerInterval = null;
let timerStart = null;

function setDashStat(id, text) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = text;
    el.classList.remove('stat-pending');
}

async function loadDashboard() {
    const today = new Date().toISOString().split('T')[0];
    const weekStart = new Date();
    weekStart.setDate(weekStart.getDate() - 6);
    const fromWeek = weekStart.toISOString().split('T')[0];

    const [recentRes, todayRes, weekRes, activeRes, projRes, userRes] = await Promise.all([
        apiCall('GET', '/api/time-logs?per_page=5'),
        apiCall('GET', `/api/time-logs?date=${today}&per_page=100`),
        apiCall('GET', `/api/time-logs?from=${fromWeek}&to=${today}&per_page=200`),
        apiCall('GET', '/api/time-logs?active_only=1&per_page=1'),
        apiCall('GET', '/api/projects'),
        apiCall('GET', '/api/users'),
    ]);

    const container = document.getElementById('recentLogs');
    if (recentRes.success && recentRes.data?.data) {
        const logs = recentRes.data.data;
        if (!logs.length) {
            container.innerHTML = '<div style="text-align:center;padding:20px 0;color:var(--muted);font-size:14px">No logs yet</div>';
        } else {
            container.innerHTML = logs.map(log => `
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-size:14px;font-weight:500">${escapeHtml(log.project?.name) || '—'}</div>
                        <div style="font-size:11px;color:var(--muted)">${new Date(log.started_at).toLocaleDateString()}</div>
                    </div>
                    <span class="badge ${log.ended_at ? 'badge-green' : 'badge-yellow'}">${log.ended_at ? (escapeHtml(log.duration_formatted) || 'done') : 'Active'}</span>
                </div>
            `).join('');
        }
    } else {
        container.innerHTML = '<div style="text-align:center;padding:20px 0;color:var(--muted);font-size:14px">Could not load logs</div>';
    }

    let todayMins = 0;
    if (todayRes.success && todayRes.data?.data) {
        todayRes.data.data.forEach(l => {
            if (l.duration_minutes) todayMins += l.duration_minutes;
        });
    }
    setDashStat('todayHours', (todayMins / 60).toFixed(1) + 'h');

    let weekMins = 0;
    if (weekRes.success && weekRes.data?.data) {
        weekRes.data.data.forEach(l => {
            if (l.duration_minutes) weekMins += l.duration_minutes;
        });
    }
    setDashStat('weekHours', (weekMins / 60).toFixed(1) + 'h');

    if (activeRes.success && activeRes.data?.data?.length) {
        showActiveTimer(activeRes.data.data[0]);
    } else {
        document.getElementById('noActiveTimer').style.display = 'block';
        document.getElementById('activeTimerDisplay').style.display = 'none';
        activeLogId = null;
        clearInterval(timerInterval);
    }

    if (projRes.success) {
        const projects = projRes.data.data || projRes.data;
        setDashStat('activeProjects', String(projects.filter(p => p.status === 'active').length));

        const tbody = document.getElementById('projectsTable');
        if (!projects.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No projects yet</td></tr>';
        } else {
            tbody.innerHTML = projects.slice(0, 5).map(p => `
                <tr>
                    <td><strong>${escapeHtml(p.name)}</strong><div style="font-size:12px;color:var(--muted)">${escapeHtml(p.description || '')}</div></td>
                    <td><span class="badge ${p.status==='active'?'badge-green':p.status==='completed'?'badge-gray':'badge-yellow'}">${escapeHtml(p.status)}</span></td>
                    <td style="color:var(--muted)">${typeof p.total_hours === 'number' ? p.total_hours + 'h' : '—'}</td>
                    <td>
                        <button type="button" class="btn btn-ghost" style="font-size:12px;padding:5px 10px" onclick="quickStart(${p.id})">▶ Track</button>
                    </td>
                </tr>
            `).join('');
        }

        const sel = document.getElementById('timerProjectSelect');
        sel.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = 'Choose project...';
        sel.appendChild(opt0);
        projects.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name;
            sel.appendChild(opt);
        });
    } else {
        setDashStat('activeProjects', '—');
        document.getElementById('projectsTable').innerHTML =
            '<tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">Could not load projects</td></tr>';
        const sel = document.getElementById('timerProjectSelect');
        sel.innerHTML = '';
        const errOpt = document.createElement('option');
        errOpt.value = '';
        errOpt.textContent = 'Unavailable';
        sel.appendChild(errOpt);
    }

    if (userRes.success && Array.isArray(userRes.data)) {
        setDashStat('teamCount', String(userRes.data.length));
    } else {
        setDashStat('teamCount', '—');
    }
}

function showActiveTimer(log) {
    activeLogId = log.id;
    timerStart = new Date(log.started_at);
    document.getElementById('noActiveTimer').style.display = 'none';
    document.getElementById('activeTimerDisplay').style.display = 'block';
    document.getElementById('timerProject').textContent = log.project?.name || 'Unknown project';
    clearInterval(timerInterval);
    timerInterval = setInterval(updateClock, 1000);
    updateClock();
}

function updateClock() {
    const diff = Math.floor((Date.now() - timerStart.getTime()) / 1000);
    const h = Math.floor(diff/3600).toString().padStart(2,'0');
    const m = Math.floor((diff%3600)/60).toString().padStart(2,'0');
    const s = (diff%60).toString().padStart(2,'0');
    document.getElementById('timerClock').textContent = `${h}:${m}:${s}`;
}

async function startTimer() {
    const projectId = document.getElementById('timerProjectSelect').value;
    const notes = document.getElementById('timerNotes').value;
    if (!projectId) { showToast('Please select a project', 'error'); return; }

    const res = await apiCall('POST', '/api/time-logs/start', { project_id: parseInt(projectId), notes });
    if (res.success) {
        closeModal('startTimerModal');
        showToast('Timer started!', 'success');
        showActiveTimer(res.data);
    } else {
        showToast(res.message || 'Failed to start timer', 'error');
    }
}

async function quickStart(projectId) {
    const res = await apiCall('POST', '/api/time-logs/start', { project_id: projectId });
    if (res.success) {
        showToast('Timer started!', 'success');
        showActiveTimer(res.data);
    } else {
        showToast(res.message || 'Already tracking time', 'error');
    }
}

async function stopActiveTimer() {
    if (!activeLogId) return;
    const res = await apiCall('PATCH', `/api/time-logs/${activeLogId}/stop`);
    if (res.success) {
        clearInterval(timerInterval);
        activeLogId = null;
        document.getElementById('noActiveTimer').style.display = 'block';
        document.getElementById('activeTimerDisplay').style.display = 'none';
        showToast('Timer stopped! Duration: ' + (res.data.duration || '—'), 'success');
        loadDashboard();
    }
}

loadDashboard();
</script>
@endpush
