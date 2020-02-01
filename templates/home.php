<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Home Page template
 * @uses $this - \Attogram\Justrefs\Template
 */

$this->include('html_head');

?><div class="body">
<h1>Just Refs</h1>
<ul>
<li>Extract just the <b>references</b> and <b>related topics</b> from any page on the English Wikipedia.</li>
<li>Remove the distraction of prose written by others!</li>
<li><a href="about/">More <b>about</b> this site.</a></li>
</ul>
<br /><br />
<form>
<input name="q" value="" type="text" size="30">
<input type="submit" value="   search   ">
</form>
<br /><br />
<small>Example Topics:</small>
<ul>
<li><a href="r/Art">Art</a></li>
<li><a href="r/Culture">Culture</a></li>
<li><a href="r/Entertainment">Entertainment</a></li>
<li><a href="r/Geography">Geography</a></li>
<li><a href="r/Health">Health</a></li>
<li><a href="r/History">History</a></li>
<li><a href="r/Logic">Logic</a></li>
<li><a href="r/Mathematics">Mathematics</a></li>
<li><a href="r/Medicine">Medicine</a></li>
<li><a href="r/Nature">Nature</a></li>
<li><a href="r/People">People</a></li>
<li><a href="r/Philosophy">Philosophy</a></li>
<li><a href="r/Religion">Religion</a></li>
<li><a href="r/Society">Society</a></li>
<li><a href="r/Sport">Sport</a></li>
<li><a href="r/Technology">Technology</a></li>
</ul>
<br />
</div><?php

$this->include('footer');
