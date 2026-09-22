<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Rome');

const NC_SITE_NAME = 'NotaChiara';
const NC_SITE_TAGLINE = 'Dalla nota tecnica al messaggio che l’utente capisce.';
const NC_DATA_FILE = __DIR__ . '/data/messages.json';
const NC_SEED_FILE = __DIR__ . '/data/messages.seed.json';
const NC_MAX_NOTE_LENGTH = 4000;
const NC_MAX_SERVICE_LENGTH = 80;
const NC_MAX_ARCHIVE = 40;
const NC_SEVERITIES = ['down', 'degradato', 'operativo'];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
