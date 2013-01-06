<?php
# Writer's Block v3.8: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

header('Content-type: application/xml');
require ('admin/control.php');
$site = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
$getlatest = mysql_query("SELECT LatestComments FROM ".CONFIG_TBL);
$latest = mysql_fetch_array($getlatest);
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
?>

<rss version="2.0">
    <channel>
        <language>en-us</language>
        <title><?php echo $sitename_is; ?>: Latest comments</title>
        <description>The latest comments made on <?php echo $sitename_is; ?>.</description>
        <link><?php echo $site; ?></link>
        <copyright>Comments are property of their authors, from the date of their posting.</copyright>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<generator>http://www.desiquintans.com/writersblock</generator>
        <?php
        $newestcomments = mysql_query("SELECT CommID, PostID, Name, Url, Body, Timestamp FROM ".COMMENTS_TBL." ORDER BY CommID DESC LIMIT ".$latest['LatestComments']);
        while($commentrow = mysql_fetch_array($newestcomments)) {
            $comment = strip_tags($commentrow['Body']);
            $body = substr($comment, 0, 100);
            if(strlen($comment) > 100) $body = $body.'&#8230;';
            $gettitle = mysql_query("SELECT Title FROM ".POSTS_TBL." WHERE PostID=".$commentrow['PostID']);
            $title = mysql_fetch_array($gettitle);
            
            echo '
            <item>
            <title>'.$commentrow['Name'].' re. '.$title['Title'].'</title>
            <pubDate>'.date('D, w M Y H:i:s', $commentrow['Timestamp']).' GMT</pubDate>
            <description>'.$body.'</description>
            <link>'.$site.'permalink.php?PostID='.$commentrow['PostID'].'</link>
            <guid isPermaLink="true">'.$site.'permalink.php?PostID='.$commentrow['PostID'].'#C'.$commentrow['CommID'].'</guid>
            </item>
            ';
        }
        ?>
    </channel>
</rss>
