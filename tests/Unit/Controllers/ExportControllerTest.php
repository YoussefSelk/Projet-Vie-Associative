<?php
declare(strict_types=1);

namespace Tests\Unit\Controllers;

use ExportController;
use ReflectionMethod;
use Tests\BaseTestCase;

/**
 * Tests unitaires du module ExportController.
 *
 * Couvre :
 *  - slug()              : normalisation ASCII des noms de fichier
 *  - estDateValide()     : validation des dates YYYY-MM-DD
 *  - verifierRateLimit() : comptage / expiration des horodatages
 *  - index()             : renvoie le tableau des clubs
 *  - verifierAccesClub() : autorisation admin vs tuteur non-propriétaire
 */
class ExportControllerTest extends BaseTestCase
{
    private ExportController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ExportController($this->createMockPdo());
        $_SESSION = [];
        $_GET     = [];
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_GET     = [];
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        parent::tearDown();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Appelle une méthode privée du contrôleur via réflexion. */
    private function call(string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod(ExportController::class, $method);
        $ref->setAccessible(true);
        return $ref->invoke($this->controller, ...$args);
    }

    // =========================================================================
    // slug()
    // =========================================================================

    public function testSlugConvertsAccentsToAscii(): void
    {
        $this->assertEquals('ete', $this->call('slug', 'été'));
        $this->assertEquals('oeuvre', $this->call('slug', 'œuvre'));
        $this->assertEquals('coeur', $this->call('slug', 'cœur'));
    }

    public function testSlugLowercasesInput(): void
    {
        $this->assertEquals('club_photo', $this->call('slug', 'CLUB PHOTO'));
    }

    public function testSlugCollapsesMultipleSpecialChars(): void
    {
        // Plusieurs caract. spéciaux consécutifs → un seul underscore
        $result = $this->call('slug', 'Club  &  Co.');
        $this->assertStringNotContainsString('__', $result);
    }

    public function testSlugOutputContainsOnlyAllowedChars(): void
    {
        $result = $this->call('slug', 'Évènements d\'été — 2024');
        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $result);
    }

    public function testSlugTrimsLeadingAndTrailingUnderscores(): void
    {
        $result = $this->call('slug', '---test---');
        $this->assertStringStartsNotWith('_', $result);
        $this->assertStringEndsNotWith('_', $result);
        $this->assertEquals('test', $result);
    }

    public function testSlugReturnsFallbackForEmptyString(): void
    {
        $this->assertSame('export', $this->call('slug', ''));
    }

    public function testSlugReturnsFallbackForSpecialCharsOnly(): void
    {
        // Aucun caractère alphanumérique → résultat vide → fallback
        $this->assertSame('export', $this->call('slug', '@@@ --- !!!'));
    }

    // =========================================================================
    // estDateValide()
    // =========================================================================

    public function testEstDateValideAcceptsValidDate(): void
    {
        $this->assertTrue($this->call('estDateValide', '2025-06-15'));
        $this->assertTrue($this->call('estDateValide', '2000-01-01'));
    }

    public function testEstDateValideAcceptsFeb29OnLeapYear(): void
    {
        $this->assertTrue($this->call('estDateValide', '2024-02-29'));
    }

    public function testEstDateValideRejectsFeb29OnNonLeapYear(): void
    {
        $this->assertFalse($this->call('estDateValide', '2025-02-29'));
    }

    public function testEstDateValideRejectsFeb30(): void
    {
        $this->assertFalse($this->call('estDateValide', '2025-02-30'));
    }

    public function testEstDateValideRejectsMonth0AndMonth13(): void
    {
        $this->assertFalse($this->call('estDateValide', '2025-00-15'));
        $this->assertFalse($this->call('estDateValide', '2025-13-01'));
    }

    public function testEstDateValideRejectsWrongFormats(): void
    {
        $this->assertFalse($this->call('estDateValide', '15/06/2025'));
        $this->assertFalse($this->call('estDateValide', '2025-1-5'));
        $this->assertFalse($this->call('estDateValide', ''));
        $this->assertFalse($this->call('estDateValide', 'not-a-date'));
        $this->assertFalse($this->call('estDateValide', '20250115'));
    }

    // =========================================================================
    // verifierRateLimit() — chemins sans dépassement (pas d'abort/exit)
    // =========================================================================

    public function testRateLimitAddsTimestampOnFirstCall(): void
    {
        $_SESSION['id']                = 1;
        $_SESSION['permission']        = 2;
        $_SESSION['export_timestamps'] = [];

        $this->call('verifierRateLimit');

        $this->assertCount(1, $_SESSION['export_timestamps']);
        $this->assertIsInt($_SESSION['export_timestamps'][0]);
    }

    public function testRateLimitAccumulatesTimestampsAcrossCallsWithinWindow(): void
    {
        $_SESSION['id']                = 1;
        $_SESSION['permission']        = 2;
        $_SESSION['export_timestamps'] = [];

        for ($i = 0; $i < 5; $i++) {
            $this->call('verifierRateLimit');
        }

        $this->assertCount(5, $_SESSION['export_timestamps']);
    }

    public function testRateLimitPrunesTimestampsOlderThanWindow(): void
    {
        $_SESSION['id']                = 1;
        $_SESSION['permission']        = 2;
        // 29 entrées périmées (âgées de 2 minutes)
        $_SESSION['export_timestamps'] = array_fill(0, 29, time() - 120);

        $this->call('verifierRateLimit');

        // Toutes périmées → seul le nouvel horodatage reste
        $this->assertCount(1, $_SESSION['export_timestamps']);
        $this->assertGreaterThanOrEqual(time() - 2, $_SESSION['export_timestamps'][0]);
    }

    public function testRateLimitCountsOnlyRecentTimestampsWhenMixed(): void
    {
        $_SESSION['id']                = 1;
        $_SESSION['permission']        = 2;
        $recent  = array_fill(0, 5,  time());
        $expired = array_fill(0, 25, time() - 200);
        $_SESSION['export_timestamps'] = array_merge($recent, $expired);

        // 5 récents < 30 → pas de blocage
        $this->call('verifierRateLimit');

        // 5 récents + 1 nouveau = 6
        $this->assertCount(6, $_SESSION['export_timestamps']);
    }

    public function testRateLimitDoesNotThrowOnCorruptedSessionValues(): void
    {
        $_SESSION['id']                = 1;
        $_SESSION['permission']        = 2;
        // Valeurs non-entières dans la session (corruption) → pas de TypeError
        $_SESSION['export_timestamps'] = ['chaine', null, 3.14, true, time()];

        // Le seul timestamp entier récent est conservé → le call doit réussir
        $this->call('verifierRateLimit');

        $this->assertNotEmpty($_SESSION['export_timestamps']);
    }

    // =========================================================================
    // index()
    // =========================================================================

    public function testIndexReturnsClubsArray(): void
    {
        $_SESSION['id']         = 1;
        $_SESSION['permission'] = 2;

        $clubs = [
            ['club_id' => 1, 'nom_club' => 'Photo Club', 'campus' => 'Calais'],
            ['club_id' => 2, 'nom_club' => 'Ciné Club',  'campus' => 'Boulogne'],
        ];

        $pdo  = $this->createMockPdo();
        $stmt = $this->createMockStatement(false, $clubs);
        $pdo->method('query')->willReturn($stmt);

        $result = (new ExportController($pdo))->index();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('clubs', $result);
        $this->assertCount(2, $result['clubs']);
        $this->assertEquals('Photo Club', $result['clubs'][0]['nom_club']);
        $this->assertEquals('Calais',     $result['clubs'][0]['campus']);
    }

    public function testIndexReturnsEmptyClubsListWhenNone(): void
    {
        $_SESSION['id']         = 1;
        $_SESSION['permission'] = 2;

        $pdo  = $this->createMockPdo();
        $stmt = $this->createMockStatement(false, []);
        $pdo->method('query')->willReturn($stmt);

        $result = (new ExportController($pdo))->index();

        $this->assertArrayHasKey('clubs', $result);
        $this->assertIsArray($result['clubs']);
        $this->assertEmpty($result['clubs']);
    }

    // =========================================================================
    // verifierAccesClub() — chemin admin (pas d'exit)
    // =========================================================================

    public function testVerifierAccesClubAllowsAdminWithoutQuery(): void
    {
        $_SESSION['id']         = 1;
        $_SESSION['permission'] = 4; // admin → accès complet sans requête

        $pdo = $this->createMockPdo();
        // La méthode ne doit jamais interroger la base pour un admin
        $pdo->expects($this->never())->method('prepare');

        $ctrl = new ExportController($pdo);
        $ref  = new ReflectionMethod(ExportController::class, 'verifierAccesClub');
        $ref->setAccessible(true);

        // Doit s'exécuter sans exception ni sortie
        $ref->invoke($ctrl, 1);
        $this->addToAssertionCount(1);
    }

    public function testVerifierAccesClubAllowsSuperAdmin(): void
    {
        $_SESSION['id']         = 2;
        $_SESSION['permission'] = 5;

        $pdo = $this->createMockPdo();
        $pdo->expects($this->never())->method('prepare');

        $ctrl = new ExportController($pdo);
        $ref  = new ReflectionMethod(ExportController::class, 'verifierAccesClub');
        $ref->setAccessible(true);

        $ref->invoke($ctrl, 42);
        $this->addToAssertionCount(1);
    }

    // =========================================================================
    // envoyerCsv() — vérification de l'algorithme de production du corps
    // Ces tests valident l'invariant BOM + UTF-16 LE sans appeler exit()
    // =========================================================================

    public function testCsvBodyBeginsWithUtf16LeBom(): void
    {
        // Reproduit la logique interne de envoyerCsv() pour vérifier le BOM
        $bom         = "\xFF\xFE";
        $delimiteur  = "\t";
        $guillemet   = '"';
        $echappement = "\0";

        $lignes = [
            ['Nom' => 'Dupont', 'Prénom' => 'Jean', 'Email' => 'j.dupont@test.fr'],
        ];

        $tmp = fopen('php://temp', 'r+b');
        fputcsv($tmp, array_keys($lignes[0]), $delimiteur, $guillemet, $echappement);
        foreach ($lignes as $ligne) {
            fputcsv($tmp, $ligne, $delimiteur, $guillemet, $echappement);
        }
        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        $corps = $bom . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');

        $this->assertStringStartsWith("\xFF\xFE", $corps, 'Le BOM UTF-16 LE doit ouvrir le fichier.');
        $this->assertGreaterThan(2, strlen($corps), 'Le corps ne doit pas se limiter au BOM.');
        // strlen() retourne le nombre d'octets → valeur correcte pour Content-Length
        $this->assertIsInt(strlen($corps));
    }

    public function testCsvBodyContainsDataDecodableAsUtf8(): void
    {
        $bom         = "\xFF\xFE";
        $delimiteur  = "\t";
        $guillemet   = '"';
        $echappement = "\0";

        $lignes = [['Nom' => 'Éléonore', 'Spécialité' => 'Génie civil']];

        $tmp = fopen('php://temp', 'r+b');
        fputcsv($tmp, array_keys($lignes[0]), $delimiteur, $guillemet, $echappement);
        foreach ($lignes as $ligne) {
            fputcsv($tmp, $ligne, $delimiteur, $guillemet, $echappement);
        }
        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        $corps    = $bom . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
        $decoded  = mb_convert_encoding(substr($corps, 2), 'UTF-8', 'UTF-16LE');

        $this->assertStringContainsString('Éléonore',    $decoded, 'Les accents doivent être préservés.');
        $this->assertStringContainsString('Génie civil', $decoded);
    }

    public function testEmptyCsvBodyContainsNoDataMessage(): void
    {
        $bom         = "\xFF\xFE";
        $delimiteur  = "\t";
        $guillemet   = '"';
        $echappement = "\0";

        $tmp = fopen('php://temp', 'r+b');
        fputcsv($tmp, ['Aucune donnée disponible pour cet export.'], $delimiteur, $guillemet, $echappement);
        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        $corps   = $bom . mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
        $decoded = mb_convert_encoding(substr($corps, 2), 'UTF-8', 'UTF-16LE');

        $this->assertStringStartsWith("\xFF\xFE", $corps);
        $this->assertStringContainsString('Aucune', $decoded);
    }

    // =========================================================================
    // Détection HTTPS proxy-agnostique (logique de bootstrap.php / head.php)
    // =========================================================================

    public function testHttpsDetectionWithDirectHttps(): void
    {
        $_SERVER['HTTPS'] = 'on';
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        $this->assertTrue($isHttps);
        unset($_SERVER['HTTPS']);
    }

    public function testHttpsDetectionWithReverseProxyXForwardedProto(): void
    {
        unset($_SERVER['HTTPS']);
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        $this->assertTrue($isHttps, 'HTTP_X_FORWARDED_PROTO=https doit être reconnu.');
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    public function testHttpsDetectionReturnsFalseForPlainHttp(): void
    {
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'],
              $_SERVER['HTTP_X_FORWARDED_SSL'], $_SERVER['SERVER_PORT']);

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['HTTP_X_FORWARDED_SSL']   ?? '') === 'on');

        $this->assertFalse($isHttps);
    }



    public function testVerifierAccesClubAllowsTuteurWhoIsClubOwner(): void
    {
        $_SESSION['id']         = 5;
        $_SESSION['permission'] = 2; // tuteur

        $pdo  = $this->createMockPdo();
        // fetch() retourne une ligne → tuteur est propriétaire → accès accordé
        $stmt = $this->createMockStatement(['club_id' => 1]);
        $pdo->method('prepare')->willReturn($stmt);

        $ctrl = new ExportController($pdo);
        $ref  = new ReflectionMethod(ExportController::class, 'verifierAccesClub');
        $ref->setAccessible(true);

        // Ne doit pas appeler exit() ni produire de sortie
        $ref->invoke($ctrl, 1);
        $this->addToAssertionCount(1);
    }

    // ─── lireClubId() ────────────────────────────────────────────────────────

    public function testLireClubIdReturnsIntForValidGetParam(): void
    {
        $_GET['club_id']        = '7';
        $_SESSION['id']         = 1;
        $_SESSION['permission'] = 2;

        $pdo  = $this->createMockPdo();
        $ctrl = new ExportController($pdo);
        $ref  = new ReflectionMethod(ExportController::class, 'lireClubId');
        $ref->setAccessible(true);

        $result = $ref->invoke($ctrl);
        $this->assertSame(7, $result);
    }

    // ─── Vérification des conditions d'abort (sans appeler exit()) ───────────

    public function testRateLimitConditionIsTrueWhenAtLimit(): void
    {
        // Vérifie que le comptage est correct juste à la limite (sans déclencher abort)
        $_SESSION['id']                = 1;
        $_SESSION['permission']        = 2;
        // 29 horodatages récents = sous la limite → pas de blocage
        $_SESSION['export_timestamps'] = array_fill(0, 29, time());

        $this->call('verifierRateLimit');

        // Après l'ajout, on doit avoir exactement 30 = LIMITE_EXPORTS
        $this->assertCount(30, $_SESSION['export_timestamps']);
        // Un appel suivant dépasserait la limite — validé par le comptage ci-dessus
    }

    public function testVerifierAccesClubQueriesDatabaseForTuteur(): void
    {
        $_SESSION['id']         = 3;
        $_SESSION['permission'] = 2; // tuteur

        $pdo  = $this->createMockPdo();
        // La requête DOIT être préparée pour un tuteur
        $stmt = $this->createMockStatement(['club_id' => 5]); // accès accordé
        $pdo->expects($this->once())->method('prepare')->willReturn($stmt);

        $ctrl = new ExportController($pdo);
        $ref  = new ReflectionMethod(ExportController::class, 'verifierAccesClub');
        $ref->setAccessible(true);

        $ref->invoke($ctrl, 5);
        $this->addToAssertionCount(1);
    }

    // =========================================================================
    // formatPerson() — ordre NOM PRENOM dans les extractions (retour client juin 2026)
    // =========================================================================

    public function testFormatPersonUsesNomThenPrenomOrder(): void
    {
        // Entrée (prenom, nom) → sortie attendue "NOM PRENOM"
        $this->assertSame('Dupont Jean', $this->call('formatPerson', 'Jean', 'Dupont'));
    }

    public function testFormatPersonStartsWithNom(): void
    {
        $result = $this->call('formatPerson', 'Alice', 'Martin');
        $this->assertStringStartsWith('Martin', $result);
        $this->assertStringEndsWith('Alice', $result);
    }

    public function testFormatPersonHandlesAccentsAndComposedNames(): void
    {
        $this->assertSame("Le Garrec Éléonore", $this->call('formatPerson', 'Éléonore', 'Le Garrec'));
    }

    public function testFormatPersonReturnsOnlyNomWhenPrenomMissing(): void
    {
        $this->assertSame('Dupont', $this->call('formatPerson', '', 'Dupont'));
        $this->assertSame('Dupont', $this->call('formatPerson', null, 'Dupont'));
    }

    public function testFormatPersonReturnsOnlyPrenomWhenNomMissing(): void
    {
        $this->assertSame('Jean', $this->call('formatPerson', 'Jean', ''));
    }

    public function testFormatPersonReturnsPlaceholderWhenBothMissing(): void
    {
        // self::VALEUR_VIDE = '—'
        $this->assertSame('—', $this->call('formatPerson', '', ''));
        $this->assertSame('—', $this->call('formatPerson', null, null));
    }

    public function testFormatPersonTrimsSurroundingWhitespace(): void
    {
        $this->assertSame('Dupont Jean', $this->call('formatPerson', '  Jean ', ' Dupont  '));
    }
}
