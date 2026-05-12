<?php $__env->startSection('title', 'Projects'); ?>

<?php $__env->startSection('topbar-actions'); ?>
    <?php if(auth()->user()?->role === 'admin'): ?>
    <button class="btn btn-primary" onclick="openModal('projectModal')">+ New Project</button>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="projectsTable">
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:32px">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>


<div class="modal-overlay" id="projectModal">
    <div class="modal">
        <div class="modal-title" id="projectModalTitle">New Project</div>
        <input type="hidden" id="editProjectId">
        <div class="form-group">
            <label class="form-label">Project Name</label>
            <input type="text" class="form-input" id="projectName" placeholder="e.g. Website Redesign">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-textarea" id="projectDesc" rows="2" placeholder="Brief project description..."></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-select" id="projectStatus">
                <option value="active">Active</option>
                <option value="paused">On Hold</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeProjectModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveProject()">Save Project</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function loadProjects() {
    const res = await apiCall('GET', '/api/projects');
    const tbody = document.getElementById('projectsTable');
    if (!res.success) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:32px">Failed to load</td></tr>'; return; }
    const projects = res.data.data || res.data;
    if (!projects.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:32px">No projects yet. Create one!</td></tr>';
        return;
    }
    tbody.innerHTML = projects.map(p => `
        <tr>
            <td><strong>${escapeHtml(p.name)}</strong></td>
            <td style="color:var(--muted);font-size:13px">${escapeHtml(p.description || '—')}</td>
            <td><span class="badge ${p.status==='active'?'badge-green':p.status==='completed'?'badge-gray':'badge-yellow'}">${escapeHtml(String(p.status).replace('_',' '))}</span></td>
            <td style="color:var(--muted);font-size:13px">${new Date(p.created_at).toLocaleDateString()}</td>
            <td style="display:flex;gap:8px">
                <button type="button" class="btn btn-ghost" style="font-size:12px;padding:5px 10px" onclick='editProject(${JSON.stringify(p)})'>Edit</button>
                <button type="button" class="btn btn-danger" style="font-size:12px;padding:5px 10px" onclick="deleteProject(${p.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

function editProject(p) {
    document.getElementById('projectModalTitle').textContent = 'Edit Project';
    document.getElementById('editProjectId').value = p.id;
    document.getElementById('projectName').value = p.name;
    document.getElementById('projectDesc').value = p.description || '';
    document.getElementById('projectStatus').value = p.status;
    openModal('projectModal');
}

function closeProjectModal() {
    document.getElementById('projectModalTitle').textContent = 'New Project';
    document.getElementById('editProjectId').value = '';
    document.getElementById('projectName').value = '';
    document.getElementById('projectDesc').value = '';
    document.getElementById('projectStatus').value = 'active';
    closeModal('projectModal');
}

async function saveProject() {
    const id = document.getElementById('editProjectId').value;
    const data = {
        name: document.getElementById('projectName').value,
        description: document.getElementById('projectDesc').value,
        status: document.getElementById('projectStatus').value,
    };
    if (!data.name.trim()) { showToast('Project name is required', 'error'); return; }
    const res = id
        ? await apiCall('PUT', `/api/projects/${id}`, data)
        : await apiCall('POST', '/api/projects', data);
    if (res.success) {
        closeProjectModal();
        showToast(id ? 'Project updated!' : 'Project created!', 'success');
        loadProjects();
    } else {
        showToast(res.message || 'Failed to save', 'error');
    }
}

async function deleteProject(id) {
    if (!confirm('Delete this project?')) return;
    const res = await apiCall('DELETE', `/api/projects/${id}`);
    if (res.success) { showToast('Project deleted', 'success'); loadProjects(); }
    else showToast(res.message || 'Failed', 'error');
}

loadProjects();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/projects/index.blade.php ENDPATH**/ ?>