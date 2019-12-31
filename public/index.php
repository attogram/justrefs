<?php
use function Functional\true;
use function Functional\false;

/**
 * Raw Wiki - Main Index Page
 */
$verbose = false;

$vendor = '../vendor/autoload.php';

if (!is_readable($vendor)) {
    exit('Site down for maintenance');
}

require_once($vendor);

if (!class_exists('\Attogram\Router\Router') || !class_exists('\Raw\Wiki')) {
    exit('Site down for maintenance.');
}

$raw = new \Raw\Wiki($verbose);

$raw->route();
