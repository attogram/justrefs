<?php
/**
 * Just Refs
 * Topic page template
 */

?>
<h1><?= $this->vars['h1'] ?></h1>
<hr />
<ul>
  <li><a href="#refs"><?= count($this->vars['refs']) ?> References</a></li>
  <li><a href="#topics"><?= count($this->vars['topics']) ?> Main Topics</a></li>
  <li><a href="#internal.topics"><?= count($this->vars['topics_internal']) ?> Internal Topics</a></li>
  <li><a href="#templates"><?= count($this->vars['templates']) ?> Included Templates</a></li>
  <li>Cached <?= $this->vars['dataAge'] ?> UTC
  - <a href="<?= $this->vars['refresh'] ?>">Refresh</a></li>
  <li>Served <?= $this->vars['now'] ?> UTC</li>
  <li>Extracted from &lt;<a href="<?= $this->vars['source'] ?>" target="_blank"><?= 
    $this->vars['source'] ?></a>&gt; released under the 
    Creative Commons Attribution-Share-Alike License 3.0</li>
</ul>

<hr />
<div class="flex-container">
  <div class="topics">
  <a name="topics"></a>
    <small><b><?= count($this->vars['topics']) ?></b> Main Topics:</small>
    <ol><?php
    foreach ($this->vars['topics'] as $topic) {
        print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
    }
    ?></ol>
  </div>
  <div class="refs">
    <a name="refs"></a>
    <small><b><?= count($this->vars['refs']) ?></b> References:</small>
    <ol><?php
    foreach ($this->vars['refs'] as $ref) {
        print '<li><a href="' . $ref . '" target="_blank">' . $ref . '</a></li>';
    } ?></ol> 
  </div>
</div>

<hr />

<div class="flex-container">
  <div class="topics">
    <a name="internal.topics"></a>
    <small><b><?= count($this->vars['topics_internal']) ?></b> Internal Topics:</small>
    <ol><?php
    foreach ($this->vars['topics_internal'] as $topic) {
        print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
    }
    ?></ol>
  </div>
  <div class="refs">
    <a name="templates"></a>
    <small><b><?= count($this->vars['templates']) ?></b> Included Templates:</small>
    <ol><?php
        foreach ($this->vars['templates'] as $template) {
            print '<li><a href="' . $this->getLink($template) . '">' . $template . '</a>'
                //. ' - <small>(? topics, ? references, ? templates)</small>'
                . '</li>';
        }
    ?></ol>
  </div>
</div>
<hr />