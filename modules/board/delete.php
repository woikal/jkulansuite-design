<?php
$LSCurFile = __FILE__;

switch ($_GET['step']) {
	default:
    include_once('modules/board/show.php');
	break;

	case 2:
		$text = $lang['board']['del_forum_quest'];

		$link_target_yes = "index.php?mod=board&action=delete&step=3&fid={$_GET['fid']}";
		$link_target_no = "index.php?mod=board&action=delete";
		$func->question($text, $link_target_yes, $link_target_no);
	break;

	case 3:
		$res = $db->query("SELECT tid FROM {$config["tables"]["board_threads"]} WHERE fid='{$_GET['fid']}'");
		while ($row = $db->fetch_array($res)) {
			$db->query("DELETE FROM {$config["tables"]["board_posts"]} WHERE tid='{$row['tid']}'");
		}
		$db->free_result($res);
		$db->query("DELETE FROM {$config["tables"]["board_threads"]} WHERE fid='{$_GET['fid']}'");
		$db->query("DELETE FROM {$config["tables"]["board_forums"]} WHERE fid='{$_GET['fid']}'");

		$func->confirmation($lang['board']['forum_del'], "index.php?mod=board&action=delete"); 
	break;
}

?>
