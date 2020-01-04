<?php
/**
 * Just Refs
 * Topic page template
 */

$countRefs = count($this->vars['refs']);
$countTopics = count($this->vars['topics']);
$countInternalTopics = count($this->vars['topics_internal']);
$countTemplates = count($this->vars['templates']);

?>
<h1><?= $this->vars['h1'] ?></h1>
<hr />
<ul>
  <li><a href="#refs"><b><?= $countRefs ?></b> References</a></li>
  <li><a href="#topics"><b><?= $countTopics ?></b> Main Topics</a></li>
  <li><a href="#internal.topics"><b><?= $countInternalTopics ?></b> Internal Topics</a></li>
  <li><a href="#templates"><b><?= $countTemplates ?></b> Included Templates</a></li>
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
    <a name="topics"><small><b><?= $countTopics ?></b> Main Topics:</small></a>
    <ol><?php
    foreach ($this->vars['topics'] as $topic) {
        $class = '';
        //if (empty($this->vars['meta'][$topic]['exists'])) {
        //    $class = 'missing';
        //}
        print '<li><a href="' . $this->getLink($topic) 
            . '" class="' . $class . '">' . $topic . '</a></li>';
    }
    ?></ol>
  </div>
  <div class="refs">
    <a name="refs"><small><b><?= $countRefs  ?></b> References:</small></a>
    <ol><?php
    foreach ($this->vars['refs'] as $ref) {
        print '<li><a href="' . $ref . '" target="_blank">' . $ref . '</a></li>';
    } ?></ol> 
  </div>
</div>

<hr />

<div class="flex-container">
  <div class="topics">
    <a name="internal.topics"><small><b><?= $countInternalTopics ?></b> Internal Topics:</small></a>
    <ol><?php
    foreach ($this->vars['topics_internal'] as $topic) {
        $class = '';
        if (empty($this->vars['meta'][$topic]['exists'])) {
            $class = 'missing';
        }
        print '<li><a href="' . $this->getLink($topic) 
            . '" class="' . $class . '">' . $topic . '</a></li>';
    }
    ?></ol>
  </div>
  <div class="refs">
    <a name="templates"><small><b><?= $countTemplates ?></b> Included Templates:</small></a>
    <ol><?php
    foreach ($this->vars['templates'] as $template) {
        $class = '';
        if (empty($this->vars['meta'][$template]['exists'])) {
            $class = 'missing';
        }
        print '<li><a href="' . $this->getLink($template) 
            . '" class="' . $class . '">' . $template . '</a></li>';
    }
    ?></ol>
  </div>
</div>
<hr />