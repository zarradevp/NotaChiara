<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function nc_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function nc_len(string $text): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }

    $parts = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

    return is_array($parts) ? count($parts) : strlen($text);
}

function nc_lower(string $text): string
{
    return function_exists('mb_strtolower')
        ? mb_strtolower($text, 'UTF-8')
        : strtolower($text);
}

function nc_clip(string $text, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length, 'UTF-8');
    }

    if ($length < 1) {
        return '';
    }

    if (preg_match('/^.{0,' . $length . '}/us', $text, $match)) {
        return $match[0];
    }

    return $text;
}

function nc_join_it(array $items): string
{
    $items = array_values(array_map(static fn (mixed $item): string => (string) $item, $items));
    $count = count($items);

    if ($count === 0) {
        return '';
    }

    if ($count === 1) {
        return $items[0];
    }

    $last = array_pop($items);

    return implode(', ', $items) . ' e ' . $last;
}

function nc_csrf_token(): string
{
    if (empty($_SESSION['nc_csrf']) || !is_string($_SESSION['nc_csrf'])) {
        $_SESSION['nc_csrf'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['nc_csrf'];
}

function nc_csrf_ok(?string $token): bool
{
    $expected = $_SESSION['nc_csrf'] ?? '';

    return is_string($token) && is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

function nc_ensure_data(): void
{
    $dir = dirname(NC_DATA_FILE);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (!is_file(NC_DATA_FILE) && is_file(NC_SEED_FILE)) {
        copy(NC_SEED_FILE, NC_DATA_FILE);
    }

    if (!is_file(NC_DATA_FILE)) {
        file_put_contents(NC_DATA_FILE, '[]');
    }
}

function nc_read_messages(): array
{
    nc_ensure_data();

    $handle = fopen(NC_DATA_FILE, 'rb');

    if ($handle === false) {
        return [];
    }

    flock($handle, LOCK_SH);
    $raw = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode($raw ?: '[]', true);

    return is_array($data) ? $data : [];
}

function nc_write_messages(array $messages): void
{
    nc_ensure_data();

    $json = json_encode(array_values($messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $handle = fopen(NC_DATA_FILE, 'cb');

    if ($handle === false) {
        throw new RuntimeException('Impossibile scrivere l’archivio.');
    }

    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    fwrite($handle, $json ?: '[]');
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function nc_save_message(array $message): void
{
    $messages = nc_read_messages();
    array_unshift($messages, $message);
    $messages = array_slice($messages, 0, NC_MAX_ARCHIVE);
    nc_write_messages($messages);
}

function nc_severity_label(string $severity): string
{
    return match ($severity) {
        'down' => 'Down',
        'degradato' => 'Degradato',
        'operativo' => 'Operativo',
        default => 'Sconosciuto',
    };
}

function nc_format_datetime(string $iso): string
{
    try {
        $date = new DateTimeImmutable($iso);
    } catch (Exception) {
        return $iso;
    }

    return $date->format('d/m/Y H:i');
}

function nc_latest_by_service(array $messages): array
{
    $latest = [];

    foreach ($messages as $message) {
        $service = $message['service'] ?? '';

        if ($service === '' || isset($latest[$service])) {
            continue;
        }

        $latest[$service] = $message;
    }

    return $latest;
}

function nc_detect_signals(string $note): array
{
    $normalized = nc_lower($note);
    $map = [
        'disco' => ['disco', 'disk', 'filesystem', 'inode', 'spazio disco', '/var', '/home', 'capacity'],
        'memoria' => ['memoria', 'memory', 'ram', 'oom', 'swap'],
        'cpu' => ['cpu', 'load average', 'carico elevato'],
        'rete' => ['vpn', 'rete', 'network', 'dns', 'firewall', 'latenza', 'packet', 'wifi', 'ipsec', 'tunnel'],
        'web' => ['httpd', 'apache', 'nginx', 'iis', '502', '503', '504', 'php-fpm', 'http 5'],
        'database' => ['database', 'mysql', 'postgres', 'sql server', 'oracle', 'mongodb', 'deadlock', 'tablespace'],
        'auth' => ['login', 'autenticazione', 'sso', 'password', 'oauth', 'active directory', 'ldap', 'credenziali'],
        'posta' => ['smtp', 'mailbox', 'exchange', 'outlook', 'posta', 'email', 'postfix', 'mail queue', 'relay'],
        'certificato' => ['ssl', 'tls', 'certificato', 'expired', 'scaduto'],
        'riavvio' => ['restart', 'riavvio', 'reboot', 'service restart'],
        'timeout' => ['timeout', 'timed out', 'non risponde', 'hang'],
        'backup' => ['backup', 'restore', 'snapshot'],
    ];

    $found = [];

    foreach ($map as $signal => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($normalized, $needle)) {
                $found[] = $signal;
                break;
            }
        }
    }

    if (preg_match('/(\d{2,3})\s*%/', $normalized, $match) && (int) $match[1] >= 85) {
        if (!in_array('disco', $found, true)) {
            $found[] = 'disco';
        }
    }

    return $found;
}

function nc_extract_eta(string $note): ?string
{
    if (preg_match('/(\d+)\s*(minuti|minuto|min)\b/iu', $note, $match)) {
        $n = (int) $match[1];
        return $n === 1 ? 'circa 1 minuto' : "circa {$n} minuti";
    }

    if (preg_match('/(\d+)\s*(ore|ora|h)\b/iu', $note, $match)) {
        $n = (int) $match[1];
        return $n === 1 ? 'circa 1 ora' : "circa {$n} ore";
    }

    if (preg_match('/entro\s+le\s+(\d{1,2}[:.]\d{2})/iu', $note, $match)) {
        return 'entro le ' . str_replace('.', ':', $match[1]);
    }

    return null;
}

function nc_cause_sentences(array $signals, string $severity): array
{
    $lines = [];

    foreach ($signals as $signal) {
        $lines[] = match ($signal) {
            'disco' => 'Il rallentamento è legato allo spazio di archiviazione sui sistemi, non al tuo computer.',
            'memoria' => 'I sistemi stanno lavorando con poca memoria disponibile: alcune operazioni possono andare in timeout.',
            'cpu' => 'Il carico sui server è più alto del solito, quindi le pagine possono aprirsi lentamente.',
            'rete' => 'La connessione tra la tua postazione e i sistemi interni è instabile o interrotta.',
            'web' => 'Il sito o il portale risponde in modo irregolare: alcune pagine possono non caricarsi al primo tentativo.',
            'database' => 'L’applicazione non riesce a leggere o salvare i dati in modo puntuale.',
            'auth' => 'Il problema riguarda l’accesso al servizio, non necessariamente la tua password.',
            'posta' => 'L’invio o la ricezione dei messaggi può accumulare ritardo.',
            'certificato' => 'La connessione sicura del servizio va aggiornata: il browser potrebbe mostrare un avviso.',
            'riavvio' => 'È in corso un riavvio controllato dei sistemi.',
            'timeout' => 'Alcune richieste impiegano più tempo del previsto a rispondere.',
            'backup' => 'È in corso un’operazione di copia o ripristino dei dati: il servizio può risultare occupato.',
            default => '',
        };
    }

    $lines = array_values(array_filter($lines));

    if ($lines === []) {
        $lines[] = match ($severity) {
            'down' => 'Il servizio non è raggiungibile in questo momento.',
            'degradato' => 'Il servizio è attivo ma non sta rispondendo in modo regolare.',
            default => 'Il servizio risulta di nuovo utilizzabile.',
        };
    }

    return array_slice($lines, 0, 2);
}

function nc_suggested_actions(array $signals, string $severity): array
{
    $actions = [];

    if ($severity === 'down') {
        $actions[] = 'Non ripetere l’operazione in loop: attendi il ripristino.';
    } else {
        $actions[] = 'Riprova tra qualche minuto, senza inviare più volte lo stesso modulo.';
    }

    if (in_array('rete', $signals, true)) {
        $actions[] = 'Se usi la VPN, disconnettila e collegati di nuovo.';
    } elseif (in_array('auth', $signals, true)) {
        $actions[] = 'Non cambiare la password: il problema è sul servizio di accesso.';
    }

    if (in_array('posta', $signals, true)) {
        $actions[] = 'Controlla posta in uscita prima di reinviare lo stesso messaggio.';
    }

    if ($severity === 'operativo') {
        return ['Puoi tornare a usare il servizio. Se qualcosa non torna, aggiorna la pagina e riprova una volta.'];
    }

    return array_slice($actions, 0, 3);
}

function nc_ticket_actions(array $signals): array
{
    $actions = [];
    $map = [
        'disco' => 'Verificare spazio disco, ruotare i log e liberare i filesystem sopra soglia.',
        'memoria' => 'Controllare processi memory-hungry e, se necessario, aumentare risorse o riavviare il servizio.',
        'cpu' => 'Identificare i processi a carico elevato e valutare uno scaling o un restart mirato.',
        'rete' => 'Verificare tunnel VPN, DNS, firewall e log di autenticazione di rete.',
        'web' => 'Controllare error log del web server, health check e eventuali 5xx.',
        'database' => 'Verificare sessioni, lock, tablespace e connettività verso il database.',
        'auth' => 'Controllare identity provider, certificati SSO e lock-out account di servizio.',
        'posta' => 'Ispezionare la coda SMTP, i log di relay e lo stato del server di posta.',
        'certificato' => 'Verificare scadenza e rinnovo del certificato TLS.',
        'riavvio' => 'Documentare finestra di riavvio e validare i servizi dopo il boot.',
        'timeout' => 'Misurare latenza end-to-end e timeout di applicazione/proxy.',
        'backup' => 'Verificare che job di backup/restore non saturino I/O in orario di servizio.',
    ];

    foreach ($signals as $signal) {
        if (isset($map[$signal])) {
            $actions[] = $map[$signal];
        }
    }

    if ($actions === []) {
        $actions[] = 'Aprire un incident, raccogliere log e comunicare uno stato utente entro 15 minuti.';
    }

    return $actions;
}

function nc_compose_user(string $service, string $severity, array $signals, ?string $eta): string
{
    $opening = match ($severity) {
        'down' => "Il servizio {$service} al momento non è disponibile. Stiamo già lavorando per ripristinarlo.",
        'degradato' => "Il servizio {$service} è attivo, ma in questa fascia può risultare più lento o instabile del solito.",
        default => "Il servizio {$service} è di nuovo operativo.",
    };

    $causes = implode(' ', nc_cause_sentences($signals, $severity));
    $etaLine = $eta !== null
        ? "Aggiornamento previsto {$eta}."
        : 'Comunicheremo un nuovo aggiornamento quando lo stato cambia.';

    if ($severity === 'operativo') {
        $etaLine = 'Puoi riprendere le attività abituali.';
    }

    $actions = nc_suggested_actions($signals, $severity);
    $actionText = 'Cosa puoi fare: ' . implode(' ', $actions);

    return $opening . "\n\n" . $causes . ' ' . $etaLine . "\n\n" . $actionText;
}

function nc_compose_ticket(string $service, string $severity, array $signals, string $note, ?string $eta): string
{
    $impact = match ($severity) {
        'down' => 'Servizio non disponibile per gli utenti.',
        'degradato' => 'Servizio utilizzabile con degrado (lentezza, errori intermittenti o funzionalità parziali).',
        default => 'Servizio ripristinato; da confermare con un giro di verifica.',
    };

    $signalLabel = $signals === [] ? 'nessun segnale automatico (usare la nota originale)' : nc_join_it($signals);
    $excerpt = nc_excerpt($note, 280);
    $actions = nc_ticket_actions($signals);
    $etaLine = $eta !== null ? $eta : 'non dichiarata';

    $lines = [
        "Servizio: {$service}",
        'Gravità: ' . nc_severity_label($severity),
        "Impatto: {$impact}",
        "Segnali rilevati: {$signalLabel}",
        "ETA: {$etaLine}",
        "Nota tecnica (estratto): {$excerpt}",
        'Azioni consigliate:',
    ];

    foreach ($actions as $action) {
        $lines[] = '- ' . $action;
    }

    $lines[] = 'Next step: aggiornare la comunicazione utente a ogni cambio di stato.';

    return implode("\n", $lines);
}

function nc_compose_status(string $service, string $severity, array $signals, ?string $eta): string
{
    $head = match ($severity) {
        'down' => "{$service}: disservizio in corso. Il servizio non è raggiungibile.",
        'degradato' => "{$service}: servizio degradato. Possibili rallentamenti o errori intermittenti.",
        default => "{$service}: servizio operativo.",
    };

    $hint = '';

    if (in_array('auth', $signals, true) && $severity !== 'operativo') {
        $hint = ' Possibili difficoltà in fase di accesso.';
    } elseif (in_array('posta', $signals, true) && $severity !== 'operativo') {
        $hint = ' Possibili ritardi nella posta in arrivo o in uscita.';
    } elseif (in_array('rete', $signals, true) && $severity !== 'operativo') {
        $hint = ' Verificare anche la connessione VPN.';
    }

    $tail = match ($severity) {
        'operativo' => ' Gli utenti possono tornare a usare il servizio.',
        default => $eta !== null
            ? " Intervento in corso, prossimo aggiornamento {$eta}."
            : ' Intervento in corso. Aggiorneremo questa pagina a ogni variazione.',
    };

    return $head . $hint . $tail;
}

function nc_excerpt(string $text, int $max): string
{
    $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

    if (nc_len($text) <= $max) {
        return $text;
    }

    return rtrim(nc_clip($text, $max - 1)) . '…';
}

function nc_generate(string $service, string $severity, string $note): array
{
    $signals = nc_detect_signals($note);
    $eta = nc_extract_eta($note);

    return [
        'user_message' => nc_compose_user($service, $severity, $signals, $eta),
        'ticket_summary' => nc_compose_ticket($service, $severity, $signals, $note, $eta),
        'status_update' => nc_compose_status($service, $severity, $signals, $eta),
        'signals' => $signals,
        'eta' => $eta,
    ];
}

function nc_validate_demo(array $post): array
{
    $errors = [];

    $service = trim((string) ($post['service'] ?? ''));
    $severity = (string) ($post['severity'] ?? '');
    $note = trim((string) ($post['note'] ?? ''));

    if ($service === '') {
        $errors[] = 'Indica il nome del servizio.';
    } elseif (nc_len($service) > NC_MAX_SERVICE_LENGTH) {
        $errors[] = 'Il nome del servizio è troppo lungo.';
    }

    if (!in_array($severity, NC_SEVERITIES, true)) {
        $errors[] = 'Seleziona una gravità valida.';
    }

    if ($note === '') {
        $errors[] = 'Incolla la nota tecnica.';
    } elseif (nc_len($note) > NC_MAX_NOTE_LENGTH) {
        $errors[] = 'La nota supera il limite di caratteri.';
    }

    if (!nc_csrf_ok($post['csrf'] ?? null)) {
        $errors[] = 'Sessione scaduta. Ricarica la pagina e riprova.';
    }

    return [
        'errors' => $errors,
        'service' => $service,
        'severity' => $severity,
        'note' => $note,
    ];
}

function nc_known_services(): array
{
    $services = [];

    foreach (nc_read_messages() as $message) {
        $name = trim((string) ($message['service'] ?? ''));

        if ($name !== '') {
            $services[$name] = $name;
        }
    }

    return array_values($services);
}
