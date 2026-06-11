<?php
/**
 * admin/leads.php — view + manage contact-form leads (no creation; they come from the public form)
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_leads');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.'); redirect(url('/admin/leads'));
    }
    $editingId = (int)post('id', 0);
    $status    = post('status', 'new');
    $priority  = post('priority', 'medium');
    $notes     = trim((string)post('notes', ''));
    if ($editingId > 0) {
        $db->prepare('UPDATE leads SET status=:status, priority=:priority, notes=:notes WHERE id=:id');
        $db->bind(':status', $status); $db->bind(':priority', $priority);
        $db->bind(':notes', $notes); $db->bind(':id', $editingId);
        $db->execute();
        flash('success', 'Lead updated.');
    }
    redirect(url('/admin/leads'));
}

if ($action === 'delete' && $id > 0) {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) { flash('danger', 'Invalid request token.'); redirect(url('/admin/leads')); }
    $db->prepare('DELETE FROM leads WHERE id=:id'); $db->bind(':id', $id); $db->execute();
    flash('success', 'Lead deleted.');
    redirect(url('/admin/leads'));
}

if ($action === 'view' && $id > 0) {
    $db->prepare('SELECT * FROM leads WHERE id=:id'); $db->bind(':id', $id);
    $lead = $db->fetch();
    if (!$lead) { flash('danger', 'Lead not found.'); redirect(url('/admin/leads')); }
    admin_layout_start('Lead #' . (int)$lead['id'], 'leads');
    ?>
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card"><div class="card-body">
                <h2 class="h5"><?php echo e($lead['subject'] ?: 'No subject'); ?></h2>
                <p class="text-muted mb-3"><i class="fas fa-clock" aria-hidden="true"></i> <?php echo e(formatDate($lead['created_at'], 'M d, Y H:i')); ?></p>
                <dl class="row mb-3">
                    <dt class="col-sm-3">Name</dt><dd class="col-sm-9"><?php echo e($lead['name']); ?></dd>
                    <dt class="col-sm-3">Email</dt><dd class="col-sm-9"><a href="mailto:<?php echo e($lead['email']); ?>"><?php echo e($lead['email']); ?></a></dd>
                    <dt class="col-sm-3">Phone</dt><dd class="col-sm-9"><?php echo e($lead['phone'] ?: '—'); ?></dd>
                    <dt class="col-sm-3">Source</dt><dd class="col-sm-9"><?php echo e($lead['source'] ?: '—'); ?></dd>
                    <dt class="col-sm-3">IP</dt><dd class="col-sm-9"><code><?php echo e($lead['ip_address']); ?></code></dd>
                </dl>
                <h3 class="h6">Message</h3>
                <p style="white-space:pre-wrap;"><?php echo e($lead['message']); ?></p>
            </div></div>
        </div>
        <div class="col-lg-5">
            <form method="POST" class="card card-body">
                <h2 class="h6">Update</h2>
                <?php echo Security::getCSRFField(); ?>
                <input type="hidden" name="id" value="<?php echo (int)$lead['id']; ?>">
                <?php
                admin_field('Status', 'status', $lead['status'], 'select', ['options' => ['new' => 'New','contacted' => 'Contacted','qualified' => 'Qualified','rejected' => 'Rejected','converted' => 'Converted'], 'required' => true]);
                admin_field('Priority', 'priority', $lead['priority'], 'select', ['options' => ['low' => 'Low','medium' => 'Medium','high' => 'High'], 'required' => true]);
                admin_field('Notes', 'notes', $lead['notes'] ?? '', 'textarea', ['rows' => 6]);
                ?>
                <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> <span>Save</span></button>
                    <a class="btn btn-outline-secondary" href="<?php echo url('/admin/leads'); ?>">Back</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    admin_layout_end(); return;
}

$status = $_GET['status'] ?? '';
$sql = 'SELECT * FROM leads';
$params = [];
if ($status !== '') { $sql .= ' WHERE status = :status'; $params[':status'] = $status; }
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$db->prepare($sql);
foreach ($params as $k => $v) { $db->bind($k, $v); }
$leads = $db->fetchAll();
$csrf = Security::generateCSRFToken();

admin_layout_start('Leads', 'leads');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group" aria-label="Filter leads by status">
        <?php
        $filters = ['' => 'All', 'new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'converted' => 'Converted', 'rejected' => 'Rejected'];
        foreach ($filters as $k => $label):
            $active = ($status === $k) ? ' active' : '';
        ?>
            <a class="btn btn-sm btn-outline-primary<?php echo $active; ?>" href="<?php echo url('/admin/leads' . ($k !== '' ? '?status=' . urlencode($k) : '')); ?>"><?php echo e($label); ?></a>
        <?php endforeach; ?>
    </div>
    <p class="text-muted mb-0">Total: <?php echo count($leads); ?></p>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Priority</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php if (empty($leads)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No leads yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($leads as $r): ?>
        <tr>
            <td><?php echo e($r['name']); ?></td>
            <td><a href="mailto:<?php echo e($r['email']); ?>"><?php echo e($r['email']); ?></a></td>
            <td><?php echo e($r['subject'] ?: '—'); ?></td>
            <td><?php echo status_badge($r['status']); ?></td>
            <td>
                <?php
                $pmap = ['low' => 'success', 'medium' => 'warning text-dark', 'high' => 'danger'];
                $cls = $pmap[$r['priority']] ?? 'secondary';
                ?>
                <span class="badge bg-<?php echo $cls; ?>"><span class="visually-hidden">Priority: </span><?php echo e(ucfirst($r['priority'])); ?></span>
            </td>
            <td><?php echo e(formatDate($r['created_at'], 'M d, Y')); ?></td>
            <td class="text-end">
                <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary icon-btn" href="<?php echo url('/admin/leads?action=view&id=' . (int)$r['id']); ?>" aria-label="View lead from <?php echo e($r['name']); ?>"><i class="fas fa-eye" aria-hidden="true"></i></a>
                    <a class="btn btn-sm btn-outline-danger icon-btn"
                       href="<?php echo url('/admin/leads?action=delete&id=' . (int)$r['id'] . '&csrf=' . urlencode($csrf)); ?>"
                       aria-label="Delete lead from <?php echo e($r['name']); ?>"
                       onclick="return confirm('Delete this lead?');"><i class="fas fa-trash" aria-hidden="true"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php
admin_layout_end();
