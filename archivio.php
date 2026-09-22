<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$messages = nc_read_messages();
$filter = (string) ($_GET['gravita'] ?? '');

if ($filter !== '' && in_array($filter, NC_SEVERITIES, true)) {
    $messages = array_values(array_filter(
        $messages,
        static fn (array $row): bool => ($row['severity'] ?? '') === $filter
    ));
} else {
    $filter = '';
}

$ncPage = 'archivio';
$ncTitle = 'Archivio — NotaChiara';
$ncDescription = 'Storico delle comunicazioni generate da note tecniche, con filtro per gravità.';

require __DIR__ . '/includes/header.php';
?>

<main id="contenuto" class="nc-page">
  <header class="nc-page__intro" data-reveal>
    <p class="nc-kicker">Storico</p>
    <h1>Archivio comunicazioni</h1>
    <p class="nc-lead">Le ultime note trasformate. Utile in colloquio: mostri il pezzo “AMS” — traccia, gravità, messaggio utente.</p>
  </header>

  <form class="nc-filter" method="get" action="archivio.php">
    <label for="gravita">Filtra per gravità</label>
    <select id="gravita" name="gravita" onchange="this.form.submit()">
      <option value="" <?= $filter === '' ? 'selected' : '' ?>>Tutte</option>
      <?php foreach (NC_SEVERITIES as $severity): ?>
        <option value="<?= nc_e($severity) ?>" <?= $filter === $severity ? 'selected' : '' ?>><?= nc_e(nc_severity_label($severity)) ?></option>
      <?php endforeach; ?>
    </select>
    <noscript><button class="nc-btn nc-btn--small" type="submit">Applica</button></noscript>
  </form>

  <?php if ($messages === []): ?>
    <p class="nc-empty">Nessun messaggio in questo filtro. <a href="demo.php">Generane uno dalla demo</a>.</p>
  <?php else: ?>
    <ol class="nc-archive">
      <?php foreach ($messages as $row): ?>
        <?php $severity = (string) ($row['severity'] ?? 'operativo'); ?>
        <li class="nc-archive__item">
          <header>
            <h2><?= nc_e((string) $row['service']) ?></h2>
            <p>
              <span class="nc-badge nc-badge--<?= nc_e($severity) ?>"><?= nc_e(nc_severity_label($severity)) ?></span>
              <time datetime="<?= nc_e((string) $row['created_at']) ?>"><?= nc_e(nc_format_datetime((string) $row['created_at'])) ?></time>
            </p>
          </header>
          <p><?= nc_e(nc_excerpt((string) ($row['user_message'] ?? ''), 220)) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
