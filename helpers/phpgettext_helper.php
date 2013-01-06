<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stores phpgettext variables in session
 * 
 * @access		public
 * @param		none. It uses GET
 * @var			
 * @return		nothing
 * @example
 * @see
 * 
 * @author 		Damiano Venturin
 * @since		Sep 17, 2012		
 */
function setupPhpGettext() {

	$CI =& get_instance();

	$CI->load->config('phpgettext');

	// define constants
	if(!defined('PROJECT_DIR')) define('PROJECT_DIR', realpath($CI->config->item('gettextProjectDir')));
	if(!defined('LOCALE_DIR')) define('LOCALE_DIR', realpath($CI->config->item('gettextLocaleDir')));
	if(!defined('DEFAULT_LOCALE')) define('DEFAULT_LOCALE', $CI->config->item('gettextDefaultLocale'));

	if(is_file($CI->config->item('gettextInc')))
	{
		if (!function_exists('_gettext')) {
			require_once($CI->config->item('gettextInc'));
			log_message('debug','File '.$CI->config->item('gettextInc').' has been included.');
		}
	} else {
		log_message('debug','File '.$CI->config->item('gettextInc').' can not be found.');
	}

	$encoding = $CI->config->item('gettextEncoding');

	//LOCALE settings
	$supported_locales = $CI->config->item('gettextSupportedLocales');

	unset($locale);

	//check if the user clicked on one of the language tabs
	$language = $CI->input->get('language');
	//stores the language value in session or retrieves from session (if set)
	if($language) {
		if(in_array($language, $supported_locales)) {
			$locale = $language;
	
			$CI->session->set_userdata('locale',$language);
			//$test = $CI->session->userdata('locale');
		}
	} else {
		//check if the user (who is logged in) has a preferred language
		$preferred_language = $CI->session->userdata('preferred_language');
		if($preferred_language) {
			switch ($preferred_language) {
				case 'english':
					$locale = 'en_US';
				break;

				case 'italian' || 'italiano':
					$locale = 'it_IT';
				break;
									
				default:
					$locale = 'en_US';
				break;
			}
		} else {
			$language_session = $CI->session->userdata('locale');
			if($language_session) {
				$locale = $language_session;
			}
		}
		
		//just in case
		if(empty($locale)) $locale = 'en_US';
	}

	//if no language is saved in session loads the default set in the config file
	if(!isset($locale)) {
		$locale = $CI->config->item('gettextDefaultLocale'); //(isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
	}

	$gettext_settings = array(
			'locale' => $locale,
			'encoding' => $encoding,
			'supported_locales' => $supported_locales,
			'project_dir' => PROJECT_DIR,
			'locale_dir' => LOCALE_DIR,
			'default_locale' => DEFAULT_LOCALE,
	);
	
	foreach ($gettext_settings as $key => $value) {
		$CI->session->set_userdata($key,$value);
	}
}

/**
 * Retrieves phpgettext variables from session
 *
 * @access		public
 * @param		none
 * @var
 * @return		array
 * @example
 * @see
 *
 * @author 		Damiano Venturin
 * @copyright 	2V S.r.l.
 * @license	GPL
 * @since		Sep 17, 2012
 */
function getGettextSettings() {
	
	$CI =& get_instance();
	
	$gettext_settings = array();
	
	$gettext_settings['locale'] = $CI->session->userdata('locale');
	$gettext_settings['encoding'] = $CI->session->userdata('encoding');
	$gettext_settings['supported_locales'] = $CI->session->userdata('supported_locales');
	$gettext_settings['project_dir'] = $CI->session->userdata('project_dir');
	$gettext_settings['locale_dir'] = $CI->session->userdata('locale_dir');
	$gettext_settings['default_locale'] = $CI->session->userdata('defaul_locale');
	
	return $gettext_settings;
}
/* End of file phpgettext_helper.php */
/* Location: ./application/helpers/phpgettext_helper.php */