<?php
/**
 * admin/services.php — CRUD for services (drives /services public page)
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_services');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.'); redirect(url('/admin/services'));
    }
    $title       = trim((string)post('title', ''));
    $slug        = trim((string)post('slug', '')) ?: slug($title);
    $description = (string)post('description', '');
    $icon        = trim((string)post('icon', 'fa-cube'));
    $isActive    = (int)((bool)post('is_active', 0));
    $isFeatured  = (int)((bool)post('is_featured', 0));
    $displayOrd  = (int)post('display_order', 0);
    $editingId   = (int)post('id', 0);

    if ($title === '' || $description === '') {
        flash('danger', 'Title and description are required.');
        redirect(url('/admin/services?action=' . ($editingId ? 'edit&id=' . $editingId : 'new')));
    }
    if ($editingId > 0) {
        $db->prepare('UPDATE services SET title=:title, slug=:slug, description=:description, icon=:icon, is_active=:is_active, is_featured=:is_featured, display_order=:display_order WHERE id=:id');
        $db->bind(':id', $editingId);
    } else {
        $db->prepare('INSERT INTO services (title, slug, description, icon, is_active, is_featured, display_order) VALUES (:title,:slug,:description,:icon,:is_active,:is_featured,:display_order)');
    }
    $db->bind(':title', $title);
    $db->bind(':slug', $slug);
    $db->bind(':description', $description);
    $db->bind(':icon', $icon);
    $db->bind(':is_active', $isActive);
    $db->bind(':is_featured', $isFeatured);
    $db->bind(':display_order', $displayOrd);
    if ($db->execute()) {
        flash('success', $editingId ? 'Service updated.' : 'Service created.');
    } else {
        flash('danger', 'Save failed.');
    }
    redirect(url('/admin/services'));
}

if ($action === 'delete' && $id > 0) {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) { flash('danger', 'Invalid request token.'); redirect(url('/admin/services')); }
    $db->prepare('DELETE FROM services WHERE id=:id'); $db->bind(':id', $id); $db->execute();
    flash('success', 'Service deleted.');
    redirect(url('/admin/services'));
}

if (in_array($action, ['edit', 'new'], true)) {
    $svc = [
        'id' => 0, 'title' => '', 'slug' => '', 'description' => '', 'icon' => 'fa-cube',
        'is_active' => 1, 'is_featured' => 0, 'display_order' => 0,
    ];
    if ($action === 'edit' && $id > 0) {
        $db->prepare('SELECT * FROM services WHERE id=:id'); $db->bind(':id', $id);
        $svc = $db->fetch();
        if (!$svc) { flash('danger', 'Service not found.'); redirect(url('/admin/services')); }
    }
    admin_layout_start($action === 'new' ? 'New Service' : 'Edit Service', 'services');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <?php if ((int)$svc['id'] > 0): ?>
            <input type="hidden" name="id" value="<?php echo (int)$svc['id']; ?>">
        <?php endif; ?>
        <?php
        admin_field('Title', 'title', $svc['title'], 'text', ['required' => true]);
        admin_field('Slug', 'slug', $svc['slug'], 'text', ['help' => 'Leave blank to auto-generate']);
        admin_field('Description', 'description', $svc['description'], 'textarea', ['rows' => 6, 'required' => true]);
        admin_field('Icon (Font Awesome class)', 'icon', $svc['icon'], 'text', ['help' => 'e.g. fa-paint-brush, fa-code, fa-mobile-alt']);
        admin_field('Display order', 'display_order', (int)$svc['display_order'], 'number');
        ?>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo $svc['is_active'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $svc['is_featured'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_featured">Featured</label>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> <span>Save</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/services'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end(); return;
}

$db->prepare('SELECT * FROM services ORDER BY display_order, id');
$rows = $db->fetchAll();
$csrf = Security::generateCSRFToken();

admin_layout_start('Services', 'services');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Total: <?php echo count($rows); ?></p>
    <a class="btn btn-primary" href="<?php echo url('/admin/services?action=new'); ?>">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New service</span>
    </a>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Order</th><th>Icon</th><th>Title</th><th>Active</th><th>Featured</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No services yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['display_order']; ?></td>
            <td><i class="fas <?php echo e($r['icon'] ?: 'fa-cube'); ?>" aria-hidden="true"></i> <code class="text-muted small"><?php echo e($r['icon']); ?></code></td>
            <td><?php echo e($r['title']); ?></td>
            <td><?php echo $r['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
            <td><?php echo $r['is_featured'] ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-light text-dark">No</span>'; ?></td>
            <td class="text-end">
                <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary icon-btn" href="<?php echo url('/admin/services?action=edit&id=' . (int)$r['id']); ?>" aria-label="Edit service <?php echo e($r['title']); ?>"><i class="fas fa-pen" aria-hidden="true"></i></a>
                    <a class="btn btn-sm btn-outline-danger icon-btn"
                       href="<?php echo url('/admin/services?action=delete&id=' . (int)$r['id'] . '&csrf=' . urlencode($csrf)); ?>"
                       aria-label="Delete service <?php echo e($r['title']); ?>"
                       onclick="return confirm('Delete this service?');"><i class="fas fa-trash" aria-hidden="true"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php
admin_layout_end();
