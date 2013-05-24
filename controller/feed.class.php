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
}