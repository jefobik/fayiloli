<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ThemeService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ThemeService $themeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->themeService = app(ThemeService::class);
    }

    /** @test */
    public function it_returns_system_theme_by_default()
    {
        $theme = $this->themeService->getThemePreference();
        $this->assertEquals('system', $theme);
    }

    /** @test */
    public function it_validates_theme_correctly()
    {
        $this->assertTrue($this->themeService->isValidTheme('light'));
        $this->assertTrue($this->themeService->isValidTheme('dark'));
        $this->assertTrue($this->themeService->isValidTheme('system'));
        $this->assertFalse($this->themeService->isValidTheme('invalid'));
    }

    /** @test */
    public function it_returns_available_themes()
    {
        $themes = $this->themeService->getAvailableThemes();
        $this->assertCount(3, $themes);
        $this->assertContains('light', $themes);
        $this->assertContains('dark', $themes);
        $this->assertContains('system', $themes);
    }

    /** @test */
    public function it_gets_theme_labels()
    {
        $this->assertEquals('Light', $this->themeService->getThemeLabel('light'));
        $this->assertEquals('Dark', $this->themeService->getThemeLabel('dark'));
        $this->assertEquals('System', $this->themeService->getThemeLabel('system'));
    }

    /** @test */
    public function it_gets_theme_icons()
    {
        $this->assertEquals('fas fa-sun', $this->themeService->getThemeIcon('light'));
        $this->assertEquals('fas fa-moon', $this->themeService->getThemeIcon('dark'));
        $this->assertEquals('fas fa-desktop', $this->themeService->getThemeIcon('system'));
    }

    /** @test */
    public function it_sets_theme_preference_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->themeService->setThemePreference('dark');

        $this->assertEquals('dark', $user->fresh()->theme);
    }

    /** @test */
    public function it_throws_exception_for_invalid_theme()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->themeService->setThemePreference('invalid');
    }

    /** @test */
    public function it_generates_bootstrap_script()
    {
        $script = $this->themeService->generateThemeBootstrapScript('dark');

        $this->assertStringContainsString('<script>', $script);
        $this->assertStringContainsString('</script>', $script);
        $this->assertStringContainsString('dark', $script);
    }

    /** @test */
    public function it_generates_bootstrap_script_without_tags()
    {
        $script = $this->themeService->generateThemeBootstrapScript('light', false);

        $this->assertStringNotContainsString('<script>', $script);
        $this->assertStringNotContainsString('</script>', $script);
        $this->assertStringContainsString('light', $script);
    }

    /** @test */
    public function it_returns_html_attributes()
    {
        $user = User::factory()->create(['theme' => 'dark']);
        $this->actingAs($user);

        $attrs = $this->themeService->getHtmlAttributes();

        $this->assertArrayHasKey('data-theme', $attrs);
        $this->assertArrayHasKey('data-theme-preference', $attrs);
        $this->assertEquals('dark', $attrs['data-theme']);
    }

    /** @test */
    public function it_handles_user_logout()
    {
        $user = User::factory()->create(['theme' => 'dark']);
        $this->actingAs($user);

        $this->assertEquals('dark', $this->themeService->getThemePreference());

        // Logout
        auth()->logout();

        $this->assertEquals('system', $this->themeService->getThemePreference());
    }
}
