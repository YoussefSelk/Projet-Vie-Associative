<?php
declare(strict_types=1);

/**
 * Envoi des rappels d'evenements (J-48 et J-24).
 *
 * Usage:
 *   php scripts/send_event_reminders.php
 *
 * Optionnel:
 *   php scripts/send_event_reminders.php --window=30
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('MODELS_PATH', ROOT_PATH . '/models');
define('LOGS_PATH', ROOT_PATH . '/logs');

require_once CONFIG_PATH . '/Environment.php';
Environment::load();
date_default_timezone_set(Environment::getTimezone());

require_once CONFIG_PATH . '/ErrorHandler.php';
require_once CONFIG_PATH . '/Database.php';
require_once CONFIG_PATH . '/Email.php';
require_once MODELS_PATH . '/EventSubscription.php';

$windowMinutes = 60;
foreach ($argv as $arg) {
    if (strpos($arg, '--window=') === 0) {
        $value = (int)substr($arg, strlen('--window='));
        if ($value > 0) {
            $windowMinutes = $value;
        }
    }
}

try {
    $database = new Database();
    $db = $database->connect();
    $subscriptionModel = new EventSubscription($db);

    $summary = [
        '48h' => ['sent' => 0, 'failed' => 0, 'total' => 0],
        '24h' => ['sent' => 0, 'failed' => 0, 'total' => 0],
    ];

    $batches = [
        ['hours' => 48, 'column' => 'reminder_48h_sent_at', 'label' => '48h'],
        ['hours' => 24, 'column' => 'reminder_24h_sent_at', 'label' => '24h'],
    ];

    foreach ($batches as $batch) {
        $hours = (int)$batch['hours'];
        $column = (string)$batch['column'];
        $label = (string)$batch['label'];

        $dueReminders = $subscriptionModel->getDueEventReminders($hours, $column, $windowMinutes);
        $summary[$label]['total'] = count($dueReminders);

        foreach ($dueReminders as $row) {
            $userId = (int)($row['user_id'] ?? 0);
            $eventId = (int)($row['event_id'] ?? 0);
            $email = trim((string)($row['mail'] ?? ''));
            $fullName = trim((string)($row['prenom'] ?? '') . ' ' . (string)($row['nom'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Participant';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $summary[$label]['failed']++;
                ErrorHandler::logError('Reminder skipped: invalid recipient email', 'WARNING', [
                    'user_id' => $userId,
                    'event_id' => $eventId,
                    'email' => $email,
                    'hours_before' => $hours,
                ]);
                continue;
            }

            $eventDate = (string)($row['date_ev'] ?? '');
            $eventTime = (string)($row['horaire_debut'] ?? '00:00:00');
            $eventDateTimeLabel = $eventDate;
            if ($eventDate !== '') {
                $eventDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventDate . ' ' . $eventTime);
                if ($eventDateTime instanceof DateTimeImmutable) {
                    $eventDateTimeLabel = $eventDateTime->format('d/m/Y H:i');
                }
            }

            $message = buildEventReminderEmail(
                $fullName,
                (string)($row['titre'] ?? 'Evenement'),
                $eventDateTimeLabel,
                (string)($row['campus'] ?? 'Non precise'),
                (string)($row['lieu'] ?? 'Non precise'),
                $hours
            );

            $subject = '[Rappel J-' . $hours . 'h] ' . (string)($row['titre'] ?? 'Evenement');
            $sent = sendEmail($email, $subject, $message);

            if ($sent) {
                $subscriptionModel->markReminderSent($userId, $eventId, $column);
                $summary[$label]['sent']++;
            } else {
                $summary[$label]['failed']++;
            }
        }
    }

    echo 'Event reminders summary' . PHP_EOL;
    echo '48h: total=' . $summary['48h']['total'] . ', sent=' . $summary['48h']['sent'] . ', failed=' . $summary['48h']['failed'] . PHP_EOL;
    echo '24h: total=' . $summary['24h']['total'] . ', sent=' . $summary['24h']['sent'] . ', failed=' . $summary['24h']['failed'] . PHP_EOL;

    exit(0);
} catch (Throwable $e) {
    ErrorHandler::logError('Reminder script failure: ' . $e->getMessage(), 'ERROR');
    fwrite(STDERR, 'Reminder script failed. Check logs.' . PHP_EOL);
    exit(1);
}
