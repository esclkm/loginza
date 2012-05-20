<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=message.first
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');
require_once(cot_langfile('loginza'));
if ($msg == 777 or $msg == 778)
{
	$title = $L['msg' . $msg . '_title'];
	$body = $L['msg' . $msg . '_body'];

	$rd = 7;
	$ru = cot_url('users', 'm=register&send=input', '', TRUE);
}
?>
