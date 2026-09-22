<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$errors = [];
$old = [
    'service' => '',
    'severity' => 'degradato',
    'note' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validated = nc_validate_demo($_POST);
    $errors = $validated['errors'];
    $old = [
        'service' => $validated['service'],
        'severity' => in_array($validated['severity'], NC_SEVERITIES, true) ? $validated['severity'] : 'degradato',
        'note' => $validated['note'],
    ];

    if ($errors === []) {
        $generated = nc_generate($old['service'], $old['severity'], $old['note']);
        $record = [
            'id' => 'nc_' . bin2hex(random_bytes(6)),
            'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
            'service' => $old['service'],
            'severity' => $old['severity'],
            'note' => $old['note'],
            'user_message' => $generated['user_message'],
            'ticket_summary' => $generated['ticket_summary'],
            'status_update' => $generated['status_update'],
            'source' => 'demo',
        ];

        nc_save_message($record);
        $_SESSION['nc_last'] = $record;
        header('Location: demo.php?ok=1', true, 303);
        exit;
    }
}

$result = null;

if (isset($_GET['ok']) && !empty($_SESSION['nc_last']) && is_array($_SESSION['nc_last'])) {
    $result = $_SESSION['nc_last'];
    $old['service'] = (string) ($result['service'] ?? $old['service']);
    $old['severity'] = (string) ($result['severity'] ?? $old['severity']);
    $old['note'] = (string) ($result['note'] ?? $old['note']);
}

$ncPage = 'demo';
$ncTitle = 'Demo — NotaChiara';
$ncDescription = 'Incolla una nota tecnica e ottieni il messaggio per gli utenti, il riassunto da ticket e l’aggiornamento di stato.';

require __DIR__ . '/includes/header.php';
?>

<main id="contenuto" class="nc-page nc-page--demo">
  <header class="nc-demo-hero">
    <p class="nc-kicker">Demo interattiva</p>
    <h1>Dalla nota ai tre messaggi.</h1>
    <p class="nc-lead">Scegli un esempio o incolla il testo. NotaChiara prepara utenti, ticket e pagina stato — senza account.</p>
  </header>

  <div class="nc-examples" role="group" aria-label="Parti da un esempio">
    <button type="button" class="nc-example" data-example="disco">
      <span class="nc-example__meta nc-example__meta--degradato">Degradato</span>
      <strong>Disco pieno</strong>
      <span class="nc-example__note">/var al 93% · timeout su /login</span>
    </button>
    <button type="button" class="nc-example" data-example="vpn">
      <span class="nc-example__meta nc-example__meta--down">Down</span>
      <strong>VPN giù</strong>
      <span class="nc-example__note">Tunnel IPSec down · handshake fallito</span>
    </button>
    <button type="button" class="nc-example" data-example="posta">
      <span class="nc-example__meta nc-example__meta--degradato">Degradato</span>
      <strong>Coda SMTP</strong>
      <span class="nc-example__note">8000 messaggi in retry · nessuna perdita</span>
    </button>
  </div>

  <?php if ($errors !== []): ?>
    <div class="nc-alert nc-alert--error" role="alert">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= nc_e($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($result !== null): ?>
    <p class="nc-alert nc-alert--ok" role="status">Messaggi salvati. Li trovi in <a href="archivio.php">archivio</a> e in <a href="stato.php">stato servizi</a>.</p>
  <?php endif; ?>

  <div class="nc-workspace">
    <form class="nc-form nc-composer" method="post" action="demo.php">
      <input type="hidden" name="csrf" value="<?= nc_e(nc_csrf_token()) ?>">

      <div class="nc-composer__bar">
        <div class="nc-composer__service">
          <label for="service">Servizio</label>
          <input id="service" name="service" type="text" maxlength="<?= NC_MAX_SERVICE_LENGTH ?>" required autocomplete="off" list="servizi-noti" value="<?= nc_e($old['service']) ?>" placeholder="Es. Portale dipendenti">
          <datalist id="servizi-noti">
            <?php foreach (nc_known_services() as $serviceName): ?>
              <option value="<?= nc_e($serviceName) ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </div>

        <fieldset class="nc-seg">
          <legend class="nc-visually-hidden">Gravità</legend>
          <?php foreach (NC_SEVERITIES as $severity): ?>
            <label class="nc-seg__item nc-seg__item--<?= nc_e($severity) ?>">
              <input type="radio" name="severity" value="<?= nc_e($severity) ?>" <?= $old['severity'] === $severity ? 'checked' : '' ?>>
              <span><?= nc_e(nc_severity_label($severity)) ?></span>
            </label>
          <?php endforeach; ?>
        </fieldset>
      </div>

      <label class="nc-composer__note-label" for="note">Nota tecnica</label>
      <textarea class="nc-composer__note" id="note" name="note" rows="9" maxlength="<?= NC_MAX_NOTE_LENGTH ?>" required placeholder="Incolla log, alert o appunti da SSH…"><?= nc_e($old['note']) ?></textarea>

      <div class="nc-composer__foot">
        <p class="nc-field__hint"><span id="note-count">0</span> / <?= NC_MAX_NOTE_LENGTH ?></p>
        <button class="nc-btn nc-btn--primary" type="submit">Genera messaggi</button>
      </div>
    </form>

    <section class="nc-board<?= $result === null ? ' nc-board--idle' : '' ?>" aria-live="polite" aria-label="Output">
      <?php if ($result === null): ?>
        <p class="nc-board__idle-kicker">Anteprima output</p>
        <ul class="nc-board__slots">
          <li>
            <span class="nc-board__index">01</span>
            <div>
              <strong>Utenti</strong>
              <p>Messaggio piano, senza hostname e senza gergo.</p>
            </div>
          </li>
          <li>
            <span class="nc-board__index">02</span>
            <div>
              <strong>Ticket interno</strong>
              <p>Impatto, segnali, azioni e next step da attaccare al ticket.</p>
            </div>
          </li>
          <li>
            <span class="nc-board__index">03</span>
            <div>
              <strong>Pagina stato</strong>
              <p>Una riga pubblica: operativo, degradato o down.</p>
            </div>
          </li>
        </ul>
      <?php else: ?>
        <div class="nc-board__nav">
          <div class="nc-board__tabs" role="tablist" aria-label="Tipo di messaggio">
            <button type="button" class="nc-board__tab is-active" role="tab" id="tab-user" aria-controls="panel-user" aria-selected="true" data-tab="user">Utenti</button>
            <button type="button" class="nc-board__tab" role="tab" id="tab-ticket" aria-controls="panel-ticket" aria-selected="false" data-tab="ticket">Ticket</button>
            <button type="button" class="nc-board__tab" role="tab" id="tab-status" aria-controls="panel-status" aria-selected="false" data-tab="status">Stato</button>
          </div>
          <div class="nc-board__actions">
            <button type="button" class="nc-btn nc-btn--small" data-copy="out-user" data-copy-active>Copia</button>
            <button type="button" class="nc-btn nc-btn--small" data-copy-all>Copia tutti</button>
          </div>
        </div>

        <div class="nc-board__panels">
          <div class="nc-board__panel is-active" role="tabpanel" id="panel-user" aria-labelledby="tab-user" data-panel="user">
            <pre id="out-user"><?= nc_e($result['user_message']) ?></pre>
          </div>
          <div class="nc-board__panel" role="tabpanel" id="panel-ticket" aria-labelledby="tab-ticket" data-panel="ticket" hidden>
            <pre id="out-ticket"><?= nc_e($result['ticket_summary']) ?></pre>
          </div>
          <div class="nc-board__panel" role="tabpanel" id="panel-status" aria-labelledby="tab-status" data-panel="status" hidden>
            <pre id="out-status"><?= nc_e($result['status_update']) ?></pre>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
