<?php
$GLOBALS['config']['site_name'] = '投食';
$GLOBALS['config']['site_subtitle'] = '每天投递新鲜的精神早餐到你的Kindle';
$GLOBALS['config']['site_domain'] = 'http://localhost/toushi';
$GLOBALS['config']['default_controller'] = 'feed';
$GLOBALS['config']['signup_verify'] = FALSE;
$GLOBALS['config']['max_feeds'] = 3;

$GLOBALS['config']['available_account'][] = '@kindle.com';
$GLOBALS['config']['available_account'][] = '@free.kindle.com';
$GLOBALS['config']['available_account'][] = '@iduokan.com'; 

$GLOBALS['config']['feed_type'][0] = '<span class="label">' . __('DELIVER_PER_DAY') . '</span>';
$GLOBALS['config']['feed_type'][1] = '<span class="label label-info">' . __('DELIVER_PER_WEEK') . '</span>';
$GLOBALS['config']['feed_type'][2] = '<span class="label label-inverse">' . __('DELIVER_PER_MONTH') . '</span>';