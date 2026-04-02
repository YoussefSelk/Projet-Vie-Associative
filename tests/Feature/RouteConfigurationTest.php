<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\BaseTestCase;

final class RouteConfigurationTest extends BaseTestCase
{
    public function testLegalRouteIsPublicAndMappedToHomeController(): void
    {
        $routes = require ROOT_PATH . '/routes/web.php';

        $this->assertArrayHasKey('legal', $routes);
        $this->assertSame('HomeController', $routes['legal']['controller']);
        $this->assertSame('legal', $routes['legal']['method']);
        $this->assertFalse($routes['legal']['auth']);
        $this->assertSame('/home/legal.php', $routes['legal']['view']);
    }
}
