<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Non Trouvée | EILCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --secondary: #8b5cf6;
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
                radial-gradient(ellipse at 20% 40%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 60%, rgba(139, 92, 246, 0.08) 0%, transparent 50%);
        }

        /* Floating Shapes */
        .shapes {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border: 2px solid rgba(59, 130, 246, 0.1);
            border-radius: 20px;
            animation: floatShape 20s infinite ease-in-out;
        }

        .shape:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 60px; height: 60px; top: 60%; left: 80%; animation-delay: -5s; border-radius: 50%; }
        .shape:nth-child(3) { width: 100px; height: 100px; top: 70%; left: 20%; animation-delay: -10s; }
        .shape:nth-child(4) { width: 50px; height: 50px; top: 30%; left: 70%; animation-delay: -15s; border-radius: 50%; }

        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 0.3; }
            25% { transform: translate(20px, -30px) rotate(90deg); opacity: 0.5; }
            50% { transform: translate(-10px, -20px) rotate(180deg); opacity: 0.3; }
            75% { transform: translate(15px, 10px) rotate(270deg); opacity: 0.5; }
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 40px;
            max-width: 550px;
        }

        /* 404 Animation */
        .error-visual {
            position: relative;
            margin-bottom: 30px;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
            position: relative;
        }

        .zero {
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }

        .zero:nth-child(2) { animation-delay: 0.2s; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            color: rgba(255, 255, 255, 0.1);
            animation: searchMove 3s ease-in-out infinite;
        }

        @keyframes searchMove {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
            25% { transform: translate(-30%, -60%) rotate(-10deg); }
            50% { transform: translate(-70%, -50%) rotate(10deg); }
            75% { transform: translate(-50%, -40%) rotate(-5deg); }
        }

        h1 {
            font-size: 1.8rem;
            color: var(--text);
            margin-bottom: 15px;
        }

        p {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 35px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            max-width: 400px;
            margin: 0 auto 30px;
        }

        .search-box input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .search-box input::placeholder {
            color: var(--text-muted);
        }

        .search-box button {
            padding: 14px 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.3);
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
            box-shadow: 0 8px 30px rgba(59, 130, 246, 0.3);
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

        .suggestions {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .suggestions h3 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .suggestion-links {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .suggestion-links a {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .suggestion-links a:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.3);
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="bg-effect"></div>
    <div class="shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="error-visual">
            <div class="error-code">
                4<span class="zero">0</span>4
            </div>
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </div>
        
        <h1>Page Non Trouvée</h1>
        <p>La page que vous recherchez semble avoir été déplacée, supprimée ou n'existe pas.</p>
        
        <div class="actions">
            <a href="?page=home" class="btn btn-primary">Retour à l'accueil</a>
            <button onclick="history.back()" class="btn btn-ghost">Page précédente</button>
        </div>

        <div class="suggestions">
            <h3>Pages populaires</h3>
            <div class="suggestion-links">
                <a href="?page=event-list">Événements</a>
                <a href="?page=club-list">Clubs</a>
                <a href="?page=login">Connexion</a>
                <a href="?page=register">Inscription</a>
            </div>
        </div>
    </div>
</body>
</html>
