<?php
/**
 * admin/pages.php — CRUD for site pages
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_pages');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.'); redirect(url('/admin/pages'));
    }

    $title       = trim((string)post('title', ''));
    $slug        = trim((string)post('slug', '')) ?: slug($title);
    $excerpt     = trim((string)post('excerpt', ''));
    $content     = (string)post('content', '');
    $status      = post('status', 'draft');
    $editingId   = (int)post('id', 0);
    $u           = getUser();
    $authorId    = (int)($u['id'] ?? 0);

    if ($title === '' || $content === '') {
        flash('danger', 'Title and content are required.');
        redirect(url('/admin/pages?action=' . ($editingId ? 'edit&id=' . $editingId : 'new')));
    }

    if ($editingId > 0) {
        $db->prepare('UPDATE pages SET title=:title, slug=:slug, excerpt=:excerpt, content=:content, status=:status, published_at=CASE WHEN :status2="published" AND published_at IS NULL THEN NOW() ELSE published_at END WHERE id=:id');
        $db->bind(':title', $title);
        $db->bind(':slug', $slug);
        $db->bind(':excerpt', $excerpt);
        $db->bind(':content', $content);
        $db->bind(':status', $status);
        $db->bind(':status2', $status);
        $db->bind(':id', $editingId);
        $db->execute();
        flash('success', 'Page updated.');
    } else {
        $db->prepare('INSERT INTO pages (title, slug, excerpt, content, status, author_id, published_at) VALUES (:title,:slug,:excerpt,:content,:status,:author_id, CASE WHEN :status2="published" THEN NOW() ELSE NULL END)');
        $db->bind(':title', $title);
        $db->bind(':slug', $slug);
        $db->bind(':excerpt', $excerpt);
        $db->bind(':content', $content);
        $db->bind(':status', $status);
        $db->bind(':status2', $status);
        $db->bind(':author_id', $authorId);
        $db->execute();
        flash('success', 'Page created.');
    }
    redirect(url('/admin/pages'));
}

if ($action === 'delete' && $id > 0) {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) { flash('danger', 'Invalid request token.'); redirect(url('/admin/pages')); }
    $db->prepare('DELETE FROM pages WHERE id=:id'); $db->bind(':id', $id); $db->execute();
    flash('success', 'Page deleted.');
    redirect(url('/admin/pages'));
}

if ($action === 'edit' && $id > 0) {
    $db->prepare('SELECT * FROM pages WHERE id=:id'); $db->bind(':id', $id);
    $p = $db->fetch();
    if (!$p) { flash('danger', 'Page not found.'); redirect(url('/admin/pages')); }
    admin_layout_start('Edit Page', 'pages');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
        <?php
        admin_field('Title', 'title', $p['title'], 'text', ['required' => true]);
        admin_field('Slug', 'slug', $p['slug'], 'text', ['help' => 'URL-safe identifier; leave to auto-generate']);
        admin_field('Excerpt', 'excerpt', $p['excerpt'] ?? '', 'textarea', ['rows' => 2]);
        admin_field('Content', 'content', $p['content'], 'textarea', ['rows' => 14, 'required' => true]);
        admin_field('Status', 'status', $p['status'], 'select', ['options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'], 'required' => true]);
        ?>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> <span>Save changes</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/pages'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end(admin_wysiwyg_scripts()); return;
}

if ($action === 'new') {
    admin_layout_start('New Page', 'pages');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <?php
        admin_field('Title', 'title', '', 'text', ['required' => true]);
        admin_field('Slug', 'slug', '', 'text', ['help' => 'Leave blank to auto-generate from the title']);
        admin_field('Excerpt', 'excerpt', '', 'textarea', ['rows' => 2]);
        admin_field('Content', 'content', '', 'textarea', ['rows' => 14, 'required' => true]);
        admin_field('Status', 'status', 'draft', 'select', ['options' => ['draft' => 'Draft', 'published' => 'Published'], 'required' => true]);
        ?>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> <span>Create page</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/pages'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end(admin_wysiwyg_scripts()); return;
}

$db->prepare('SELECT p.*, u.first_name, u.last_name FROM pages p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.updated_at DESC');
$rows = $db->fetchAll();
$csrf = Security::generateCSRFToken();

admin_layout_start('Pages', 'pages');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Total: <?php echo count($rows); ?></p>
    <a class="btn btn-primary" href="<?php echo url('/admin/pages?action=new'); ?>">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New page</span>
    </a>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Title</th><th>Slug</th><th>Author</th><th>Status</th><th>Views</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo e($r['title']); ?></td>
            <td><code><?php echo e($r['slug']); ?></code></td>
            <td><?php echo e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: '—'); ?></td>
            <td><?php echo status_badge($r['status']); ?></td>
            <td><?php echo (int)$r['view_count']; ?></td>
            <td><?php echo e(formatDate($r['updated_at'], 'M d, Y')); ?></td>
            <td class="text-end">
                <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary icon-btn" href="<?php echo url('/admin/pages?action=edit&id=' . (int)$r['id']); ?>" aria-label="Edit page <?php echo e($r['title']); ?>"><i class="fas fa-pen" aria-hidden="true"></i></a>
                    <a class="btn btn-sm btn-outline-danger icon-btn"
                       href="<?php echo url('/admin/pages?action=delete&id=' . (int)$r['id'] . '&csrf=' . urlencode($csrf)); ?>"
                       aria-label="Delete page <?php echo e($r['title']); ?>"
                       onclick="return confirm('Delete this page?');"><i class="fas fa-trash" aria-hidden="true"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php
admin_layout_end();
