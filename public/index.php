<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Public Index Page
 */
$verbose = false;

$vendor = '../vendor/autoload.php';
if (!is_readable($vendor)) {
    exit('Site down for maintenance');
}
require_once($vendor);
if (!class_exists('\Attogram\Router\Router')
    || !class_exists('\Attogram\Justrefs\Web')
    || !class_exists('\Attogram\Justrefs\Filesystem')
    || !class_exists('\Attogram\Justrefs\Mediawiki')
) {
    exit('Site down for maintenance.');
}
$jr = new \Attogram\Justrefs\Web();
$jr->verbose = $verbose;
$jr->route();
