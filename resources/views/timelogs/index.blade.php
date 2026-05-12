@extends('layouts.app')
@section('title', 'Time Logs')

@section('topbar-actions')
    <button class="btn btn-primary" onclick="openModal('startModal')">▶ Start Timer</button>
@endsection

@section('content')

{{-- Summary bar --}}
<div class="grid grid-3" style="margin-bottom:24px">
    <div class="stat-card yellow">
        <div class="stat-label">Total Logs</div>
        <div class="stat-value" id="totalLogs">—</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Active Now</div>
        <div class="stat-value" id="activeLogs">—</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">Filter by Date</div>
        <input type="date" class="form-input" id="filterDate" style="margin-top:6px" onchange="loadLogs()">
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Started</th>
                    <th>Ended</th>
                    <th>Duration</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="logsTable">
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Loading...</td></tr>
            </tbody>
        </table>
    </div>
    <div id="pagination" style="display:flex;gap:8px;margin-top:16px;justify-content:center"></div>
</div>

{{-- Start Timer Modal --}}
<div class="modal-overlay" id="startModal">
    <div class="modal">
        <div class="modal-title">▶ Start Timer</div>
        <div class="form-group">
            <label class="form-label">Project</label>
            <select class="form-select" id="startProjectId">
                <option value="">Loading...</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-textarea" id="startNotes" rows="2" placeholder="What are you working on?"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal('startModal')">Cancel</button>
            <button class="btn btn-primary" onclick="startLog()">▶ Start</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;

async function loadLogs(page = 1) {
    currentPage = page;
    const date = document.getElementById('filterDate').value;
    let url = `/api/time-logs?page=${page}`;
    if (date) url += `&date=${date}`;
    const res = await apiCall('GET', url);
    const tbody = document.getElementById('logsTable');
    if (!res.success) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Failed to load</td></tr>'; return; }
    const logs = res.data.data;
    document.getElementById('totalLogs').textContent = res.data.total || logs.length;
    document.getElementById('activeLogs').textContent = logs.filter(l => !l.ended_at).length;

    if (!logs.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">No logs found</td></tr>';
        return;
    }
    tbody.innerHTML = logs.map(l => `
        <tr>
            <td><strong>${escapeHtml(l.project?.name) || '—'}</strong></td>
            <td style="font-size:13px;color:var(--muted)">${new Date(l.started_at).toLocaleString()}</td>
            <td style="font-size:13px;color:var(--muted)">${l.ended_at ? new Date(l.ended_at).toLocaleString() : '<span class="badge badge-yellow">Running</span>'}</td>
            <td>${l.duration_formatted ? escapeHtml(l.duration_formatted) : (l.ended_at ? '—' : '<span style="color:var(--accent)">Active</span>')}</td>
            <td style="font-size:13px;color:var(--muted)">${escapeHtml(l.notes || '—')}</td>
            <td style="display:flex;gap:6px">
                ${!l.ended_at ? `<button type="button" class="btn btn-success" style="font-size:12px;padding:5px 10px" onclick="stopLog(${l.id})">■ Stop</button>` : ''}
                <button type="button" class="btn btn-danger" style="font-size:12px;padding:5px 10px" onclick="deleteLog(${l.id})">Delete</button>
            </td>
        </tr>
    `).join('');

    // Pagination
    const pagDiv = document.getElementById('pagination');
    const lastPage = res.data.last_page || 1;
    pagDiv.innerHTML = '';
    for (let i = 1; i <= lastPage; i++) {
        const btn = document.createElement('button');
        btn.className = 'btn ' + (i === currentPage ? 'btn-primary' : 'btn-ghost');
        btn.style.padding = '5px 12px';
        btn.style.fontSize = '13px';
        btn.textContent = i;
        btn.onclick = () => loadLogs(i);
        pagDiv.appendChild(btn);
    }
}

async function startLog() {
    const projectId = document.getElementById('startProjectId').value;
    const notes = document.getElementById('startNotes').value;
    if (!projectId) { showToast('Select a project', 'error'); return; }
    const res = await apiCall('POST', '/api/time-logs/start', { project_id: parseInt(projectId), notes });
    if (res.success) { closeModal('startModal'); showToast('Timer started!', 'success'); loadLogs(); }
    else showToast(res.message || 'Error', 'error');
}

async function stopLog(id) {
    const res = await apiCall('PATCH', `/api/time-logs/${id}/stop`);
    if (res.success) { showToast('Stopped! ' + (res.data.duration || ''), 'success'); loadLogs(); }
    else showToast(res.message || 'Error', 'error');
}

async function deleteLog(id) {
    if (!confirm('Delete this log?')) return;
    const res = await apiCall('DELETE', `/api/time-logs/${id}`);
    if (res.success) { showToast('Deleted', 'success'); loadLogs(); }
    else showToast('Error', 'error');
}

// Load projects into select
async function loadProjects() {
    const res = await apiCall('GET', '/api/projects');
    if (res.success) {
        const projects = res.data.data || res.data;
        const sel = document.getElementById('startProjectId');
        sel.innerHTML = '';
        const o0 = document.createElement('option');
        o0.value = '';
        o0.textContent = 'Choose project...';
        sel.appendChild(o0);
        projects.forEach(p => {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.name;
            sel.appendChild(o);
        });
    }
}

loadLogs();
loadProjects();
</script>
@endpush
