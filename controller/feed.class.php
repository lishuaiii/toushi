<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'controller'.DS.'app.class.php' );

class feedController extends appController
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		$data['title'] = $data['top_title'] = __('INDEX_PAGE');
		$data['term'] = z(t(v('term')));
		render($data);
	}

	function item()
	{
		$filter = $data['filter'] = z(t(v('filter')));
		$term = $data['term'] = z(t(v('term')));
		$page = $data['page'] = v('page') && intval(v('page')) > 1 ? intval(v('page')) : 1;
		$term = $data['term'] = v('term') ? v('term') : g('term');
		$max_page = $data['max_page'] = max_page($term, !empty($filter));
		$data['feed'] = search($term, !empty($filter), $page);
		if($data['feed'] === false)
		{
			render(array('code' => TS_NOT_LOGIN, 'error_message' => __('TS_NOT_LOGIN')), 'rest');
		}
		else
		{
			$data['older'] = $page - 1 > 1 ? $page - 1 : 1;
			$data['newer'] = $page + 1 < $max_page ? $page + 1 : $max_page;
			render($data);
		}
	}

	function toggle()
	{
		$fid = z(t(v('fid')));
		if($label = toggleSubscribe($fid))
		{
			render(array('code' => 0, 'data' => array('label' => $label)), 'rest');
		}
		else
		{
			render(array('code' => TS_NOT_LOGIN, 'error_message' => __('TS_NOT_LOGIN')), 'rest');
		}
	}

	function deliver()
	{
		$fid = z(t(v('fid')));
		if($label = deliver($fid))
		{
			render(array('code' => 0, 'data' => array('label' => $label)), 'rest');
		}
		else
		{
			render(array('code' => TS_NOT_LOGIN, 'error_message' => __('TS_NOT_LOGIN')), 'rest');
		}
	}

	function fetch()
	{
		fetch();
	}

	function dispatch()
	{
		dispatch();
	}

	function add()
	{
		$data['title'] = $data['top_title'] = __('ADD_FEED');
		if(v('add'))
		{
			$name = z(t(v('name')));
			if(strlen($name) < 1) $data['error']['name'][] = __('NAME_EMPTY');
			
			$file = z(t(v('file')));
			if(strlen($file) < 1) $data['error']['file'][] = __('FILE_EMPTY');

			$recipe = z(t(v('recipe')));
			if(strlen($recipe) < 1) $data['error']['recipe'][] = __('RECIPE_EMPTY');

			$favicon = z(t(v('favicon')));
			if(strlen($favicon) < 1) $data['error']['favicon'][] = __('FAVICON_EMPTY');

			$site_url = z(t(v('site_url')));
			if(strlen($site_url) < 1) $data['error']['site_url'][] = __('SITE_URL_EMPTY');

			$crontab = z(t(v('crontab')));
			if(strlen($crontab) < 1) $data['error']['crontab'][] = __('CRONTAB_EMPTY');
			
			$des = z(t(v('des')));
			if(strlen($des) < 1) $data['error']['des'][] = __('DES_EMPTY');
			
			$type = z(t(v('type')));
			if(strlen($type) < 1) $data['error']['type'][] = __('TYPE_EMPTY');
			
			if(!isset($data['error']))
			{
				if(add($name, $file, $recipe, $crontab, $favicon, $site_url, $type, $des))
				{
					return info_page(__('ADD_FEED_TEXT', $email), __('ADD_FEED'));
				}
			}
		}
		render($data);
	}
}