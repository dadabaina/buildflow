<?php
namespace Tests\Feature;

class PwaSmokeTest extends RecetteTestCase
{
    public function test_pwa_tags_present_and_static_files_valid(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $page = $this->get(route('dashboard'));
        $page->assertOk();
        $page->assertSee('rel="manifest" href="' . asset('manifest.webmanifest') . '"', false);
        $page->assertSee('theme-color', false);

        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertSame('BuildFlow', $manifest['name']);
        $this->assertCount(3, $manifest['icons']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path($icon['src']));
        }

        $this->assertFileExists(public_path('sw.js'));
        $sw = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString("addEventListener('fetch'", $sw);
    }
}
