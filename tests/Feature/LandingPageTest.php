<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_successfully(): void
    {
        $view = $this->view('landing');

        $view->assertSee('eKalinga+');
        $view->assertSee('Municipality of Sulop');
        $view->assertSee('Budget Management');
        $view->assertSee('Project Distribution');
        $view->assertSee('Masterlist (CRS Live)');
        $view->assertSee('GGMS Transactions');
        $view->assertSee('Reports & Audits', false);
    }
}
