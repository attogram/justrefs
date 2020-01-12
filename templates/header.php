<?php
/**
 * Just Refs
 * Header template
 * 
 * @uses $this - Attogram\Justrefs\Web
 */
?><!doctype html>
<html lang="en">
<head>
<?php $this->includeTemplate('custom' . DIRECTORY_SEPARATOR . 'head.top') ?>    
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $this->router->getHome() ?>apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $this->router->getHome() ?>favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $this->router->getHome() ?>favicon-16x16.png">
<link rel="manifest" href="<?= $this->router->getHome() ?>site.webmanifest">
<link rel="stylesheet" href="<?= $this->router->getHome() ?>style.css">
<title><?= $this->title ?></title></head><body><div class="head">
<a href="<?= $this->router->getHome() ?>"><?= $this->siteName ?></a> 
- <a href="<?= $this->router->getHome() ?>about/">About</a>
<div style="float:right;"><form action="<?= $this->router->getHome() ?>">
<input name="q" value="" type="text" size="18"><input type="submit" value="search"></form>
</div></div><div class="body">
