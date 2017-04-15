<?php
/**
 * Generate Signonstatus. Show Counter and Bar
 *
 * @package lansuite_core
 * @author knox
 * @version $Id: signonstatus.php 1956 2009-08-14 14:07:05Z johannes.pieringer@gmail.com $
 */
$partybox = array();

$slots['max'] = $_SESSION['party_info']['max_guest'];

// include orgas?
$qry_filter_orga = ($cfg["guestlist_showorga"] == 0) ? 'type = 1' : 'type >= 1';

// count registered users
$get_cur = $db->qry_first('SELECT count(userid) as cnt FROM %prefix%user AS user WHERE %plain%', $qry_filter_orga);
$reg     = $get_cur['cnt'];

// count paid
$get_cur       =
    $db->qry_first('SELECT count(userid) as cnt FROM %prefix%user AS user LEFT JOIN %prefix%party_user AS party ON user.userid = party.user_id WHERE (%plain%) AND (party.paid > 0) AND party_id=%int%', $qry_filter_orga, $party->party_id);
$slots['paid'] = min($get_cur["cnt"], $slots['max']);

// count enrolled users
$get_cur           =
    $db->qry_first('SELECT count(userid) as cnt FROM %prefix%user AS user LEFT JOIN %prefix%party_user AS party ON user.userid = party.user_id WHERE party_id=%int% AND (%plain%)', $party->party_id, $qry_filter_orga);
$slots['enrolled'] = $get_cur['cnt'];

// calculate pct of bar
if ($slots['max'] >= $slots['enrolled']) {
// more slots than enrolled guests
    $pct['paid']       = (int)($slots['paid'] * 100 / $slots['max']);
    $pct['enrolled']   = (int)(($slots['enrolled'] - $slots['paid']) * 100 / $slots['max']);
    $pct['vacant']     = 100 - $pct['enrolled'] - $pct['paid'];
    $pct['overbooked'] = 0;
} else {
    $slots['overbooked'] = $slots['enrolled'] - $slots['max'];

    $pct['overbooked'] = (int)($slots['overbooked'] * 100 / $slots['enrolled']);
    $pct['vacant']     = 0;

    if ($slots['paid'] >= $slots['enrolled']) {
        $pct['paid']     = (int)($slots['enrolled'] * 100 / $slots['enrolled']);
        $pct['enrolled'] = 0;
    } else {
        $pct['paid']     = (int)($slots['paid'] * 100 / $slots['enrolled']);
        $pct['enrolled'] = 100 - $pct['overbooked'] - $pct['paid'];
    }
}
// Bar erzeugen
$barplot = '<ul class="barplot">' . PHP_EOL;

/*if ($pct['paid'] > 0) {
    $barplot .= '<li class="paid" style="width: ' . $pct['paid'] . '%"></li>';
}
if ($pct['enrolled'] > 0) {
    $barplot .= '<li class="enrolled" style="width: ' . $pct['enrolled'] . '%"></li>';
}*/
if ($pct['paid'] + $pct['enrolled'] > 0) {
    $barplot .= '<li class="paid" style="width: ' . ($pct['paid'] + $pct['enrolled']) . '%"></li>';
} // hide payment status ::woikerl
if ($pct['vacant'] > 0) {
    $barplot .= '<li class="vacant" style="width: ' . $pct['vacant'] . '%"></li>';
}
if ($pct['overbooked'] > 0) {
    $barplot .= '<li class="overbooked" style="width: ' . $pct['overbooked'] . '%"></li>';
}
$barplot .= '</ul>';
$partybox['barplot'] = $barplot;

//$partybox['paid' ]= $slots'paid'];
//$partybox['enrolled'] = $slots['enrolled'];
$partybox['l_enrolled'] = t('Angemeldet');
$partybox['enrolled']   = $slots['enrolled'] + $slots['paid'];
$partybox['l_vacant']   = t('Frei');
$partybox['vacant']     = $slots['max'] - $partybox['enrolled'];

if ($cfg['sys_internet']) {
    $partybox['intranet'] = false;

    $options = '';
    $res     = $db->qry('SELECT party_id, name FROM %prefix%partys');
    if ($db->num_rows($res) > 1 && ($cfg['display_change_party'] || $auth['type'] >= 2)) {
        $partybox['change_form'] = true;

        while ($row = $db->fetch_array($res)) {
            $selected = ($row['party_id'] == $party->party_id) ? ' selected' : '';
            if (strlen($row['name']) > 20) {
                $row['name'] = substr($row['name'], 0, 18) . '...';
            }
            $partybox['parties'][] =
                array('value' => $row['party_id'], 'selected' => $selected, 'name' => $row['name']);
        }
        $partybox['sel_name'] = 'set_party_id';

    } else {
        $partybox['name'] = $_SESSION['party_info']['name'];
    }
    $db->free_result($res);

    date_default_timezone_set($cfg['sys_timezone']);
    $partybox['date'] = date("d.m.y", $_SESSION['party_info']['partybegin']);
    $partybox['date'] .= '&nbsp;-&nbsp;';
    $partybox['date'] .= date("d.m.y", $_SESSION['party_info']['partyend']);

    if ($_SESSION['party_info']['partyend'] < time()) {
        $countdown = t('Diese Party ist bereits vorüber');
    } else {
        $countdown = ceil(($_SESSION['party_info']['partybegin'] - time()) / 60);
        if ($countdown <= 1) {
            $countdown = t('Die Party läuft gerade!');
        } elseif ($countdown <= 120) {
            $countdown = t('Noch %1 Minuten.', array($countdown));
        } elseif ($countdown > 120 AND $countdown <= 2880) {
            $countdown = t('Noch %1 Stunden.', array(floor($countdown / 60)));
        } else {
            $countdown = t('Noch %1 Tage.', array(floor($countdown / 1440)));
        }

        $partybox['l_checked'] = t('Letzter Kontocheck');
        // $partybox['checked'] = $db->qry_first("SELECT UNIX_TIMESTAMP(checked) AS n FROM %prefix%partys WHERE party_id = %int%", $party->party_id);
        $partybox['checked'] = ''; // hide payment data ::woikerl
    }
    $partybox['l_counter'] = t('Counter');
    $partybox['countdown'] = $countdown;


} else {
    $partybox['intranet']    = true;
    $partybox['l_checkedin'] = t('Eingecheckt');
    $partybox['checkedin']   = $db->qry_first('SELECT COUNT(p.user_id) as n FROM %prefix%user AS u LEFT JOIN %prefix%party_user AS p ON u.userid = p.user_id
    WHERE (%plain%) AND (p.checkin > 0) AND p.party_id = %int%', $qry_filter_orga, $party->party_id);

    $partybox['l_checkedout'] = t('Ausgecheckt');
    $partybox['checkedout']   = $db->qry_first('SELECT COUNT(p.user_id) as n FROM %prefix%user AS u LEFT JOIN %prefix%party_user AS p ON u.userid = p.user_id
    WHERE (%plain%) AND (p.checkout > 0) AND p.party_id = %int%', $qry_filter_orga, $party->party_id);
}

$smarty->assign('partybox', $partybox);
$box->AddTemplate($smarty->fetch('modules/usrmgr/templates/box_partyinfo.htm'));
?>