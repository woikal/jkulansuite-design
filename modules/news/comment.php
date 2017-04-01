<?php
// CHECK IF NEWSID IS VALID
$check = $db->qry_first('SELECT caption FROM %prefix%news WHERE newsid = %int%', $_GET['newsid']);
if ($check["caption"] != "")
{

    $framework->AddToPageTitle($check["caption"]);
    $func->SetRead('news', $_GET['newsid']);

    // GET NEWS DATA
    $get_news                       = $db->qry_first('SELECT n.*, UNIX_TIMESTAMP(n.date) AS date, u.userid, u.username FROM %prefix%news n LEFT JOIN %prefix%user u ON u.userid = n.poster WHERE n.newsid = %int%', $_GET['newsid']);
    $templ_news_single_row_priority = $get_news["priority"];

    if ($templ_news_single_row_priority == 1)
    {
        $news_type = "important";
    }
    else
    {
        $news_type = "normal";
    }

    $smarty->assign('caption', $get_news["caption"]);
    $smarty->assign('userid', $get_news["poster"]);
    $smarty->assign('username', $dsp->FetchUserIcon($get_news['userid'], $get_news["username"]));
    $smarty->assign('date', $func->unixstamp2date($get_news["date"], "daydatetime"));

    $text = '';
    if ($auth["type"] > 1)
    {
        $text .= $dsp->FetchIcon("index.php?mod=news&action=delete&came_from=2&step=2&newsid={$_GET["newsid"]}", "delete", '', '', 'right');
        $text .= $dsp->FetchIcon("index.php?mod=news&action=change&came_from=1&step=2&newsid={$_GET["newsid"]}", "edit", '', '', 'right');
    }
    if ($cfg["news_html"] == 1)
    {
        $get_news['text'] = $func->text2html($get_news['text']);
    }
    else
    {
        $get_news['text'] = $func->AllowHTML($get_news['text']);
    }
    $text .= $get_news['text'];
    if ($get_news['link_1'])
    {
        $text .= '<br><u>' . t('Links zum Thema:') . '</u><br><a href="' . $get_news['link_1'] . '" target="_blank">' . $get_news['link_1'] . '</a>';
    }
    if ($get_news['link_2'])
    {
        $text .= '<br><a href="' . $get_news['link_2'] . '" target="_blank">' . $get_news['link_2'] . '</a>';
    }
    if ($get_news['link_3'])
    {
        $text .= '<br><a href="' . $get_news['link_3'] . '" target="_blank">' . $get_news['link_3'] . '</a>';
    }
    $smarty->assign('text', $text);

    // SELECT ACTION TYPE
    if ($_GET["mcact"] == "" OR $_GET["mcact"] == "show")
    {
        $dsp->NewContent(t('Newsmeldung + Kommentare'), t('Hier kannst du diese News kommentieren'));
        $dsp->AddSingleRow($smarty->fetch("modules/news/templates/show_single_row_$news_type.htm"));
        $dsp->AddSingleRow($dsp->FetchSpanButton(t('Newsübersicht'), "index.php?mod=news&action=show"));
    }

    if ($cfg['news_comments_allowed'] == false)
    {
        $dsp->AddSingleRow(t('Kommentare wurden deaktiviert.'));
    }
    else
    {
        include('inc/classes/class_mastercomment.php');
        new Mastercomment('news', $_GET['newsid'], array('news' => 'newsid'));
    }


    /* Prepare Facebook Meta Tags */
    $fb_title = $get_news["caption"];

    $fb_description = preg_replace('#<img (.*?) \/>#', "", $get_news['text']);// remove image tags
    $fb_description = preg_replace('#</?(.*?)>#', "", $fb_description);// remove all other tags
    $fb_description = substr($fb_description, 0, 200); // cut off after 200 chars
    $fb_description = preg_replace('#\s(\w+)$#', " ...", $fb_description); // remove  last (possibly cut through) word

    $fb_image = preg_replace('#(.*?)<img(.*?) (src=")#', "", $get_news['text']);// remove image tags
    $fb_image = preg_replace('#\"(.*?)\/>(.*)#', "", $fb_image);// remove image tags


    $framework->add_facebook_meta_props($fb_title, $fb_description, $fb_image);

}
else
{
    $func->error(t('Diese Newsmeldung existiert nicht'));
}
?>