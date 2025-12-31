<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erreur Serveur | EILCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ef4444;
            --secondary: #f97316;
            --bg: #0a0a0f;
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg-effect {
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(ellipse at 30% 30%, rgba(239, 68, 68, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 70%, rgba(249, 115, 22, 0.08) 0%, transparent 50%);
        }

        /* Circuit Lines */
        .circuits {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .circuit-line {
            position: absolute;
            background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.3), transparent);
            height: 1px;
            animation: circuitPulse 4s infinite;
        }

        .circuit-line:nth-child(1) { top: 20%; left: 0; width: 30%; animation-delay: 0s; }
        .circuit-line:nth-child(2) { top: 40%; right: 0; width: 25%; animation-delay: 1s; }
        .circuit-line:nth-child(3) { top: 60%; left: 0; width: 35%; animation-delay: 2s; }
        .circuit-line:nth-child(4) { top: 80%; right: 0; width: 20%; animation-delay: 3s; }

        @keyframes circuitPulse {
            0%, 100% { opacity: 0; transform: scaleX(0); }
            50% { opacity: 1; transform: scaleX(1); }
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 40px;
            max-width: 550px;
        }

        .error-visual {
            position: relative;
            margin-bottom: 30px;
        }

        /* Rotating Gears */
        .gears {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }

        .gear {
            position: absolute;
            border: 4px solid rgba(239, 68, 68, 0.3);
            border-radius: 50%;
        }

        .gear::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30%;
            height: 30%;
            background: rgba(239, 68, 68, 0.2);
            border-radius: 50%;
        }

        .gear-1 {
            width: 100px;
            height: 100px;
            top: 10px;
            left: 10px;
            animation: spinGear 8s linear infinite;
        }

        .gear-2 {
            width: 70px;
            height: 70px;
            top: 50px;
            right: 10px;
            animation: spinGear 6s linear infinite reverse;
        }

        @keyframes spinGear {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Warning Icon */
        .warning-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            animation: pulse 2s ease-in-out infinite;
        }

        .warning-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: var(--primary);
            stroke-width: 2;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.7; transform: translate(-50%, -50%) scale(1.1); }
        }

        .error-code {
            font-size: 5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
        }

        h1 {
            font-size: 1.8rem;
            color: var(--text);
            margin-bottom: 15px;
        }

        p {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        /* Status Indicator */
        .status-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
            padding: 15px 25px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .status-text {
            color: var(--primary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .error-ref {
            display: block;
            margin-top: 8px;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-family: 'Courier New', monospace;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(239, 68, 68, 0.3);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
        }

        /* Info Box */
        .info-box {
            margin-top: 35px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            text-align: left;
        }

        .info-box h4 {
            font-size: 0.85rem;
            color: var(--text);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            color: var(--text-muted);
            font-size: 0.9rem;
            padding: 5px 0;
            padding-left: 20px;
            position: relative;
        }

        .info-box li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="bg-effect"></div>
    <div class="circuits">
        <div class="circuit-line"></div>
        <div class="circuit-line"></div>
        <div class="circuit-line"></div>
        <div class="circuit-line"></div>
    </div>

    <div class="container">
        <div class="error-visual">
            <div class="gears">
                <div class="gear gear-1"></div>
                <div class="gear gear-2"></div>
                <div class="warning-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 9v4m0 4h.01M4.93 4.93l14.14 14.14M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                    </svg>
                </div>
            </div>
            <div class="error-code">500</div>
        </div>

        <h1>Erreur Serveur Interne</h1>
        <p>Une erreur inattendue s'est produite. Nos équipes techniques ont été notifiées et travaillent à résoudre le problème.</p>

        <div class="status-bar">
            <span class="status-dot"></span>
            <span class="status-text">Incident détecté • Diagnostic en cours</span>
        </div>

        <?php if (isset($errorRef)): ?>
        <span class="error-ref">Référence: <?= htmlspecialchars($errorRef) ?></span>
        <?php endif; ?>

        <div class="actions">
            <a href="?page=home" class="btn btn-primary">Retour à l'accueil</a>
            <button onclick="location.reload()" class="btn btn-ghost">Réessayer</button>
        </div>

        <div class="info-box">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 16v-4m0-4h.01"/>
                </svg>
                Que pouvez-vous faire ?
            </h4>
            <ul>
                <li>Rafraîchir la page dans quelques instants</li>
                <li>Vider le cache de votre navigateur</li>
                <li>Contacter le support si le problème persiste</li>
            </ul>
        </div>
    </div>
</body>
</html>
