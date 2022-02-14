<?php
include_once 'HtmlThing.php';

class HtmlText extends HtmlThing {
    public $content;

    public function isText() {
	return true;
    }
    public function __construct($text) {
	$this->content=$text;
    }
    public function __toString() {
	return $this->content;
    }
}

?>
