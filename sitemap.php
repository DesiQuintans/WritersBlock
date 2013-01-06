<?php
# Writer's Block v3.8: http://www.desiquintans.com/writersblock
# Writer's Block is free under version 2 or later of the GPL.
# This program is distributed with cursory support, but without
# warranty or guarantee of any sort.

require ('admin/control.php');
require ('template/'.LANGUAGE_FILE);

$pagetitle =& $language['sitemap'];

$getblog = mysql_query("SELECT PostCatUrl, PostCatName FROM ".POSTCAT_TBL);
$blogcats = '<ul>';
while ($bcat = mysql_fetch_array($getblog)) {
    $blogcats .= '<li><a href="archive.php?cat='.$bcat['PostCatUrl'].'">'.$bcat['PostCatName'].'</a></li>';
}
$blogcats .= '</ul>';

$anypages = NULL;
$getcats = mysql_query("SELECT ACatID, AnyCatUrl, AnyCatName FROM ".ANYCAT_TBL);
while ($acat = mysql_fetch_array($getcats)) {
    $anypages .= '
    <ul>
    <li><a href="articles.php?cat='.$acat['AnyCatUrl'].'">'.$acat['AnyCatName'].'</a></li>
    <ol>
    ';

    $getpages = mysql_query("SELECT UrlName, Title FROM ".ANYPAGE_TBL." WHERE AnyCat1='".$acat['ACatID']."' ORDER BY AnyID DESC");
    while($pages = mysql_fetch_array($getpages)) {
        $anypages .= '
        <li><a href="articles.php?page='.$pages['UrlName'].'">'.$pages['Title'].'</a></li>
        ';
    }
    $anypages .= '</ol></ul>';
}

include ('template/header.htm');

$use_template = array('{TITLE}' => $pagetitle,'{BLOG_CATEGORIES}' => $blogcats,'{ANYPAGES}' => $anypages);
if ($file = file_get_contents('template/sitemap.htm')) {
    foreach($use_template as $key => $value) {
        $file = @str_replace($key, $value, $file);
    }
    echo $file;
}

include ('template/footer.htm');
?>