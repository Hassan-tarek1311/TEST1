@extends('layouts.app')
@section('title', 'Users')

@section('topbar-actions')
    <button class="btn btn-primary" onclick="openModal('userModal')">+ Add User</button>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="usersTable">
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit User Modal --}}
<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-title" id="userModalTitle">Add User</div>
        <input type="hidden" id="editUserId">
        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-input" id="userName" placeholder="John Doe">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" id="userEmail" placeholder="john@company.com">
        </div>
        <div id="passwordFields">
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" id="userPassword" placeholder="••••••••">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Role</label>
            <select class="form-select" id="userRole">
                <option value="employee">Employee</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeUserModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveUser()">Save User</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadUsers() {
    const res = await apiCall('GET', '/api/users');
    const tbody = document.getElementById('usersTable');
    if (!res.success) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Access denied or failed</td></tr>'; return; }
    const users = res.data.data || res.data;
    if (!users.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">No users found</td></tr>'; return; }
    tbody.innerHTML = users.map(u => `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:12px;color:#0d0d0f;flex-shrink:0">${u.name.substring(0,2).toUpperCase()}</div>
                    <strong>${u.name}</strong>
                </div>
            </td>
            <td style="color:var(--muted);font-size:13px">${u.email}</td>
            <td><span class="badge ${u.role==='admin'?'badge-orange':u.role==='manager'?'badge-yellow':'badge-gray'}">${u.role}</span></td>
            <td><span class="badge ${u.is_active?'badge-green':'badge-red'}">${u.is_active?'Active':'Inactive'}</span></td>
            <td style="color:var(--muted);font-size:13px">${new Date(u.created_at).toLocaleDateString()}</td>
            <td style="display:flex;gap:6px">
                <button class="btn btn-ghost" style="font-size:12px;padding:5px 10px" onclick='editUser(${JSON.stringify(u)})'>Edit</button>
                <button class="btn ${u.is_active?'btn-danger':'btn-success'}" style="font-size:12px;padding:5px 10px" onclick="toggleUser(${u.id})">${u.is_active?'Deactivate':'Activate'}</button>
            </td>
        </tr>
    `).join('');
}

function editUser(u) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('editUserId').value = u.id;
    document.getElementById('userName').value = u.name;
    document.getElementById('userEmail').value = u.email;
    document.getElementById('userRole').value = u.role;
    document.getElementById('passwordFields').style.display = 'none';
    openModal('userModal');
}

function closeUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('editUserId').value = '';
    document.getElementById('userName').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userRole').value = 'employee';
    document.getElementById('passwordFields').style.display = 'block';
    closeModal('userModal');
}

async function saveUser() {
    const id = document.getElementById('editUserId').value;
    const data = {
        name: document.getElementById('userName').value,
        email: document.getElementById('userEmail').value,
        role: document.getElementById('userRole').value,
    };
    const pw = document.getElementById('userPassword').value;
    if (!id && pw) data.password = pw;
    if (!data.name || !data.email) { showToast('Name and email required', 'error'); return; }
    const res = id
        ? await apiCall('PUT', `/api/users/${id}`, data)
        : await apiCall('POST', '/api/users', { ...data, password: pw, password_confirmation: pw });
    if (res.success) { closeUserModal(); showToast('Saved!', 'success'); loadUsers(); }
    else showToast(res.message || Object.values(res.errors || {})[0]?.[0] || 'Error', 'error');
}

async function toggleUser(id) {
    const res = await apiCall('PATCH', `/api/users/${id}/toggle-status`);
    if (res.success) { showToast('Status updated', 'success'); loadUsers(); }
    else showToast('Error', 'error');
}

loadUsers();
</script>
@endpush
