<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?= $errorCode ?? 500 ?> | EILCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #6366f1;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #0f172a;
            --darker: #020617;
            --light: #f8fafc;
            --gray: #64748b;
            --border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--darker);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background */
        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(14, 165, 233, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(14, 165, 233, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(14, 165, 233, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(239, 68, 68, 0.05) 0%, transparent 70%);
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.3;
            animation: float 15s infinite ease-in-out;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 20s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; animation-duration: 18s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; animation-duration: 22s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 1s; animation-duration: 19s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 3s; animation-duration: 21s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 5s; animation-duration: 17s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 2s; animation-duration: 23s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 4s; animation-duration: 16s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 1s; animation-duration: 24s; }

        @keyframes float {
            0%, 100% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }

        /* Main Container */
        .error-container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 40px;
            max-width: 600px;
            width: 90%;
        }

        /* Glitch Effect for Error Code */
        .error-code {
            font-size: clamp(100px, 20vw, 180px);
            font-weight: 700;
            color: transparent;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            position: relative;
            line-height: 1;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }

        .error-code::before,
        .error-code::after {
            content: '<?= $errorCode ?? 500 ?>';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--danger), var(--warning));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .error-code::before {
            animation: glitch-1 3s infinite linear;
        }

        .error-code::after {
            animation: glitch-2 3s infinite linear;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        @keyframes glitch-1 {
            0%, 100% { clip-path: inset(0 0 100% 0); }
            5% { clip-path: inset(40% 0 30% 0); transform: translate(-2px, 0); }
            10% { clip-path: inset(0 0 100% 0); }
        }

        @keyframes glitch-2 {
            0%, 100% { clip-path: inset(100% 0 0 0); }
            15% { clip-path: inset(20% 0 60% 0); transform: translate(2px, 0); }
            20% { clip-path: inset(100% 0 0 0); }
        }

        /* Status Indicator */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 50px;
            margin-bottom: 30px;
            font-size: 0.85rem;
            color: var(--danger);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            animation: blink 1.5s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Error Title */
        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--light);
            margin-bottom: 15px;
        }

        /* Error Message */
        .error-message {
            font-size: 1.1rem;
            color: var(--gray);
            line-height: 1.7;
            margin-bottom: 40px;
        }

        /* Action Buttons */
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(14, 165, 233, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Technical Info (Production) */
        .tech-info {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid var(--border);
        }

        .tech-info p {
            font-size: 0.85rem;
            color: var(--gray);
        }

        .tech-info code {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(255, 255, 255, 0.05);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        /* Decorative Elements */
        .decorative-line {
            position: absolute;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            height: 1px;
            width: 100%;
            left: 0;
        }

        .decorative-line.top { top: 20%; opacity: 0.1; }
        .decorative-line.bottom { bottom: 20%; opacity: 0.1; }

        /* Responsive */
        @media (max-width: 640px) {
            .error-container {
                padding: 30px 20px;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-message {
                font-size: 1rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-gradient"></div>
    
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="decorative-line top"></div>
    <div class="decorative-line bottom"></div>

    <div class="error-container">
        <div class="error-code"><?= $errorCode ?? 500 ?></div>
        
        <div class="status-indicator">
            <span class="status-dot"></span>
            <?= $errorType ?? 'Erreur Système' ?>
        </div>
        
        <h1 class="error-title"><?= $errorTitle ?? 'Une erreur est survenue' ?></h1>
        
        <p class="error-message">
            <?= $errorMessage ?? 'Nous rencontrons un problème technique. Notre équipe a été notifiée et travaille à sa résolution.' ?>
        </p>
        
        <div class="error-actions">
            <a href="?page=home" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Retour à l'accueil
            </a>
            <button onclick="history.back()" class="btn btn-secondary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Page précédente
            </button>
        </div>
        
        <div class="tech-info">
            <p>Référence: <code><?= $errorRef ?? strtoupper(substr(md5(time()), 0, 8)) ?></code> • <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>
</body>
</html>
