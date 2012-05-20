<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=users.register.add.done
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

// Automatically validates and logs in a user who has registered with Logiza
if (!empty($lz_uid) && !$cfg['regrequireadmin'])
{
	$db->query("UPDATE $db_users SET user_lzid = '" . $lz_uid . "' WHERE user_id = $userid");
	if (!$cfg['regnoactivation'])
	{
		$sql = $db->query("UPDATE $db_users SET user_maingrp=4 WHERE user_id='$userid'");
		$sql = $db->query("UPDATE $db_groups_users SET gru_groupid=4 WHERE gru_groupid=2 AND gru_userid='$userid'");
		cot_auth_clear($userid);
	}
	// Automatically log user in
	$row['user_id'] = $userid;
	$row['user_name'] = $rusername;
	$row['user_password'] = $mdpass;
	$row['user_maingrp'] = 4;
	lz_autologin($row);
	exit;
}
?>
