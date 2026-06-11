<?php
/**
 * admin/blog.php — CRUD for blog posts
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_blog');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

$db->prepare('SELECT id, name FROM categories WHERE is_active=1 ORDER BY display_order');
$catRows = $db->fetchAll();
$categories = [0 => '— Uncategorized —'];
foreach ($catRows as $c) { $categories[$c['id']] = $c['name']; }

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.'); redirect(url('/admin/blog'));
    }
    $title       = trim((string)post('title', ''));
    $slug        = trim((string)post('slug', '')) ?: slug($title);
    $excerpt     = trim((string)post('excerpt', ''));
    $content     = (string)post('content', '');
    $categoryId  = ((int)post('category_id', 0)) ?: null;
    $status      = post('status', 'draft');
    $editingId   = (int)post('id', 0);
    $u           = getUser();
    $authorId    = (int)($u['id'] ?? 0);

    if ($title === '' || $content === '') {
        flash('danger', 'Title and content are required.');
        redirect(url('/admin/blog?action=' . ($editingId ? 'edit&id=' . $editingId : 'new')));
    }

    if ($editingId > 0) {
        $db->prepare('UPDATE blog_posts SET title=:title, slug=:slug, excerpt=:excerpt, content=:content, category_id=:category_id, status=:status, published_at=CASE WHEN :status2="published" AND published_at IS NULL THEN NOW() ELSE published_at END WHERE id=:id');
        $db->bind(':title', $title);
        $db->bind(':slug', $slug);
        $db->bind(':excerpt', $excerpt);
        $db->bind(':content', $content);
        $db->bind(':category_id', $categoryId);
        $db->bind(':status', $status);
        $db->bind(':status2', $status);
        $db->bind(':id', $editingId);
        $db->execute();
        flash('success', 'Post updated.');
    } else {
        $db->prepare('INSERT INTO blog_posts (title, slug, excerpt, content, category_id, author_id, status, published_at) VALUES (:title,:slug,:excerpt,:content,:category_id,:author_id,:status, CASE WHEN :status2="published" THEN NOW() ELSE NULL END)');
        $db->bind(':title', $title);
        $db->bind(':slug', $slug);
        $db->bind(':excerpt', $excerpt);
        $db->bind(':content', $content);
        $db->bind(':category_id', $categoryId);
        $db->bind(':author_id', $authorId);
        $db->bind(':status', $status);
        $db->bind(':status2', $status);
        $db->execute();
        flash('success', 'Post created.');
    }
    redirect(url('/admin/blog'));
}

if ($action === 'delete' && $id > 0) {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) { flash('danger', 'Invalid request token.'); redirect(url('/admin/blog')); }
    $db->prepare('DELETE FROM blog_posts WHERE id=:id'); $db->bind(':id', $id); $db->execute();
    flash('success', 'Post deleted.');
    redirect(url('/admin/blog'));
}

if ($action === 'edit' && $id > 0) {
    $db->prepare('SELECT * FROM blog_posts WHERE id=:id'); $db->bind(':id', $id);
    $p = $db->fetch();
    if (!$p) { flash('danger', 'Post not found.'); redirect(url('/admin/blog')); }
    admin_layout_start('Edit Blog Post', 'blog');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
        <?php
        admin_field('Title', 'title', $p['title'], 'text', ['required' => true]);
        admin_field('Slug', 'slug', $p['slug'], 'text', ['help' => 'Leave blank to auto-generate']);
        admin_field('Excerpt', 'excerpt', $p['excerpt'] ?? '', 'textarea', ['rows' => 2]);
        admin_field('Content', 'content', $p['content'], 'textarea', ['rows' => 14, 'required' => true]);
        admin_field('Category', 'category_id', (int)($p['category_id'] ?? 0), 'select', ['options' => $categories]);
        admin_field('Status', 'status', $p['status'], 'select', ['options' => ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'], 'required' => true]);
        ?>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> <span>Save changes</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/blog'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end(admin_wysiwyg_scripts()); return;
}

if ($action === 'new') {
    admin_layout_start('New Blog Post', 'blog');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <?php
        admin_field('Title', 'title', '', 'text', ['required' => true]);
        admin_field('Slug', 'slug', '', 'text', ['help' => 'Leave blank to auto-generate']);
        admin_field('Excerpt', 'excerpt', '', 'textarea', ['rows' => 2]);
        admin_field('Content', 'content', '', 'textarea', ['rows' => 14, 'required' => true]);
        admin_field('Category', 'category_id', 0, 'select', ['options' => $categories]);
        admin_field('Status', 'status', 'draft', 'select', ['options' => ['draft' => 'Draft', 'published' => 'Published'], 'required' => true]);
        ?>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> <span>Create post</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/blog'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end(admin_wysiwyg_scripts()); return;
}

$db->prepare('SELECT bp.*, u.first_name, u.last_name, c.name AS category_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id=u.id LEFT JOIN categories c ON bp.category_id=c.id ORDER BY bp.updated_at DESC');
$rows = $db->fetchAll();
$csrf = Security::generateCSRFToken();

admin_layout_start('Blog Posts', 'blog');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Total: <?php echo count($rows); ?></p>
    <a class="btn btn-primary" href="<?php echo url('/admin/blog?action=new'); ?>">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New post</span>
    </a>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Views</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo e($r['title']); ?></td>
            <td><?php echo e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: '—'); ?></td>
            <td><?php echo e($r['category_name'] ?? '—'); ?></td>
            <td><?php echo status_badge($r['status']); ?></td>
            <td><?php echo (int)$r['view_count']; ?></td>
            <td><?php echo e(formatDate($r['updated_at'], 'M d, Y')); ?></td>
            <td class="text-end">
                <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary icon-btn" href="<?php echo url('/admin/blog?action=edit&id=' . (int)$r['id']); ?>" aria-label="Edit post <?php echo e($r['title']); ?>"><i class="fas fa-pen" aria-hidden="true"></i></a>
                    <a class="btn btn-sm btn-outline-danger icon-btn"
                       href="<?php echo url('/admin/blog?action=delete&id=' . (int)$r['id'] . '&csrf=' . urlencode($csrf)); ?>"
                       aria-label="Delete post <?php echo e($r['title']); ?>"
                       onclick="return confirm('Delete this post?');"><i class="fas fa-trash" aria-hidden="true"></i></a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php
admin_layout_end();
