<?php
include_once 'HtmlThing.php';

class HtmlComment extends HtmlThing {
    public $content;

    public function isComment() {
	return true;
    }
    public function __construct($comment) {
	preg_match("/^<!--([\s\S]*?)-->$/",$comment,$match);
	$this->content=$match[1];
    }
    public function __toString() {
	return "<!--".$this->content."-->";
    }
}

?>
