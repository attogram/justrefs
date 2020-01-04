<?php
/**
 * Just Refs
 * Topic page template
 */

?>
<h1><?= $this->vars['h1'] ?></h1>
<div class="flex-container">
  <div class="topics">
    <small><b><?= count($this->vars['topics']) ?></b> Main Topics:</small>
    <ol>
<?php
    foreach ($this->vars['topics'] as $topic) {
        print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
    }
?>
    </ol>
  </div>
  <div class="refs">
    <small><b><?= count($this->vars['refs']) ?></b> References:</small>
    <ol>
<?php
    foreach ($this->vars['refs'] as $ref) {
        print '<li><a href="' . $ref . '" target="_blank">' . $ref . '</a></li>';
    }
?>
    </ol> 
  </div>
</div>

<hr />
<small><b><?= count($this->vars['topics_internal']) ?></b> Internal Topics:</small>
<ol>
<?php
    foreach ($this->vars['topics_internal'] as $topic) {
        print '<li><a href="' . $this->getLink($topic) . '">' . $topic . '</a></li>';
    }
?>
    </ol>

<hr />

<small><b><?= count($this->vars['templates']) ?></b> Included Templates:</small>
<ol>
<?php
    foreach ($this->vars['templates'] as $template) {
        print '<li><a href="' . $this->getLink($template) . '">' . $template . '</a>'
            //. ' - <small>(? topics, ? references, ? templates)</small>'
            . '</li>';
    }
?>
</ol>
<hr />
<small>
Extracted from 
&lt;<a href="<?= $this->vars['source'] ?>" target="_blank"><?= $this->vars['source'] ?></a>&gt;
released under the Creative Commons Attribution-Share-Alike License 3.0<br />
Page served <?= $this->vars['now'] ?> UTC<br />
Data cached <?= $this->vars['dataAge'] ?> UTC<br />
<a href="<?= $this->vars['refresh'] ?>">Refresh Data</a>
</small>
