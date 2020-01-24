<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Header template
 * 
 * @uses $this - Attogram\Justrefs\Template
 */
?><!doctype html>
<html lang="en">
<head>
<?php $this->include('custom' . DIRECTORY_SEPARATOR . 'head.top'); ?>    
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $this->var('home') ?>apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $this->var('home') ?>favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $this->var('home') ?>favicon-16x16.png">
<link rel="manifest" href="<?= $this->var('home') ?>site.webmanifest">
<link rel="stylesheet" href="<?= $this->var('home') ?>style.css">
<title><?= $this->var('title') ?></title></head><body><div class="head">
<a href="<?= $this->var('home') ?>"><?= $this->var('title') ?></a> 
- <a href="<?= $this->var('home') ?>about/">About</a>
<div style="float:right;"><form action="<?= $this->var('home') ?>">
<input name="q" value="" type="text" size="18"><input type="submit" value="search"></form>
</div></div><div class="body">
