<?php
/**
 * Generate Loginbox
 *
 * @package lansuite_core
 * @author knox
 * @version $Id: login.php 1798 2009-01-18 17:46:03Z maztah $
 */
$smarty->assign('l_login', 'Einloggen');
$smarty->assign('l_register', t('Registrieren'));
$smarty->assign('l_pwrecover', t('Passwort vergessen'));

$smarty->assign('u_login', 'index.php' . ($_GET['mod'] == 'logout' ? '' : '?mod=auth&action=login'));
$smarty->assign('u_register', 'index.php?mod=signon');
$smarty->assign('u_pwrecover', 'index.php?mod=usrmgr&amp;action=pwrecover');

/*
// 62.67.200.4 = Proxy IP of https://sslsites.de/lansuite.orgapage.de
if ($cfg['sys_partyurl_ssl'] and ($_SERVER['HTTPS'] != 'on' and getenv(REMOTE_ADDR) != "62.67.200.4"))
  $smarty->assign('l_ssl_link', 'SSL Login');
  $smarty->assign('u_ssl_link', $cfg['sys_partyurl_ssl']); // hide ssl-link::woikerl*/

$box->AddTemplate($smarty->fetch('modules/boxes/templates/box_login_content.htm'));
?>