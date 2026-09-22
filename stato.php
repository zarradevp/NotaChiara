<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$messages = nc_read_messages();
$services = nc_latest_by_service($messages);

$ncPage = 'stato';
$ncTitle = 'Stato servizi — NotaChiara';
$ncDescription = 'Pagina pubblica con l’ultimo aggiornamento di ogni servizio comunicato tramite NotaChiara.';

require __DIR__ . '/includes/header.php';
?>

<main id="contenuto" class="nc-page">
  <header class="nc-page__intro" data-reveal>
    <p class="nc-kicker">Pagina pubblica</p>
    <h1>Stato dei servizi</h1>
    <p class="nc-lead">Ogni servizio mostra l’ultimo avviso generato. È il pezzo che gli utenti vedrebbero: una riga chiara, non il log della VM.</p>
  </header>

  <?php if ($services === []): ?>
    <p class="nc-empty">Nessun servizio in archivio. <a href="demo.php">Genera il primo avviso</a>.</p>
  <?php else: ?>
    <ul class="nc-status-list">
      <?php foreach ($services as $row): ?>
        <?php
        $severity = (string) ($row['severity'] ?? 'operativo');
        ?>
        <li class="nc-status nc-status--<?= nc_e($severity) ?>">
          <div class="nc-status__meta">
            <h2><?= nc_e((string) $row['service']) ?></h2>
            <p>
              <span class="nc-badge nc-badge--<?= nc_e($severity) ?>"><span class="nc-badge__dot" aria-hidden="true"></span><?= nc_e(nc_severity_label($severity)) ?></span>
              <time datetime="<?= nc_e((string) $row['created_at']) ?>"><?= nc_e(nc_format_datetime((string) $row['created_at'])) ?></time>
            </p>
          </div>
          <p class="nc-status__text"><?= nc_e((string) $row['status_update']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
