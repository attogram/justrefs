<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * About Page template
 * @uses $this - \Attogram\Justrefs\Template
 */
$this->include('html_head');
$this->include('header');
?><div class="body">
<h1>
    About <b><?= $this->get('name') ?></b> <small>v<?= $this->get('version') ?></small>
</h1>
<p>
    The <a href="../r/Goal">goal</a> of this <a href="../r/Website">website</a>
    is to help <a href="../r/Student">students</a>
    and <a href="../r/Research">researchers</a>
    by <a href="../r/Information_extraction">extracting</a>
    lists of <b><a href="../r/Reference">references</a></b>
    and <b><a href="../r/Relation_(history_of_concept)">related topics</a></b>
    from any <a href="../r/Web_page">page</a> on the
    <a href="../r/English_Wikipedia">English Wikipedia</a>.
</p>
<p>
    This removes the <a href="../r/Distraction">distraction</a> of the 
    <a href="../r/Prose">prose</a> written by
    <a href="../r/Wikipedia_community">others</a>, and allows concentrating on
    <a href="../r/Judgement">judging</a> the quality
    of the <a href="../r/Reference">references</a>.
</p>
<p>
    <b><a href="../"><?= $this->get('name') ?></a></b> is not
    <a href="../r/Affiliate_(commerce)">affiliated</a> with the
    <a href="../r/Wikimedia_Foundation">Wikimedia Foundation</a>,
    or any <a href="../r/List_of_Wikipedias">Wikipedia</a> website.
</p>
<p>
    <?= $this->get('name') ?> is an <a href="../r/Open_source">open source</a> project.
    Find out more at 
    &lt;<a href="https://github.com/attogram/justrefs">https://github.com/attogram/justrefs</a>&gt;
<p>
</div><?php
$this->include('footer');
