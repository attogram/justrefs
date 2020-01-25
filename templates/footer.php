<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Footer template
 * 
 * @uses $this - Attogram\Justrefs\Template
 */
?><footer>
  <b><a href="<?= $this->get('home') ?>"><?= $this->get('name') ?></a></b>
  <small>v<?= $this->get('version') ?></small>
  - <small>page generated in <?= $this->endTimer('page') ?> seconds</small>
</footer>
</body></html>
