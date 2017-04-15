<?php
/**
 * Generate Box for Userdata
 *
 * @package lansuite_core
 * @author knox
 * @version $Id: userdata.php 1993 2009-11-08 09:14:02Z jochen.jung $
 */

// If an admin is logged in as an user
// show admin name and switch back link

if ($olduserid > 0) {
    $adminuser = $db->qry_first('SELECT username FROM %prefix%user WHERE userid=%int%', $olduserid);

    if (strlen($adminuser['username']) > 14) {
        $adminuser['username'] = substr($adminuser['username'], 0, 11) . "...";
    }

    $userbox['view'] = array(
        'caption' => t('Admin'),
        'l_switch' => t('Zurück wechseln'),
        'adminid' => sprintf("%04d", $olduserid),
        'adminname' => $adminuser["username"],
        'u_switch' => 'index.php?mod=auth&amp;action=switch_back');
}

// Show username and ID
if (strlen($auth['username']) > 14) {
    $username = substr($auth['username'], 0, 11) . "...";
} else {
    $username = $auth['username'];
}

$userbox['user'] = array(
    'caption' => t('Benutzer'),
    'id' => sprintf("%04d", $auth['userid']),
    'name' => $dsp->FetchUserIcon($auth['userid'], $username));

$userbox['logout'] = array(
    'url' => 'index.php?mod=auth&action=logout',
    'caption' => t('Ausloggen'),
    'class' => 'icon_delete');


// Show last log in and login count
$user_lg = $db->qry_first("SELECT user.logins, max(auth.logintime) AS logintime
	FROM %prefix%user AS user
	LEFT JOIN %prefix%stats_auth AS auth ON auth.userid = user.userid
	WHERE user.userid = %int%
	GROUP BY auth.userid", $auth["userid"]);

if (isset($_POST['login']) and isset($_POST['password'])) {
    date_default_timezone_set($cfg['sys_timezone']);
    $userbox['login'] = array(
        'l_count' => t('Logins'),
        'count' => $user_lg["logins"],
        'l_last' => t('Zuletzt eingeloggt'),
        'last' => date('d.m H:i', $user_lg["logintime"]));
}

// Show Clan
if (($auth['clanid'] != NULL and $auth['clanid'] > 0) and $func->isModActive('clanmgr')) {
    $userbox['clan'] =
        ['caption' => t('Mein Clan'),
         'url' => 'index.php?mod=clanmgr&amp;step=2&clanid=' . $auth['clanid']];
}

// New-Mail Notice
if ($func->isModActive('mail')) {
    $mails_new = $db->qry("SELECT mailID
		FROM %prefix%mail_messages
		WHERE ToUserID = %int% AND mail_status = 'active' AND rx_date IS NULL
		", $auth['userid']);

    $notify = '';
    if ($cfg['mail_popup_on_new_mails'] and $db->num_rows($mails_new) > 0) {
        $found_not_popped_up_mail = false;
        while ($mail_new = $db->fetch_array($mails_new)) {
            if (!isset($_SESSION['mail_popup'][$mail_new['mailID']])) {
                $_SESSION['mail_popup'][$mail_new['mailID']] = 1;
                $found_not_popped_up_mail                    = true;
            }
        }
        $notify = ' notify';
    }

    $db->free_result($mails_new);

    $userbox['mail'] = array(
        'url' => 'index.php?mod=mail',
        'caption' => t('Mein Postfach'),
        'notify' => $notify);
}

// PDF-Ticket
if ($cfg["user_show_ticket"]) {
    $userbox['ticket'] = array(
        'url' => 'index . php ? mod = usrmgr & amp;action = myticket',
        'caption' => t('Meine Eintrittskarte'));
}

// Zeige Anmeldestatus
if ($party->count > 0 and $_SESSION['party_info']['partyend'] > time()) {
    $qry_enrolled = $db->qry_first("SELECT * FROM %prefix%party_user AS pu
		WHERE pu.user_id = %int% AND pu.party_id = %int%", $auth["userid"], $party->party_id);

    // signed in to next party?
    if ($qry_enrolled == null) {
        $enrolled   = ' < span class="negative" > ' . t('Nein') . '!</span > ';
        $enrolllink = '<a href = "index.php?mod=signon" > ' . t('Hier anmelden') . ' </a > ';
        /*$paidstat = '<font color = "red" > '. t('Nein') .'!</font > ';*/
    } else {
        $enrolled   = '<span class="positive" > ' . t('Ja') . '!</span > ';
        $enrolllink = '';

        // paid?
        if (($qry_enrolled["paid"] == 1) || ($qry_enrolled["paid"] == 2)) {
            $paid    = ' < span class="positive" > ' . t('Ja') . '!</span > ';
            $paylink = '';
        } else {
            $paid    = ' < span class="negative" > ' . t('Nein') . '!</span > ';
            $paylink =
                $cfg['signon_paylink'] ? ' < a href =
        "' . $cfg['signon_paylink'] . '" > ' . t('Bezahltinfos') . ' </a > ' : '';
        }
    }

    $query_partys = $db->qry_first("SELECT * FROM %prefix%partys AS p WHERE p.party_id = %int%", $_SESSION["party_id"]);

    $userbox['party'] = array('name' => $query_partys["name"],
                              'class' => 'menu',
                              'enroll_caption' => t('Angemeledet'),
                              'enrolled' => $enrolled,
                              'enroll' => $enrolllink,
                              /*'payment_caption' => t('Bezahltinfos'),
                              'paid' => $paid,
                              'pay' => $enrolllink*/);

}

$smarty->assign('userbox', $userbox);
$box->AddTemplate($smarty->fetch('modules/usrmgr/templates/box_usermenu.htm'));
?>