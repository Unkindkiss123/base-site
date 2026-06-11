<?php
/**
 * admin/gallery.php — CRUD for the gallery (drives /portfolio)
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_gallery');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.'); redirect(url('/admin/gallery'));
    }
    $title       = trim((string)post('title', ''));
    $description = trim((string)post('description', ''));
    $image       = trim((string)post('image', ''));
    $altText     = trim((string)post('alt_text', '')) ?: $title;
    $displayOrd  = (int)post('display_order', 0);
    $isActive    = (int)((bool)post('is_active', 0));
    $editingId   = (int)post('id', 0);

    if ($title === '' || $image === '') {
        flash('danger', 'Title and image URL are required.');
        redirect(url('/admin/gallery?action=' . ($editingId ? 'edit&id=' . $editingId : 'new')));
    }
    if ($editingId > 0) {
        $db->prepare('UPDATE gallery SET title=:title, description=:description, image=:image, alt_text=:alt_text, display_order=:display_order, is_active=:is_active WHERE id=:id');
        $db->bind(':id', $editingId);
    } else {
        $db->prepare('INSERT INTO gallery (title, description, image, alt_text, display_order, is_active) VALUES (:title,:description,:image,:alt_text,:display_order,:is_active)');
    }
    $db->bind(':title', $title);
    $db->bind(':description', $description);
    $db->bind(':image', $image);
    $db->bind(':alt_text', $altText);
    $db->bind(':display_order', $displayOrd);
    $db->bind(':is_active', $isActive);
    $db->execute();
    flash('success', $editingId ? 'Gallery item updated.' : 'Gallery item created.');
    redirect(url('/admin/gallery'));
}

if ($action === 'delete' && $id > 0) {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) { flash('danger', 'Invalid request token.'); redirect(url('/admin/gallery')); }
    $db->prepare('DELETE FROM gallery WHERE id=:id'); $db->bind(':id', $id); $db->execute();
    flash('success', 'Gallery item deleted.');
    redirect(url('/admin/gallery'));
}

if (in_array($action, ['edit', 'new'], true)) {
    $g = ['id' => 0, 'title' => '', 'description' => '', 'image' => '', 'alt_text' => '', 'display_order' => 0, 'is_active' => 1];
    if ($action === 'edit' && $id > 0) {
        $db->prepare('SELECT * FROM gallery WHERE id=:id'); $db->bind(':id', $id);
        $g = $db->fetch();
        if (!$g) { flash('danger', 'Item not found.'); redirect(url('/admin/gallery')); }
    }
    admin_layout_start($action === 'new' ? 'New Gallery Item' : 'Edit Gallery Item', 'gallery');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <?php if ((int)$g['id'] > 0): ?>
            <input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
        <?php endif; ?>
        <?php
        admin_field('Title', 'title', $g['title'], 'text', ['required' => true]);
        admin_field('Description', 'description', $g['description'] ?? '', 'textarea', ['rows' => 3]);
        admin_field('Image URL', 'image', $g['image'], 'url', ['required' => true, 'help' => 'Full URL or absolute path like /assets/images/portfolio-1.jpg']);
        admin_field('Alt text', 'alt_text', $g['alt_text'] ?? '', 'text', ['help' => 'Defaults to title if blank (used by screen readers)']);
        admin_field('Display order', 'display_order', (int)$g['display_order'], 'number');
        ?>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo $g['is_active'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_active">Visible</label>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> <span>Save</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/gallery'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end(); return;
}

$db->prepare('SELECT * FROM gallery ORDER BY display_order, id');
$rows = $db->fetchAll();
$csrf = Security::generateCSRFToken();

admin_layout_start('Gallery', 'gallery');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Total: <?php echo count($rows); ?></p>
    <a class="btn btn-primary" href="<?php echo url('/admin/gallery?action=new'); ?>">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New item</span>
    </a>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Order</th><th>Preview</th><th>Title</th><th>Visible</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php if (empty($rows)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No gallery items yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['display_order']; ?></td>
            <td><img src="<?php echo e($r['image']); ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:4px;" loading="lazy"></td>
            <td><?php echo e($r['title']); ?></td>
            <td><?php echo $r['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
            <td class="text-end">
                <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary icon-btn" href="<?php echo url('/admin/gallery?action=edit&id=' . (int)$r['id']); ?>" aria-label="Edit gallery item <?php echo e($r['title']); ?>"><i class="fas fa-pen" aria-hidden="true"></i></a>
                    <a class="btn btn-sm btn-outline-danger icon-btn"
                       href="<?php echo url('/admin/gallery?action=delete&id=' . (int)$r['id'] . '&csrf=' . urlencode($csrf)); ?>"
                       aria-label="Delete gallery item <?php echo e($r['title']); ?>"
                       onclick="return confirm('Delete this item?');"><i class="fas fa-trash" aria-hidden="true"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php
admin_layout_end();
