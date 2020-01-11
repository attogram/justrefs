<?php
/**
 * Just Refs
 * Home Page template
 */
$menus = [
    'Art', 'Culture', 'Entertainment', 'Geography', 'Health', 'History', 'Logic',
    'Mathematics', 'Medicine', 'Nature', 'People', 'Philosophy', 'Religion',
    'Society', 'Sport', 'Technology',
];

print '<p>';
foreach ($menus as $menu) {
    print '<a href="r/' . $menu . '">' . $menu . '</a> &nbsp; &nbsp;';
}

print '</p>'
    . '<form>'
    . '<input name="q" value="" type="text" size="30">'
    . '<input type="submit" value="   search   ">'
    . '</form>';

print '<p><a href="about/">About this site</a></p>';
