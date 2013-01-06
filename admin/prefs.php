<?php
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('control.php');

// Check for authentication
if (!isset($_COOKIE['auth_id'], $_COOKIE['auth_username'], $_COOKIE['auth_hash'])) {
    include('login.php');
    exit();
} else {
    $authenticate = mysql_query("SELECT Permission FROM ".USERS_TBL." WHERE UserID='".$_COOKIE['auth_id']."' AND
    UserName='".$_COOKIE['auth_username']."' AND Password='".$_COOKIE['auth_hash']."'");
    if(mysql_num_rows($authenticate) != 1) {
        include('login.php');
        exit();
    } else {
        $extend_expiry = time()+7200;
        setcookie('auth_id', $_COOKIE['auth_id'], $extend_expiry, '', '', 0);
        setcookie('auth_username', $_COOKIE['auth_username'], $extend_expiry, '', '', 0);
        setcookie('auth_hash', $_COOKIE['auth_hash'], $extend_expiry, '', '', 0);
    }
    $p = mysql_fetch_array($authenticate);
    if ($p['Permission'] != 1) {
        $pagetitle = 'Not authorised';
        include ('include/head.htm');
        print '<p align="center"><img src="include/img/unauthorised.png" width="16" height="16" alt="" /> You are not authorised to change settings.</p>';
        include ('include/foot.htm');
        exit();
    }
}

$pagetitle = 'Configure Writer\'s Block';
include ('include/head.htm');
include ('include/xmlfriendly.inc');

$getlatest = mysql_query("SELECT LatestComments, TZoffset, BlogFeedNum FROM ".CONFIG_TBL." LIMIT 1");
$row = mysql_fetch_array($getlatest);

if($_POST[submit]) {
    $settings_changed = mysql_query("UPDATE ".CONFIG_TBL." SET ShowPosts='".$_POST['showposts']."', PostDatePattern='".$_POST['postdate']."',
    CommDatePattern='".$_POST['commdate']."', AnyDatePattern='".$_POST['anydate']."', ListDatePattern='".$_POST['listdate']."', ShowNew='".$_POST['shownew']."',
    CommentOrder='".$_POST['commentorder']."', ThreadLife='".$_POST['threadlife']."', SiteUrl='".$_POST['siteurl']."', DefaultTitle='".$_POST['indextitle']."',
    Tagline='".$_POST['tagline']."', Archive='".$_POST['archive']."', Articles='".$_POST['articles']."', TZoffset='".$_POST['tzoffset']."',
    Author='".$_POST['author']."', SiteName='".$_POST['sitename']."', SiteDesc='".$_POST['sitedesc']."', LatestComments='".$_POST['latestcomments']."',
    BlogFeedNum='".$_POST['blogfeednum']."', DelimiterMain='".$_POST['delimitermain']."', DelimiterList='".$_POST['delimiterlist']."',
    DelimiterNewest='".$_POST['delimiternewest']."'");
    if ($settings_changed) {
        echo '<p align="center"><img src="include/img/success.png" width="16" height="16" alt="" /> Writer\'s Block\'s settings have been updated.</p>';
        include ('include/foot.htm');
        exit();
    }
}
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<fieldset>
<legend>Date and time display (<a href="http://www.desiquintans.com/writersblock/manual/index.php?page=formattingdates">[how?]</a>)</legend>
Date on posts:<input type="text" size="15" name="postdate" value="<?php echo $postdatepattern_is; ?>" />
Date on comments:<input type="text" size="15" name="commdate" value="<?php echo $commdatepattern_is; ?>" /><br /><br />
Date on Anypages:<input type="text" size="15" name="anydate" value="<?php echo $anydatepattern_is; ?>" />
Date on lists:<input type="text" size="15" name="listdate" value="<?php echo $listdatepattern_is; ?>" />
<p>
<input type="submit" name="submit" value="Update Preferences" />
</p>
</fieldset>
<fieldset>
<legend>Number of items to display in public site</legend>
Posts on front page:<input type="text" size="2" name="showposts" maxlength="2" value="<?php echo $showposts_is; ?>" />
Newest Articles in Articles page:<input type="text" size="2" name="shownew" maxlength="2" value="<?php echo $shownew_is; ?>" />
<p>
Posts in archive pages:<input type="text" size="3" name="archive" maxlength="3" value="<?php echo $archive_is; ?>" />
Anypages in category listings:<input type="text" size="3" name="articles" maxlength="3" value="<?php echo $articles_is; ?>" />
</p>
<input type="submit" name="submit" value="Update Preferences" />
</fieldset>
<fieldset>
<legend>Site Settings</legend>
Address of index.php:<br />
<input type="text" size="40" name="siteurl" value="<?php echo $siteurl_is; ?>" />
<p>
Title of front page (e.g. <strong>Welcome</strong> -- mysite.com):<br />
<input type="text" size="40" name="indextitle" value="<?php echo $defaulttitle_is; ?>" />
</p><p>
Tagline (appears on every page: e.g. Welcome<strong> -- mysite.com</strong>):<br />
<input type="text" size="40" name="tagline" value="<?php echo $tagline_is; ?>" />
</p><p>
Show comments in this order:<br />
<?php
switch ($commentorder_is) {
case 'DESC':
    print '
    <input type="radio" name="commentorder" id="codesc" value="DESC" checked="checked" /><label for="codesc">New to old</label><br />
    <input type="radio" name="commentorder" id="coasc" value="ASC" /><label for="coasc">Old to New</label>
    ';
    break;
default:
    print '
    <input type="radio" name="commentorder" id="codesc" value="DESC" /><label for="codesc">New to old</label><br />
    <input type="radio" name="commentorder" id="coasc" value="ASC" checked="checked" /><label for="coasc">Old to New</label>
    ';
    break;
}
?>
</p><p>
Number of hours before comment threads automatically close (0 is infinite):<br />
<input type="text" size="3" name="threadlife" maxlength="3" value="<?php echo $threadlife_is/60/60; ?>" />
</p><p>
Delimiter between category names:<br />
In lists:<input type="text" size="15" name="delimiterlist" value="<?php echo $delimiterlist_is; ?>" />
In Newest Articles:<input type="text" size="15" name="delimiternewest" value="<?php echo $delimiternewest_is; ?>" />
In main view:<input type="text" size="15" name="delimitermain" value="<?php echo $delimitermain_is; ?>" />
</p>
<input type="submit" name="submit" value="Update Preferences" />
</fieldset>

<fieldset><legend>Syndication/Metatag information</legend>
Name of your site:<input type="text" size="30" name="sitename" value="<?php echo $sitename_is; ?>" />
Name of author:<input type="text" size="20" name="author" value="<?php echo $author_is; ?>" />
<p>
Short description of site:<br />
<textarea cols="40" rows="5" name="sitedesc"><?php echo $sitedesc_is; ?></textarea>
<p>
GMT offset in hours:<input type="text" size="5" maxlength="5" name="tzoffset" value="<?php echo $row['TZoffset']; ?>" />
'Latest Comments' shown in feed:<input type="text" size="2" name="latestcomments" maxlength="2" value="<?php echo $row['LatestComments']; ?>" />
</p><p>
Blog posts shown in feed:<input type="text" size="2" name="blogfeednum" maxlength="2" value="<?php echo $row['BlogFeedNum']; ?>" />
</p>
<input type="submit" name="submit" value="Update Preferences" />
</fieldset>
</form>

<?php
include ('include/foot.htm');
?>
