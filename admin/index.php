<?php
/**
 * admin/index.php — Admin Dashboard
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth();

$db = Database::getInstance();

$db->prepare('SELECT COUNT(*) AS c FROM users');         $userCount = (int)($db->fetch()['c'] ?? 0);
$db->prepare("SELECT COUNT(*) AS c FROM blog_posts WHERE status='published'"); $postCount = (int)($db->fetch()['c'] ?? 0);
$db->prepare("SELECT COUNT(*) AS c FROM pages WHERE status='published'");      $pageCount = (int)($db->fetch()['c'] ?? 0);
$db->prepare("SELECT COUNT(*) AS c FROM leads WHERE status='new'");            $leadCount = (int)($db->fetch()['c'] ?? 0);

$db->prepare('SELECT id, name, email, subject, status, created_at FROM leads ORDER BY created_at DESC LIMIT 5');
$recentLeads = $db->fetchAll();

admin_layout_start('Dashboard', 'dashboard');
?>
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total Users', $userCount, 'fa-users', '/admin/users'],
        ['Blog Posts',  $postCount, 'fa-blog',  '/admin/blog'],
        ['Pages',       $pageCount, 'fa-file',  '/admin/pages'],
        ['New Leads',   $leadCount, 'fa-envelope', '/admin/leads'],
    ];
    foreach ($cards as [$label, $count, $icon, $path]): ?>
        <div class="col-sm-6 col-lg-3">
            <a class="text-decoration-none" href="<?php echo url($path); ?>" aria-label="<?php echo e($label . ', ' . $count); ?>">
                <div class="stat-card d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size:2rem;font-weight:700;color:var(--brand-primary);"><?php echo (int)$count; ?></div>
                        <div class="stat-label text-muted"><?php echo e($label); ?></div>
                    </div>
                    <i class="fas <?php echo $icon; ?>" aria-hidden="true" style="font-size:2rem;color:var(--brand-primary);opacity:.25;"></i>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Recent leads</h2>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo url('/admin/leads'); ?>">View all</a>
            </div>
            <?php if (empty($recentLeads)): ?>
                <p class="text-muted mb-0">No leads yet.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                <?php foreach ($recentLeads as $l): ?>
                    <li class="d-flex justify-content-between border-bottom py-2">
                        <span>
                            <strong><?php echo e($l['name']); ?></strong>
                            — <span class="text-muted"><?php echo e($l['subject'] ?: 'No subject'); ?></span>
                        </span>
                        <span class="text-muted small"><?php echo e(formatDate($l['created_at'], 'M d')); ?> · <?php echo status_badge($l['status']); ?></span>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card"><div class="card-body">
            <h2 class="h5 mb-3">Quick actions</h2>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="<?php echo url('/admin/pages?action=new'); ?>"><i class="fas fa-file" aria-hidden="true"></i> <span>New page</span></a>
                <a class="btn btn-primary" href="<?php echo url('/admin/blog?action=new'); ?>"><i class="fas fa-blog" aria-hidden="true"></i> <span>New post</span></a>
                <a class="btn btn-primary" href="<?php echo url('/admin/services?action=new'); ?>"><i class="fas fa-briefcase" aria-hidden="true"></i> <span>New service</span></a>
                <a class="btn btn-outline-primary" href="<?php echo url('/admin/settings'); ?>"><i class="fas fa-cog" aria-hidden="true"></i> <span>Settings</span></a>
            </div>
        </div></div>
    </div>
</div>
<?php
admin_layout_end();
