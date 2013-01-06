<?php
# Writer's Block v3.8: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

header('Content-type: application/xml');
require ('admin/control.php');

$gettime = mysql_query("SELECT Timestamp FROM ".POSTS_TBL." WHERE Draft=0 ORDER BY Timestamp DESC LIMIT 1");
$feed_updated = mysql_fetch_array($gettime);
$pupdated = date('Y-m-d\TH:i:s\Z', $feed_updated['Timestamp']);

$getoffset = mysql_query("SELECT TZoffset, BlogFeedNum FROM ".CONFIG_TBL." LIMIT 1");
$offset = mysql_fetch_array($getoffset);
$postupdated = $pupdated.$offset['TZoffset'];

$get_user_names = mysql_query("SELECT UserID, DispName FROM ".USERS_TBL);
while($get_user_names_result = mysql_fetch_array($get_user_names)) {
    $users[$get_user_names_result['UserID']] = $get_user_names_result['DispName'];
}

$site = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
if(substr($site, -1) != '/') {
	$site .= '/';
}
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
?>

<feed xmlns="http://www.w3.org/2005/Atom">
    <title type="text"><?php echo $sitename_is; ?></title>
    <subtitle type="html"><?php echo $sitedesc_is; ?></subtitle>
    <author>
        <name><?php echo $author_is; ?></name>
        <uri><?php echo $siteurl_is; ?></uri>
    </author>
    <updated><?php echo $postupdated; ?></updated>
    <id><?php echo $site; ?></id>
    <link rel="alternate" type="text/html" hreflang="en" href="<?php echo $siteurl_is; ?>"/>
    <link rel="self" type="application/atom+xml" href="<?php echo $site.'atom.php'; ?>"/>
    <rights>Copyright <?php echo $author_is.', '.date('Y'); ?></rights>
    <generator uri="http://www.desiquintans.com/writersblock" version="v3.8">Writer's Block</generator>
    <?php
    $getid = mysql_query("SELECT PostID, Timestamp, Title, Body, PostCat1, PostCat2, PostCat3,
        PostCat4, Author FROM ".POSTS_TBL." WHERE Draft=0 ORDER BY Timestamp DESC LIMIT ".$offset['BlogFeedNum']);
    $get_cat_names = mysql_query("SELECT PCatID, PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
    while($get_cat_names_result = mysql_fetch_array($get_cat_names)) {
        $cname[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatName'];
        $curl[$get_cat_names_result['PCatID']] = $get_cat_names_result['PostCatUrl'];
    }

    while ($row = mysql_fetch_array($getid)) {
        $date = date('Y-m-d\TH:i:s\Z', $row['Timestamp']).$tzoffset;
        $full = strip_tags($row['Body'], '<a>');
        $body = substr($full, 0, 500);
        if(strlen($full) > 500) $body = $body.'&#8230;';
        
        $category = NULL;
        for ($i = 1; $i <= 4; ++$i) {
            if(empty($row['PostCat'.$i])) {$category .= '';} else {
                $category .= '<category term="'.$curl[$row['PostCat'.$i]].'" scheme="'.$site.'archive.php?cat=
                    '.$curl[$row['PostCat'.$i]].'" label="'.$cname[$row['PostCat'.$i]].'"/>';
            }
        }
    
        echo '
        <entry>
        <contributor>'.$users[$row['Author']].'</contributor>
        <title>'.$row['Title'].'</title>
        <link rel="alternate" type="text/html" href="'.$site.'permalink.php?PostID='.$row['PostID'].'"/>
        <id>'.$site.'permalink.php?PostID='.$row['PostID'].'</id>
        <updated>'.$date.'</updated>
        '.$category.'
        <content type="text">
        '.$body.'
        </content>
        </entry>
        ';
        $category = NULL;
    }
    ?>
</feed>
