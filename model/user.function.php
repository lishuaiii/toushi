<?php
define('USER_INFO', '`uid`, `email`, `nickname`, `subscribed_account`, `timezone`');

function signup($email, $nickname, $password, $subscribed_account)
{
	$uniqid = md5(uniqid());
	$sql = prepare("INSERT INTO `user` (`email`, `nickname`, `password`, `subscribed_account`, `verify_code`, `status`) VALUES (?s, ?s, ?s, ?s, ?s, ?i)", array($email, $nickname, tspasswd($password), $subscribed_account, $uniqid, !c('signup_verify')));
	run_sql($sql);
	if(db_errno() != 0) return false;
	return $uniqid;
}

function is_existed($k, $v, $uid = null)
{
	$sql = prepare("SELECT `$k` FROM `user` WHERE `$k` = ?s", array($v));
	if($uid)
	{
		$sql .= prepare(" AND `uid` != ?i", array($uid));
	}
	return get_line($sql);
}

function verify($email, $password)
{
	$sql = prepare("SELECT * FROM `user` WHERE `email` = ?s", array($email));
	$user = get_line($sql);
	if($user['password'] !== tspasswd($password)) return false;
	return $user;
}

function get_user_info_by_email($email)
{
	$sql = prepare("SELECT " . USER_INFO . "  FROM `user` WHERE `email` = ?s", array($email));
	return get_line($sql);
}

function update_profile_by_uid($nickname, $subscribed_account, $timezone, $uid)
{
	$sql = prepare("UPDATE `user` SET `nickname` = ?s, `subscribed_account` = ?s, `timezone` = ?i WHERE `uid` = ?i", array($nickname, $subscribed_account, $timezone, $uid));
	run_sql($sql);
	if(db_errno() != 0) return false;
	return true;
}

function change_password($password, $uid)
{
	$sql = prepare("UPDATE `user` SET `password` = ?s WHERE `uid` = ?i", array(tspasswd($password), $uid));
	run_sql($sql);
	if(db_errno() != 0) return false;
	return true;
}