<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
$theme = $_COOKIE['speedex_theme'] ?? 'dark';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $theme ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="nhr Courier Service - Fast, Safe, Reliable Delivery All Over Bangladesh">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?>nhr Courier Service</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/extras.css" rel="stylesheet">
</head>
<body>
<div id="appLoader" class="app-loader"><div class="loader-ring"></div><span>Loading nhr…</span></div>
<script>
  (function(){
    function hideLoader(){
      var l = document.getElementById('appLoader');
      if (l) l.classList.add('hidden');
    }
    if (document.readyState === 'complete') {
      setTimeout(hideLoader, 200);
    } else {
      window.addEventListener('load', function(){ setTimeout(hideLoader, 200); });
    }
    // safety fallback
    setTimeout(hideLoader, 2000);
  })();
</script>
