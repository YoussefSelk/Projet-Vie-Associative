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
            ? '<i class="fas fa-bell-slash" style="color:rgb(149, 0, 0);"></i>'
            : '<i class="fas fa-bell" style="color: #005795;"></i>';
                    
            let subscriptionBtn;
            <?php if (isset($_SESSION['id'])): ?>
                subscriptionBtn = `
                    <button id="subscriptionBtn" 
                        data-event-id="${eventId}" 
                        data-is-subscribed="${subscriptionStatus}" 
                        data-event-name="${encodeURIComponent(titre)}"
                        style="background: none; border: none; cursor: pointer; font-size: 1.5em; position: absolute; top: 20px; right: 20px;"
                        onclick="toggleSubscription(event)">
                        ${bellIcon}
                    </button>
                `;
            <?php else: ?>
                subscriptionBtn = `
                    <div style="position: absolute; top: 20px; right: 20px; font-size: 0.8em; color: #777;">
                        <i class="far fa-bell"></i> Connectez-vous pour vous abonner
                    </div>
                `;
            <?php endif; ?>
        <?php else: ?>
            subscriptionBtn = `
                <div style="position: absolute; top: 20px; right: 20px; font-size: 0.8em; color: #777;">
                    <i class="far fa-bell"></i> Connectez-vous pour vous abonner
                </div>
            `;
        <?php endif; ?>

        const detailsHTML = `
            <div style="text-align: left; direction: ltr; position: relative;">
                <h3 style="text-align: left; padding-right: 40px;">${titre}</h3>
                ${subscriptionBtn}
                <p style="text-align: left;"><strong style="display: inline-block; width: 140px;">Horaire de début:</strong> ${horaireDebut}</p>
                <p style="text-align: left;"><strong style="display: inline-block; width: 140px;">Horaire de fin:</strong> ${horaireFin}</p>
                <p style="text-align: left;"><strong style="display: inline-block; width: 140px;">Lieu:</strong> ${lieu}</p>
            </div>
        `;

        const detailsContainer = document.createElement('div');
        detailsContainer.innerHTML = detailsHTML;
        detailsContainer.style.position = 'fixed';
        detailsContainer.style.top = '50%';
        detailsContainer.style.left = '50%';
        detailsContainer.style.transform = 'translate(-50%, -50%)';
        detailsContainer.style.padding = '20px';
        detailsContainer.style.backgroundColor = '#fff';
        detailsContainer.style.border = '2px solid #005795';
        detailsContainer.style.boxShadow = '0px 4px 6px rgba(0, 0, 0, 0.1)';
        detailsContainer.style.zIndex = '1000';
        detailsContainer.style.textAlign = 'left';
        detailsContainer.style.direction = 'ltr';
        detailsContainer.style.borderRadius = '8px';
        detailsContainer.style.minWidth = '300px';

        const closeButton = document.createElement('button');
        closeButton.innerText = 'Fermer';
        closeButton.style.marginTop = '10px';
        closeButton.style.backgroundColor = '#005795';
        closeButton.style.color = 'white';
        closeButton.style.border = 'none';
        closeButton.style.padding = '10px 20px';
        closeButton.style.borderRadius = '8px';
        closeButton.style.fontSize = '1em';
        closeButton.style.cursor = 'pointer';
        closeButton.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.2)';
        closeButton.addEventListener('click', () => {
        detailsContainer.remove();
        // Nettoyage pour éviter réapparition après refresh
        history.replaceState(null, '', location.pathname);
        });
        detailsContainer.appendChild(closeButton);

        document.body.appendChild(detailsContainer);
    }
    
    function toggleSubscription(event) {
        event.stopPropagation(); // Empêcher la propagation de l'événement
        
        const button = event.currentTarget;
        const eventId = button.getAttribute('data-event-id');
        const isSubscribed = button.getAttribute('data-is-subscribed') === 'true';
        const eventName = button.getAttribute('data-event-name');
        
        // Inverser l'état d'abonnement
        const action = isSubscribed ? 'unsubscribe' : 'subscribe';
        
        // Rediriger vers un script PHP qui gère l'abonnement
        window.location.href = `subscribe_event.php?event_id=${eventId}&action=${action}&event_name=${eventName}`;
    }
</script>

</body>
</html>