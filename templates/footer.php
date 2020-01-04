<?php
/**
 * Just Refs
 * Footer template
 * 
 * @uses $this
 */
?>
</div><footer>
<a href="<?= $this->router->getHome() ?>"><?= $this->siteName ?></a> 
- <a href="<?= $this->router->getHome() ?>about/">About</a>
<br /><small>page generated in <?= $this->endTimer('page') ?> seconds</small>
</footer></body></html>
