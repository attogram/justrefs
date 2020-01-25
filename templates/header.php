<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Header template
 * 
 * @uses $this - Attogram\Justrefs\Template
 */
?>
<div class="head">
  <b><a href="<?= $this->get('home') ?>"><?= $this->get('name') ?></a></b>
  <div style="float:right;">
    <form action="<?= $this->get('home') ?>">
      <input name="q" value="" type="text" size="18"><input type="submit" value="search">
    </form>
  </div>
</div>
