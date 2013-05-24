<?php
function not_empty($str)
{
	return strlen($str) > 0;
}

function is_email($email)
{
	return filter_var($email , FILTER_VALIDATE_EMAIL); 
}

function forward( $url )
{
	header("Location: " . $url);
}

function jsforword($url)
{
	return '<script>location="' . $url . '"</script>';
}

function image($filename)
{
	return 'static/image/' . $filename;
}

function gravatar($email, $size = '27')
{
    $default = 'http://1.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=' . $size;
    return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
}

function ctime($timeline)
{
    $time = strtotime($timeline);
    if(time() > ($time+60*60*24*300)) return date(__('DATE_FULL_FORMAT'), $time);
    elseif(time() > ($time+60*60*8)) return date(__('DATE_SHORT_FORMAT'), $time);
    else return date("H:i:s",$time);
}

function rtime($time = false, $limit = 86400, $format = null) 
{
	if($format === null) $format = __('DATE_SHORT_FORMAT');

    $time = strtotime($time);

	$now = time();
	$relative = '';

	if ($time === $now) $relative = __('DATE_RELATED_NOW');
	elseif ($time > $now) $relative = __('DATE_RELATED_AFTER') ;
	else 
	{
		$diff = $now - $time;

		if ($diff >= $limit) $relative = date($format, $time);

		elseif ($diff < 60) 
		{
			$relative = __('DATE_RELATED_LESS_THAN_A_MINUTE');
		}
		elseif (($minutes = ceil($diff/60)) < 60)
		{
			if((int)$minutes === 1) $relative = __('DATE_RELATED_ONE_MINUTE');
            else  $relative = __('DATE_RELATED_SOME_MINUTES', $minutes);      
		}
		else
		{
			$hours = ceil($diff/3600);

            if((int)$hours === 1) $relative = __('DATE_RELATED_ONE_HOUR');
            else  $relative = __('DATE_RELATED_SOME_HOURS', $hours);  

		}
	}

	return $relative;
}

function tspasswd($password, $salt = '')
{
    return substr(md5(md5($password) . 'TOU$SHI' . $salt), 0, 30);
}

function login($email, $password)
{
    return verify($email, $password);
}

function is_login()
{
    @session_start();
    return isset($_SESSION['role']);
}