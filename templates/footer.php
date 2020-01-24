<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Footer template
 * 
 * @uses $this - Attogram\Justrefs\Template
 */
?>
</div><footer>
<a href="<?= $this->var('home') ?>"><?= $this->var('title') ?></a> 
- <a href="<?= $this->var('home') ?>about/">About</a>
<br /><small>page generated in <?= $this->endTimer('page') ?> seconds</small>
</footer></body></html>
