<?php
/**
 * Just Refs
 * Topic page template
 */

function printItems($name, $displayName, $webObject, $externalLink = false) {
    if (!isset($webObject->vars[$name]) || !is_array($webObject->vars[$name])) {
        print '<a name="' . $name . '"><b>0</b> ' . $displayName . ':</a><br />';
        return;
    }
    print '<a name="' . $name . '"><b>' . count($webObject->vars[$name]) . '</b> ' . $displayName . ':</a>';
    print '<ol>';
    foreach ($webObject->vars[$name] as $item) {
        print '<li>';

        if ($externalLink) {
            print '<a href="' . $item . '" target="_blank">';
        } else {
            print '<a href="' . $webObject->getLink($item) . '">';
        }
        print $item . '</a></li>';
    }
    print '</ol>';
}

?>
<h1><?= $this->vars['h1'] ?></h1>
<hr />
<ul>
  <li><a href="#refs"><b><?= count($this->vars['refs']) ?></b> References</a>,
      <a href="#topics"><b><?= count($this->vars['main']) ?></b> Topics</a></li>
  <li><a href="#template"><b><?= count($this->vars['template']) ?></b> Templates</a>,
      <a href="#portal"><b><?= count($this->vars['portal']) ?></b> Portals</a>,
      <a href="#wikipedia"><b><?= count($this->vars['wikipedia']) ?></b> Wikipedia</a>,
      <a href="#help"><b><?= count($this->vars['help']) ?></b> Help</a>,
      <a href="#technical_template"><b><?= count($this->vars['technical_template']) ?></b> Support Templates</a>,
      <a href="#module"><b><?= count($this->vars['module']) ?></b> Modules</a>,
      <a href="#draft"><b><?= count($this->vars['draft']) ?></b> Drafts</a>,
      <a href="#user"><b><?= count($this->vars['user']) ?></b> Users</a>
    </li>
  <li>Cached <?= $this->vars['dataAge'] ?> UTC (<a href="<?= $this->vars['refresh'] ?>">refresh</a>)</li>
  <li>Served <?= $this->vars['now'] ?> UTC</li>
  <li>Extracted from &lt;<a href="<?= $this->vars['source'] ?>" target="_blank"><?= 
    $this->vars['source'] ?></a>&gt; released under the 
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
  <div class="lcol"><?php printItems('technical_template', 'Support Templates', $this); ?></div>
  <div class="rcol"><?php printItems('module', 'Modules', $this); ?></div>
</div>
<hr />
<div class="flex-container">
  <div class="lcol"><?php printItems('draft', 'Drafts', $this); ?></div>
  <div class="rcol"><?php printItems('user', 'Users', $this); ?></div>
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
