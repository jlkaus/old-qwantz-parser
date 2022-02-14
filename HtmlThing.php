<?php

class HtmlThing {
    public function __construct() {
    }
    
    public function isElement() {
	return false;
    }
    public function isText() {
	return false;
    }
    public function isComment() {
	return false;
    }
    public function isDocument() {
	return false;
    }
    
    public function __toString() {
	return "";
    }
    
    public function getChildElementsByTag($tag) {
	return array();
    }
    public function getChildElementsByTagAndAtt($tag,$attr,$val) {
	return array();
    }
    public function getChildElements() {
	return array();
    }
    public function getChildTexts() {
	return array();
    }
    public function getChildComments() {
	return array();
    }
    public function getChildTextAndElements() {
	return array();
    }
    public function getChildTextAndElementsAsString() {
	return "";
    }
}

?>
