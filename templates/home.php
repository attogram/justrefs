<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 * Home Page template
 */
?>
<h1>Just Refs</h1>

<ul>
<li>View lists of <b>references</b> and <b>related topics</b> 
    from any page on the English Wikipedia.</li>
<li>Remove the distraction of prose written by others!</li>
<li><a href="about/">More about this site.</a></li>
</ul>

<br /><br />

<form>
<input name="q" value="" type="text" size="30">
<input type="submit" value="   search   ">
</form>

<br /><br />
Example topics:

<?php
$menus = [
    'Art', 'Culture', 'Entertainment', 'Geography', 'Health', 'History', 'Logic',
    'Mathematics', 'Medicine', 'Nature', 'People', 'Philosophy', 'Religion',
    'Society', 'Sport', 'Technology',
];
print '<p>';
foreach ($menus as $menu) {
    print '<a href="r/' . $menu . '">' . $menu . '</a> &nbsp; &nbsp;';
}
print '</p>';