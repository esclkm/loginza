<?php

/* ====================
[BEGIN_COT_EXT]
Hooks=input
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

if (cot_import('send', 'G', 'TXT') == 'input')
{
	$_SESSION['loginza']['update'] = 0;
	require_once $cfg['plugins_dir'] . '/loginza/inc/LoginzaAPI.class.php';
	require_once $cfg['plugins_dir'] . '/loginza/inc/functions.php';
	$LoginzaAPI = new LoginzaAPI();
	// проверка переданного токена
	if (!empty($_POST['token']))
	{
		// получаем профиль авторизованного пользователя
		$UserProfile = $LoginzaAPI->getAuthInfo($_POST['token']);

		// проверка на ошибки
		if (!empty($UserProfile->error_type))
		{
			// есть ошибки, выводим их
			// в рабочем примере данные ошибки не следует выводить пользователю, так как они несут информационный характер только для разработчика
			//echo $UserProfile->error_type.": ".$UserProfile->error_message;
		}
		elseif (empty($UserProfile))
		{
			// прочие ошибки
			echo 'Temporary error.';
		}
		else
		{
			// ошибок нет запоминаем пользователя как авторизованного
			$_SESSION['loginza']['is_auth'] = 1;
			// запоминаем профиль пользователя в сессию или создаем локальную учетную запись пользователя в БД
			$_SESSION['loginza']['profile'] = $UserProfile;
		}
	}
	elseif (isset($_GET['quit']))
	{
		// выход пользователя
		unset($_SESSION['loginza']);
		$_SESSION['loginza']['is_auth'] = 0;
	}
	//=================================================================
	if (!empty($_SESSION['loginza']['is_auth']))
	{
		$LoginzaAPI->UserInfo($_SESSION['loginza']['profile']);
		//$lz_uid=(!$_SESSION['loginza_info']['uid'])? trim(preg_replace('/[^\w]+/i', '-', $_SESSION['loginza_info']['identity']), '-'):$_SESSION['loginza_info']['uid'];
		$lz_uid = trim(preg_replace('/[^\w]+/i', '-', $_SESSION['loginza_info']['identity']), '-');


		if ($usr['id'] > 0)
		{
			$_SESSION['loginza']['update'] = 0;
			// Logged in both on LZ and Cotonti

			if (empty($usr['user_lzid']))
			{
				$db->query("UPDATE $db_users SET user_lzid = '" . $lz_uid . "' WHERE user_id = " . $usr['id']);

				$lz_res = $db->query("SELECT * FROM $db_users WHERE user_lzid = '" . $lz_uid . "'");
				if ($row = $lz_res->fetch())
				{
					lz_autologin($row);
				}
				$_SESSION['loginza']['update'] = 1;
				//  lz_autologin($usr['profile']);
			}
			// continue normal execution
		}
		elseif (!defined('SED_USERS') && !defined('SED_MESSAGE')) // avoid deadlocks and loops
		{
			// Check if this FB user has a native Cotonti account
			$lz_res = $db->query("SELECT * FROM $db_users WHERE user_lzid = '" . $lz_uid . "'");
			if ($row = $lz_res->fetch())
			{
				// Load user account and log him in
				lz_autologin($row);
			}
			else
			{
				if ($cfg['plugin']['loginza']['autoreg'])
				{
					$row = $_SESSION['loginza_info'];

					$login = ($row['nickname']) ? $row['nickname'] : $row['full_name'];
					if (empty($login) and (!empty($row['first_name']) or !empty($row['last_name']) ))
					{
						$login = $row['first_name'] . " " . $row['last_name'];
					}
					if (empty($login))
					{
						$login = Nickname($row['identity']);
					}
					if (empty($login))
					{
						$login = "Nologin_" . RandomPassword();
					}

					$res1 = $db->query("SELECT COUNT(*) FROM $db_users WHERE user_name='" . $db->prep($login) . "'")->fetchColumn();

					if ($row['email'])
					{
						$res2 = $db->query("SELECT COUNT(*) FROM $db_users WHERE user_email='" . $db->prep($row['email']) . "'")->fetchColumn();
					}

					if ($res1 > 0)
					{
						cot_redirect(cot_url('message', 'msg=777', '', true));
						exit;
					}
					if ($res2 > 0)
					{
						cot_redirect(cot_url('message', 'msg=778', '', true));
						exit;
					}

					if ($row['dob'] and $row['provider'] == 'http://www.facebook.com/')
					{
						preg_match('#(\d+)-(\d+)-(\d+)#', $row['dob'], $mt);
						$row['dob'] = (int)$mt[1] . "-" . (int)$mt[3] . "-" . (int)$mt[2];
					}
					//-----------------------
					$row['dob'] = (empty($row['dob'])) ? '0000-00-00' : $row['dob'];


					$defgroup = 4;

					$row['gender'] = (!$row['gender']) ? 'U' : $row['gender'];
					$prepass = RandomPassword();
					$mdpass = md5($prepass);
					$validationkey = md5(microtime());

					$ssql = "INSERT into $db_users
						(user_name,
						user_password,
						user_maingrp,
						user_text,
						user_email,
						user_hideemail,
						user_pmnotify,
						user_theme,
						user_scheme,
						user_lang,
						user_regdate,
						user_logcount,
						user_lostpass,
						user_gender,
						user_birthdate,
						user_lastip,
						user_lzid
						)
						VALUES
						('" . $db->prep($login) . "',
						'$mdpass',
						" . (int)$defgroup . ",
						'',
						'" . $db->prep($row['email']) . "',
						1,
						0,
						'" . $cfg['defaulttheme'] . "',
						'" . $cfg['defaultscheme'] . "',
						'" . $cfg['defaultlang'] . "',
						" . (int)$sys['now_offset'] . ",
						0,
						'$validationkey',
						'" . $db->prep($row['gender']) . "',
						'" . $row['dob'] . "',
						'" . $usr['ip'] . "',
						'" . $lz_uid . "'
						)";

					$sql = $db->query($ssql);

					$userid = $db->lastInsertId();
					$sql = $db->query("INSERT INTO $db_groups_users (gru_userid, gru_groupid) VALUES (" . (int)$userid . ", " . (int)$defgroup . ")");

					$row['user_id'] = $userid;
					$row['user_name'] = $login;
					$row['user_maingrp'] = (int)$defgroup;
					$row['user_banexpire'] = 0;
					$row['user_skin'] = $cfg['defaultskin'];
					$row['user_theme'] = $cfg['defaulttheme'];
					$rremember = $cfg['plugin']['loginza']['remember'];

					//-----MAIL------
					if ($cfg['plugin']['loginza']['s_mail'] and !empty($row['email']))
					{
						$rsubject = "{$cfg['maintitle']} - {$L['lz_regok_title']}";
						$rbody = sprintf($L['lz_regok'], $cfg['maintitle'], htmlspecialchars($login), $prepass, $row['provider']);

						cot_mail($remail, $rsubject, $rbody);
					}
					//--------------

					lz_autologin($row);
				}
				else
				{
					cot_redirect(cot_url('users', 'm=register&send=input', '', TRUE));
				}
				$lz_res = null;
			}
		}
	}
}
?>
