# NotaChiara

Progetto di portfolio: trasforma una nota tecnica (ticket, SSH, monitoring) in tre testi pronti — messaggio per gli utenti, riassunto da attaccare al ticket, riga per la pagina stato.

Niente account, niente framework, niente API esterne. HTML, CSS, JavaScript e PHP.

## Cosa fa

Incolli servizio, gravità (`down`, `degradato`, `operativo`) e il testo così come l’hai scritto. NotaChiara toglie hostname e gergo e genera:

- **Utenti** — linguaggio piano, cosa sta succedendo, cosa fare
- **Ticket interno** — impatto, segnali, azioni consigliate, next step
- **Pagina stato** — una frase pubblica da aggiornare a ogni cambio

Ogni generazione resta in archivio e aggiorna lo stato del servizio, come un piccolo AMS.

## Pagine

| File | Contenuto |
| --- | --- |
| `index.php` | Prodotto e esempio prima / dopo |
| `demo.php` | Form interattivo, esempi, copia dei tre output |
| `stato.php` | Pagina stato pubblica dei servizi |
| `archivio.php` | Storico delle comunicazioni generate |
| `privacy.php` | Informativa breve |

Tema chiaro / scuro dal pulsante in header. La preferenza resta nel browser.

## Avvio in locale

Serve **PHP 8.1+** (provato con 8.4).

```bash
php -S localhost:8000 router.php
```

Poi apri [http://localhost:8000](http://localhost:8000).

`router.php` è solo per il server di sviluppo: blocca l’accesso a `/data/`. Su Apache vale `.htaccess` (`Options -Indexes` e JSON non esposti).

## Dati

Le note della demo stanno in `data/messages.json` (file locale, non versionato). Al primo avvio viene copiato da `data/messages.seed.json`.

Non inserire dati personali reali: è una demo di portfolio, la riscrittura avviene tutta in PHP su questo stesso sito.

## Hosting

GitHub Pages **non** è adatto: non esegue PHP. Serve un hosting con PHP (shared classico, Railway, Render, Fly.io o un VPS). In produzione la document root è la cartella del progetto; `data/` deve essere scrivibile e non esposta pubblicamente.
