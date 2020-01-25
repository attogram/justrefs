<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Topic page template
 */

function printItems($name, $displayName, $templateObject, $externalLink = false) {
    if (!is_array($templateObject->get($name))) {
        print '<a name="' . $name . '"><b>0</b> ' . $displayName . ':</a><br />';
        return;
    }
    print '<a name="' . $name . '"><b>' . count($templateObject->get($name)) . '</b> ' . $displayName . ':</a>';
    print '<ol>';
    foreach ($templateObject->get($name) as $item) {
        if (in_array($item, $templateObject->get('missing'))) {
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
        //if ($name == 'template' || $name == 'technical_template') {
        if ($name == 'template') {
            if (!in_array($item, $templateObject->get('exists'))) {
                $class = ' class="missing"';
            }
        }
        print '<li><a href="'
            . $templateObject->get('home')
            . $templateObject->getLink($item) . '"' 
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
  <li><a href="#refs"><b><?= count($this->get('refs')) ?></b> References</a>,
      <a href="#main"><b><?= count($this->get('main')) ?></b> Topics</a></li>
  <li><a href="#template"><b><?= count($this->get('template')) ?></b> Templates</a>,
      <a href="#portal"><b><?= count($this->get('portal')) ?></b> Portals</a>,
      <a href="#wikipedia"><b><?= count($this->get('wikipedia')) ?></b> Wikipedia</a>,
      <a href="#help"><b><?= count($this->get('help')) ?></b> Help</a>,
      <a href="#technical_template"><b><?= count($this->get('technical_template')) ?></b> Support Templates</a>,
      <a href="#module"><b><?= count($this->get('module')) ?></b> Modules</a>,
      <a href="#draft"><b><?= count($this->get('draft')) ?></b> Drafts</a>,
      <a href="#user"><b><?= count($this->get('user')) ?></b> Users</a>
    </li>
  <li>Cached <?= $this->get('dataAge') ?> UTC (<a href="<?= $this->get('refresh') ?>">refresh</a>)</li>
  <li>Served <?= $this->get('now') ?> UTC</li>
  <li>Extracted from &lt;<a href="<?= $this->get('source') ?>" target="_blank"><?= 
    $this->get('source') ?></a>&gt; released under the 
    Creative Commons Attribution-Share-Alike License 3.0</li>
</ul>
<hr />
<div class="flex-container">
  <div class="lcol"><?php printItems('main', 'Topics', $this); ?></div>
  <div class="rcol"><?php printItems('refs', 'References', $this, true); ?></div>
</div>
<hr>
<div class="flex-container">
  <div class="lcol"><?php printItems('template', 'Templates', $this); ?></div>
  <div class="rcol"><?php printItems('portal', 'Portals', $this); ?></div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol"><?php printItems('wikipedia', 'Wikipedia', $this); ?></div>
  <div class="rcol"><?php printItems('help', 'Help', $this); ?></div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol"><?php printItems('draft', 'Drafts', $this); ?></div>
  <div class="rcol"><?php printItems('user', 'Users', $this); ?></div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol"><?php printItems('technical_template', 'Support Templates', $this); ?></div>
  <div class="rcol"><?php printItems('module', 'Modules', $this); ?></div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol">
    <?php printItems('talk', 'Talk', $this); ?><br />
    <?php printItems('user_talk', 'User talk', $this); ?><br />
    <?php printItems('wikipedia_talk', 'Wikipedia talk', $this); ?><br />
    <?php printItems('help_talk', 'Help talk', $this); ?>
  </div>
  <div class="rcol">
    <?php printItems('portal_talk', 'Portal talk', $this); ?><br />
    <?php printItems('template_talk', 'Template talk', $this); ?><br />
    <?php printItems('draft_talk', 'Draft talk', $this); ?><br />
    <?php printItems('module_talk', 'Module talk', $this); ?>
  </div>
</div>
<hr />
</div><?php

$this->include('footer');
