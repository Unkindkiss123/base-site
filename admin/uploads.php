<?php
/**
 * admin/uploads.php — File upload manager (browse + upload + delete).
 * Files live in /uploads/ at the project root and are served as static assets.
 */
require_once __DIR__ . '/../includes/admin_layout.php';

admin_require_auth();

$uploadsDir = realpath(__DIR__ . '/..') . '/uploads';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0775, true);
}
// Drop a hardening .htaccess so nothing in /uploads can be executed as PHP
$hard = $uploadsDir . '/.htaccess';
if (!file_exists($hard)) {
    @file_put_contents($hard,
        "# Auto-generated. Do not edit.\n" .
        "<FilesMatch \"\\.(php|phtml|phar|cgi|pl|py|sh)$\">\n" .
        "    Require all denied\n" .
        "</FilesMatch>\n" .
        "Options -Indexes\n"
    );
}

$allowedExt = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
               'gif' => 'image/gif',  'webp' => 'image/webp', 'svg' => 'image/svg+xml',
               'pdf' => 'application/pdf'];
$maxSize    = 5 * 1024 * 1024; // 5 MB

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.'); redirect(url('/admin/uploads'));
    }
    if (!empty($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) { flash('danger', 'Upload error (' . (int)$f['error'] . ').'); redirect(url('/admin/uploads')); }
        if ($f['size'] > $maxSize) { flash('danger', 'File exceeds 5 MB limit.'); redirect(url('/admin/uploads')); }

        $orig = (string)$f['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!isset($allowedExt[$ext])) {
            flash('danger', 'Filetype not allowed.'); redirect(url('/admin/uploads'));
        }
        // Verify MIME via finfo, not the client-supplied header
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->file($f['tmp_name']);
        // SVG is text/xml or image/svg+xml depending on libmagic version
        $okMime = ($detected === $allowedExt[$ext])
                || ($ext === 'svg'  && in_array($detected, ['image/svg+xml', 'image/svg', 'text/xml', 'text/plain', 'text/html'], true));
        if (!$okMime) {
            flash('danger', 'File content does not match its extension.'); redirect(url('/admin/uploads'));
        }
        // Generate safe filename
        $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($orig, PATHINFO_FILENAME));
        $base = trim($base, '-') ?: 'file';
        $base = substr($base, 0, 60);
        $name = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (!move_uploaded_file($f['tmp_name'], $dest)) {
            flash('danger', 'Could not save uploaded file.');
        } else {
            @chmod($dest, 0644);
            flash('success', 'Uploaded ' . $name);
        }
    }
    redirect(url('/admin/uploads'));
}

if (($_GET['action'] ?? '') === 'delete') {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) { flash('danger', 'Invalid request token.'); redirect(url('/admin/uploads')); }
    $name = basename((string)($_GET['name'] ?? ''));
    $target = $uploadsDir . '/' . $name;
    if ($name !== '' && $name !== '.htaccess' && is_file($target) && strpos(realpath($target), $uploadsDir) === 0) {
        @unlink($target);
        flash('success', 'Deleted ' . $name);
    } else {
        flash('danger', 'File not found.');
    }
    redirect(url('/admin/uploads'));
}

// List files
$files = [];
foreach (scandir($uploadsDir) ?: [] as $entry) {
    if ($entry[0] === '.') continue;
    $full = $uploadsDir . '/' . $entry;
    if (!is_file($full)) continue;
    $files[] = [
        'name' => $entry,
        'size' => filesize($full),
        'mtime' => filemtime($full),
        'ext' => strtolower(pathinfo($entry, PATHINFO_EXTENSION)),
    ];
}
usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
$csrf = Security::generateCSRFToken();

admin_layout_start('Uploads', 'uploads');
?>
<form method="POST" enctype="multipart/form-data" class="card card-body mb-3">
    <?php echo Security::getCSRFField(); ?>
    <label class="form-label" for="file">Upload a file (JPG, PNG, GIF, WEBP, SVG, PDF · max 5 MB)</label>
    <div class="d-flex gap-2">
        <input class="form-control" type="file" id="file" name="file" required
               accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf">
        <button class="btn btn-primary"><i class="fas fa-upload" aria-hidden="true"></i> <span>Upload</span></button>
    </div>
</form>

<div class="card"><div class="card-body table-responsive">
<?php if (empty($files)): ?>
    <p class="text-muted mb-0">No uploads yet.</p>
<?php else: ?>
<table class="table table-hover align-middle">
    <thead><tr><th>Preview</th><th>Filename</th><th>Size</th><th>Uploaded</th><th>URL</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($files as $f): $url = '/uploads/' . rawurlencode($f['name']); ?>
        <tr>
            <td style="width:80px;">
                <?php if (in_array($f['ext'], ['jpg','jpeg','png','gif','webp','svg'], true)): ?>
                    <img src="<?php echo e($url); ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:4px;" loading="lazy">
                <?php else: ?>
                    <i class="fas fa-file" aria-hidden="true" style="font-size:24px;color:var(--text-muted);"></i>
                <?php endif; ?>
            </td>
            <td><code><?php echo e($f['name']); ?></code></td>
            <td><?php echo number_format($f['size'] / 1024, 1); ?> KB</td>
            <td><?php echo e(date('Y-m-d H:i', $f['mtime'])); ?></td>
            <td>
                <input type="text" class="form-control form-control-sm" readonly value="<?php echo e($url); ?>"
                       onclick="this.select(); navigator.clipboard.writeText(this.value);" aria-label="Copyable URL">
            </td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-danger icon-btn"
                   href="<?php echo url('/admin/uploads?action=delete&name=' . rawurlencode($f['name']) . '&csrf=' . urlencode($csrf)); ?>"
                   aria-label="Delete file <?php echo e($f['name']); ?>"
                   onclick="return confirm('Delete <?php echo e($f['name']); ?>?');">
                    <i class="fas fa-trash" aria-hidden="true"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div></div>
<?php
admin_layout_end();
