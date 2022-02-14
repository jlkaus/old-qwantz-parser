<?php 
    
include_once 'HtmlParser.php';

if(isset($_GET["comic"])) {
    $whichone=$_GET["comic"];
} else {
    $whichone="";
}

$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,"http://www.qwantz.com/index.pl?comic=$whichone");
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
$predoc=curl_exec($ch);
curl_close($ch);

// $predoc=file_get_contents("http://www.qwantz.com/index.pl?comic=$whichone");
$doc=htmlParseString(htmlPreParse($predoc),0);

$doctitle=htmlGetTitle($doc);

$metatitle=reset(htmlGetMetaContent($doc,"title"));
$metadesc=reset(htmlGetMetaContent($doc,"description"));
$metakeys=reset(htmlGetMetaContent($doc,"keywords"));
$uniq_content=reset(htmlGetMetaContent($doc,"content"));

$uniq_title=htmlDescendText(reset(htmlGetCommentedSpans($doc,"rss-title")));
$uniq_pubdate=htmlDescendText(reset(htmlGetCommentedSpans($doc,"rss-pubdate")));
$uniq_link=htmlDescendText(reset(htmlGetCommentedSpans($doc,"rss-link")));
$rss_id=htmlDescendText(reset(htmlGetCommentedSpans($doc,"rss-id")));

$uniq_mailto=reset(htmlDescendSeekAndAtt($doc,"a","href","/^mailto:/i"))->attributes["href"];

$nspans=htmlGetNormalSpans($doc,"rss-content");
$logocontent=trim(htmlDescendTextWithTags($nspans[0],"/^(a|img)$/i")->getChildTextAndElementsAsString());
$logotitle=reset(htmlDescendSeekAndAtt($nspans[0],"img","title","//"))->attributes["title"];

$trexpic=reset(htmlDescendSeekAndAtt($doc,"img","title","/t-rex/"));
$randcomic=reset(htmlDescendSeekAndAtt($doc,"a","href","/comic=/"));

$uniq_comic=trim(htmlDescendTextWithTags($nspans[3],"/^(a|img)$/i")->getChildTextAndElementsAsString());
$uniq_hover=reset(htmlDescendSeekAndAtt($nspans[3],"img","title","//"))->attributes["title"];

$uniq_blog=trim(htmlDescendTextWithTags($nspans[6],"/^(a|img)$/i")->getChildTextAndElementsAsString());



?>

<html>
	<head>
    	<title><?php print $title; ?></title>
	</head>
	<body>
		<p>
			<b>
				<font size="+4">
					<?php print $metatitle; ?>
				</font>
				</br>
				<font size="+2">
					<?php print $metadesc; ?>
				</font>
			</b>
			<br/>
			<br/>	
			<font size="-6">
				(<?php print $metakeys; ?>)
			</font>
			<br/>
			<hr/>
		</p>
		<p>
			CONTENT: <br/>
			<font size="-2">
				<pre>
<?php print $uniq_content; ?>
				</pre>
			</font>
		</p>
		<hr/>
		TITLE:   <?php print $uniq_title; ?><br/>
		PUBDATE: <?php print $uniq_pubdate; ?><br/>
		LINK: 	 <?php print $uniq_link; ?><br/>
		RSS-ID:  <?php print $rss_id; ?>
		<hr/>
		MAILTO:  <?php print $uniq_mailto; ?>
		<hr/>
		<?php print $logocontent; ?><br/>
		[<?php print $logotitle; ?>]
		<hr/>
		<?php print $trexpic; print $randcomic; ?>
		<hr/>
		COMIC:   <?php print $uniq_comic; ?><br/>
		HOVER:   [<?php print $uniq_hover; ?>]
		<hr/>
		BLOG  :  <?php print $uniq_blog; ?>
		<hr/>
	</body>
</html>
