<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Public Index Page
 */
$verbose = false;

$vendor = '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_readable($vendor)) {
    exit('Site down for maintenance');
}
require_once($vendor);

$jr = new \Attogram\Justrefs\JustRefs($verbose);
$jr->route();
