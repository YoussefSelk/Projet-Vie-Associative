<!--
    Page d'erreur 403 - Acces Refuse
    
    Affichee lorsqu'un utilisateur tente d'acceder a une ressource
    sans les permissions necessaires.
    
    Design moderne avec animations CSS :
    - Icone bouclier animee
    - Effet de fond gradient
    - Boutons de navigation (retour/accueil)
    
    @package Views/Errors
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès Refusé | EILCO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --bg: #0a0a0f;
            --surface: #12121a;
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
                radial-gradient(ellipse at 30% 30%, rgba(245, 158, 11, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 70%, rgba(239, 68, 68, 0.05) 0%, transparent 50%);
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 40px;
            max-width: 550px;
        }

        .icon-shield {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(239, 68, 68, 0.1));
            border: 2px solid rgba(245, 158, 11, 0.3);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .icon-shield svg {
            width: 60px;
            height: 60px;
            color: var(--primary);
        }

        .error-code {
            font-size: 5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), #ef4444);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 15px;
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
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.4);
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
    </style>
</head>
<body>
    <div class="bg-effect"></div>
    <div class="container">
        <div class="icon-shield">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
        </div>
        <div class="error-code">403</div>
        <h1>Accès Refusé</h1>
        <p>Vous n'avez pas les permissions nécessaires pour accéder à cette ressource. Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur.</p>
        <div class="actions">
            <a href="?page=home" class="btn btn-primary">Retour à l'accueil</a>
            <button onclick="history.back()" class="btn btn-ghost">Page précédente</button>
        </div>
    </div>
</body>
</html>
