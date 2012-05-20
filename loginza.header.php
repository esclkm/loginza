<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=header.body
Tags=header.tpl:{LZ_FORM}
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

if ($usr['id'] < 1)
{	
	$lzget = $_GET;
	
	$lzget = array();
	foreach($_GET as $gk => $gv)
	{
		if (is_array($gv))
		{
			foreach ($gv as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $sk => $sv)
					{
						$lzget[$gk.'[' . $k . '][' . $sk . ']'] = $sv;
					}
				}
				else
				{
					$lzget[$gk.'[' . $k . ']'] = $v;
				}
			}
		}
		else
		{
			$lzget[$gk] = $gv;
		}
	}
	unset($lzget['x']);
	$lzget['x'] = $sys['xk'];
	$lzget['send'] = 'input';
	
	require_once(cot_langfile('loginza'));
	$lz = new XTemplate(cot_tplfile(array('loginza', 'header'), 'plug'));
	
	$def_log_lang = ($usr['lang'] == 'ru') ? $usr['lang'] : 'en';
	$addproviders = (!empty($cfg['plugin']['loginza']['providers'])) ? '&providers_set=' . str_replace(' ', '', $cfg['plugin']['loginza']['providers']) : '';
	$fullname = urlencode($cfg['mainurl'] . '/' . cot_url($env['ext'], $lzget, '', true)) .'&amp;lang=' . $def_log_lang;
	$lz->assign(array(
		"TOKEN_URL_FULL" => $fullname . $addproviders,
		"TOKEN_URL_SHORT" => $fullname,
		"TOKEN_URL_IFRAME" => '<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script><iframe src="https://loginza.ru/api/widget?overlay=loginza&token_url='.$fullname.'" style="width:630px; height:200px;" scrolling="no" frameborder="no"></iframe>'
		));

	$lz->parse("MAIN");
	$lz_exit = $lz->text("MAIN");
	$t->assign(array(
		'LZ_FORM' => $lz_exit,
	));
}

?>
