<?php

namespace App\Http\Controllers;

use App\Services\SitemapBuilder;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(SitemapBuilder $builder): Response
    {
        abort_unless(config('seo.indexable'), 404);

        return response($builder->xml(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        if (! config('seo.indexable')) {
            $body = "User-agent: *\nDisallow: /\n";
        } else {
            $body = implode("\n", [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /tanya-via-whatsapp',
                'Disallow: /livewire',
                '',
                'Sitemap: '.url('/sitemap.xml'),
            ])."\n";
        }

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
