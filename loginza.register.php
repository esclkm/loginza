<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=users.register.main
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');


if (!empty($lz_uid) && $usr['id'] == 0)
{
	if (empty($a))
	{
		if (empty($rusername))
		{
			$rusername = $_SESSION['loginza_info']['nickname'];
			if (empty($rusername))
				$rusername = $_SESSION['loginza_info']['full_name'];
		}

		if (empty($ruseremail))
			$ruseremail = $_SESSION['loginza_info']['email'];

		if (empty($rusergender))
		{
			$rusergender = $_SESSION['loginza_info']['gender'];
			$form_usergender = cot_selectbox_gender($rusergender, 'rusergender');
		}

		if (empty($rmonth) && empty($rdate) && empty($ryear))
		{


			if (preg_match('#(\d+)-(\d+)-(\d+)#', $_SESSION['loginza_info']['dob'], $mt))
			{
				if ($_SESSION['loginza_info']['provider'] == 'http://www.facebook.com/')
				{
					$rmonth = (int)$mt[3];
					$rday = (int)$mt[2];
					$ryear = (int)$mt[1];
				}
				else
				{
					$rmonth = (int)$mt[2];
					$rday = (int)$mt[3];
					$ryear = (int)$mt[1];
				}
			}
			$form_birthdate = ($rmonth == 'x' || $rday == 'x' || $ryear == 'x' || empty($rmonth) || empty($rday) || empty($ryear)) ?
				cot_selectbox_date('', 'short', '', date('Y', $sys['now_offset'])) :
				cot_selectbox_date(cot_mktime(1, 0, 0, $rmonth, $rday, $ryear), 'short', '', date('Y', $sys['now_offset']));
		}
	}
}
?>
