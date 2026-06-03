<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!class_exists(PHPMailer::class)) {
    require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/PHPMailer.php';
    require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/SMTP.php';
    require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/Exception.php';
}

/**
 * Échappe une chaîne pour l'injection HTML dans les templates e-mail.
 */
function emailEscape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Retourne une configuration SMTP robuste avec fallback MAIL_* et SMTP_*.
 */
function getEmailTransportConfig(string $scope = 'primary'): array {
    $isFallback = ($scope === 'fallback');
    $smtpPrefix = $isFallback ? 'SMTP_FALLBACK_' : 'SMTP_';
    $mailPrefix = $isFallback ? 'MAIL_FALLBACK_' : 'MAIL_';

    $hostDefault = $isFallback ? '' : 'ssl0.ovh.net';
    $fromNameDefault = $isFallback ? 'Projet Tech' : 'Projet Tech';

    $host = (string) Environment::get($smtpPrefix . 'HOST', Environment::get($mailPrefix . 'HOST', $hostDefault));
    $username = (string) Environment::get(
        $smtpPrefix . 'USER',
        Environment::get($smtpPrefix . 'USERNAME', Environment::get($mailPrefix . 'USERNAME', ''))
    );
    $password = (string) Environment::get(
        $smtpPrefix . 'PASS',
        Environment::get($smtpPrefix . 'PASSWORD', Environment::get($mailPrefix . 'PASSWORD', ''))
    );
    $port = (int) Environment::get($smtpPrefix . 'PORT', Environment::get($mailPrefix . 'PORT', 465));
    $fromName = (string) Environment::get(
        $smtpPrefix . 'FROM_NAME',
        Environment::get($mailPrefix . 'FROM_NAME', $fromNameDefault)
    );
    $fromEmail = (string) Environment::get(
        $smtpPrefix . 'FROM_EMAIL',
        Environment::get($mailPrefix . 'FROM', $username)
    );
    $encryption = strtolower((string) Environment::get($smtpPrefix . 'ENCRYPTION', Environment::get($mailPrefix . 'ENCRYPTION', '')));
    $timeout = (int) Environment::get($smtpPrefix . 'TIMEOUT', Environment::get($mailPrefix . 'TIMEOUT', 15));
    $verifyPeer = filter_var(
        Environment::get($smtpPrefix . 'VERIFY_PEER', Environment::get($mailPrefix . 'VERIFY_PEER', 'true')),
        FILTER_VALIDATE_BOOLEAN
    );
    $verifyPeerName = filter_var(
        Environment::get($smtpPrefix . 'VERIFY_PEER_NAME', Environment::get($mailPrefix . 'VERIFY_PEER_NAME', 'true')),
        FILTER_VALIDATE_BOOLEAN
    );
    $allowSelfSigned = filter_var(
        Environment::get($smtpPrefix . 'ALLOW_SELF_SIGNED', Environment::get($mailPrefix . 'ALLOW_SELF_SIGNED', 'false')),
        FILTER_VALIDATE_BOOLEAN
    );
    $enabled = $isFallback
        ? filter_var(
            Environment::get($smtpPrefix . 'ENABLED', Environment::get($mailPrefix . 'ENABLED', 'false')),
            FILTER_VALIDATE_BOOLEAN
        )
        : true;

    if ($encryption === '') {
        $encryption = ($port === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    }

    return [
        'scope' => $scope,
        'enabled' => $enabled,
        'host' => $host,
        'username' => $username,
        'password' => $password,
        'port' => $port,
        'from_name' => $fromName,
        'from_email' => $fromEmail,
        'encryption' => $encryption,
        'timeout' => max(5, $timeout),
        'verify_peer' => $verifyPeer,
        'verify_peer_name' => $verifyPeerName,
        'allow_self_signed' => $allowSelfSigned,
    ];
}

/**
 * Détermine si un fallback SMTP doit être tenté selon l'erreur remontée.
 */
function shouldUseEmailFallback(string $errorInfo): bool {
    $onAnyError = filter_var(
        Environment::get('SMTP_FALLBACK_ON_ANY_ERROR', Environment::get('MAIL_FALLBACK_ON_ANY_ERROR', 'false')),
        FILTER_VALIDATE_BOOLEAN
    );
    if ($onAnyError) {
        return true;
    }

    $normalized = strtolower($errorInfo);
    $fallbackSignals = [
        '5.7.1',
        '550',
        'user blocked from sending mail',
        'authentication failed',
        'data not accepted',
        'service unavailable',
    ];

    foreach ($fallbackSignals as $signal) {
        if (strpos($normalized, $signal) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Tente un envoi sur un transport SMTP donné.
 *
 * @return array{success:bool,error:string}
 */
function sendEmailWithTransport(
    array $config,
    string $to,
    string $subject,
    string $htmlBody,
    string $textBody
): array {
    if (empty($config['host']) || empty($config['username']) || empty($config['password'])) {
        return [
            'success' => false,
            'error' => 'transport_incomplete',
        ];
    }

    if (!filter_var((string)$config['from_email'], FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'error' => 'invalid_sender',
        ];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = (string)$config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string)$config['username'];
        $mail->Password = (string)$config['password'];
        $mail->SMTPSecure = (string)$config['encryption'];
        $mail->Port = (int)$config['port'];
        $mail->SMTPAutoTLS = true;
        $mail->Timeout = (int)$config['timeout'];
        $mail->SMTPKeepAlive = false;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => (bool)$config['verify_peer'],
                'verify_peer_name' => (bool)$config['verify_peer_name'],
                'allow_self_signed' => (bool)$config['allow_self_signed'],
            ],
        ];

        $mail->setFrom((string)$config['from_email'], (string)$config['from_name']);
        $mail->addReplyTo((string)$config['from_email'], (string)$config['from_name']);
        $mail->addAddress($to);

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;

        if ($mail->send()) {
            return [
                'success' => true,
                'error' => '',
            ];
        }

        return [
            'success' => false,
            'error' => (string)$mail->ErrorInfo,
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => (string)$mail->ErrorInfo,
        ];
    }
}

/**
 * Normalise le sujet et bloque l'injection d'en-têtes SMTP.
 */
function emailNormalizeSubject(string $subject): string {
    $clean = preg_replace('/[\r\n]+/', ' ', trim($subject));
    if ($clean === null || $clean === '') {
        return 'Notification';
    }

    return mb_substr($clean, 0, 200, 'UTF-8');
}

/**
 * Détecte rapidement si le contenu ressemble à du HTML.
 */
function emailLooksLikeHtml(string $message): bool {
    return (bool) preg_match('/<[^>]+>/', $message);
}

/**
 * Convertit un message texte simple en mini HTML propre.
 */
function emailPlainToHtml(string $message): string {
    $escaped = emailEscape($message);
    return nl2br($escaped);
}

/**
 * Produit un fallback texte lisible à partir d'un HTML.
 */
function emailHtmlToText(string $html): string {
    $normalized = preg_replace('/\s+/u', ' ', trim(strip_tags($html)));
    return html_entity_decode((string) $normalized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Template principal responsive et compatible clients mail classiques.
 *
 * @param array<string, mixed> $data
 * @return array{html:string,text:string}
 */
function renderProfessionalEmailTemplate(array $data): array {
    $brand = emailEscape((string)($data['brand'] ?? 'Vie Étudiante EILCO'));
    $title = emailEscape((string)($data['title'] ?? 'Notification'));
    $intro = emailEscape((string)($data['intro'] ?? 'Bonjour,'));
    $preheader = emailEscape((string)($data['preheader'] ?? $title));
    $accent = emailEscape((string)($data['accent'] ?? '#0a5bd3'));
    $footer = emailEscape((string)($data['footer'] ?? 'Équipe Vie Étudiante EILCO'));
    $bodyLines = $data['body_lines'] ?? [];
    $metaLines = $data['meta_lines'] ?? [];
    $buttonLabel = isset($data['button_label']) ? emailEscape((string)$data['button_label']) : '';
    $buttonUrl = isset($data['button_url']) ? (string)$data['button_url'] : '';

    $bodyHtml = '';
    $bodyText = [];
    foreach ($bodyLines as $line) {
        $safeLine = emailEscape((string)$line);
        $bodyHtml .= '<p style="margin:0 0 14px; font-size:16px; line-height:1.6; color:#243145;">' . $safeLine . '</p>';
        $bodyText[] = (string)$line;
    }

    $metaHtml = '';
    $metaText = [];
    if (!empty($metaLines)) {
        $metaHtml .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 20px; border-collapse:collapse;">';
        foreach ($metaLines as $line) {
            $safeLine = emailEscape((string)$line);
            $metaHtml .= '<tr><td style="padding:8px 12px; border:1px solid #d9e2f3; background-color:#f6f9ff; font-size:14px; color:#1f2c3f;">' . $safeLine . '</td></tr>';
            $metaText[] = (string)$line;
        }
        $metaHtml .= '</table>';
    }

    $buttonHtml = '';
    $buttonText = '';
    if ($buttonLabel !== '' && filter_var($buttonUrl, FILTER_VALIDATE_URL)) {
        $safeUrl = emailEscape($buttonUrl);
        $buttonHtml =
            '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px; border-collapse:separate;">' .
                '<tr>' .
                    '<td align="center" bgcolor="' . $accent . '" style="border-radius:8px;">' .
                        '<a href="' . $safeUrl . '" style="display:inline-block; padding:13px 22px; font-size:15px; line-height:1; color:#ffffff; text-decoration:none; font-weight:700;">' .
                            $buttonLabel .
                        '</a>' .
                    '</td>' .
                '</tr>' .
            '</table>';
        $buttonText = $buttonLabel . ': ' . $buttonUrl;
    }

    $html =
        '<!doctype html>' .
        '<html lang="fr">' .
        '<head>' .
            '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' .
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' .
            '<title>' . $title . '</title>' .
        '</head>' .
        '<body style="margin:0; padding:0; background-color:#eef3fa; font-family:Segoe UI, Arial, Helvetica, sans-serif;">' .
            '<div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">' . $preheader . '</div>' .
            '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; background-color:#eef3fa;">' .
                '<tr>' .
                    '<td align="center" style="padding:28px 12px;">' .
                        '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%; max-width:600px; border-collapse:collapse; background-color:#ffffff; border:1px solid #d8e2f0; border-radius:14px; overflow:hidden;">' .
                            '<tr>' .
                                '<td style="padding:20px 24px; background:' . $accent . '; color:#ffffff;">' .
                                    '<p style="margin:0; font-size:12px; line-height:1.2; letter-spacing:1px; text-transform:uppercase; opacity:.92;">' . $brand . '</p>' .
                                    '<h1 style="margin:10px 0 0; font-size:22px; line-height:1.3; color:#ffffff;">' . $title . '</h1>' .
                                '</td>' .
                            '</tr>' .
                            '<tr>' .
                                '<td style="padding:24px;">' .
                                    '<p style="margin:0 0 16px; font-size:16px; line-height:1.5; color:#1c2b3c; font-weight:600;">' . $intro . '</p>' .
                                    $bodyHtml .
                                    $metaHtml .
                                    $buttonHtml .
                                    '<p style="margin:0; font-size:13px; line-height:1.6; color:#5f7088;">Ceci est un e-mail automatique, merci de ne pas y répondre directement.</p>' .
                                '</td>' .
                            '</tr>' .
                        '</table>' .
                        '<p style="margin:14px 0 0; font-size:12px; line-height:1.5; color:#617089;">' . $footer . '</p>' .
                    '</td>' .
                '</tr>' .
            '</table>' .
        '</body>' .
        '</html>';

    $textParts = [
        html_entity_decode((string)($data['title'] ?? 'Notification'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        '',
        html_entity_decode((string)($data['intro'] ?? 'Bonjour,'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        '',
    ];

    foreach ($bodyText as $line) {
        $textParts[] = $line;
    }

    if (!empty($metaText)) {
        $textParts[] = '';
        foreach ($metaText as $line) {
            $textParts[] = '- ' . $line;
        }
    }

    if ($buttonText !== '') {
        $textParts[] = '';
        $textParts[] = $buttonText;
    }

    $textParts[] = '';
    $textParts[] = 'Ceci est un e-mail automatique, merci de ne pas y répondre directement.';
    $textParts[] = $footer;

    return [
        'html' => $html,
        'text' => implode("\n", $textParts),
    ];
}

/**
 * Template pour l'inscription utilisateur.
 *
 * @return array{html:string,text:string}
 */
function buildRegistrationVerificationEmail(string $firstName, string $code): array {
    return renderProfessionalEmailTemplate([
        'title' => 'Code de vérification de votre compte',
        'preheader' => 'Utilisez votre code pour finaliser votre inscription.',
        'intro' => 'Bonjour ' . trim($firstName) . ',',
        'body_lines' => [
            'Nous avons reçu une demande de création de compte sur la plateforme Vie Étudiante.',
            'Saisissez le code ci-dessous pour finaliser votre inscription.',
            'Le code expire dans 5 minutes.',
        ],
        'meta_lines' => [
            'Code de vérification: ' . $code,
        ],
    ]);
}

/**
 * Template pour la réinitialisation de mot de passe.
 *
 * @return array{html:string,text:string}
 */
function buildPasswordResetEmail(?string $firstName, string $token): array {
    $recipient = trim((string)$firstName);
    $intro = $recipient !== '' ? ('Bonjour ' . $recipient . ',') : 'Bonjour,';

    return renderProfessionalEmailTemplate([
        'title' => 'Code de réinitialisation de mot de passe',
        'preheader' => 'Votre code temporaire de réinitialisation est disponible.',
        'intro' => $intro,
        'body_lines' => [
            'Une demande de réinitialisation a été enregistrée pour votre compte.',
            'Utilisez le code ci-dessous pour définir un nouveau mot de passe.',
            'Le code expire dans 5 minutes.',
        ],
        'meta_lines' => [
            'Code de réinitialisation: ' . $token,
        ],
        'accent' => '#0f766e',
    ]);
}

/**
 * Template de notification pour validation par tuteur.
 *
 * @return array{html:string,text:string}
 */
function buildTutorValidationNotificationEmail(
    string $tutorFullName,
    string $creatorName,
    string $typeLabel,
    string $itemName,
    ?string $actionUrl = null
): array {
    $body = [
        $creatorName . ' a créé un nouveau ' . $typeLabel . ' qui requiert votre validation.',
        'Connectez-vous à la plateforme pour approuver ou rejeter cette demande.',
    ];

    $data = [
        'title' => 'Nouvelle demande de validation',
        'preheader' => 'Un nouveau ' . $typeLabel . ' est en attente de votre validation.',
        'intro' => 'Bonjour ' . trim($tutorFullName) . ',',
        'body_lines' => $body,
        'meta_lines' => [
            ucfirst($typeLabel) . ': ' . $itemName,
        ],
        'accent' => '#1d4ed8',
    ];

    if (!empty($actionUrl)) {
        $data['button_label'] = 'Accéder aux validations';
        $data['button_url'] = $actionUrl;
    }

    return renderProfessionalEmailTemplate($data);
}

/**
 * Template de notification pour validation BDE.
 *
 * @return array{html:string,text:string}
 */
function buildBdeValidationNotificationEmail(
    string $bdeFullName,
    string $creatorName,
    string $typeLabel,
    string $itemName,
    ?string $actionUrl = null
): array {
    $body = [
        $creatorName . ' a soumis un ' . $typeLabel . ' qui requiert votre validation BDE.',
        'Merci de vérifier les informations et de statuer depuis l\'espace de validation.',
    ];

    $data = [
        'title' => 'Validation BDE requise',
        'preheader' => 'Un nouveau ' . $typeLabel . ' attend une décision BDE.',
        'intro' => 'Bonjour ' . trim($bdeFullName) . ',',
        'body_lines' => $body,
        'meta_lines' => [
            ucfirst($typeLabel) . ': ' . $itemName,
            'Niveau de validation: BDE',
        ],
        'accent' => '#b45309',
    ];

    if (!empty($actionUrl)) {
        $data['button_label'] = 'Traiter la validation BDE';
        $data['button_url'] = $actionUrl;
    }

    return renderProfessionalEmailTemplate($data);
}

/**
 * Template de notification pour validation administrateur.
 *
 * @return array{html:string,text:string}
 */
function buildAdminValidationNotificationEmail(
    string $adminFullName,
    string $creatorName,
    string $typeLabel,
    string $itemName,
    ?string $actionUrl = null
): array {
    $body = [
        $creatorName . ' a soumis un ' . $typeLabel . ' en attente de validation administrateur.',
        'Merci de contrôler la conformité du dossier avant décision finale.',
    ];

    $data = [
        'title' => 'Validation administrateur requise',
        'preheader' => 'Un nouveau ' . $typeLabel . ' attend une validation administrateur.',
        'intro' => 'Bonjour ' . trim($adminFullName) . ',',
        'body_lines' => $body,
        'meta_lines' => [
            ucfirst($typeLabel) . ': ' . $itemName,
            'Niveau de validation: Administrateur',
        ],
        'accent' => '#7c2d12',
    ];

    if (!empty($actionUrl)) {
        $data['button_label'] = 'Traiter la validation admin';
        $data['button_url'] = $actionUrl;
    }

    return renderProfessionalEmailTemplate($data);
}

/**
 * Template de rappel pour un événement à venir (J-48 / J-24).
 *
 * @return array{html:string,text:string}
 */
function buildEventReminderEmail(
    string $fullName,
    string $eventTitle,
    string $eventDateTimeLabel,
    string $campus,
    string $lieu,
    int $hoursBefore
): array {
    $hoursBefore = max(1, $hoursBefore);
    $accent = $hoursBefore <= 24 ? '#b91c1c' : '#1d4ed8';

    return renderProfessionalEmailTemplate([
        'title' => 'Rappel événement: J-' . $hoursBefore . 'h',
        'preheader' => 'Votre événement approche. Pensez à préparer votre participation.',
        'intro' => 'Bonjour ' . trim($fullName) . ',',
        'body_lines' => [
            'Vous êtes inscrit à un événement prévu dans environ ' . $hoursBefore . ' heures.',
            'Ce message est un rappel automatique pour vous aider à anticiper votre participation.',
        ],
        'meta_lines' => [
            'Événement: ' . $eventTitle,
            'Date et heure: ' . $eventDateTimeLabel,
            'Campus: ' . $campus,
            'Lieu: ' . $lieu,
        ],
        'accent' => $accent,
    ]);
}

/**
 * Template de notification de statut d'une demande (club/événement)
 * pour les membres de bureau (président/secrétaire).
 *
 * @return array{html:string,text:string}
 */
function buildLeadershipRequestStatusEmail(
    string $fullName,
    string $clubName,
    string $requestType,
    string $itemName,
    string $statusLabel,
    ?string $reason = null,
    ?string $actionUrl = null
): array {
    $normalizedType = strtolower(trim($requestType)) === 'club' ? 'club' : 'événement';
    $accent = '#1d4ed8';
    if (stripos($statusLabel, 'rejet') !== false) {
        $accent = '#b91c1c';
    } elseif (stripos($statusLabel, 'valid') !== false || stripos($statusLabel, 'approuv') !== false) {
        $accent = '#0f766e';
    }

    $isClubRequest = ($normalizedType === 'club');
    // Élision correcte : "de club" mais "d'événement".
    $demandeLabel = $isClubRequest ? 'de club' : "d'événement";

    // Pour une demande de club, le nom du club et l'item sont identiques : on
    // évite la ligne "Club:" en double. Pour un événement, on affiche les deux.
    $metaLines = ['Club: ' . $clubName];
    if (!$isClubRequest) {
        $metaLines[] = 'Événement: ' . $itemName;
    }
    $metaLines[] = 'Statut: ' . $statusLabel;

    if (!empty($reason)) {
        $metaLines[] = 'Motif: ' . trim($reason);
    }

    $data = [
        'title' => 'Mise à jour de votre demande ' . $demandeLabel,
        'preheader' => 'Le statut de votre demande a été mis à jour.',
        'intro' => 'Bonjour ' . trim($fullName) . ',',
        'body_lines' => [
            'La demande ' . $demandeLabel . ' de votre club a été mise à jour.',
            'Veuillez consulter le statut ci-dessous.',
        ],
        'meta_lines' => $metaLines,
        'accent' => $accent,
    ];

    if (!empty($actionUrl)) {
        $data['button_label'] = 'Consulter la demande';
        $data['button_url'] = $actionUrl;
    }

    return renderProfessionalEmailTemplate($data);
}

/**
 * Envoi d'email SMTP avec fallback texte pour les clients stricts.
 *
 * @param string|array{html?:string,text?:string} $message
 */
function sendEmail($to, $subject, $message) {
    $primaryConfig = getEmailTransportConfig('primary');
    $fallbackConfig = getEmailTransportConfig('fallback');
    $normalizedSubject = emailNormalizeSubject((string)$subject);

    // Validation configuration SMTP
    if (empty($primaryConfig['username']) || empty($primaryConfig['password'])) {
        ErrorHandler::logError('Email configuration error: SMTP credentials not set', 'WARNING');
        return false;
    }

    // Validation email destinataire
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        ErrorHandler::logError("Invalid recipient email address: $to", 'WARNING', ['recipient' => $to]);
        return false;
    }

    $htmlBody = '';
    $textBody = '';

    if (is_array($message)) {
        $htmlBody = isset($message['html']) ? (string)$message['html'] : '';
        $textBody = isset($message['text']) ? (string)$message['text'] : '';
    } else {
        $rawMessage = (string)$message;
        if (emailLooksLikeHtml($rawMessage)) {
            $htmlBody = $rawMessage;
            $textBody = emailHtmlToText($rawMessage);
        } else {
            $htmlBody = emailPlainToHtml($rawMessage);
            $textBody = $rawMessage;
        }
    }

    if ($htmlBody === '' && $textBody !== '') {
        $htmlBody = emailPlainToHtml($textBody);
    }
    if ($textBody === '' && $htmlBody !== '') {
        $textBody = emailHtmlToText($htmlBody);
    }

    if ($htmlBody === '' || $textBody === '') {
        ErrorHandler::logError('Email content error: missing HTML or text body', 'WARNING', [
            'recipient' => $to,
            'subject' => $normalizedSubject,
        ]);
        return false;
    }

    $primaryAttempt = sendEmailWithTransport($primaryConfig, $to, $normalizedSubject, $htmlBody, $textBody);
    if ($primaryAttempt['success']) {
        return true;
    }

    $primaryError = trim((string)$primaryAttempt['error']);
    ErrorHandler::logError('Email sending failed on primary transport: ' . $primaryError, 'ERROR', [
        'recipient' => $to,
        'subject' => $normalizedSubject,
    ]);

    $canUseFallback = (bool)($fallbackConfig['enabled'] ?? false);
    if ($canUseFallback && shouldUseEmailFallback($primaryError)) {
        $fallbackAttempt = sendEmailWithTransport($fallbackConfig, $to, $normalizedSubject, $htmlBody, $textBody);
        if ($fallbackAttempt['success']) {
            ErrorHandler::logError('Email sent using fallback SMTP transport', 'WARNING', [
                'recipient' => $to,
                'subject' => $normalizedSubject,
                'primary_error' => $primaryError,
            ]);
            return true;
        }

        ErrorHandler::logError('Email sending failed on fallback transport: ' . $fallbackAttempt['error'], 'ERROR', [
            'recipient' => $to,
            'subject' => $normalizedSubject,
        ]);
    }

    // Ne jamais propager d'information technique sensible à l'interface.
    return false;
}

/**
 * Construit une URL absolue vers une page de la plateforme (si BASE_URL connue).
 */
function buildPlatformUrl(string $page): ?string {
    if (defined('BASE_URL') && is_string(BASE_URL) && BASE_URL !== '') {
        return rtrim(BASE_URL, '/') . '/?page=' . $page;
    }
    return null;
}

/**
 * Récupère les valideurs à notifier : BDE (permission 3), administrateurs
 * (permission >= 4) et, si fourni, le tuteur du club.
 *
 * @return array<int, array<string,mixed>> Indexé par id utilisateur
 */
function getValidatorRecipients(PDO $db, ?int $tutorId = null): array {
    $recipients = [];
    try {
        $stmt = $db->query("SELECT id, nom, prenom, mail, permission FROM users WHERE permission >= 3 AND mail IS NOT NULL AND mail <> ''");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $recipients[(int)$row['id']] = $row;
        }
    } catch (\PDOException $e) {
        // En cas d'erreur DB, on continue avec ce qu'on a (tuteur éventuel).
    }

    if ($tutorId) {
        try {
            $stmt = $db->prepare("SELECT id, nom, prenom, mail, permission FROM users WHERE id = ? AND mail IS NOT NULL AND mail <> ''");
            $stmt->execute([$tutorId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $recipients[(int)$row['id']] = $row;
            }
        } catch (\PDOException $e) {
            // ignore
        }
    }

    return $recipients;
}

/**
 * Notifie les valideurs (BDE + Tuteur + Admin) qu'un étudiant vient de créer
 * un club ou un événement nécessitant leur validation. (Retour client juin 2026)
 */
function notifyValidatorsNewSubmission(PDO $db, string $type, string $itemName, string $creatorName, ?int $tutorId = null): void {
    // La notification ne doit JAMAIS faire échouer l'action utilisateur (création).
    try {
        $typeLabel = (strtolower(trim($type)) === 'club') ? 'club' : 'événement';
        $bdeAdminPage = ($typeLabel === 'club') ? 'pending-clubs' : 'pending-events';

        foreach (getValidatorRecipients($db, $tutorId) as $recipient) {
            $email = trim((string)($recipient['mail'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $fullName = trim((string)($recipient['prenom'] ?? '') . ' ' . (string)($recipient['nom'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Valideur';
            }
            $perm = (int)($recipient['permission'] ?? 0);

            if ($perm >= 4) {
                $message = buildAdminValidationNotificationEmail($fullName, $creatorName, $typeLabel, $itemName, buildPlatformUrl($bdeAdminPage));
            } elseif ($perm === 3) {
                $message = buildBdeValidationNotificationEmail($fullName, $creatorName, $typeLabel, $itemName, buildPlatformUrl($bdeAdminPage));
            } else {
                $message = buildTutorValidationNotificationEmail($fullName, $creatorName, $typeLabel, $itemName, buildPlatformUrl('tutoring'));
            }

            $subject = 'Nouveau ' . $typeLabel . ' à valider : ' . $itemName;
            sendEmail($email, $subject, $message);
        }
    } catch (\Throwable $e) {
        if (class_exists('ErrorHandler')) { ErrorHandler::logError('notifyValidatorsNewSubmission: ' . $e->getMessage(), 'WARNING'); }
    }
}

/**
 * Notifie les valideurs qu'un rapport d'événement vient d'être déposé.
 * (Retour client juin 2026)
 */
function notifyValidatorsReportDeposited(PDO $db, string $eventTitle, string $clubName, string $depositorName, ?int $tutorId = null): void {
    // La notification ne doit JAMAIS faire échouer le dépôt du rapport.
    try {
        $actionUrl = buildPlatformUrl('admin-event-reports');

        foreach (getValidatorRecipients($db, $tutorId) as $recipient) {
            $email = trim((string)($recipient['mail'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $fullName = trim((string)($recipient['prenom'] ?? '') . ' ' . (string)($recipient['nom'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Valideur';
            }

            $message = renderProfessionalEmailTemplate([
                'title' => 'Nouveau rapport d\'événement déposé',
                'preheader' => 'Un rapport d\'événement vient d\'être déposé sur la plateforme.',
                'intro' => 'Bonjour ' . $fullName . ',',
                'body_lines' => [
                    $depositorName . ' a déposé le rapport de l\'événement « ' . $eventTitle . ' ».',
                    'Vous pouvez le consulter depuis la plateforme.',
                ],
                'meta_lines' => [
                    'Club: ' . $clubName,
                    'Événement: ' . $eventTitle,
                ],
                'accent' => '#0f766e',
            ] + ($actionUrl ? ['button_label' => 'Consulter les rapports', 'button_url' => $actionUrl] : []));

            sendEmail($email, 'Rapport déposé : ' . $eventTitle, $message);
        }
    } catch (\Throwable $e) {
        if (class_exists('ErrorHandler')) { ErrorHandler::logError('notifyValidatorsReportDeposited: ' . $e->getMessage(), 'WARNING'); }
    }
}

/**
 * Notifie les AUTRES valideurs qu'un valideur a refusé un club/événement,
 * en précisant le motif. (Retour client juin 2026)
 *
 * @param int|null $excludeUserId Identifiant du valideur ayant rejeté (à ne pas notifier)
 */
function notifyValidatorsRefusal(PDO $db, string $type, string $itemName, string $refuserRole, ?string $reason, ?int $tutorId = null, ?int $excludeUserId = null): void {
    // La notification ne doit JAMAIS faire échouer l'action de validation/refus.
    try {
        $typeLabel = (strtolower(trim($type)) === 'club') ? 'club' : 'événement';
        $reason = trim((string)$reason);

        foreach (getValidatorRecipients($db, $tutorId) as $userId => $recipient) {
            if ($excludeUserId !== null && (int)$userId === $excludeUserId) {
                continue;
            }
            $email = trim((string)($recipient['mail'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $fullName = trim((string)($recipient['prenom'] ?? '') . ' ' . (string)($recipient['nom'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Valideur';
            }

            $perm = (int)($recipient['permission'] ?? 0);
            $actionUrl = ($perm >= 3)
                ? buildPlatformUrl($typeLabel === 'club' ? 'pending-clubs' : 'pending-events')
                : buildPlatformUrl('tutoring');

            $metaLines = [
                ucfirst($typeLabel) . ': ' . $itemName,
                'Refusé par: ' . $refuserRole,
            ];
            if ($reason !== '') {
                $metaLines[] = 'Motif du refus: ' . $reason;
            }

            $message = renderProfessionalEmailTemplate([
                'title' => 'Un ' . $typeLabel . ' a été refusé',
                'preheader' => 'Un autre valideur a refusé une demande.',
                'intro' => 'Bonjour ' . $fullName . ',',
                'body_lines' => [
                    'Le ' . $typeLabel . ' « ' . $itemName . ' » a été refusé par ' . $refuserRole . '.',
                    'Cette information vous est transmise afin que tous les valideurs aient connaissance du refus et de son motif.',
                ],
                'meta_lines' => $metaLines,
                'accent' => '#b91c1c',
            ] + ($actionUrl ? ['button_label' => 'Voir les validations', 'button_url' => $actionUrl] : []));

            sendEmail($email, 'Refus d\'un ' . $typeLabel . ' : ' . $itemName, $message);
        }
    } catch (\Throwable $e) {
        if (class_exists('ErrorHandler')) { ErrorHandler::logError('notifyValidatorsRefusal: ' . $e->getMessage(), 'WARNING'); }
    }
}

// Alias for backward compatibility
function envoyerMail($to, $subject, $message) {
    return sendEmail($to, $subject, $message);
}
