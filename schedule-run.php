<?php
$_SERVER['argv'] = [
    'artisan',
    'schedule:run',
];

// On lance artisan
require __DIR__.'/artisan';