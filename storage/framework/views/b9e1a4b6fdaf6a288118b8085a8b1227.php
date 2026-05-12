<?php $__env->startSection('title', 'AI Productivity Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-2" style="gap:20px;margin-bottom:20px">
    
    <div class="card">
        <div class="section-header">
            <div class="section-title">🤖 My Productivity Report</div>
        </div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px">AI analysis of your time tracking patterns and productivity score</p>
        <button class="btn btn-primary" onclick="loadMyReport()" id="myReportBtn">Generate Report</button>
        <div id="myReportResult" style="margin-top:20px"></div>
    </div>

    
    <?php if(auth()->user()?->role === 'admin'): ?>
    <div class="card">
        <div class="section-header">
            <div class="section-title">👥 Team Summary</div>
        </div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:16px">AI-generated overview of team productivity and insights</p>
        <button class="btn btn-primary" onclick="loadTeamSummary()" id="teamSummaryBtn">Generate Summary</button>
        <div id="teamSummaryResult" style="margin-top:20px"></div>
    </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.ai-report-box {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    font-size:14px;
    line-height:1.7;
}
.score-ring {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 12px 0;
}
.score-circle {
    width: 90px; height: 90px;
    border-radius: 50%;
    border: 4px solid var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    font-family: 'Syne', sans-serif;
}
.score-num { font-size: 26px; font-weight: 800; color: var(--accent); }
.score-label { font-size: 10px; color: var(--muted); }
.ai-insights { margin-top: 12px; }
.ai-insights p { margin-bottom: 8px; }
.insight-item {
    display: flex; gap: 8px; align-items: flex-start;
    margin-bottom: 8px; font-size: 13px; color: var(--muted);
}
.insight-dot { width:5px; height:5px; border-radius:50%; background:var(--accent); flex-shrink:0; margin-top:6px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function loadMyReport() {
    const btn = document.getElementById('myReportBtn');
    const container = document.getElementById('myReportResult');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';
    container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--muted)">Generating AI report...</div>';

    const userId = <?php echo e(auth()->id()); ?>;
    const res = await apiCall('GET', `/api/ai/productivity/${userId}`);

    btn.disabled = false;
    btn.textContent = 'Regenerate';

    if (!res.success) {
        container.innerHTML = '<div class="alert alert-error">Failed to generate report</div>';
        return;
    }
    const d = res.data;
    container.innerHTML = `
        <div class="ai-report-box">
            <div class="score-ring">
                <div class="score-circle">
                    <div class="score-num">${d.productivity_score ?? '—'}</div>
                    <div class="score-label">/ 100</div>
                </div>
            </div>
            <div style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:16px">
                ${d.total_hours ? `${d.total_hours}h tracked · ${d.total_logs || 0} sessions` : 'No data yet'}
            </div>
            <div class="ai-insights">
                ${d.insights ? d.insights.map(i => `<div class="insight-item"><div class="insight-dot"></div><div>${i}</div></div>`).join('') : ''}
                ${d.summary ? `<p>${d.summary}</p>` : ''}
                ${d.message ? `<p>${d.message}</p>` : ''}
            </div>
            ${d.recommendation ? `<div style="margin-top:12px;padding:12px;background:rgba(245,200,66,0.06);border-radius:8px;border-left:3px solid var(--accent);font-size:13px">${d.recommendation}</div>` : ''}
        </div>
    `;
}

async function loadTeamSummary() {
    const btn = document.getElementById('teamSummaryBtn');
    const container = document.getElementById('teamSummaryResult');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';
    container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--muted)">Generating team summary...</div>';

    const res = await apiCall('GET', '/api/ai/team-summary');
    btn.disabled = false;
    btn.textContent = 'Regenerate';

    if (!res.success) {
        container.innerHTML = '<div class="alert alert-error">Failed to generate</div>';
        return;
    }
    const d = res.data;
    container.innerHTML = `
        <div class="ai-report-box">
            <div style="margin-bottom:12px">
                <span class="badge badge-yellow">Team Score: ${d.team_productivity_score ?? '—'}/100</span>
            </div>
            ${d.summary ? `<p style="margin-bottom:12px">${d.summary}</p>` : ''}
            ${d.team_insights ? d.team_insights.map(i => `<div class="insight-item"><div class="insight-dot"></div><div>${i}</div></div>`).join('') : ''}
            ${d.top_performer ? `<div style="margin-top:12px;padding:12px;background:rgba(45,212,160,0.06);border-radius:8px;border-left:3px solid var(--success);font-size:13px">🏆 Top Performer: <strong>${d.top_performer}</strong></div>` : ''}
        </div>
    `;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/ai/report.blade.php ENDPATH**/ ?>