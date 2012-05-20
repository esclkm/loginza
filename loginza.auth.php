<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=users.auth.tags,profile.tags
Tags=users.auth.tpl:{LZ_FORM}
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

if ($usr['id'] < 1 || $m == 'profile')
{	
	require_once(cot_langfile('loginza'));
	$lz = new XTemplate(cot_tplfile(array('loginza', $m), 'plug'));
	
	$addproviders = (!empty($cfg['plugin']['loginza']['providers'])) ? '&providers_set=' . str_replace(' ', '', $cfg['plugin']['loginza']['providers']) : '';
	$lzpath = ($m == 'profile') ? cot_url('users', 'm='.$m.'&send=input&x=' . $sys['xk'], '', true) : cot_url('index', 'send=input&x=' . $sys['xk'], '', true);
	$def_log_lang = ($usr['lang'] == 'ru') ? $usr['lang'] : 'en';
	$fullname = urlencode($cfg['mainurl'] . '/' . $lzpath) .'&lang=' . $def_log_lang;
	$lz->assign(array(
		"TOKEN_URL_FULL" => $fullname . $addproviders,
		"TOKEN_URL_SHORT" => $fullname,
		"TOKEN_URL_IFRAME" => '<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script><iframe src="https://loginza.ru/api/widget?overlay=loginza&token_url='.$fullname.'" style="width:630px; height:200px;" scrolling="no" frameborder="no"></iframe>'
		));

	$lz->parse("MAIN");

	$t->assign(array(
		'LZ_FORM' => $lz->text("MAIN"),
		'LZ_INFO' => (($env['ext'] == 'users' && $m == 'profile') && $_SESSION['loginza']['update']) ? $L['lz_update_ok'] : ''
	));

	$_SESSION['loginza']['update'] = 0;
}
?>
