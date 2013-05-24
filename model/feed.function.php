<?php
function search($term = null, $mine = false, $page = 1, $size = 5)
{
	$term = empty($term) ? '' : ' AND `name` LIKE "%' . $term . '%" ';
	$sql = 'SELECT * FROM `feed` WHERE 1 = 1 ' . $term . ' LIMIT ' . ($page - 1) * $size . ', ' . $size;
	if(is_login())
	{
		$sql = 'SELECT `feed`.*, `user_feed`.`uid` FROM `feed` LEFT JOIN `user_feed` ON `user_feed`.`fid` = `feed`.`fid` AND `user_feed`.`uid` = ' . $_SESSION['uid'] . ' WHERE 1 = 1 ' . $term . ' LIMIT ' . ($page - 1) * $size . ', ' . $size;
		if($mine)
		{
			$sql = 'SELECT `feed`.*, `user_feed`.`uid` FROM `feed`, `user_feed` WHERE `user_feed`.`fid` = `feed`.`fid` AND `user_feed`.`uid` = ' . $_SESSION['uid'] . $term . ' LIMIT ' . ($page - 1) * $size . ', ' . $size;
		}
	}
	else if($mine) return false;
	$feeds = get_data($sql);
	if(db_errno() != 0) return false;
	return $feeds;
}

function max_page($term = null, $mine = false, $size = 5)
{
	$term = empty($term) ? '' : ' AND `name` LIKE "%' . $term . '%" ';
	$sql = 'SELECT count(*) FROM `feed` WHERE 1 = 1' . $term;
	if($mine && is_login())
	{
		$sql = 'SELECT count(*) FROM `feed`, `user_feed` WHERE `user_feed`.`fid` = `feed`.`fid` AND `user_feed`.`uid` = ' . $_SESSION['uid'] . $term;
	}
	$count = get_var($sql);
	$max_page = ceil($count / $size);
	return $max_page > 0 ? $max_page : 1;  
}

function get_type_label($type)
{
	$labels = c('feed_type');
	return isset($labels[$type]) ? $labels[$type] : current($labels);
}

function toggleSubscribe($fid)
{
	if(!is_login()) return false;
	
	$sql = prepare("SELECT * FROM `user_feed` WHERE `uid` = ?i AND `fid` = ?i", array($_SESSION['uid'], $fid));
	$toggleSql = prepare("INSERT INTO `user_feed` (`uid`, `fid`) VALUES (?i, ?i)", array($_SESSION['uid'], $fid));
	$label = __('UNSUBSCRIBE');
	if(get_line($sql))
	{
		$toggleSql = prepare("DELETE FROM `user_feed` WHERE `uid` = ?i AND `fid` = ?i", array($_SESSION['uid'], $fid));
		$label = __('SUBSCRIBE');
	}

	run_sql($toggleSql);
	if(db_errno() != 0) return false;
	return $label;
}

function deliver($fid)
{
	if(!is_login()) return false;
	return __('DELIVERED');
}

function fetch()
{
	date_default_timezone_set("Etc/GMT");
	$perWeek = '* ' . date('G') . ' * * ' . date('w');
	$perDay = '* ' . date('G') . ' * * *';
	$sql = prepare("SELECT * FROM `feed` WHERE `crontab` = ?s OR `crontab` = ?s", array($perDay, $perWeek));
	if($feeds = get_data($sql))
	{
		foreach($feeds as $feed)
		{
			exec('ebook-convert ' . $feeds['recipe'] . ' ' . $feeds['file'] . ' --output-profile kindle');
			$update_date_sql = prepare("UPDATE `feed` SET `update_time` = NOW() WHERE `fid` = ?i", array($feed['fid']));
			run_sql($update_date_sql);
		}
	}
}

function dispatch()
{
	$sql = 'SELECT `user`.`email`, `feed`.`file`, `feed`.`type`, `user`.`timezone` FROM `user`, `feed`, `user_feed` WHERE `user`.`uid` = `user_feed`.`uid` AND `feed`.`fid` = `user_feed`.`fid`';
	if($data = get_data($sql))
	{
		foreach($data as $val)
		{
			$timezone = intval($val['timezone']);
			if($timezone > 0)
			{
				$timezone = 'Etc/GMT+' . $timezone;
			}
			else if($timezone == 0)
			{
				$timezone = 'Etc/GMT';
			} 
			else
			{
				$timezone = 'Etc/GMT' . $timezone;
			}
			
			date_default_timezone_set($timezone);
			$hour = date('G');
			$day  = date('j');

			if($hour == 6 && (($val['type'] == 0) || ($day = 1 && $val['type'] == 1)))
			{
				exec('echo "" | mutt -a "' . $val['file'] . '" -s "" -- ' . $val['email']);
			}
		}
	}
}