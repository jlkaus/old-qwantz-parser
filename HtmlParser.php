<?php
include_once 'HtmlDocument.php';
include_once 'HtmlElement.php';
include_once 'HtmlComment.php';
include_once 'HtmlText.php';

function htmlParseString($inputstring,$verb) {
    # We've got to assume at this point that all empty elements have been fixed up
    # We will close all open tags when they hit the same tagname or their parent closes... complain
    # We can ignore closing tags that have no opener as crossers... complain but ignore

    $doc=new HtmlDocument();
    $opentags=array($doc);

    while($inputstring !== "") {
	if(preg_match("/^<!--[\s\S]*?-->/",$inputstring,$matches)) {	
########## comment ##################################
	    if($verb) {print "<!-- comment -->\n";}
            $inputstring=preg_replace("/^<!--[\s\S]*?-->/","",$inputstring,1);
	    array_push(end($opentags)->content,new HtmlComment($matches[0]));
	} elseif(preg_match("/^<![\s\S]*?>/",$inputstring,$matches)) {
########## processing ###############################
	    if($verb) {print "<!-- processing -->\n";}
            $inputstring=preg_replace("/^<![\s\S]*?>/","",$inputstring,1);
	    array_push(end($opentags)->content,new HtmlText($matches[0]));
	} elseif(preg_match("/^<\/\s*([^>\s]+)\s*>/",$inputstring,$matches)) {
########## closer ###################################
            $inputstring=preg_replace("/^<\/\s*([^>\s]+)\s*>/","",$inputstring,1);
	    $isopen=false;
	    $val=end($opentags);
	    if(!strcasecmp($val->name,$matches[1])) {
		array_pop($opentags);
		if($verb) {print "<!-- $matches[1] closure proper -->\n";}
		continue;
	    }
	    if(preg_match("/^(center|font|noscript)$/i",$matches[1])) {   # span removed... want that!
		if($verb) {print "<!-- $matches[1] closure possibly crossover... autoclosure later -->\n";}
		continue;
	    }
	    do {
		if(!strcasecmp($val->name,$matches[1])) {
		    $isopen=true;
		}
	    } while($val=prev($opentags));
	    if($isopen) {
		if($verb) {print "<!-- $matches[1] closure caused autoclosures of: -->\n";}
		while(strcasecmp($fc=array_pop($opentags)->name,$matches[1])) {
		    if($verb) {print "<!--        $fc -->\n";}
		}
	    } else {
		if($verb) {print "<!-- $matches[1] closure with no opener detected -->\n";}
	    }
	} elseif(preg_match("/^<\s*([^\/>\s]+)(\s+[^\s=]+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*))*\s*\/>/",$inputstring,$matches)) {
########## empty ####################################
            $inputstring=preg_replace("/^<\s*([^\/>\s]+)(\s+[^\s=]+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*))*\s*\/>/","",$inputstring,1);
	    if(preg_match("/^(p|li|dt|dd)$/i",$matches[1])) {
		$isopen=false;
		$val=end($opentags);
		do {
		    if(!strcasecmp($val->name,$matches[1])) {
			$isopen=true;
		    }
		} while($val=prev($opentags));
		if($isopen) {
		    if($verb) {print "<!-- $matches[1] opener(empty) forced early closures of: -->\n";}
		    while(strcasecmp($fc=array_pop($opentags)->name,$matches[1])) {
			if($verb) {print "<!--         $fc -->\n";}
		    }
		}
	    }
	    if($verb) {print "<!-- $matches[1] opener(empty) -->\n";}
	    array_push(end($opentags)->content,new HtmlElement($matches[0]));
	} elseif(preg_match("/^<\s*([^\/>\s]+)(\s+[^\s=]+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*))*\s*>/",$inputstring,$matches)) {
########## opener ###################################
            $inputstring=preg_replace("/^<\s*([^\/>\s]+)(\s+[^\s=]+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*))*\s*>/","",$inputstring,1);
	    if(preg_match("/^(p|li|dt|dd)$/i",$matches[1])) {
		$isopen=false;
		$val=end($opentags);
		do {
		    if(!strcasecmp($val->name,$matches[1])) {
			$isopen=true;
		    }
		} while($val=prev($opentags));
		if($isopen) {
		    if($verb) {print "<!-- $matches[1] opener forced early closures of: -->\n";}
		    while(strcasecmp($fc=array_pop($opentags)->name, $matches[1])) {
			if($verb) {print "<!--         $fc -->\n";}
		    }
		}
	    }
	    if($verb) {print "<!-- $matches[1] opener -->\n";}
	    array_push(end($opentags)->content,new HtmlElement($matches[0]));
	    array_push($opentags,end(end($opentags)->content));
	} elseif(preg_match("/^[^<]+/",$inputstring,$matches)) {
########## free text ################################
            $inputstring=preg_replace("/^[^<]+/","",$inputstring,1);
	    array_push(end($opentags)->content,new HtmlText($matches[0]));
	} else {
########## default ##################################
	    if($verb) {print "<!-- Don't understand: [".$inputstring."] -->\n";}
	    break;
	}
    }

    while(array_pop($opentags)->isElement()) {}

    return $doc;
}

function htmlPreParse($inputstring) {
    return preg_replace("/<\s*(hr|br|img|meta|link|input|area)((\s+[^\s=]+\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\'\"\s\/>]*))*)\s*>/i","<$1$2/>",$inputstring);
}

function htmlGetBodyLinks($container) {
    # a hrefs live in the container at any depth
    # will return list of a elements

}

function htmlGetMetaContent($doc,$name) {
    # metas live in the head
    # will return list of content strings
    $metas= reset(
		  reset(
			$doc->getChildElementsByTag("html")
			)->getChildElementsByTag("head")
		  )->getChildElementsByTagAndAtt("meta","name","/^{$name}$/i");
    $rslt=array();
    foreach($metas as $var) {
	array_push($rslt,$var->attributes["content"]);
    }
    return $rslt;
}

function htmlGetTitle($doc) {
    # title lives in the head
    # will return one string
    return reset(
		 reset(
		       reset(
			     reset(
				   $doc->getChildElementsByTag("html")
				   )->getChildElementsByTag("head")
			     )->getChildElementsByTag("title")
		       )->getChildTexts()
		 )->content;
}

function htmlDescendSeek($doc,$tag) {
    # ok, here we use doc as base, and recursively decend through
    # element children looking for tags of $tag
    $rslt=array();

    if($doc->isDocument() || $doc->isElement()) {
	if(!strcasecmp($doc->name,$tag)) {
	    array_push($rslt,$doc);
	}
	$childres=$doc->content;
	foreach($childres as $var) {
	    $rslt=array_merge($rslt,htmlDescendSeek($var,$tag));
	}
    }

    return $rslt;
}

function htmlDescendSeekAndAtt($doc,$tag,$att,$ss) {
    # ok, here we use doc as base, and recursively decend through
    # element children looking for tags of $tag and attr[$att] matching $ss
    $rslt=array();

    if($doc->isDocument() || $doc->isElement()) {
	if((!strcasecmp($doc->name,$tag)) &&
	   isset($doc->attributes[strtolower($att)]) &&
	   preg_match($ss,$doc->attributes[strtolower($att)])) {
	    array_push($rslt,$doc);
	}
	$childres=$doc->content;
	foreach($childres as $var) {
	    $rslt=array_merge($rslt,htmlDescendSeekAndAtt($var,$tag,$att,$ss));
	}
    }

    return $rslt;
}


function htmlDescendText($doc) {
    $rslt="";

    if($doc->isDocument() || $doc->isElement()) {
	$childres=$doc->content;
	foreach($childres as $var) {
	    $rslt.=htmlDescendText($var);
	}

    }
    if($doc->isText()) {
	$rslt.=$doc->content;
    }
    return $rslt;
}

function htmlDescendTextWithTags($doc,$ss) {
    $ndoc=new HtmlElement("");
    $ndoc->name=$doc->name;
    $ndoc->attributes=$doc->attributes;
   
    foreach($doc->content as $var) {
	if($var->isText()) {
	    array_push($ndoc->content,$var);
	}
	if($var->isElement()) {
	    if(preg_match($ss,$var->name)) {
		array_push($ndoc->content,
			   htmlDescendTextWithTags($var,$ss));
	    } else {
		$ndoc->content=array_merge($ndoc->content,
					   htmlDescendTextWithTags($var,$ss)->content);
	    }
	}
    }
    return $ndoc;
}

function htmlGetNormalSpans($doc,$class) {
    # spans can live anywhere in the body
    # will return list of span elements
    $nspans=htmlDescendSeek($doc,"span");
    $rslts=array();
    foreach($nspans as $var) {
	if(!strcasecmp($var->attributes["class"],$class)) {
	    array_push($rslts,$var);
	}
    }
    return $rslts;
}

function htmlDescendComments($doc) {
    $rslt=array();

    if($doc->isDocument() || $doc->isElement()) {
	$childres=$doc->content;
	foreach($childres as $var) {
	    $rslt=array_merge($rslt,htmlDescendComments($var));
	}
    }
    if($doc->isComment()) {
	array_push($rslt,$doc->content);
    }

    return $rslt;
}

function htmlGetCommentedSpans($doc,$class) {
    # spans can live in comments anywhere in the body
    # will return list of span elements
    $cspans=htmlDescendComments($doc);

    $rslt=array();
    foreach($cspans as $var) {
	$cdoc=htmlParseString($var,0);
	$rslt=array_merge($rslt,htmlGetNormalSpans($cdoc,$class));
    }
    return $rslt;
}


?>
