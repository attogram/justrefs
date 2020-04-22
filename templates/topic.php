<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Topic page template
 * @uses $this - \Attogram\Justrefs\Template
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
  <li><a href="#template"><b><?= $this->get('portal_count') ?></b> Portals</a>,
    <a href="#wikipedia"><b><?= $this->get('wikipedia_count') ?></b> Wikipedia</a>,
    <a href="#help"><b><?= $this->get('help_count') ?></b> Help</a>,
    <a href="#template_secondary"><b><?= $this->get('template_secondary_count') ?></b> Secondary-Templates</a>,
    <a href="#module"><b><?= $this->get('module_count') ?></b> Modules</a>,
    <a href="#draft"><b><?= $this->get('draft_count') ?></b> Drafts</a>,
    <a href="#user"><b><?= $this->get('user_count') ?></b> Users</a>
  </li>
  <li>Cached <?= $this->get('cached') ?> UTC (<a href="<?= $this->get('refresh') ?>">refresh</a>)</li>
  <li>Served <?= $this->get('now') ?> UTC</li>
  <li>Extracted from &lt;<a href="<?= $this->get('source') ?>" target="_blank"><?= $this->get('source') ?></a>&gt;
      released under the Creative Commons Attribution-Share-Alike License 3.0</li>
</ul>
<hr />
<div class="flex-container">
  <div class="lcol" id="main">
    <b><?= $this->get('main_count') ?></b> Topics:<br /><?= $this->get('main_list') ?>
  </div>
  <div class="rcol" id="refs">
    <b><?= $this->get('refs_count') ?></b> References:<br /><?= $this->get('refs_list') ?>
  </div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol" id="main_secondary">
    <b><?= $this->get('main_secondary_count') ?></b> Secondary-Topics:<br /><?= $this->get('main_secondary_list') ?>
  </div>
  <div class="rcol" id="template">
    <b><?= $this->get('template_count') ?></b> Templates:<br /><?= $this->get('template_list') ?><br />
    <hr />
    <b><?= $this->get('portal_count') ?></b> Portals:<br /><?= $this->get('portal_list') ?>
  </div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol" id="help">
    <b><?= $this->get('help_count') ?></b> Help:<br /><?= $this->get('help_list') ?>
    <hr />
    <a name="wikipedia"></a>
    <b><?= $this->get('wikipedia_count') ?></b> Wikipedia:<br /><?= $this->get('wikipedia_list') ?>
  </div>
  <div class="rcol" id="template_secondary">
    <b><?= $this->get('template_secondary_count') ?></b> Secondary-Templates:
    <br /><?= $this->get('template_secondary_list') ?><br />
    <hr />
    <a name="module"></a>
    <b><?= $this->get('module_count') ?></b> Modules:<br /><?= $this->get('module_list') ?>
  </div>
</div>
<hr />
<div class="flex-container" id="draft">
  <div class="lcol">
    <b><?= $this->get('draft_count') ?></b> Drafts:<br /><?= $this->get('draft_list') ?>
  </div>
  <div class="rcol" id="user">
    <b><?= $this->get('user_count') ?></b> Users:<br /><?= $this->get('user_list') ?>
  </div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol" id="talk">
    <b><?= $this->get('talk_count') ?></b> Talk:<br /><?= $this->get('talk_list') ?><br />
    <hr />
    <b><?= $this->get('user_talk_count') ?></b> User talk:<br /><?= $this->get('user_talk_list') ?><br />
    <hr />
    <b><?= $this->get('wikipedia_talk_count') ?></b> Wikipedia talk:<br /><?= $this->get('wikipedia_talk_list') ?><br />
    <hr />
    <b><?= $this->get('help_talk_count') ?></b> Help talk:<br /><?= $this->get('help_talk_list') ?>
  </div>
  <div class="rcol" id="talk_2">
    <b><?= $this->get('portal_talk_count') ?></b> Portal talk:<br /><?= $this->get('portal_talk_list') ?><br />
    <hr />
    <b><?= $this->get('template_talk_count') ?></b> Template talk:<br /><?= $this->get('template_talk_list') ?><br />
    <hr />
    <b><?= $this->get('draft_talk_count') ?></b> Draft talk:<br /><?= $this->get('draft_talk_list') ?><br />
    <hr />
    <b><?= $this->get('module_talk_count') ?></b> Module talk:<br /><?= $this->get('module_talk_list') ?>
  </div>
</div>
<hr />
</div><?php
$this->include('footer');
