<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$ncPage = 'home';
$ncTitle = 'NotaChiara — dalla nota tecnica al messaggio chiaro';
$ncDescription = 'Trasforma appunti da ticket, SSH e monitoring in comunicazioni per gli utenti, per il ticket interno e per la pagina stato.';

require __DIR__ . '/includes/header.php';
?>

<main id="contenuto">
  <section class="nc-hero">
    <div class="nc-hero__copy">
      <p class="nc-kicker"><span class="nc-live" aria-hidden="true"></span> Comunicazione incident · Portfolio</p>
      <h1>Dalla nota tecnica al messaggio che l’utente capisce.</h1>
      <p class="nc-lead">Incolli quello che hai scritto sul ticket o dopo un accesso SSH. NotaChiara toglie il gergo e prepara tre testi pronti: per gli utenti, per il ticket e per la pagina stato.</p>
      <div class="nc-hero__actions">
        <a class="nc-btn nc-btn--primary" href="demo.php">Prova con una nota vera</a>
        <a class="nc-btn nc-btn--ghost" href="stato.php">Vedi lo stato servizi</a>
      </div>
    </div>

    <aside class="nc-compare" aria-label="Esempio prima e dopo">
      <article class="nc-compare__panel nc-compare__panel--before">
        <h2>Nota tecnica</h2>
        <pre><span data-typewriter="before">Alert: disco /var al 93% su vm-web-02.
Apache timeout su /login.
Pulizia log in corso. ETA 40 min.
Non è un problema password.</span><span class="nc-caret" aria-hidden="true"></span></pre>
      </article>
      <p class="nc-compare__flow" aria-hidden="true"><span>Traduce</span></p>
      <article class="nc-compare__panel nc-compare__panel--after" data-after-panel>
        <h2>Messaggio utente</h2>
        <p>Il Portale dipendenti è attivo, ma può risultare più lento in accesso.</p>
        <p>Non dipende dal tuo account: stiamo liberando spazio sui sistemi. Puntiamo a tornare alla normalità in circa 40 minuti.</p>
      </article>
    </aside>
  </section>

  <section class="nc-section" aria-labelledby="come-funziona" data-reveal>
    <h2 id="come-funziona">Tre passi, niente account</h2>
    <ol class="nc-steps">
      <li class="nc-step">
        <h3>Incolli la nota</h3>
        <p>Servizio, gravità e il testo così come l’hai scritto: VM, percentuali, restart e timeout.</p>
      </li>
      <li class="nc-step">
        <h3>Escono tre versioni</h3>
        <p>Messaggio per gli utenti, riassunto da attaccare al ticket e riga per la pagina stato pubblica.</p>
      </li>
      <li class="nc-step">
        <h3>Copi e tieni lo storico</h3>
        <p>Ogni generazione resta in archivio e aggiorna lo stato del servizio, come in un piccolo AMS.</p>
      </li>
    </ol>
  </section>

  <section class="nc-section nc-section--alt" aria-labelledby="per-chi" data-reveal>
    <div class="nc-split">
      <div>
        <h2 id="per-chi">Per chi sta in mezzo tra i sistemi e le persone</h2>
        <p>Chi fa helpdesk o application support passa il tempo a tradurre. L’utente non ha bisogno del nome della VM. Il ticket sì. La pagina stato ha bisogno di una riga sola.</p>
        <p>NotaChiara è quel passaggio, messo in un sito: HTML, CSS, JavaScript e PHP, senza framework.</p>
      </div>
      <ul class="nc-audience">
        <li>Helpdesk di primo livello</li>
        <li>AMS / application support</li>
        <li>Chi pubblica gli avvisi di disservizio</li>
      </ul>
    </div>
  </section>

  <section class="nc-section" aria-labelledby="output" data-reveal>
    <h2 id="output">Cosa ottieni da una sola nota</h2>
    <div class="nc-cards">
      <article class="nc-card">
        <h3>Utenti</h3>
        <p>Linguaggio piano, cosa sta succedendo e cosa fare, senza hostname e senza gergo.</p>
      </article>
      <article class="nc-card">
        <h3>Ticket interno</h3>
        <p>Impatto, segnali rilevati, estratto della nota, azioni consigliate e next step.</p>
      </article>
      <article class="nc-card">
        <h3>Pagina stato</h3>
        <p>Una frase pubblica: operativo, degradato o down, da aggiornare a ogni cambio.</p>
      </article>
    </div>
    <p class="nc-section__cta">
      <a class="nc-btn nc-btn--primary" href="demo.php">Apri la demo</a>
    </p>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
