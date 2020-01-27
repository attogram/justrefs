<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Topic page template
 */

$this->include('html_head'); 
$this->include('header');

?><div class="body">
<h1><?= $this->get('h1') ?></h1>
<hr />
<ul>
  <li><a href="#refs"><b><?= $this->get('refs_count') ?></b> References</a>,
    <a href="#main"><b><?= $this->get('main_count') ?></b> Topics</a>,
    <a href="#main_secondary"><b><?= $this->get('main_secondary_count') ?></b> Secondary-Topics</a>,
    <a href="#template"><b><?= $this->get('template_count') ?></b> Templates</a>
  </li>
  <li><a href="#portal"><b><?= $this->get('portal_count') ?></b> Portals</a>,
    <a href="#wikipedia"><b><?= $this->get('wikipedia_count') ?></b> Wikipedia</a>,
    <a href="#help"><b><?= $this->get('help_count') ?></b> Help</a>,
    <a href="#template_secondary"><b><?= $this->get('template_secondary_count') ?></b> Secondary-Templates</a>,
    <a href="#module"><b><?= $this->get('module_count') ?></b> Modules</a>,
    <a href="#draft"><b><?= $this->get('draft_count') ?></b> Drafts</a>,
    <a href="#user"><b><?= $this->get('user_count') ?></b> Users</a>
  </li>
  <li>Cached <?= $this->get('dataAge') ?> UTC (<a href="<?= $this->get('refresh') ?>">refresh</a>)</li>
  <li>Served <?= $this->get('now') ?> UTC</li>
  <li>Extracted from &lt;<a href="<?= $this->get('source') ?>" target="_blank"><?= $this->get('source') ?></a>&gt;
      released under the Creative Commons Attribution-Share-Alike License 3.0</li>
</ul>
<hr />
<div class="flex-container">
  <div class="lcol">
    <a name="main"><b><?= $this->get('main_count') ?></b> Topics:</a><br />
    <?= $this->get('main_list') ?>
  </div>
  <div class="rcol">
    <a name="refs"><b><?= $this->get('refs_count') ?></b> References:</a><br />
    <?= $this->get('refs_list') ?>
  </div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol">
    <a name="main_secondary"><b><?= $this->get('main_secondary_count') ?></b> Secondary-Topics:</a><br />
    <?= $this->get('main_secondary_list') ?>
  </div>
  <div class="rcol">
    <a name="template"><b><?= $this->get('template_count') ?></b> Templates:</a><br />
    <?= $this->get('template_list') ?><br /><hr />
    <a name="portal"><b><?= $this->get('portal_count') ?></b> Portals:</a><br />
    <?= $this->get('portal_list') ?>
  </div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol">
    <a name="wikipedia"><b><?= $this->get('wikipedia_count') ?></b> Wikipedia:</a><br />
    <?= $this->get('wikipedial_list') ?><br /><hr />
    <a name="help"><b><?= $this->get('help_count') ?></b> Help:</a><br />
    <?= $this->get('help_list') ?>
  </div>
  <div class="rcol">
    <a name="template_secondary"><b><?= $this->get('template_secondary_count') ?></b> Support-Templates:</a><br />
    <?= $this->get('template_secondary_list') ?><br /><hr />
    <a name="module"><b><?= $this->get('module_count') ?></b> Modules:</a><br />
    <?= $this->get('module_list') ?>
  </div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol">
    <a name="draft"><b><?= $this->get('draft_count') ?></b> Drafts:</a><br />
    <?= $this->get('draft_list') ?>
  </div>
  <div class="rcol">
    <a name="user"><b><?= $this->get('user_count') ?></b> Users:</a><br />
    <?= $this->get('user_list') ?>
  </div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol">
    <a name="talk"><b><?= $this->get('talk_count') ?></b> Talk:</a><br />
    <?= $this->get('talk_list') ?><br /><hr />
    <a name="user_talk"><b><?= $this->get('user_talk_count') ?></b> User talk:</a><br />
    <?= $this->get('user_talk_list') ?><br /><hr />
    <a name="wikipedia_talk"><b><?= $this->get('wikipedia_talk_count') ?></b> Wikipedia talk:</a><br />
    <?= $this->get('wikipedia_talk_list') ?><br /><hr />
    <a name="help_talk"><b><?= $this->get('help_talk_count') ?></b> Help talk:</a><br />
    <?= $this->get('help_talk_list') ?>
  </div>
  <div class="rcol">
    <a name="portal_talk"><b><?= $this->get('portal_talk_count') ?></b> Portal talk:</a><br />
    <?= $this->get('portal_talk_list') ?><br /><hr />
    <a name="template_talk"><b><?= $this->get('template_talk_count') ?></b> Template talk:</a><br />
    <?= $this->get('template_talk_list') ?><br /><hr />
    <a name="draft_talk"><b><?= $this->get('draft_talk_count') ?></b> Draft talk:</a><br />
    <?= $this->get('draft_talk_list') ?><br /><hr />
    <a name="module_talk"><b><?= $this->get('module_talk_count') ?></b> Module talk:</a><br />
    <?= $this->get('module_talk_list') ?>
  </div>
</div>
<hr />
</div><?php

$this->include('footer');
