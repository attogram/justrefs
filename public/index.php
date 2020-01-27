<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Public Index Page
 */
$verbose = false;

$vendor = '../vendor/autoload.php';
if (!is_readable($vendor)) {
    exit('Site down for maintenance');
}
require_once($vendor);

$jr = new \Attogram\Justrefs\JustRefs();
$jr->verbose = $verbose;
$jr->route();
