<?php
include_once 'HtmlElement.php';

class HtmlDocument extends HtmlElement {
    public function __construct() {
	parent::__construct("");
    }
    public function isElement() {
	return false;
    }
    public function isDocument() {
	return true;
    }
    public function __toString() {
	$rslt="";
	foreach($this->content as $var) {
	    $rslt.=$var->__toString();
	}
	return $rslt;
    }
}
?>
