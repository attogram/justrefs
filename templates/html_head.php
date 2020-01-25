<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * HTML head template
 * 
 * @uses $this - Attogram\Justrefs\Template
 */
?><!doctype html>
<html lang="en">
<head><?php $this->include('custom' . DIRECTORY_SEPARATOR . 'head.top'); ?>    
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="apple-touch-icon" sizes="180x180" href="<?= $this->get('home') ?>apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $this->get('home') ?>favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $this->get('home') ?>favicon-16x16.png">
<link rel="manifest" href="<?= $this->get('home') ?>site.webmanifest">
<link rel="stylesheet" href="<?= $this->get('home') ?>style.css">
<title><?= $this->get('title') ?></title></head><body>