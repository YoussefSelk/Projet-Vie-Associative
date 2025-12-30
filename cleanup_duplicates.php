<?php
/**
 * Database Analysis and Cleanup Script
 * Analyzes duplicates and fixes them properly
 */

require_once __DIR__ . '/config/bootstrap.php';

echo "<html><head><title>Database Cleanup</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #0066cc; }
h2 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #0066cc; color: white; }
tr:nth-child(even) { background: #f9f9f9; }
.success { color: green; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.danger { color: red; font-weight: bold; }
.btn { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
.btn-success { background: #28a745; }
.btn-primary { background: #0066cc; }
</style>
</head><body><div class='container'>";

echo "<h1>🔍 Analyse de la base de données</h1>";

// 1. Find all duplicates
echo "<h2>1. Clubs en double</h2>";
$stmt = $db->query("
    SELECT nom_club, COUNT(*) as cnt, GROUP_CONCAT(club_id ORDER BY club_id) as ids
    FROM fiche_club 
    GROUP BY nom_club 
    HAVING cnt > 1
    ORDER BY cnt DESC
");
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "<p class='success'>✅ Aucun doublon trouvé!</p>";
} else {
    echo "<p class='danger'>⚠️ " . count($duplicates) . " noms de clubs en double trouvés!</p>";
    echo "<table><tr><th>Nom du club</th><th>Occurrences</th><th>IDs</th><th>Détails</th></tr>";
    
    foreach ($duplicates as $dup) {
        $ids = explode(',', $dup['ids']);
        
        // Get details for each duplicate
        $details = [];
        foreach ($ids as $id) {
            $detailStmt = $db->prepare("
                SELECT c.club_id, c.description, c.validation_finale, 
                       (SELECT COUNT(*) FROM membres_club WHERE club_id = c.club_id) as member_count
                FROM fiche_club c WHERE c.club_id = ?
            ");
            $detailStmt->execute([$id]);
            $detail = $detailStmt->fetch(PDO::FETCH_ASSOC);
            $details[] = "ID $id: " . ($detail['member_count'] ?? 0) . " membres, " . 
                        (empty($detail['description']) ? "❌ Pas de description" : "✅ A une description") . ", " .
                        ($detail['validation_finale'] == 1 ? "✅ Validé" : "⏳ Non validé");
        }
        
        echo "<tr>
            <td><strong>" . htmlspecialchars($dup['nom_club']) . "</strong></td>
            <td class='danger'>{$dup['cnt']}</td>
            <td>{$dup['ids']}</td>
            <td><small>" . implode("<br>", $details) . "</small></td>
        </tr>";
    }
    echo "</table>";
}

// 2. Show total counts
echo "<h2>2. Statistiques</h2>";
$totalClubs = $db->query("SELECT COUNT(*) FROM fiche_club")->fetchColumn();
$validatedClubs = $db->query("SELECT COUNT(*) FROM fiche_club WHERE validation_finale = 1")->fetchColumn();
$clubsWithDesc = $db->query("SELECT COUNT(*) FROM fiche_club WHERE description IS NOT NULL AND TRIM(description) != ''")->fetchColumn();
$totalMembers = $db->query("SELECT COUNT(*) FROM membres_club")->fetchColumn();

echo "<table>
<tr><td>Total clubs</td><td><strong>$totalClubs</strong></td></tr>
<tr><td>Clubs validés</td><td><strong>$validatedClubs</strong></td></tr>
<tr><td>Clubs avec description</td><td><strong>$clubsWithDesc</strong></td></tr>
<tr><td>Total membres</td><td><strong>$totalMembers</strong></td></tr>
</table>";

// 3. Cleanup action
if (isset($_GET['cleanup']) && $_GET['cleanup'] === 'yes') {
    echo "<h2>3. 🧹 Nettoyage en cours...</h2>";
    
    $deletedClubs = 0;
    $movedMembers = 0;
    
    foreach ($duplicates as $dup) {
        $ids = explode(',', $dup['ids']);
        
        // Find the best club to keep (the one with description and most members)
        $bestId = null;
        $bestScore = -1;
        
        foreach ($ids as $id) {
            $scoreStmt = $db->prepare("
                SELECT c.club_id,
                       (CASE WHEN c.description IS NOT NULL AND TRIM(c.description) != '' THEN 100 ELSE 0 END) +
                       (CASE WHEN c.validation_finale = 1 THEN 50 ELSE 0 END) +
                       (SELECT COUNT(*) FROM membres_club WHERE club_id = c.club_id) as score
                FROM fiche_club c WHERE c.club_id = ?
            ");
            $scoreStmt->execute([$id]);
            $score = $scoreStmt->fetch(PDO::FETCH_ASSOC)['score'];
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = $id;
            }
        }
        
        echo "<p>📌 <strong>" . htmlspecialchars($dup['nom_club']) . "</strong>: Keeping ID $bestId (score: $bestScore)</p>";
        
        // Move members from duplicates to the best club, then delete duplicates
        foreach ($ids as $id) {
            if ($id != $bestId) {
                // Get members from this duplicate
                $membersStmt = $db->prepare("SELECT membre_id, role FROM membres_club WHERE club_id = ?");
                $membersStmt->execute([$id]);
                $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($members as $member) {
                    // Check if member already exists in best club
                    $checkStmt = $db->prepare("SELECT id FROM membres_club WHERE club_id = ? AND membre_id = ?");
                    $checkStmt->execute([$bestId, $member['membre_id']]);
                    
                    if (!$checkStmt->fetch()) {
                        // Move member to best club
                        $db->prepare("UPDATE membres_club SET club_id = ? WHERE club_id = ? AND membre_id = ?")
                           ->execute([$bestId, $id, $member['membre_id']]);
                        $movedMembers++;
                    } else {
                        // Delete duplicate member entry
                        $db->prepare("DELETE FROM membres_club WHERE club_id = ? AND membre_id = ?")
                           ->execute([$id, $member['membre_id']]);
                    }
                }
                
                // Delete any remaining members
                $db->prepare("DELETE FROM membres_club WHERE club_id = ?")->execute([$id]);
                
                // Delete the duplicate club
                $db->prepare("DELETE FROM fiche_club WHERE club_id = ?")->execute([$id]);
                $deletedClubs++;
                
                echo "<p class='warning'>   ↳ Deleted duplicate club ID $id</p>";
            }
        }
    }
    
    echo "<p class='success'>✅ Nettoyage terminé! $deletedClubs clubs supprimés, $movedMembers membres transférés.</p>";
    echo "<a href='index.php?page=clubs' class='btn btn-success'>Voir les clubs</a> ";
    echo "<a href='cleanup_duplicates.php' class='btn btn-primary'>Vérifier à nouveau</a>";
    
} else {
    if (!empty($duplicates)) {
        echo "<h2>3. Action</h2>";
        echo "<p>Cliquez sur le bouton ci-dessous pour nettoyer les doublons. Le script va:</p>
        <ul>
            <li>Garder le club avec la meilleure description et le plus de membres</li>
            <li>Transférer les membres des doublons vers le club conservé</li>
            <li>Supprimer les clubs en double</li>
        </ul>";
        echo "<a href='cleanup_duplicates.php?cleanup=yes' class='btn' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer les doublons?');\">🗑️ Nettoyer les doublons</a>";
    }
}

echo "<h2>4. Retour</h2>";
echo "<a href='index.php?page=home' class='btn btn-primary'>Retour à l'accueil</a>";

echo "</div></body></html>";
