<?php

namespace Tests\Feature;

use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_and_footer_use_the_bundled_mark_and_two_tone_name_without_an_uploaded_logo(): void
    {
        WebsiteSetting::set('business_name', 'Empatra DigiTech');

        $html = $this->get('/')->assertOk()->getContent();

        // Ikon bawaan dipakai di header dan footer.
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'images/logo-mark.png'));
        // Kata pertama navy, sisanya merah (versi terang di footer).
        $this->assertStringContainsString('<span class="text-brand">Empatra</span>', $html);
        $this->assertStringContainsString('<span class="text-accent">DigiTech</span>', $html);
        $this->assertStringContainsString('<span class="text-accent-light">DigiTech</span>', $html);
    }

    public function test_single_word_name_has_no_second_line(): void
    {
        WebsiteSetting::set('business_name', 'Empatra');

        // Halaman Kontak tidak punya hero, jadi tidak ada teks merah selain baris kedua nama bisnis.
        $html = $this->get('/kontak')->assertOk()->getContent();

        $this->assertStringContainsString('<span class="text-brand">Empatra</span>', $html);
        $this->assertStringNotContainsString('text-accent">', $html);
    }

    public function test_uploaded_logo_replaces_the_bundled_mark(): void
    {
        WebsiteSetting::set('business_name', 'Empatra DigiTech');
        WebsiteSetting::set('logo_path', 'branding/logo.png');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('storage/branding/logo.png', $html);
        $this->assertStringNotContainsString('images/logo-mark.png', $html);
    }

    public function test_the_bundled_logo_files_exist(): void
    {
        $this->assertFileExists(public_path('images/logo-mark.png'));
        $this->assertFileExists(public_path('images/logo-empatra.png'));
    }

    public function test_business_name_is_escaped_in_the_brand(): void
    {
        WebsiteSetting::set('business_name', '<script>alert(1)</script> Rental');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }
}
