<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Footer template
 *
 * @uses $this - \Attogram\Justrefs\Template
 */
?><footer>
  <b><a href="<?= $this->get('home') ?>"><?= $this->get('name') ?></a></b>
  - <a href="<?= $this->get('home') ?>about/">About</a>
  <br /><br />
  <ul>
  <li>Powered by <b><a href="https://github.com/attogram/justrefs">attogram/justrefs</a></b>
    v<?= $this->get('version') ?>
  <li><a href="https://github.com/sponsors/attogram"><b>Sponsor</b> the Just Refs open source project</a></li>
  <li>Page generated in <?= $this->endTimer('page') ?> seconds</li>
  </ul>
</footer>
</body></html>
