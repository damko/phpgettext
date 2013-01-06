<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config_file = APPPATH.'config/phpgettext.php';

if(is_file($config_file)) {

	include $config_file;
	
} else {

	$config['gettextProjectDir'] = SPARKPATH.'phpgettext/1.0.11';
	$config['gettextLocaleDir'] = APPPATH.'language';
	$config['gettextDefaultLocale'] = 'en_US';
	$config['gettextInc'] = SPARKPATH.'phpgettext/1.0.11/gettext.inc';
	$config['gettextSupportedLocales'] = array(
			'en_US',
			//'it_IT',
			//'wh_AT',
			//'ev_ER',
	);
	$config['gettextEncoding'] = 'UTF-8';
}
