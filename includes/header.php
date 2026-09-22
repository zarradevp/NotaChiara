<?php

declare(strict_types=1);

$ncPage = $ncPage ?? 'home';
$ncTitle = $ncTitle ?? NC_SITE_NAME;
$ncDescription = $ncDescription ?? NC_SITE_TAGLINE;
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= nc_e($ncTitle) ?></title>
  <meta name="description" content="<?= nc_e($ncDescription) ?>">
  <meta name="theme-color" content="#fffaf6">
  <meta name="color-scheme" content="light dark">
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700&family=Source+Sans+3:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=15">
  <link rel="prefetch" href="index.php">
  <link rel="prefetch" href="demo.php">
  <link rel="prefetch" href="stato.php">
  <link rel="prefetch" href="archivio.php">
  <script>
    document.documentElement.classList.add('js');
    try {
      var dir = sessionStorage.getItem('nc-nav-dir');
      if (dir === 'right' || dir === 'left') {
        document.documentElement.classList.add('nc-enter-pending-' + dir);
        sessionStorage.removeItem('nc-nav-dir');
      }
    } catch (err) {}
    (function () {
      var theme = '';
      try {
        theme = localStorage.getItem('nc-theme') || '';
      } catch (err) {}
      if (theme !== 'dark' && theme !== 'light') {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      }
      document.documentElement.setAttribute('data-theme', theme);
      document.documentElement.style.colorScheme = theme;
      var meta = document.querySelector('meta[name="theme-color"]');
      if (meta) {
        meta.setAttribute('content', theme === 'dark' ? '#141210' : '#fffaf6');
      }
    })();
  </script>
</head>
<body>
  <a class="nc-skip" href="#contenuto">Salta al contenuto</a>

  <header class="nc-header">
    <div class="nc-header__inner">
      <a class="nc-logo" href="index.php">
        <span class="nc-logo__mark" aria-hidden="true">
          <svg viewBox="0 0 32 32" width="22" height="22" focusable="false">
            <path fill="currentColor" d="M9 6h11l4 4v16H9z"/>
            <path fill="#c13d0c" d="M20 6v4h4"/>
            <rect x="12" y="16.4" width="8" height="2" rx="0.5" fill="#fff"/>
          </svg>
        </span>
        <span class="nc-logo__copy">
          <span class="nc-logo__name">NotaChiara</span>
          <span class="nc-logo__meta">Incident comms</span>
        </span>
      </a>

      <nav class="nc-nav" aria-label="Principale">
        <span class="nc-nav__glider" aria-hidden="true"></span>
        <a href="index.php" <?= $ncPage === 'home' ? 'aria-current="page"' : '' ?>>Prodotto</a>
        <a href="demo.php" <?= $ncPage === 'demo' ? 'aria-current="page"' : '' ?>>Demo</a>
        <a href="stato.php" <?= $ncPage === 'stato' ? 'aria-current="page"' : '' ?>>Stato</a>
        <a href="archivio.php" <?= $ncPage === 'archivio' ? 'aria-current="page"' : '' ?>>Archivio</a>
      </nav>

      <div class="nc-header__tools">
        <button
          class="nc-theme"
          type="button"
          data-theme-toggle
          aria-pressed="false"
          aria-label="Attiva modalità notturna"
          title="Modalità notturna"
        >
          <svg class="nc-theme__icon nc-theme__icon--moon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M21 14.5A9 9 0 1 1 9.5 3a7.2 7.2 0 1 0 11.5 11.5z"/>
          </svg>
          <svg class="nc-theme__icon nc-theme__icon--sun" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
            <path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4 7 17M17 7l1.4-1.4"/>
          </svg>
        </button>
        <a class="nc-btn nc-btn--primary nc-header__cta" href="demo.php">Prova la demo</a>
      </div>
    </div>
  </header>
