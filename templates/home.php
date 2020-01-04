<?php

$menus = [
    'Art', 'Culture', 'Entertainment', 'Geography', 'Health', 'History', 'Logic',
    'Mathematics', 'Medicine', 'Nature', 'People', 'Philosophy', 'Religion',
    'Society', 'Sport', 'Technology',
];

foreach ($menus as $menu) {
    print '<a href="r/' . $menu . '">' . $menu . '</a> &nbsp; &nbsp;';
}

print '<br /><br />'
    . '<form>'
    . '<input name="q" value="" type="text" size="30">'
    . '<input type="submit" value="   search   ">'
    . '</form>';

print '<p><a href="about/">About this site</a></p>';
