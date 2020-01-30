<?php
/**
 * Just Refs - https://github.com/attogram/justrefs
 *
 * Refresh Class
 */
declare(strict_types = 1);

namespace Attogram\Justrefs;

use function chr;
use function intval;
use function rand;
use function strlen;
use function time;

class Refresh extends Base
{
    public function refresh()
    {
        $this->setTopicFromUrl();
        if (!strlen($this->topic)) {
            $this->error404('Refresh Topic Not Found');
        }
        $this->initFilesystem();
        // does cache file exist?
        if (!$this->filesystem->exists($this->topic)) {
            $this->error404('Cache File Not Found');
        }
        $this->template->set('title', 'Refresh');
        if (!empty($_POST)) {
            $answer = isset($_POST['d']) ? $_POST['d'] : '';
            if (!strlen($answer)) {
                $this->error404('Answer Not Found');
            }
            $submitTime = !empty($_POST['c']) ? intval($_POST['c']) : false;
            if (!$submitTime || (time() - $submitTime) > 60) {
                $this->error404('Request Timed Out');
            }
            $one = isset($_POST['a']) ? $_POST['a'] : '';
            $two = isset($_POST['b']) ? $_POST['b'] : '';
            if (!strlen($one) || !strlen($two)) {
                $this->error404('Invalid Request');
            }
            if (($one + $two) != $answer) {
                $this->error404('Invalid Answer');
            }
            if (!$this->filesystem->delete($this->topic)) {
                $this->error404('Deletion Failed');
            }
            $this->template->include('html_head');
            $this->template->include('header');
            print '<div class="body"><p>OK - cache deleted</p>'
                . '<p><a href="' . $this->template->get('home') . $this->getLink($this->topic) . '">'
                . $this->topic . '</a></p></div>';
            $this->template->include('footer');
            return;
        }
        $this->template->include('html_head');
        $this->template->include('header');
        print '<div class="body"><p><b><a href="'
            . $this->template->get('home') . $this->getLink($this->topic) . '">'
            . $this->topic . '</a></b> is currently cached.</p>';
        $letterOne = chr(rand(65, 90)); // random letter A-Z
        $numOne = rand(0, 10);          // random number 0-10
        $letterTwo = chr(rand(65, 90)); // random letter A-Z
        $numTwo = rand(0, 10);          // random number 0-10
        $answer = $numOne + $numTwo;
        print '<form method="POST">'
            . '<input type="hidden" name="a" value="' . $numOne . '">'
            . '<input type="hidden" name="b" value="' . $numTwo . '">'
            . '<input type="hidden" name="c" value="' . time() . '">'
            . "If $letterOne = $numOne and $letterTwo = $numTwo"
            . " then  $letterOne + $letterTwo = "
            . '<input name="d" value="" size="4">'
            . '<br /><br /><input type="submit" value="    Delete Cache    ">'
            . '</form><br /></div>';
        $this->template->include('footer');
    }
}
