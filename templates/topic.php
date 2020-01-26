<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Topic page template
 */

function printItems($name, $template, $externalLink = false) {
    print '<ol>';
    foreach ($template->get($name) as $item) {
        if (in_array($item, $template->get('missing'))) {
          // Link to non-existing internal page (red link)
          print '<li><span class="red">' . $item . '</span></li>';
          continue;
        }
        if ($externalLink) {
          // Link to external reference
          print '<li><a href="' . $item . '" target="_blank">' . $item . '</li>';
          continue;
        } 

        // Link to internal page
        $class = '';
        //if ($name == 'template' || $name == 'template_secondary') {
        if ($name == 'template') {
            if (!in_array($item, $template->get('exists'))) {
                $class = ' class="missing"';
            }
        }
        print '<li><a href="'
            . $template->get('home')
            . $template->getLink($item) . '"' 
            . $class . '>' . $item . '</a></li>';
    }
    print '</ol>';
}

$this->include('html_head'); 
$this->include('header');

?><div class="body">
<h1><?= $this->get('h1') ?></h1>
<hr />
<ul>
  <li><a href="#refs"><b><?= $this->get('count_refs') ?></b> References</a>,
      <a href="#main"><b><?= $this->get('count_main') ?></b> Topics</a>,
      <a href="#main_secondary"><b><?= $this->get('count_main_secondary') ?></b> Secondary-Topics</a>
  </li>
  <li><a href="#template"><b><?= $this->get('count_template') ?></b> Templates</a>,
      <a href="#portal"><b><?= $this->get('count_portal') ?></b> Portals</a>,
      <a href="#wikipedia"><b><?= $this->get('count_wikipedia') ?></b> Wikipedia</a>,
      <a href="#help"><b><?= $this->get('count_help') ?></b> Help</a>,
      <a href="#template_secondary"><b><?= $this->get('count_template_secondary') ?></b> Secondary-Templates</a>,
      <a href="#module"><b><?= $this->get('count_module') ?></b> Modules</a>,
      <a href="#draft"><b><?= $this->get('count_draft') ?></b> Drafts</a>,
      <a href="#user"><b><?= $this->get('count_user') ?></b> Users</a>
    </li>
  <li>Cached <?= $this->get('dataAge') ?> UTC (<a href="<?= $this->get('refresh') ?>">refresh</a>)</li>
  <li>Served <?= $this->get('now') ?> UTC</li>
  <li>Extracted from &lt;<a href="<?= $this->get('source') ?>" target="_blank"><?= $this->get('source') ?></a>&gt;
      released under the Creative Commons Attribution-Share-Alike License 3.0</li>
</ul>
<hr />
<div class="flex-container">
  <div class="lcol">
    <a name="main"><b><?= $this->get('count_main') ?></b> Topics:</a><br />
    <?php printItems('main', $this); ?>
  </div>
  <div class="rcol">
    <a name="refs"><b><?= $this->get('count_refs') ?></b> References:</a><br />
    <?php printItems('refs', $this, true); ?>
  </div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol">
    <a name="main_secondary"><b><?= $this->get('count_main_secondary') ?></b> Secondary-Topics:</a><br />
    <?php printItems('main_secondary', $this); ?>
  </div>
  <div class="rcol">
    <a name="template"><b><?= $this->get('count_template') ?></b> Templates:</a><br />
    <?php printItems('template', $this); ?><br /><hr />
    <a name="portal"><b><?= $this->get('count_portal') ?></b> Portals:</a><br />
    <?php printItems('portal', $this); ?>
  </div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol">
    <a name="wikipedia"><b><?= $this->get('count_wikipedia') ?></b> Wikipedia:</a><br />
    <?php printItems('wikipedia', $this); ?><br /><hr />
    <a name="help"><b><?= $this->get('count_help') ?></b> Help:</a><br />
    <?php printItems('help', $this); ?>
  </div>
  <div class="rcol">
    <a name="template_secondary"><b><?= $this->get('count_template_secondary') ?></b> Support-Templates:</a><br />
    <?php printItems('template_secondary', $this); ?><br /><hr />
    <a name="module"><b><?= $this->get('count_module') ?></b> Modules:</a><br />
    <?php printItems('module', $this); ?>
  </div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol">
    <a name="draft"><b><?= $this->get('count_draft') ?></b> Drafts:</a><br />
    <?php printItems('draft', $this); ?>
  </div>
  <div class="rcol">
    <a name="user"><b><?= $this->get('count_user') ?></b> Users:</a><br />
    <?php printItems('user', $this); ?>
  </div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol">
    <a name="talk"><b><?= $this->get('count_talk') ?></b> Talk:</a><br />
    <?php printItems('talk', $this); ?><br /><hr />
    <a name="user_talk"><b><?= $this->get('count_user_talk') ?></b> User talk:</a><br />
    <?php printItems('user_talk', $this); ?><br /><hr />
    <a name="wikipedia_talk"><b><?= $this->get('count_wikipedia_talk') ?></b> Wikipedia talk:</a><br />
    <?php printItems('wikipedia_talk', $this); ?><br /><hr />
    <a name="help_talk"><b><?= $this->get('count_help_talk') ?></b> Help talk:</a><br />
    <?php printItems('help_talk', $this); ?>
  </div>
  <div class="rcol">
    <a name="portal_talk"><b><?= $this->get('count_portal_talk') ?></b> Portal talk:</a><br />
    <?php printItems('portal_talk', $this); ?><br /><hr />
    <a name="template_talk"><b><?= $this->get('count_template_talk') ?></b> Template talk:</a><br />
    <?php printItems('template_talk', $this); ?><br /><hr />
    <a name="draft_talk"><b><?= $this->get('count_draft_talk') ?></b> Draft talk:</a><br />
    <?php printItems('draft_talk', $this); ?><br /><hr />
    <a name="module_talk"><b><?= $this->get('count_module_talk') ?></b> Module talk:</a><br />
    <?php printItems('module_talk', $this); ?>
  </div>
</div>
<hr />
</div><?php

$this->include('footer');
