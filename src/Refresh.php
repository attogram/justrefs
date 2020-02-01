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
    public function get()
    {
        $this->setTopicFromUrl();
        if (!strlen($this->topic)) {
            $this->error404('Refresh Topic Not Found');
        }
        $this->initFilesystem();
        if (!$this->filesystem->exists($this->topic)) { // does cache file exist?
            $this->error404('Cache File Not Found');
        }
        $this->template->set('title', 'Refresh');
        if (empty($this->router->getPost())) {
            $this->ask();

            return;
        }
        $this->answer();
    }

    private function answer()
    {
        $answer = $this->router->getPost('d');
        if (!strlen($answer)) {
            $this->error404('Answer Not Found');
        }
        $submitTime = $this->router->getPost('c');
        if (!$submitTime || (time() - intval($submitTime)) > 60) {
            $this->error404('Request Timed Out');
        }
        $one = $this->router->getPost('a');
        $two = $this->router->getPost('b');
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
    }

    private function ask()
    {
        $this->template->include('html_head');
        $this->template->include('header');
        print '<div class="body"><p><b><a href="'
            . $this->template->get('home') . $this->getLink($this->topic) . '">'
            . $this->topic . '</a></b> is currently cached.</p>';
        $letterOne = chr(rand(65, 90)); // random letter A-Z
        $numOne = rand(0, 10);          // random number 0-10
        $letterTwo = chr(rand(65, 90)); // random letter A-Z
        $numTwo = rand(0, 10);          // random number 0-10
        print '<form method="POST">'
            . '<input type="hidden" name="a" value="' . $numOne . '">'
            . '<input type="hidden" name="b" value="' . $numTwo . '">'
            . '<input type="hidden" name="c" value="' . time() . '">'
            . "If $letterOne = $numOne and $letterTwo = $numTwo then $letterOne + $letterTwo = "
            . '<input name="d" value="" size="4">'
            . '<br /><br /><input type="submit" value="    Delete Cache    ">'
            . '</form><br /></div>';
        $this->template->include('footer');
    }
}
