<?php
// Backwards-compatible alias
header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/tracking.php' . (isset($_GET['id']) ? '?id=' . urlencode($_GET['id']) : ''));
exit;
