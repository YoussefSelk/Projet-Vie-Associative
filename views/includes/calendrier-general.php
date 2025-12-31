<?php
// Database already included via bootstrap
global $db;

if (isset($_SESSION["id"])) {
    $user_id = $_SESSION["id"];
    $today = new DateTime();
    $future_limit = (new DateTime())->modify('+7 days')->format('Y-m-d');

    $stmt = $db->prepare("
        SELECT e.event_id, e.titre, e.date_ev
        FROM fiche_event e
        JOIN abonnements a ON e.event_id = a.event_id
        WHERE a.id = :user_id
          AND e.validation_finale = 1
          AND e.date_ev BETWEEN CURDATE() AND :limit
    ");
    $stmt->execute([
        'user_id' => $user_id,
        'limit' => $future_limit
    ]);

    $events_soon = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events_soon as $event) {
        $event_date = new DateTime($event['date_ev']);
        $days_remaining = $today->diff($event_date)->days;

        echo "<div class='event-reminder' style='
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #fff8e1;
            border: 2px solid #ff9800;
            padding: 15px 20px;
            z-index: 10000;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-family: sans-serif;
            max-width: 300px;
        '>
            <strong>Rappel :</strong><br>
            L'événement <strong>« " . htmlspecialchars($event['titre']) . " »</strong> aura lieu dans <strong>$days_remaining jour(s)</strong>.
            <br><br>
            <button onclick='this.parentElement.style.display=\"none\"' style='
                background-color: #ff9800;
                color: white;
                border: none;
                border-radius: 5px;
                padding: 5px 10px;
                cursor: pointer;
                float: right;
            '>OK</button>
        </div>";
    }
}


// Récupérer l'identifiant de l'utilisateur depuis la session (la table users utilise "id")
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// Affichage du message de confirmation s'il y a des paramètres dans l'URL
if (isset($_GET['msg']) && isset($_GET['event_name'])) {
    $msg = $_GET['msg'];
    $eventName = htmlspecialchars(urldecode($_GET['event_name']));
    
    if ($msg === "subscribe") {
        $confirmationText = "Vous vous êtes abonné(e) à l'événement <strong>« $eventName »</strong>.";
    } elseif ($msg === "unsubscribe") {
        $confirmationText = "Vous ne suivez plus l'événement <strong>« $eventName »</strong>.";
    }

    echo "<div id='confirmationPopup' style='
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #e0ffe0;
            border: 2px solid #005795;
            padding: 15px 25px;
            z-index: 10000;
            border-radius: 8px;
            font-family: sans-serif;
          '>
            $confirmationText
          </div>";

    // ➕ Ajout du script pour faire disparaître la pop-up et nettoyer l’URL
    echo "<script>
            setTimeout(function() {
                var popup = document.getElementById('confirmationPopup');
                if (popup) { popup.style.display = 'none'; }
                // Supprimer les paramètres de l'URL
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('msg');
                    url.searchParams.delete('event_name');
                    window.history.replaceState(null, '', url.pathname + url.search);
                }
            }, 3000);
          </script>";
}


// Fonction pour vérifier si l'utilisateur est abonné à l'événement
function isUserSubscribed($db, $user_id, $event_id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM abonnements WHERE id = :id AND event_id = :event_id");
    $stmt->execute(['id' => $user_id, 'event_id' => $event_id]);
    return $stmt->fetchColumn() > 0;
}

// Récupérer le mois et l'année depuis l'URL ou prendre le mois courant
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Corriger les dépassements (ex : mois 13 => janvier année suivante)
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

// Infos de navigation
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Nombre de jours dans le mois
$nb_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Premier jour du mois
$first_day = date('w', strtotime("$year-$month-01"));
$first_day = ($first_day == 0) ? 6 : $first_day - 1;  // Si dimanche (0), on le met à 6 (dimanche après samedi)

// Récupération des événements depuis la base de données (ajout de l'id et club name)
$query = $db->prepare("
    SELECT e.event_id, e.titre, e.date_ev, e.campus, e.horaire_debut, e.horaire_fin, e.lieu, e.club_orga, c.nom_club
    FROM fiche_event e
    LEFT JOIN fiche_club c ON e.club_orga = c.club_id
    WHERE MONTH(e.date_ev) = :mois
    AND YEAR(e.date_ev) = :annee
    AND e.validation_finale = 1
");
$query->execute(['mois' => $month, 'annee' => $year]);
$events = $query->fetchAll(PDO::FETCH_ASSOC);

// Organisation par jour
$event_by_day = [];
foreach ($events as $event) {
    $day = date('j', strtotime($event['date_ev']));
    $event_by_day[$day][] = $event;
}

// Définir une couleur pour chaque campus
$campus_colors = [
    'Calais' => '#f8d7da',       // Rose pastel
    'Longuenesse' => '#add8e6',  // Bleu pastel
    'Boulogne' => '#b2f2bb',     // Vert pastel
    'Dunkerque' => '#fff3b0'     // Jaune pastel
];

// Nom du mois en français
$mois_francais = [
    1 => "Janvier",
    2 => "Février",
    3 => "Mars",
    4 => "Avril",
    5 => "Mai",
    6 => "Juin",
    7 => "Juillet",
    8 => "Août",
    9 => "Septembre",
    10 => "Octobre",
    11 => "Novembre",
    12 => "Décembre"
];

// Convertir $month en entier pour éviter l'erreur de clé
$month = (int)$month;

$mois_fr = $mois_francais[$month];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Calendrier</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
</head>
<body>

<div class="calendar-title">
    <h2>Programme associatif du mois</h2>
</div>

<div class="nav-calendrier">
    <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>">← Mois précédent</a>
    <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>">Mois suivant →</a>
</div>

<h2><?= ucfirst($mois_fr) ?></h2>

<table>
    <tr>
        <th>Lun</th>
        <th>Mar</th>
        <th>Mer</th>
        <th>Jeu</th>
        <th>Ven</th>
        <th>Sam</th>
        <th>Dim</th>
    </tr>
    <tr>
        <?php
        $day = 1;
        $current_day = 0;

        // Jours vides avant le 1er
        for ($i = 0; $i < $first_day; $i++) {
            echo "<td></td>";
            $current_day++;
        }

        while ($day <= $nb_days) {
            if ($current_day % 7 == 0 && $current_day != 0) echo "</tr><tr>";

            echo "<td><div class='day-number'>$day</div>";

            if (isset($event_by_day[$day])) {
                foreach ($event_by_day[$day] as $event) {
                    $campus = $event['campus'];
                    $event_color = isset($campus_colors[$campus]) ? $campus_colors[$campus] : '#FFFFFF';
                    $titre = html_entity_decode($event['titre'], ENT_QUOTES, 'UTF-8');
                    $titre_display = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
                    $event_id = $event['event_id']; // Récupérer l'ID de l'événement
                    $lieu = html_entity_decode($event['lieu'] ?? '', ENT_QUOTES, 'UTF-8');
                    $club_name = !empty($event['nom_club']) ? htmlspecialchars($event['nom_club'], ENT_QUOTES, 'UTF-8') : '';
                    // Retirer l'attribut onclick
                    echo "<div class='event campus-" . strtolower($campus) . "' style='background-color: $event_color;' title='$titre_display" . ($club_name ? " - $club_name" : "") . "' 
                         data-event-id='" . $event_id . "'
                         data-horaire-debut='" . htmlspecialchars($event['horaire_debut']) . "' 
                         data-horaire-fin='" . htmlspecialchars($event['horaire_fin']) . "' 
                         data-lieu='" . htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8') . "'
                         data-club='" . $club_name . "'>";
                    echo $titre_display;
                    if ($club_name) {
                        echo "<span class='event-club'>$club_name</span>";
                    }
                    echo "</div>";
                }
            }

            echo "</td>";

            $day++;
            $current_day++;
        }

        // Cases vides à la fin
        while ($current_day % 7 != 0) {
            echo "<td></td>";
            $current_day++;
        }
        ?>
    </tr>
</table>

<div class="legend">
    <form id="campus-filter-form">
        <?php foreach ($campus_colors as $campus => $color): ?>
            <label class="legend-item">
                <input type="checkbox" name="campus" value="<?= htmlspecialchars($campus) ?>" checked>
                <span class="legend-color" style="background-color: <?= $color ?>;"></span>
                <?= htmlspecialchars($campus) ?>
            </label>
        <?php endforeach; ?>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attacher les gestionnaires d'événements à tous les éléments avec la classe 'event'
        document.querySelectorAll('.event').forEach(function(eventElement) {
            eventElement.addEventListener('click', function() {
                showEventDetails(this);
            });
        });
        
        // Filtrage par campus (code existant)
        document.querySelectorAll('input[name="campus"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkedValues = Array.from(document.querySelectorAll('input[name="campus"]:checked'))
                    .map(cb => cb.value.toLowerCase());

                document.querySelectorAll('.event').forEach(event => {
                    const classes = event.className.split(' ');
                    const campusClass = classes.find(c => c.startsWith('campus-'));
                    const campus = campusClass?.replace('campus-', '');

                    if (checkedValues.includes(campus)) {
                        event.style.display = 'block';
                    } else {
                        event.style.display = 'none';
                    }
                });
            });
        });
    });

        function showEventDetails(eventElement) {
        const horaireDebut = eventElement.getAttribute('data-horaire-debut');
        const horaireFin = eventElement.getAttribute('data-horaire-fin');
        const lieu = eventElement.getAttribute('data-lieu');
        const titre = eventElement.innerText;
        const eventId = eventElement.getAttribute('data-event-id');
        
        // Vérifier si l'utilisateur est connecté et s'il est abonné
        <?php if ($user_id): ?>
            const isSubscribed = function(eventId) {
                const eventSubscriptions = <?php 
                    // Créer une structure de données plus simple et plus robuste
                    $subscriptions = [];
                    foreach ($events as $event) {
                        $subscriptions[$event['event_id']] = isUserSubscribed($db, $user_id, $event['event_id']);
                    }
                    echo json_encode($subscriptions);
                ?>;
                return eventSubscriptions[eventId] || false;
            };
            
            const subscriptionStatus = isSubscribed(eventId);
            const bellIcon = subscriptionStatus
                ? '<i class="fas fa-bell-slash"></i>'
                : '<i class="fas fa-bell"></i>';
            const bellTitle = subscriptionStatus
                ? 'Se désabonner des notifications'
                : 'S\'abonner aux notifications';
                    
            let subscriptionBtn;
            <?php if (isset($_SESSION['id'])): ?>
                subscriptionBtn = `
                    <button id="subscriptionBtn" 
                        data-event-id="${eventId}" 
                        data-is-subscribed="${subscriptionStatus}" 
                        title="${bellTitle}"
                        style="position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border-radius: 50%; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1em; transition: all 0.2s ease; ${subscriptionStatus ? 'background: #fef2f2; color: #dc2626;' : 'background: #eff6ff; color: #2563eb;'}"
                        onmouseover="this.style.transform='scale(1.1)'"
                        onmouseout="this.style.transform='scale(1)'"
                        onclick="toggleSubscription(event)">
                        ${bellIcon}
                    </button>
                `;
            <?php else: ?>
                subscriptionBtn = `
                    <a href="index.php?page=login" style="position: absolute; top: 16px; right: 16px; display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; font-size: 0.75em; color: #64748b; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        <i class="far fa-bell"></i> Connexion
                    </a>
                `;
            <?php endif; ?>
        <?php else: ?>
            subscriptionBtn = `
                <a href="index.php?page=login" style="position: absolute; top: 16px; right: 16px; display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; font-size: 0.75em; color: #64748b; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    <i class="far fa-bell"></i> Connexion
                </a>
            `;
        <?php endif; ?>

        const detailsHTML = `
            <div style="position: relative;">
                ${subscriptionBtn}
                <h3 style="margin: 0 0 20px 0; color: #111827; font-size: 1.25em; font-weight: 600; line-height: 1.4; padding-right: 50px;">${titre}</h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="width: 36px; height: 36px; background: #f0f9ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-clock" style="color: #0284c7; font-size: 0.9em;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.7em; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Horaires</div>
                            <div style="font-weight: 500; color: #111827; font-size: 0.95em;">${horaireDebut} - ${horaireFin}</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="width: 36px; height: 36px; background: #f0f9ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-map-marker-alt" style="color: #0284c7; font-size: 0.9em;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.7em; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Lieu</div>
                            <div style="font-weight: 500; color: #111827; font-size: 0.95em;">${lieu}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Create overlay backdrop
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.4)';
        overlay.style.zIndex = '999';
        overlay.style.backdropFilter = 'blur(2px)';
        
        const detailsContainer = document.createElement('div');
        detailsContainer.innerHTML = detailsHTML;
        detailsContainer.style.position = 'fixed';
        detailsContainer.style.top = '50%';
        detailsContainer.style.left = '50%';
        detailsContainer.style.transform = 'translate(-50%, -50%)';
        detailsContainer.style.padding = '28px';
        detailsContainer.style.backgroundColor = '#ffffff';
        detailsContainer.style.border = 'none';
        detailsContainer.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
        detailsContainer.style.zIndex = '1000';
        detailsContainer.style.textAlign = 'left';
        detailsContainer.style.direction = 'ltr';
        detailsContainer.style.borderRadius = '12px';
        detailsContainer.style.width = '380px';
        detailsContainer.style.maxWidth = 'calc(100vw - 32px)';

        const closeButton = document.createElement('button');
        closeButton.innerText = 'Fermer';
        closeButton.style.marginTop = '24px';
        closeButton.style.width = '100%';
        closeButton.style.backgroundColor = '#f3f4f6';
        closeButton.style.color = '#374151';
        closeButton.style.border = 'none';
        closeButton.style.padding = '12px 20px';
        closeButton.style.borderRadius = '8px';
        closeButton.style.fontSize = '0.9em';
        closeButton.style.fontWeight = '500';
        closeButton.style.cursor = 'pointer';
        closeButton.style.transition = 'background-color 0.15s ease';
        closeButton.onmouseover = () => { closeButton.style.backgroundColor = '#e5e7eb'; };
        closeButton.onmouseout = () => { closeButton.style.backgroundColor = '#f3f4f6'; };
        
        const closeModal = () => {
            detailsContainer.remove();
            overlay.remove();
            history.replaceState(null, '', location.pathname);
        };
        
        closeButton.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);
        
        detailsContainer.appendChild(closeButton);
        document.body.appendChild(overlay);
        document.body.appendChild(detailsContainer);
    }
    
    function toggleSubscription(event) {
        event.stopPropagation();
        
        const button = event.currentTarget;
        const eventId = button.getAttribute('data-event-id');
        const isSubscribed = button.getAttribute('data-is-subscribed') === 'true';
        
        // Use proper routing through index.php
        const page = isSubscribed ? 'unsubscribe' : 'subscribe';
        window.location.href = `index.php?page=${page}&event_id=${eventId}`;
    }
</script>

</body>
</html>