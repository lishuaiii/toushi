<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );

class userController extends appController
{
	function __construct()
	{
		parent::__construct();
	}

	function login()
	{
		if(is_login()) forward(c('site_domain'));

		$data['title'] = $data['top_title'] = __('LOGIN');
		
		if(v('login'))
		{
			$email = z(t(v('email')));
			if(strlen($email) < 1) $data['error']['email'][] = __('EMAIL_EMPTY');
			if(!is_email($email)) $data['error']['email'][] = __('EMAIL_INVALID');
			
			$password = z(t(v('password')));
			if(strlen($password) < 1) $data['error']['password'][] = __('PASSWORD_EMPTY');
			
			if(!isset($data['error']))
			{
				$user = login($email, $password);
				if(!$user)
				{
					$data['error']['email'][] = __('NOT_VERIFIED');
				}
				else
				{
					@session_start();
					$_SESSION['uid'] = $user['uid'];
					$_SESSION['nickname'] = $user['nickname'];
					$_SESSION['email'] = $user['email'];
					$_SESSION['role'] = $user['role'];
					forward("?c=feed");
				}
			}			
		}

		render($data, NULL, 'noside');
	}

	function logout()
	{
		@session_start();
		foreach($_SESSION as $key => $value)
		{
			unset($_SESSION[$key] );
		}
		@session_destroy();
		forward('?c=feed');
	}

	function signup()
	{
		if(is_login()) forward(c('site_domain'));
		
		$data['title'] = $data['top_title'] = __('SIGNUP');
		
		if(v('signup'))
		{
			$email = z(t(v('email')));
			if(strlen($email) < 1) $data['error']['email'][] = __('EMAIL_EMPTY');
			if(!is_email($email)) $data['error']['email'][] = __('EMAIL_INVALID');
			if(strlen($email) > 45) $data['error']['email'][] = __('EMAIL_TOO_LONG');
			if(is_existed('email', $email)) $data['error']['email'][] = __('EMAIL_IS_EXISTED');
			
			$nickname = z(t(v('nickname')));
			if(strlen($nickname) < 1) $data['error']['nickname'][] = __('NICKNAME_EMPTY');
			if(strlen($nickname) < 4) $data['error']['nickname'][] = __('NICKNAME_TOO_SHORT');
			if(strlen($nickname) > 45) $data['error']['nickname'][] = __('NICKNAME_TOO_LONG');
			if(is_existed('nickname', $nickname)) $data['error']['nickname'][] = __('NICKNAME_IS_EXISTED');

			$password = z(t(v('password')));
			if(strlen($password) < 1) $data['error']['password'][] = __('PASSWORD_EMPTY');
			if(strlen($password) < 4) $data['error']['password'][] = __('PASSWORD_TOO_SHORT');
			if(strlen($password) > 45) $data['error']['password'][] = __('PASSWORD_TOO_LONG');
			
			$subscribed_account = z(t(v('subscribed_account')));
			$account = z(t(v('account')));
			if(strlen($subscribed_account) < 1) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_EMPTY');
			if(!is_email($subscribed_account . $account)) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_INVALID');
			if(!in_array($account, c('available_account')))	$data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_INVALID');
			if(strlen($subscribed_account) > 45) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_TOO_LONG');
			if(is_existed('subscribed_account', $subscribed_account . $account)) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_IS_EXISTED');

			if(!isset($data['error']))
			{
				$uniqid = signup($email, $nickname, $password, $subscribed_account . $account);
				if(!c('signup_verify'))
				{
					return info_page(__('SIGNED_UP_TEXT', $email), __('SIGNED_UP'));
				}
			}
		}

		render($data, NULL, 'noside');
	}

	function profile()
	{
		if(!is_login()) forward('?c=user&a=login');

		$data['title'] = $data['top_title'] = __('PROFILE');
		$data += get_user_info_by_email($_SESSION['email']);
		$data['subscribed_account'] = explode('@', $data['subscribed_account']);

		if(v('profile'))
		{
			$data['nickname'] = $nickname = z(t(v('nickname')));
			if(strlen($nickname) < 1) $data['error']['nickname'][] = __('NICKNAME_EMPTY');
			if(strlen($nickname) < 4) $data['error']['nickname'][] = __('NICKNAME_TOO_SHORT');
			if(strlen($nickname) > 45) $data['error']['nickname'][] = __('NICKNAME_TOO_LONG');
			if(is_existed('nickname', $nickname, $data['uid'])) $data['error']['nickname'][] = __('NICKNAME_IS_EXISTED');
			
			$data['subscribed_account'][0] = $subscribed_account = z(t(v('subscribed_account')));
			$data['subscribed_account'][1] = $account = z(t(v('account')));
			if(strlen($subscribed_account) < 1) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_EMPTY');
			if(!is_email($subscribed_account . $account)) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_INVALID');
			if(!in_array($account, c('available_account')))	$data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_INVALID');
			if(strlen($subscribed_account) > 45) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_TOO_LONG');
			if(is_existed('subscribed_account', $subscribed_account . $account, $data['uid'])) $data['error']['subscribed_account'][] = __('SUBSCRIBED_ACCOUNT_IS_EXISTED');

			$data['timezone'] = $timezone = intval(z(t(v('timezone'))));

			if(!isset($data['error']))
			{
				if(update_profile_by_uid($nickname, $subscribed_account, $timezone, $data['uid']))
				{
					$_SESSION['nickname'] = $nickname;
					return info_page(__('UPDATE_PROFILE_TEXT', $nickname), __('PROFILE'));
				}
			}
		}

		render($data, NULL, 'noside');
	}

	function changepasswd()
	{
		if(!is_login()) forward('?c=user&a=login');
		
		$data['title'] = $data['top_title'] = __('CHANGE_PASSWORD');
		
		if(v('change-password'))
		{
			$oldpasswd = z(t(v('oldpasswd')));
			if(strlen($oldpasswd) < 1) $data['error']['oldpasswd'][] = __('PASSWORD_EMPTY');
			if(!verify($_SESSION['email'], $oldpasswd)) $data['error']['oldpasswd'][] = __('OLD_PASSWORD_NOT_VERIFIED');
			
			$newpasswd = z(t(v('newpasswd')));
			if(strlen($newpasswd) < 1) $data['error']['newpasswd'][] = __('PASSWORD_EMPTY');
			if(strlen($newpasswd) < 4) $data['error']['newpasswd'][] = __('PASSWORD_TOO_SHORT');
			if(strlen($newpasswd) > 45) $data['error']['newpasswd'][] = __('PASSWORD_TOO_LONG');
			
			if(!isset($data['error']))
			{
				if(change_password($newpasswd, $_SESSION['uid']))
				{
					$email = $_SESSION['email'];
					@session_start();
					foreach($_SESSION as $key => $value)
					{
						unset($_SESSION[$key] );
					}
					@session_destroy();
					return info_page(__('CHANGE_PASSWORD_TEXT', $email), __('CHANGE_PASSWORD'));
				}
			}
		}

		render($data, NULL, 'noside');
	}
}