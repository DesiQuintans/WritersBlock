<?php
# Writer's Block v3.8a: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.


## Remove the double slashes from the start of the line below to disable all non-fatal error reporting.
//error_reporting(1);

## What language file should Writer's Block use for public files? Change only the second quoted value. Default is en.php.
define('LANGUAGE_FILE', 'en.php');

## Set your mySQL details:
$DB_USER = '';          // Username
$DB_PASS = '';          // Password
$DB_HOST = 'localhost';         // mySQL hostname (usually 'localhost')
$DB_NAME = '';      // Name of database to store information in

## Name the mySQL tables Writer's Block will create and use (change only the second quoted value):
define('USERS_TBL', 'wb_users');        // User info
define('CONFIG_TBL', 'wb_config');      // Settings
define('POSTS_TBL', 'wb_posts');        // Blog posts
define('POSTCAT_TBL', 'wb_postcat');    // Categories for posts
define('COMMENTS_TBL', 'wb_comments');  // Comments on posts
define('ANYPAGE_TBL', 'wb_anypage');    // Anypages
define('ANYCAT_TBL', 'wb_anycat');      // Categories for Anypages
define('MINIBLOG_TBL', 'wb_miniblog');  // Keeps track of the Mini-Blogs you've made.


## Don't edit below this line.

mysql_connect ($DB_HOST, $DB_USER, $DB_PASS) or die ('Couldn\'t connect to mySQL: '.mysql_error());
mysql_select_db ($DB_NAME) or die ('Couldn\'t select the database: '.mysql_error());

$settings = mysql_query("SELECT ShowPosts, DefaultTitle, Tagline, SiteUrl, CommentOrder, PostDatePattern, CommDatePattern, AnyDatePattern, ListDatePattern, ShowNew,
Archive, Articles, Author, SiteName, SiteDesc, ThreadLife, DelimiterList, DelimiterNewest, DelimiterMain FROM ".CONFIG_TBL);
$wbconfig = @mysql_fetch_array($settings);
    $showposts_is =& $wbconfig['ShowPosts'];
    $defaulttitle_is =& $wbconfig['DefaultTitle'];
    $tagline_is =& $wbconfig['Tagline'];
    $siteurl_is =& $wbconfig['SiteUrl'];
    $commentorder_is =& $wbconfig['CommentOrder'];
    $postdatepattern_is =& $wbconfig['PostDatePattern'];
    $commdatepattern_is =& $wbconfig['CommDatePattern'];
    $anydatepattern_is =& $wbconfig['AnyDatePattern'];
    $listdatepattern_is =& $wbconfig['ListDatePattern'];
    $shownew_is =& $wbconfig['ShowNew'];
    $archive_is =& $wbconfig['Archive'];
    $articles_is =& $wbconfig['Articles'];
    $author_is =& $wbconfig['Author'];
    $sitename_is =& $wbconfig['SiteName'];
    $sitedesc_is =& $wbconfig['SiteDesc'];
    $delimiterlist_is =& $wbconfig['DelimiterList'];
    $delimiternewest_is =& $wbconfig['DelimiterNewest'];
    $delimitermain_is =& $wbconfig['DelimiterMain'];
    $threadlife_is = $wbconfig['ThreadLife']*60*60;

## DON'T REMOVE THE FOLLOWING UNSET(). This is important for security.
unset($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
?>
