<!--
    Page d'erreur detaillee - Mode Developpement
    
    Affichee uniquement quand APP_DEBUG=true.
    ATTENTION : Ne jamais utiliser en production !
    
    Affiche des informations de debug completes :
    - Message d'erreur complet
    - Fichier et ligne de l'erreur
    - Stack trace interactive
    - Variables d'environnement
    - Informations de requete
    
    Variables attendues :
    - $errorCode : Code HTTP de l'erreur
    - $errorMessage : Message d'erreur complet
    - $errorFile : Fichier source de l'erreur
    - $errorLine : Ligne de l'erreur
    - $errorTrace : Stack trace de l'exception
    
    @package Views/Errors
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚠️ DEV ERROR <?= $errorCode ?? 500 ?> | Debug Mode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0f;
            --surface: #12121a;
            --surface-2: #1a1a24;
            --border: #2a2a3a;
            --text: #e4e4e7;
            --text-muted: #71717a;
            --primary: #3b82f6;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #22c55e;
            --purple: #a855f7;
            --cyan: #06b6d4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Header Bar */
        .dev-header {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dev-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dev-badge {
            background: rgba(0, 0, 0, 0.3);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .dev-header h1 {
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        .dev-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Main Layout */
        .dev-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            min-height: calc(100vh - 52px);
        }

        @media (max-width: 1200px) {
            .dev-container {
                grid-template-columns: 1fr;
            }
        }

        /* Main Content */
        .dev-main {
            padding: 30px;
            overflow-y: auto;
        }

        /* Error Summary Card */
        .error-summary {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .error-summary-header {
            background: var(--surface-2);
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .error-icon {
            width: 56px;
            height: 56px;
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid rgba(239, 68, 68, 0.3);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .error-title-section h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--danger);
            margin-bottom: 6px;
        }

        .error-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--danger);
            font-family: 'JetBrains Mono', monospace;
        }

        .error-summary-body {
            padding: 24px;
        }

        .error-message-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            line-height: 1.8;
            color: var(--warning);
            word-break: break-word;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .info-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .info-item span {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            color: var(--cyan);
        }

        /* Stack Trace */
        .stack-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .section-header {
            background: var(--surface-2);
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-header h3 {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h3 .icon {
            color: var(--purple);
        }

        .stack-trace {
            padding: 0;
            margin: 0;
            list-style: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .stack-frame {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .stack-frame:hover {
            background: var(--surface-2);
        }

        .stack-frame:last-child {
            border-bottom: none;
        }

        .frame-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: var(--primary);
            color: white;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 12px;
        }

        .frame-function {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            color: var(--success);
        }

        .frame-location {
            margin-top: 8px;
            padding-left: 36px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .frame-file {
            color: var(--cyan);
            font-family: 'JetBrains Mono', monospace;
        }

        .frame-line {
            color: var(--warning);
            font-family: 'JetBrains Mono', monospace;
        }

        /* Code Preview */
        .code-preview {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .code-header {
            background: var(--surface-2);
            padding: 12px 24px;
            border-bottom: 1px solid var(--border);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: var(--cyan);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .code-content {
            overflow-x: auto;
        }

        .code-content pre {
            margin: 0;
            padding: 20px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            line-height: 1.8;
        }

        .code-line {
            display: flex;
        }

        .line-number {
            color: var(--text-muted);
            text-align: right;
            padding-right: 20px;
            min-width: 50px;
            user-select: none;
        }

        .line-code {
            flex: 1;
            color: var(--text);
        }

        .code-line.highlight {
            background: rgba(239, 68, 68, 0.1);
            margin: 0 -20px;
            padding: 0 20px;
        }

        .code-line.highlight .line-number {
            color: var(--danger);
            font-weight: 600;
        }

        .code-line.highlight .line-code {
            color: var(--danger);
        }

        /* Sidebar */
        .dev-sidebar {
            background: var(--surface);
            border-left: 1px solid var(--border);
            padding: 24px;
            overflow-y: auto;
        }

        @media (max-width: 1200px) {
            .dev-sidebar {
                border-left: none;
                border-top: 1px solid var(--border);
            }
        }

        .sidebar-section {
            margin-bottom: 28px;
        }

        .sidebar-section h4 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .env-list {
            list-style: none;
        }

        .env-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
        }

        .env-item:last-child {
            border-bottom: none;
        }

        .env-key {
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .env-value {
            color: var(--success);
            font-family: 'JetBrains Mono', monospace;
            text-align: right;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Request Data */
        .data-tree {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            max-height: 200px;
            overflow-y: auto;
        }

        .data-tree pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
            color: var(--cyan);
        }

        /* Actions */
        .dev-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .dev-btn {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface-2);
            color: var(--text);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            text-align: center;
        }

        .dev-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        .copy-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dev-header">
        <div class="dev-header-left">
            <div class="dev-badge">
                <span class="pulse-dot"></span>
                Development Mode
            </div>
            <h1>Exception Debugger</h1>
        </div>
        <div class="dev-header-right">
            <span>PHP <?= phpversion() ?></span>
            <span>•</span>
            <span><?= date('Y-m-d H:i:s') ?></span>
        </div>
    </header>

    <div class="dev-container">
        <!-- Main Content -->
        <main class="dev-main">
            <!-- Error Summary -->
            <div class="error-summary">
                <div class="error-summary-header">
                    <div class="error-icon">💥</div>
                    <div class="error-title-section">
                        <h2><?= htmlspecialchars($errorTitle ?? 'Exception Occurred') ?></h2>
                        <span class="error-type"><?= htmlspecialchars($errorType ?? 'Error') ?></span>
                    </div>
                </div>
                <div class="error-summary-body">
                    <div class="error-message-box">
                        <?= htmlspecialchars($errorMessage ?? 'An unexpected error occurred') ?>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <label>File</label>
                            <span><?= htmlspecialchars($errorFile ?? 'Unknown') ?></span>
                        </div>
                        <div class="info-item">
                            <label>Line</label>
                            <span><?= $errorLine ?? 'N/A' ?></span>
                        </div>
                        <div class="info-item">
                            <label>Error Code</label>
                            <span><?= $errorCode ?? 500 ?></span>
                        </div>
                        <div class="info-item">
                            <label>Reference</label>
                            <span><?= $errorRef ?? strtoupper(substr(md5(time()), 0, 8)) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($errorFile) && !empty($errorLine) && file_exists($errorFile)): ?>
            <!-- Code Preview -->
            <div class="code-preview">
                <div class="code-header">
                    <span>📄</span>
                    <?= htmlspecialchars($errorFile) ?>
                    <span style="margin-left: auto;">
                        <button class="copy-btn" onclick="copyCode()">Copy</button>
                    </span>
                </div>
                <div class="code-content">
                    <pre><?php
                    $lines = file($errorFile);
                    $start = max(0, $errorLine - 6);
                    $end = min(count($lines), $errorLine + 5);
                    
                    for ($i = $start; $i < $end; $i++):
                        $lineNum = $i + 1;
                        $isError = $lineNum == $errorLine;
                    ?><div class="code-line <?= $isError ? 'highlight' : '' ?>"><span class="line-number"><?= $lineNum ?></span><span class="line-code"><?= htmlspecialchars($lines[$i]) ?></span></div><?php
                    endfor;
                    ?></pre>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($stackTrace)): ?>
            <!-- Stack Trace -->
            <div class="stack-section">
                <div class="section-header">
                    <h3><span class="icon">📚</span> Stack Trace</h3>
                    <button class="copy-btn" onclick="copyStack()">Copy</button>
                </div>
                <ul class="stack-trace" id="stackTrace">
                    <?php foreach ($stackTrace as $i => $frame): ?>
                    <li class="stack-frame">
                        <span class="frame-number"><?= $i ?></span>
                        <span class="frame-function"><?= htmlspecialchars(($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? 'unknown')) ?>()</span>
                        <div class="frame-location">
                            <?php if (!empty($frame['file'])): ?>
                            <span class="frame-file"><?= htmlspecialchars($frame['file']) ?></span>
                            <span class="frame-line">:<?= $frame['line'] ?? '?' ?></span>
                            <?php else: ?>
                            <span style="color: var(--text-muted);">[internal function]</span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </main>

        <!-- Sidebar -->
        <aside class="dev-sidebar">
            <div class="sidebar-section">
                <h4>🌍 Environment</h4>
                <ul class="env-list">
                    <li class="env-item">
                        <span class="env-key">APP_ENV</span>
                        <span class="env-value"><?= htmlspecialchars(Environment::get('APP_ENV', 'N/A')) ?></span>
                    </li>
                    <li class="env-item">
                        <span class="env-key">APP_DEBUG</span>
                        <span class="env-value"><?= Environment::isDebug() ? 'true' : 'false' ?></span>
                    </li>
                    <li class="env-item">
                        <span class="env-key">PHP</span>
                        <span class="env-value"><?= phpversion() ?></span>
                    </li>
                    <li class="env-item">
                        <span class="env-key">Memory</span>
                        <span class="env-value"><?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</span>
                    </li>
                    <li class="env-item">
                        <span class="env-key">Peak</span>
                        <span class="env-value"><?= round(memory_get_peak_usage() / 1024 / 1024, 2) ?> MB</span>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <h4>📨 Request</h4>
                <ul class="env-list">
                    <li class="env-item">
                        <span class="env-key">Method</span>
                        <span class="env-value"><?= $_SERVER['REQUEST_METHOD'] ?? 'N/A' ?></span>
                    </li>
                    <li class="env-item">
                        <span class="env-key">URI</span>
                        <span class="env-value" title="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>"><?= htmlspecialchars(substr($_SERVER['REQUEST_URI'] ?? '', 0, 20)) ?>...</span>
                    </li>
                    <li class="env-item">
                        <span class="env-key">IP</span>
                        <span class="env-value"><?= $_SERVER['REMOTE_ADDR'] ?? 'N/A' ?></span>
                    </li>
                </ul>
            </div>

            <?php if (!empty($_GET)): ?>
            <div class="sidebar-section">
                <h4>🔗 GET Data</h4>
                <div class="data-tree">
                    <pre><?= htmlspecialchars(json_encode($_GET, JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($_POST)): ?>
            <div class="sidebar-section">
                <h4>📝 POST Data</h4>
                <div class="data-tree">
                    <pre><?= htmlspecialchars(json_encode($_POST, JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION)): ?>
            <div class="sidebar-section">
                <h4>🔐 Session</h4>
                <div class="data-tree">
                    <pre><?php
                    $sessionDisplay = $_SESSION;
                    // Hide sensitive data
                    if (isset($sessionDisplay['csrf_token'])) $sessionDisplay['csrf_token'] = '[HIDDEN]';
                    echo htmlspecialchars(json_encode($sessionDisplay, JSON_PRETTY_PRINT));
                    ?></pre>
                </div>
            </div>
            <?php endif; ?>

            <div class="dev-actions">
                <a href="?page=home" class="dev-btn">🏠 Home</a>
                <button onclick="location.reload()" class="dev-btn">🔄 Retry</button>
            </div>
        </aside>
    </div>

    <script>
    function copyCode() {
        const code = document.querySelector('.code-content pre').innerText;
        navigator.clipboard.writeText(code);
        alert('Code copied!');
    }

    function copyStack() {
        const stack = document.getElementById('stackTrace').innerText;
        navigator.clipboard.writeText(stack);
        alert('Stack trace copied!');
    }
    </script>
</body>
</html>
