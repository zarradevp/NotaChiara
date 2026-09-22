<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$ncPage = 'privacy';
$ncTitle = 'Privacy — NotaChiara';
$ncDescription = 'Come NotaChiara tratta i dati inseriti nella demo di portfolio.';

require __DIR__ . '/includes/header.php';
?>

<main id="contenuto" class="nc-page nc-page--narrow">
  <header class="nc-page__intro">
    <p class="nc-kicker">Informativa breve</p>
    <h1>Privacy</h1>
  </header>

  <article class="nc-prose">
    <p>NotaChiara è un progetto di portfolio. Non è un servizio commerciale e non chiede registrazione.</p>
    <p>Le note che inserisci nella demo restano sul server dove gira il sito, in un file locale, per mostrare archivio e pagina stato. Non vengono vendute né usate per profilazione.</p>
    <p>La riscrittura dei messaggi avviene in PHP su questo stesso sito: in questa versione non partono richieste verso servizi di intelligenza artificiale esterni.</p>
    <p>Non usare dati personali reali di colleghi o clienti. Per una prova va benissimo una nota inventata o già anonimizzata.</p>
    <p><a href="index.php">Torna al prodotto</a></p>
  </article>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
