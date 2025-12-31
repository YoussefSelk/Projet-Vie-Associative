<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Indisponible | EILCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f59e0b;
            --secondary: #eab308;
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
                radial-gradient(ellipse at 40% 30%, rgba(245, 158, 11, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 70%, rgba(234, 179, 8, 0.06) 0%, transparent 50%);
        }

        /* Loading Bars */
        .loading-bg {
            position: fixed;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 60px;
            padding: 0 10%;
            pointer-events: none;
        }

        .loading-track {
            height: 2px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 2px;
            overflow: hidden;
        }

        .loading-bar {
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(245, 158, 11, 0.5), transparent);
            animation: loadingMove 3s ease-in-out infinite;
        }

        .loading-track:nth-child(2) .loading-bar { animation-delay: -1s; }
        .loading-track:nth-child(3) .loading-bar { animation-delay: -2s; }

        @keyframes loadingMove {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 40px;
            max-width: 550px;
        }

        .error-visual {
            margin-bottom: 30px;
        }

        /* Server Icon */
        .server-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            position: relative;
        }

        .server {
            position: relative;
            width: 80px;
            height: 100px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .server-unit {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            padding: 0 8px;
            gap: 6px;
        }

        .server-led {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: ledBlink 1.5s infinite;
        }

        .led-1 { background: var(--primary); animation-delay: 0s; }
        .led-2 { background: var(--primary); animation-delay: 0.3s; }
        .led-3 { background: #ef4444; animation-delay: 0.6s; }

        @keyframes ledBlink {
            0%, 100% { opacity: 1; box-shadow: 0 0 8px currentColor; }
            50% { opacity: 0.3; box-shadow: none; }
        }

        .server-slots {
            display: flex;
            gap: 3px;
            margin-left: auto;
        }

        .server-slot {
            width: 4px;
            height: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
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

        /* Maintenance Badge */
        .maintenance-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 50px;
            margin-bottom: 30px;
        }

        .wrench-icon {
            width: 20px;
            height: 20px;
            color: var(--primary);
            animation: wrenchTurn 2s ease-in-out infinite;
        }

        @keyframes wrenchTurn {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-20deg); }
            75% { transform: rotate(20deg); }
        }

        .maintenance-text {
            color: var(--primary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Countdown */
        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
        }

        .countdown-item {
            text-align: center;
        }

        .countdown-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
            display: block;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            min-width: 70px;
        }

        .countdown-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 8px;
            display: block;
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
            color: #0a0a0f;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.3);
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

        /* Status Updates */
        .status-updates {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            text-align: left;
        }

        .status-updates h4 {
            font-size: 0.85rem;
            color: var(--text);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .update-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .update-item:last-child {
            border-bottom: none;
        }

        .update-time {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }

        .update-text {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .update-text.active {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="bg-effect"></div>
    <div class="loading-bg">
        <div class="loading-track"><div class="loading-bar"></div></div>
        <div class="loading-track"><div class="loading-bar"></div></div>
        <div class="loading-track"><div class="loading-bar"></div></div>
    </div>

    <div class="container">
        <div class="error-visual">
            <div class="server-icon">
                <div class="server">
                    <div class="server-unit">
                        <span class="server-led led-1"></span>
                        <div class="server-slots">
                            <span class="server-slot"></span>
                            <span class="server-slot"></span>
                            <span class="server-slot"></span>
                        </div>
                    </div>
                    <div class="server-unit">
                        <span class="server-led led-2"></span>
                        <div class="server-slots">
                            <span class="server-slot"></span>
                            <span class="server-slot"></span>
                            <span class="server-slot"></span>
                        </div>
                    </div>
                    <div class="server-unit">
                        <span class="server-led led-3"></span>
                        <div class="server-slots">
                            <span class="server-slot"></span>
                            <span class="server-slot"></span>
                            <span class="server-slot"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="error-code">503</div>
        </div>

        <h1>Service Temporairement Indisponible</h1>
        <p>Notre plateforme est actuellement en maintenance pour améliorer votre expérience. Nous serons de retour très bientôt.</p>

        <div class="maintenance-badge">
            <svg class="wrench-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
            </svg>
            <span class="maintenance-text">Maintenance en cours</span>
        </div>

        <div class="countdown" id="countdown">
            <div class="countdown-item">
                <span class="countdown-value" id="hours">00</span>
                <span class="countdown-label">Heures</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="minutes">15</span>
                <span class="countdown-label">Minutes</span>
            </div>
            <div class="countdown-item">
                <span class="countdown-value" id="seconds">00</span>
                <span class="countdown-label">Secondes</span>
            </div>
        </div>

        <div class="actions">
            <button onclick="location.reload()" class="btn btn-primary">Actualiser</button>
            <a href="mailto:support@eilco.fr" class="btn btn-ghost">Contacter le support</a>
        </div>

        <div class="status-updates">
            <h4>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12,6 12,12 16,14"/>
                </svg>
                Dernières mises à jour
            </h4>
            <div class="update-item">
                <span class="update-time"><?= date('H:i') ?></span>
                <span class="update-text active">Maintenance programmée en cours</span>
            </div>
            <div class="update-item">
                <span class="update-time"><?= date('H:i', strtotime('-5 minutes')) ?></span>
                <span class="update-text">Sauvegarde des données terminée</span>
            </div>
            <div class="update-item">
                <span class="update-time"><?= date('H:i', strtotime('-10 minutes')) ?></span>
                <span class="update-text">Début de la maintenance</span>
            </div>
        </div>
    </div>

    <script>
        // Countdown Timer (15 minutes from now)
        let totalSeconds = 15 * 60;
        
        function updateCountdown() {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
            
            if (totalSeconds > 0) {
                totalSeconds--;
                setTimeout(updateCountdown, 1000);
            } else {
                location.reload();
            }
        }
        
        updateCountdown();
    </script>
</body>
</html>
