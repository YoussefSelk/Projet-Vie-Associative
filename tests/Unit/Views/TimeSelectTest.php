<?php
declare(strict_types=1);

namespace Tests\Unit\Views;

use Tests\BaseTestCase;

require_once dirname(__DIR__, 3) . '/views/includes/time_select.php';

/**
 * Tests unitaires du helper renderTimeSelect() (retour client juin 2026).
 *
 * Le helper rend un <select> d'horaires : VALEUR en 24h (HH:MM) pour le backend,
 * LIBELLE en 12h avec AM/PM pour l'affichage. La désactivation dynamique des
 * créneaux invalides est gérée côté JS (non couverte ici).
 */
class TimeSelectTest extends BaseTestCase
{
    /** Rend le select et renvoie le HTML produit. */
    private function render(string $name, string $id, string $selected = '', int $step = 15, int $startH = 7, int $endH = 23): string
    {
        ob_start();
        renderTimeSelect($name, $id, $selected, $step, $startH, $endH);
        return (string)ob_get_clean();
    }

    public function testRendersSelectWithNameIdAndRequired(): void
    {
        $html = $this->render('horaire_fin', 'horaire_fin', '17:30');
        $this->assertStringContainsString('name="horaire_fin"', $html);
        $this->assertStringContainsString('id="horaire_fin"', $html);
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('class="form-control time-select"', $html);
    }

    public function testGeneratesFullDefaultRangeWith15MinStep(): void
    {
        $html = $this->render('h', 'h');
        // 07:00 → 23:45 par pas de 15 min = 17 heures * 4 = 68 créneaux
        $this->assertSame(68, substr_count($html, '<option'));
        $this->assertStringContainsString('value="07:00"', $html);
        $this->assertStringContainsString('value="23:45"', $html);
        $this->assertStringContainsString('value="13:15"', $html);
    }

    public function testValueIs24hButLabelIs12hAmPm(): void
    {
        $html = $this->render('h', 'h');
        // Matin → AM
        $this->assertStringContainsString('value="07:00">07:00 AM', $html);
        $this->assertStringContainsString('value="11:45">11:45 AM', $html);
        // Midi → 12:00 PM
        $this->assertStringContainsString('value="12:00">12:00 PM', $html);
        // Après-midi → PM avec heure 12h
        $this->assertStringContainsString('value="13:00">01:00 PM', $html);
        $this->assertStringContainsString('value="23:45">11:45 PM', $html);
    }

    public function testSelectedValueIsMarkedSelected(): void
    {
        $html = $this->render('h', 'h', '17:30');
        $this->assertStringContainsString('value="17:30" selected>05:30 PM', $html);
    }

    public function testNormalizesHhMmSsInput(): void
    {
        // Une valeur 'HH:MM:SS' venue de la base doit présélectionner le bon créneau.
        $html = $this->render('h', 'h', '13:30:00');
        $this->assertStringContainsString('value="13:30" selected', $html);
    }

    public function testOffGridExistingValueIsPreservedAndSelected(): void
    {
        // Un ancien événement enregistré hors grille (ex 13:42) ne doit pas être perdu.
        $html = $this->render('h', 'h', '13:42');
        $this->assertStringContainsString('value="13:42" selected', $html);
    }

    public function testEmptySelectionHasNoSelectedAttribute(): void
    {
        $html = $this->render('h', 'h', '');
        $this->assertStringNotContainsString(' selected', $html);
    }

    public function testCustomStepAndRangeAreRespected(): void
    {
        // Pas de 30 min entre 08h et 10h inclus → 08:00,08:30,09:00,09:30,10:00,10:30
        $html = $this->render('h', 'h', '', 30, 8, 10);
        $this->assertSame(6, substr_count($html, '<option'));
        $this->assertStringContainsString('value="08:00"', $html);
        $this->assertStringContainsString('value="10:30"', $html);
        $this->assertStringNotContainsString('value="07:00"', $html);
        $this->assertStringNotContainsString('value="08:15"', $html);
    }

    public function testAmPmBoundaryAtNoonAndElevenPm(): void
    {
        $html = $this->render('h', 'h', '', 60, 0, 23);
        // 00:00 = minuit → 12:00 AM ; 11:00 = 11:00 AM ; 12:00 = 12:00 PM ; 23:00 = 11:00 PM
        $this->assertStringContainsString('value="00:00">12:00 AM', $html);
        $this->assertStringContainsString('value="11:00">11:00 AM', $html);
        $this->assertStringContainsString('value="12:00">12:00 PM', $html);
        $this->assertStringContainsString('value="23:00">11:00 PM', $html);
    }
}
