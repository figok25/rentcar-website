<?php

return [

    /*
    | Izinkan mesin pencari mengindeks website. Set SEO_INDEXABLE=false untuk
    | server staging/percobaan: robots.txt memblokir semua dan setiap halaman
    | memakai meta noindex.
    */
    'indexable' => (bool) env('SEO_INDEXABLE', true),

];
