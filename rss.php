<?php
# Writer's Block v3.8: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);
$site = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
if(substr($site, -1) != '/') {
	$site .= '/';
}
$getoffset = mysql_query("SELECT TZoffset, BlogFeedNum FROM ".CONFIG_TBL." LIMIT 1");
$offset = mysql_fetch_array($getoffset);

           
if(isset($_GET['miniblog'])) {
    $check = strpos($_GET['miniblog'], '+');
    $check .= strpos($_GET['miniblog'], '%20');
    if(!$check) { // If there are no spaces in the miniblog ID name, try to fetch records.
        $exist = mysql_query("SELECT * FROM ".$_GET['miniblog']." WHERE MiniDraft=0");
    }

    if($exist == false or $check) {
    ## If a user accesses this without defining a Mini-Blog table, or if the query string contains spaces
        $pagetitle =& $language['404'];
        include ('template/header.htm');
        include ('template/404.htm');
        include ('template/footer.htm');
        exit();
    } else {
        $mini_info = mysql_query("SELECT MiniBlogTitle, MiniBlogDesc, MiniBlogAuthor FROM ".MINIBLOG_TBL."
            WHERE MiniBlogUrl='".$_GET['miniblog']."' LIMIT 1");
        $mini_inf = mysql_fetch_array($mini_info);
        $get_author = mysql_query("SELECT DispName FROM ".USERS_TBL." WHERE UserID='".$mini_inf['MiniBlogAuthor']."' LIMIT 1");
        $author = mysql_fetch_array($get_author);
    }

	## Show the feed for a Mini-Blog if asked for.
	header('Content-type: application/xml');
	echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
    ?>
    <rss version="2.0">
        <channel>
            <language>en-us</language>
            <title><?php echo $mini_inf['MiniBlogTitle']; ?></title>
            <description><?php echo $mini_inf['MiniBlogDesc']; ?></description>
            <link><?php echo $site; ?></link>
            <copyright>Copyright <?php echo $author['DispName'].', '.date('Y'); ?></copyright>
    				<docs>http://blogs.law.harvard.edu/tech/rss</docs>
    				<generator>http://www.desiquintans.com/writersblock</generator>
                <?php
                $getid = mysql_query("SELECT MiniID, MiniTitle, MiniBody, MiniTimestamp FROM ".$_GET['miniblog']." WHERE MiniDraft=0 ORDER
                    BY MiniTimestamp DESC LIMIT ".$offset['BlogFeedNum']);
                while ($row = mysql_fetch_array($getid)) {
                    $full = strip_tags($row['MiniBody'], '<a>');
                    $body = substr($full, 0, 500);
                    if(strlen($full) > 500) $body = $body.'&#8230;';

                    print '
                    <item>
                    <title>'.$row['MiniTitle'].'</title>
                    <pubDate>'.date('r', $row['MiniTimestamp']).'</pubDate>
                    <author>'.$author['DispName'].'</author>
                    <description>'.strip_tags($body).'</description>
                    <link>'.$site.'miniblog.php?blog='.$_GET['miniblog'].'&amp;entry='.$row['MiniID'].'</link>
                    <guid isPermaLink="true">'.$site.'miniblog.php?blog='.$_GET['miniblog'].'&amp;entry='.$row['MiniID'].'</guid>
                    </item>
                    ';
                }
                ?>
        </channel>
    </rss>
<?php
    exit();
} else {
## Or else, just show the normal feed.
    $getoffset = mysql_query("SELECT TZoffset, BlogFeedNum FROM ".CONFIG_TBL." LIMIT 1");
    $offset = mysql_fetch_array($getoffset);

    $getid = mysql_query("SELECT PostID, Timestamp, Title, Body, PostCat1, PostCat2, PostCat3, PostCat4, Author FROM ".POSTS_TBL."
        WHERE Draft=0 ORDER BY Timestamp DESC LIMIT ".$offset['BlogFeedNum']);
    $get_cat_names = mysql_query("SELECT PCatID, PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
        $curl[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatUrl'];
    }
    header('Content-type: application/xml');
    echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
    ?>
    
    <rss version="2.0">
        <channel>
            <language>en-us</language>
            <title><?php echo $sitename_is; ?></title>
            <description><?php echo $sitedesc_is; ?></description>
            <link><?php echo $site; ?></link>
            <copyright>Copyright <?php echo $author_is.', '.date('Y'); ?></copyright>
    				<docs>http://blogs.law.harvard.edu/tech/rss</docs>
    				<generator>http://www.desiquintans.com/writersblock</generator>
                <?php
                while ($row = mysql_fetch_array($getid)) {
                    $full = strip_tags($row['Body'], '<a>');
                    $body = substr($full, 0, 500);
                    if(strlen($full) > 500) $body = $body.'&#8230;';
    
                    $category = NULL;
                    for ($i = 1; $i <= 4; ++$i) {
                        if(empty($row['PostCat'.$i])) {$category .= '';} else {
                            $category .= '<category domain="'.$site.'archive.php?cat='.$curl[$row['PostCat'.$i]].'">
                                '.$cname[$row['PostCat'.$i]].'</category>';
                        }
                    }
    
                    print '
                    <item>
                    <title>'.$row['Title'].'</title>
                    <pubDate>'.date('r', $row['Timestamp']).'</pubDate>
                    '.$category.'
                    <description>'.strip_tags($body).'</description>
                    <link>'.$site.'permalink.php?PostID='.$row['PostID'].'</link>
                    <guid isPermaLink="true">'.$site.'permalink.php?PostID='.$row['PostID'].'</guid>
                    <comments>'.$site.'permalink.php?PostID='.$row['PostID'].'</comments>
                    </item>
                    ';
                }
                ?>
        </channel>
    </rss>
<?php } ?>
