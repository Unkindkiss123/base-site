<?php
/**
 * sitemap.php — dynamic sitemap generator (served at /sitemap.xml)
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$base = rtrim(APP_URL, '/');
$today = date('Y-m-d');
$urls = [
    ['/', '1.0', 'weekly'],
    ['/about', '0.8', 'monthly'],
    ['/services', '0.8', 'weekly'],
    ['/portfolio', '0.7', 'weekly'],
    ['/blog', '0.7', 'daily'],
    ['/contact', '0.7', 'monthly'],
    ['/privacy-policy', '0.5', 'yearly'],
];

// Append published blog posts
try {
    $db = Database::getInstance();
    $db->prepare("SELECT slug, updated_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC LIMIT 1000");
    foreach ($db->fetchAll() as $row) {
        $urls[] = ['/blog/' . $row['slug'], '0.6', 'weekly'];
    }
} catch (Throwable $e) { /* DB not ready */ }

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$path, $prio, $freq]) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base . $path, ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo "    <lastmod>$today</lastmod>\n";
    echo "    <changefreq>$freq</changefreq>\n";
    echo "    <priority>$prio</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
