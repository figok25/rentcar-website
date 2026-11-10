<?php

namespace Tests\Feature;

use App\Enums\VehicleStatus;
use App\Models\Faq;
use App\Models\Tour;
use App\Models\TravelService;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleImage;
use App\Models\VehicleRate;
use App\Models\WebsiteSetting;
use App\Support\StructuredData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoQaTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(array $attrs = [], int $price = 300000, ?VehicleCategory $cat = null): Vehicle
    {
        $cat ??= VehicleCategory::factory()->create();
        $v = Vehicle::factory()->create(['vehicle_category_id' => $cat->id] + $attrs);
        VehicleRate::factory()->create(['vehicle_id' => $v->id, 'price_per_day' => $price]);
        VehicleImage::factory()->create(['vehicle_id' => $v->id, 'is_primary' => true]);

        return $v;
    }

    /** Ambil blok JSON-LD pertama dari HTML sebagai array. */
    private function jsonLd(string $html): array
    {
        $this->assertSame(1, preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m), 'JSON-LD tidak ditemukan');

        return json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
    }

    private function node(array $doc, string $type): ?array
    {
        return collect($doc['@graph'])->firstWhere('@type', $type);
    }

    // ---- sitemap & robots -------------------------------------------------

    public function test_sitemap_lists_only_public_pages(): void
    {
        $shown = $this->vehicle(['name' => 'Tampil']);
        $draft = $this->vehicle(['is_published' => false]);
        $inactive = $this->vehicle([], 100000, VehicleCategory::factory()->inactive()->create());
        $tour = Tour::factory()->create();

        $xml = $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->getContent();

        $this->assertNotFalse(simplexml_load_string($xml), 'Sitemap bukan XML valid');
        $this->assertStringContainsString(route('home'), $xml);
        $this->assertStringContainsString(route('vehicles.index'), $xml);
        $this->assertStringContainsString($shown->publicUrl(), $xml);
        $this->assertStringNotContainsString($draft->slug, $xml);
        $this->assertStringNotContainsString($inactive->slug, $xml);
        // Fitur wisata masih mati -> tidak ada.
        $this->assertStringNotContainsString($tour->slug, $xml);
        $this->assertStringNotContainsString('/rental-motor', $xml);
        $this->assertMatchesRegularExpression('#<lastmod>\d{4}-\d{2}-\d{2}</lastmod>#', $xml);
    }

    public function test_sitemap_follows_feature_toggles(): void
    {
        WebsiteSetting::set('feature_tours_enabled', true);
        WebsiteSetting::set('feature_travel_enabled', true);
        WebsiteSetting::set('feature_motor_enabled', true);
        $tour = Tour::factory()->create();
        $draftTour = Tour::factory()->unpublished()->create();
        $travel = TravelService::factory()->create();
        $motorCat = VehicleCategory::firstOrCreate(['slug' => 'motor'], ['name' => 'Motor', 'is_active' => true]);
        $motor = $this->vehicle([], 100000, $motorCat);

        $xml = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString($tour->publicUrl(), $xml);
        $this->assertStringContainsString($travel->publicUrl(), $xml);
        $this->assertStringContainsString($motor->publicUrl(), $xml);
        $this->assertStringContainsString(route('motor.index'), $xml);
        $this->assertStringNotContainsString($draftTour->slug, $xml);
    }

    public function test_robots_txt_default_and_staging_mode(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false);

        config(['seo.indexable' => false]);

        $this->get('/robots.txt')->assertSee('Disallow: /', false)->assertDontSee('Sitemap:', false);
        $this->get('/sitemap.xml')->assertNotFound();
        $this->get('/')->assertSee('<meta name="robots" content="noindex,nofollow">', false);
    }

    // ---- meta robots & canonical -----------------------------------------

    public function test_filtered_catalog_is_noindex_and_canonical_has_no_query(): void
    {
        $this->vehicle(['brand' => 'Honda']);

        $this->get('/rental-mobil')->assertDontSee('name="robots"', false);

        $this->get('/rental-mobil?brand=Honda')
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('vehicles.index').'">', false);
    }

    public function test_page_two_has_its_own_canonical(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->vehicle();
        }

        $this->get('/rental-mobil?page=2')
            ->assertSee('<link rel="canonical" href="'.route('vehicles.index').'?page=2">', false);
    }

    // ---- data terstruktur --------------------------------------------------

    public function test_home_structured_data_business_website_and_faq(): void
    {
        WebsiteSetting::set('business_name', 'Rental Maju');
        WebsiteSetting::set('address', 'Jl. Contoh 1');
        WebsiteSetting::set('instagram_url', 'https://instagram.com/rentalmaju');
        Faq::factory()->create(['question' => 'Bisa lepas kunci?', 'answer' => 'Bisa.']);

        $doc = $this->jsonLd($this->get('/')->getContent());

        $this->assertSame('https://schema.org', $doc['@context']);
        $business = $this->node($doc, 'AutoRental');
        $this->assertSame('Rental Maju', $business['name']);
        $this->assertSame('Jl. Contoh 1', $business['address']['streetAddress']);
        $this->assertSame(['https://instagram.com/rentalmaju'], $business['sameAs']);
        $this->assertArrayNotHasKey('telephone', $business);
        $this->assertNotNull($this->node($doc, 'WebSite'));
        $this->assertSame('Bisa lepas kunci?', $this->node($doc, 'FAQPage')['mainEntity'][0]['name']);
    }

    public function test_home_has_no_faq_node_when_no_faq(): void
    {
        $doc = $this->jsonLd($this->get('/')->getContent());

        $this->assertNull($this->node($doc, 'FAQPage'));
    }

    public function test_vehicle_structured_data_price_range_and_availability(): void
    {
        $v = $this->vehicle(['name' => 'Avanza', 'status' => VehicleStatus::Available], 300000);
        VehicleRate::factory()->create(['vehicle_id' => $v->id, 'service_type' => 'with_driver', 'price_per_day' => 550000]);

        $doc = $this->jsonLd($this->get($v->publicUrl())->getContent());
        $product = $this->node($doc, 'Product');

        $this->assertSame('Avanza', $product['name']);
        $this->assertSame('IDR', $product['offers']['priceCurrency']);
        $this->assertSame(300000, $product['offers']['lowPrice']);
        $this->assertSame(550000, $product['offers']['highPrice']);
        $this->assertSame('https://schema.org/InStock', $product['offers']['availability']);
        $this->assertCount(1, $product['image']);

        $crumbs = $this->node($doc, 'BreadcrumbList')['itemListElement'];
        $this->assertSame(['Beranda', 'Rental Mobil', 'Avanza'], array_column($crumbs, 'name'));
    }

    public function test_rented_vehicle_is_marked_out_of_stock(): void
    {
        $v = $this->vehicle(['status' => VehicleStatus::Rented]);

        $product = $this->node($this->jsonLd($this->get($v->publicUrl())->getContent()), 'Product');

        $this->assertSame('https://schema.org/OutOfStock', $product['offers']['availability']);
    }

    public function test_tour_and_travel_structured_data(): void
    {
        WebsiteSetting::set('feature_tours_enabled', true);
        WebsiteSetting::set('feature_travel_enabled', true);
        $tour = Tour::factory()->create(['price' => 750000]);
        $free = Tour::factory()->create(['price' => null]);
        $travel = TravelService::factory()->create(['route_label' => 'Sby - Mlg']);

        $trip = $this->node($this->jsonLd($this->get($tour->publicUrl())->getContent()), 'TouristTrip');
        $this->assertSame(750000, $trip['offers']['price']);

        $tripFree = $this->node($this->jsonLd($this->get($free->publicUrl())->getContent()), 'TouristTrip');
        $this->assertArrayNotHasKey('offers', $tripFree);

        $service = $this->node($this->jsonLd($this->get($travel->publicUrl())->getContent()), 'Service');
        $this->assertSame('Sby - Mlg', $service['areaServed']);
    }

    public function test_json_ld_cannot_break_out_of_script_tag(): void
    {
        $v = $this->vehicle(['name' => 'X</script><script>alert(1)</script>']);

        $html = $this->get($v->publicUrl())->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $doc = $this->jsonLd($html);
        $this->assertSame('X</script><script>alert(1)</script>', $this->node($doc, 'Product')['name']);
    }

    public function test_business_node_omits_empty_fields(): void
    {
        $node = StructuredData::business(['name' => 'A', 'logo' => null, 'phone' => '', 'address' => null, 'instagram' => null, 'facebook' => null]);

        $this->assertSame(['@type', '@id', 'name', 'url'], array_keys($node));
    }

    // ---- error & keamanan --------------------------------------------------

    public function test_custom_404_page(): void
    {
        $this->get('/halaman-tidak-ada')->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('name="robots" content="noindex"', false);
    }

    public function test_security_headers_on_public_pages(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
        $this->get('/sitemap.xml')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    // ---- jumlah query tidak bertambah seiring jumlah data -----------------

    private function queryCount(string $url): int
    {
        $this->app->forgetScopedInstances();
        $this->get($url)->assertOk(); // pemanasan cache pengaturan
        $this->app->forgetScopedInstances();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function test_query_count_does_not_grow_with_data(): void
    {
        WebsiteSetting::set('feature_tours_enabled', true);
        WebsiteSetting::set('feature_travel_enabled', true);

        $first = $this->vehicle(['is_featured' => true]);
        Tour::factory()->create(['is_featured' => true]);
        TravelService::factory()->create();
        Faq::factory()->create();

        $urls = ['/', '/rental-mobil', $first->publicUrl(), '/paket-wisata', '/travel', '/sitemap.xml'];
        $before = array_map(fn ($u) => $this->queryCount($u), $urls);

        for ($i = 0; $i < 8; $i++) {
            $this->vehicle(['is_featured' => true]);
            Tour::factory()->create(['is_featured' => true]);
            TravelService::factory()->create();
            Faq::factory()->create();
        }

        $after = array_map(fn ($u) => $this->queryCount($u), $urls);

        $this->assertSame($before, $after, 'Jumlah query bertambah seiring data (kemungkinan N+1)');
    }
}
