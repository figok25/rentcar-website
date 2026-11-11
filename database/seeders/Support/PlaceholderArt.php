<?php

namespace Database\Seeders\Support;

/**
 * Ilustrasi SVG sederhana sebagai foto contoh. Tidak memerlukan ekstensi GD
 * dan bisa diganti dengan foto asli dari admin kapan saja.
 */
class PlaceholderArt
{
    /** Bentuk bodi (tampak samping, menghadap kanan): [x awal, x akhir, y atas bodi, kabin dasar kiri, kabin dasar kanan, kabin atas kiri, kabin atas kanan, tinggi kabin] */
    private const SHAPES = [
        'hatch' => [200, 1000, 470, 250, 800, 330, 740, 130],
        'mpv' => [180, 1020, 460, 220, 880, 290, 840, 150],
        'suv' => [190, 1010, 440, 240, 870, 310, 800, 150],
        'van' => [230, 970, 335, 160, 930, 200, 900, 190],
        'minibus' => [110, 1090, 320, 120, 970, 160, 950, 210],
    ];

    public static function vehicle(string $shape, string $color, string $label, string $sub, bool $flip = false, int $tone = 0): string
    {
        $bg = self::background($tone);
        $art = $shape === 'motor' ? self::motor($color) : self::car($shape, $color);

        if ($flip) {
            $art = '<g transform="translate(1200 0) scale(-1 1)">'.$art.'</g>';
        }

        return self::wrap($bg, $art.self::caption($label, $sub));
    }

    public static function landscape(string $label, string $sub, int $variant = 0, bool $flip = false): string
    {
        $palettes = [
            ['#ffd9a0', '#f7a35c', '#6b4f5c', '#4a3744', '#2b2530'], // matahari terbit
            ['#cfe8f3', '#9ccbe0', '#4f8a6b', '#336b50', '#1f4a38'], // hijau
            ['#2a3a5c', '#1b2540', '#31495e', '#22364a', '#121c28'], // malam
            ['#f3e3c3', '#e3c08a', '#a4733a', '#7d5527', '#553a1a'], // kota/pasir
        ];
        [$sky1, $sky2, $m1, $m2, $ground] = $palettes[$variant % 4];

        $svg = '<defs><linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'.$sky1.'"/><stop offset="1" stop-color="'.$sky2.'"/></linearGradient></defs>'
            .'<rect width="1200" height="900" fill="url(#sky)"/>'
            .'<circle cx="'.($flip ? 900 : 300).'" cy="330" r="90" fill="#fff6d6" opacity=".9"/>'
            .'<polygon points="-50,650 280,330 560,650" fill="'.$m1.'"/>'
            .'<polygon points="350,650 700,250 1050,650" fill="'.$m2.'"/>'
            .'<polygon points="780,650 1000,420 1250,650" fill="'.$m1.'" opacity=".9"/>'
            .'<rect y="600" width="1200" height="300" fill="'.$ground.'"/>'
            .'<path d="M0 700 Q300 640 600 700 T1200 690 V900 H0Z" fill="#000" opacity=".12"/>';

        if ($flip) {
            $svg = '<g transform="translate(1200 0) scale(-1 1)">'.$svg.'</g>';
        }

        return self::wrap('', $svg.self::caption($label, $sub, true));
    }

    private static function wrap(string $bg, string $content): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900" role="img">'.$bg.$content.'</svg>';
    }

    private static function background(int $tone): string
    {
        $tones = [['#e8eef2', '#cfd9e0'], ['#f6efd2', '#ead9a0'], ['#e3ecea', '#c4d6d1'], ['#ece6ee', '#d6cadb']];
        [$a, $b] = $tones[$tone % 4];

        return '<defs><linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'.$a.'"/><stop offset="1" stop-color="'.$b.'"/></linearGradient></defs>'
            .'<rect width="1200" height="900" fill="url(#bg)"/>'
            .'<rect y="640" width="1200" height="260" fill="#2b333b" opacity=".08"/>';
    }

    private static function car(string $shape, string $color): string
    {
        [$x0, $x1, $top, $cbl, $cbr, $ctl, $ctr, $ch] = self::SHAPES[$shape];
        $base = 595;
        $tall = in_array($shape, ['van', 'minibus'], true);
        $bodyTop = $tall ? $top : $top;
        $dark = self::shade($color, -28);

        $cabinTop = $top - $ch;
        $svg = '<defs><linearGradient id="body" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'.self::shade($color, 18).'"/><stop offset="1" stop-color="'.$dark.'"/></linearGradient></defs>';
        $svg .= '<ellipse cx="600" cy="640" rx="'.(($x1 - $x0) / 2 + 30).'" ry="26" fill="#000" opacity=".18"/>';

        if ($tall) {
            // Bodi kotak satu volume.
            $svg .= '<path d="M'.$x0.' '.$base.' V'.($top + 40).' Q'.$x0.' '.$top.' '.($x0 + 40).' '.$top.' H'.($x1 - 120).' Q'.($x1 - 40).' '.$top.' '.($x1 - 10).' '.($top + 70).' L'.$x1.' '.$base.' Z" fill="url(#body)"/>';
            $svg .= '<path d="M'.($x0 + 60).' '.($top + 20).' H'.($x1 - 150).' L'.($x1 - 80).' '.($top + 120).' H'.($x0 + 60).' Z" fill="#cfe3ee" opacity=".92"/>';
            for ($i = 1; $i < 5; $i++) {
                $x = $x0 + 60 + $i * (($x1 - 210 - $x0) / 5);
                $svg .= '<rect x="'.$x.'" y="'.($top + 20).'" width="8" height="100" fill="'.$dark.'"/>';
            }
        } else {
            $svg .= '<path d="M'.$cbl.' '.$top.' L'.$ctl.' '.$cabinTop.' H'.$ctr.' L'.$cbr.' '.$top.' Z" fill="url(#body)"/>';
            $svg .= '<rect x="'.$x0.'" y="'.$top.'" width="'.($x1 - $x0).'" height="'.($base - $top).'" rx="42" fill="url(#body)"/>';
            $svg .= '<path d="M'.($cbl + 24).' '.($top - 10).' L'.($ctl + 14).' '.($cabinTop + 18).' H'.(($ctl + $ctr) / 2 - 6).' V'.($top - 10).' Z" fill="#cfe3ee" opacity=".92"/>';
            $svg .= '<path d="M'.(($ctl + $ctr) / 2 + 6).' '.($top - 10).' V'.($cabinTop + 18).' H'.($ctr - 14).' L'.($cbr - 24).' '.($top - 10).' Z" fill="#cfe3ee" opacity=".92"/>';
        }

        $svg .= '<rect x="'.($x0 + 12).'" y="'.($top + 34).'" width="30" height="22" rx="6" fill="#c8372d"/>';
        $svg .= '<rect x="'.($x1 - 44).'" y="'.($top + 34).'" width="34" height="22" rx="6" fill="#fff3b0"/>';
        $svg .= '<rect x="'.($x0 + 150).'" y="'.($top + 62).'" width="'.($x1 - $x0 - 300).'" height="6" rx="3" fill="#000" opacity=".12"/>';

        foreach ([$x0 + 190, $x1 - 190] as $wx) {
            $svg .= '<circle cx="'.$wx.'" cy="'.$base.'" r="70" fill="#1d2226"/><circle cx="'.$wx.'" cy="'.$base.'" r="30" fill="#b7c0c7"/><circle cx="'.$wx.'" cy="'.$base.'" r="10" fill="#6b757c"/>';
        }

        return $svg;
    }

    private static function motor(string $color): string
    {
        $dark = self::shade($color, -28);
        $svg = '<defs><linearGradient id="body" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'.self::shade($color, 18).'"/><stop offset="1" stop-color="'.$dark.'"/></linearGradient></defs>';
        $svg .= '<ellipse cx="600" cy="660" rx="360" ry="22" fill="#000" opacity=".18"/>';
        // Roda
        foreach ([370, 840] as $wx) {
            $svg .= '<circle cx="'.$wx.'" cy="585" r="78" fill="#1d2226"/><circle cx="'.$wx.'" cy="585" r="34" fill="#b7c0c7"/><circle cx="'.$wx.'" cy="585" r="10" fill="#6b757c"/>';
        }
        // Bodi belakang dan pijakan
        $svg .= '<path d="M300 520 Q290 400 400 390 L560 390 Q600 392 610 430 L640 500 H760 L770 540 H420 Q380 560 330 560 Z" fill="url(#body)"/>';
        // Jok
        $svg .= '<path d="M390 385 Q400 350 470 350 H590 Q620 352 625 380 Z" fill="#2b333b"/>';
        // Perisai depan dan setang
        $svg .= '<path d="M700 330 Q760 320 790 360 L850 510 H780 L720 400 Z" fill="url(#body)"/>';
        $svg .= '<path d="M730 335 L700 270 H770" stroke="#2b333b" stroke-width="16" fill="none" stroke-linecap="round" stroke-linejoin="round"/>';
        $svg .= '<path d="M815 440 L842 585" stroke="#6b757c" stroke-width="14" stroke-linecap="round"/>';
        $svg .= '<rect x="790" y="372" width="48" height="26" rx="10" fill="#fff3b0" transform="rotate(20 814 385)"/>';
        $svg .= '<rect x="288" y="430" width="30" height="22" rx="6" fill="#c8372d"/>';

        return $svg;
    }

    private static function caption(string $label, string $sub, bool $light = false): string
    {
        $ink = $light ? '#ffffff' : '#151515';
        $bar = $light ? '#151515' : '#f7c600';
        $barInk = $light ? '#ffffff' : '#151515';

        return '<rect x="0" y="770" width="1200" height="130" fill="'.$bar.'" opacity="'.($light ? '.55' : '1').'"/>'
            .'<text x="60" y="835" font-family="Bahnschrift, Arial Narrow, Arial, sans-serif" font-weight="700" font-size="54" fill="'.$barInk.'">'.self::e($label).'</text>'
            .'<text x="60" y="872" font-family="Arial, sans-serif" font-size="26" fill="'.$barInk.'" opacity=".8">'.self::e($sub).'</text>'
            .'<text x="1140" y="872" text-anchor="end" font-family="Arial, sans-serif" font-size="22" fill="'.$barInk.'" opacity=".7">Foto contoh</text>';
    }

    private static function e(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** Gelapkan/terangkan warna hex. */
    private static function shade(string $hex, int $amount): string
    {
        $hex = ltrim($hex, '#');
        $parts = array_map(fn ($c) => max(0, min(255, hexdec($c) + $amount)), str_split($hex, 2));

        return sprintf('#%02x%02x%02x', ...$parts);
    }
}
