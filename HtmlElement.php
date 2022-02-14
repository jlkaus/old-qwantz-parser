<?php
include_once 'HtmlThing.php';

class HtmlElement extends HtmlThing {
    public $name;
    public $attributes;
    public $content;

    public function __construct($opener) {
	if(preg_match("/^<\s*([^\/>\s]+)((\s+[^\s=]+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*))*)\s*\/?>$/",$opener,$matches)) {
	    $this->name=$matches[1];
	    if($matches[2] != "") {
		preg_match_all("/\s+([^\s=]+)\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*)/",$matches[2],$matchesargs);
		while(count($matchesargs[1])) {
		    $contents=array_shift($matchesargs[2]);
		    if((substr($contents,0,1) == "'") ||
		       (substr($contents,0,1) == '"')) {
			$contents=substr($contents,1,-1);
		    }
		    $this->attributes[strtolower(array_shift($matchesargs[1]))]=$contents;
		}
	    } else {
		$this->attributes=array();
	    }
	} else {
	    $this->name="";
	    $this->attributes=array();
	}
	$this->content=array();
    }
    public function isElement() {
	return true;
    }
    public function isElementName($checkname) {
	return !strcasecmp($this->name,$checkname);
    }
    public function __toString() {
	$rslt="<".$this->name;
	foreach($this->attributes as $key => $val) {
	    $rslt.=" $key=\"$val\"";
	}
	if($this->content!==array()) {
	    $rslt.=">";
	    foreach($this->content as $var) {
		$rslt.=$var->__toString();
	    }
	    $rslt.="</".$this->name.">";
	} else {
	    $rslt.="/>";
	}
	return $rslt;
    }
    public function getChildElementsByTag($tag) {
	$rslt=array();
	foreach($this->content as $var) {
	    if($var->isElement() && (!strcasecmp($var->name,$tag))) {
		array_push($rslt,$var);
	    }
	}
	return $rslt;
    }
    public function getChildElementsByTagAndAtt($tag,$attr,$val) {
	$rslt=array();
	foreach($this->content as $var) {
	    if($var->isElement() && (!strcasecmp($var->name,$tag))
	       && (preg_match($val,$var->attributes[strtolower($attr)]))) {
		array_push($rslt,$var);
	    }
	}
	return $rslt;
    }
    public function getChildElements() {
	$rslt=array();
	foreach($this->content as $var) {
	    if($var->isElement()) {
		array_push($rslt,$var);
	    }
	}
	return $rslt;
    }
    public function getChildTexts() {
	$rslt=array();
	foreach($this->content as $var) {
	    if($var->isText()) {
		array_push($rslt,$var);
	    }
	}
	return $rslt;
    }
    public function getChildComments() {
	$rslt=array();
	foreach($this->content as $var) {
	    if($var->isComment()) {
		array_push($rslt,$var);
	    }
	}
	return $rslt;
    }
    public function getChildTextAndElements() {
	$rslt=array();
	foreach($this->content as $var) {
	    if($var->isElement() || $var->isText()) {
		array_push($rslt,$var);
	    }
	}
	return $rslt;
    }
    public function getChildTextAndElementsAsString() {
	$rslt="";
	foreach($this->content as $var) {
	    if($var->isElement() || $var->isText()) {
		$rslt.=$var->__toString();
	    }
	}
	return $rslt;
    }
}
?>
