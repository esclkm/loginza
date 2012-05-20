<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=users.register.tags
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');


if (!empty($lz_uid) && $usr['id'] == 0)
{
	if (empty($a))
	{
		if (!empty($_SESSION['loginza_info']['first_name']))
		{

			$t->assign("USERS_REGISTER_FIRST_NAME", "<input value=\"" . $_SESSION['loginza_info']['first_name'] . "\" name=\"ruserfirst_name\" class=\"text\" type=\"text\" maxlength=\"25\" />");
		}
	}
}
?>
