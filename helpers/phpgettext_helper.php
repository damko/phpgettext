<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Stores phpgettext variables into CI session.
 * This is called by MY_Controller so that gettext support is available for any controller
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

		} else {
			if(!$locale = language_to_locale($language)){
				$locale = $CI->config->item('gettextDefaultLocale'); 
			}
			$CI->session->set_userdata('locale',$locale);
		}
	} else {
		//check if the user (who is logged in) has a preferred language
		$preferred_language = $CI->session->userdata('preferred_language');
		if($preferred_language) {
			if(!$locale = language_to_locale($preferred_language)){
				$locale = $CI->config->item('gettextDefaultLocale');				
			}
		} else {
			$language_session = $CI->session->userdata('locale');
			if($language_session) {
				$locale = $language_session;
			}
		}
		
		//just in case
		if(empty($locale) || !$locale) $locale = 'en_US';
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
 * Reads the config file language_to_locale and return the LCID value of the specified language
 * Ex. $language == 'italian' => returns 'it_IT' 
 * 
 * @access		public
 * @param		string $language
 * @return		string LCID or false
 * 
 * @author 		Damiano Venturin
 * @license	GPL
 * @since		Dec 8, 2012
 */
function language_to_locale($language){
		
	if(!is_string($language) || empty($language)) return false;
	
	$CI =& get_instance();
	
	$config = $CI->load->config('language_to_locale',true,true);
			
	if(!is_array($config)) {
		log_message('debug','The config file language_to_locale is missing or broken.');
		return false;
	}

	$locale_lcid = $config['locale_lcid'];
	
	if(in_array($language, array_keys($locale_lcid))){
		return $locale_lcid[$language];
	} else {
		return false;
	}
}

/**
 * Retrieves phpgettext variables from session
 *
 * @access		public
 * @param		none
 * @return		array
 *
 * @author 		Damiano Venturin
 * @since		Sep 17, 2012
 */
function getGettextSettings() {
	
	$CI =& get_instance();
	
	$gettext_settings = array();
	
	$gettext_settings['locale'] = $CI->session->userdata('locale') ? $CI->session->userdata('locale') : 'en_US';
	$gettext_settings['encoding'] = $CI->session->userdata('encoding') ? $CI->session->userdata('encoding') : 'UTF-8';
	$gettext_settings['supported_locales'] = $CI->session->userdata('supported_locales') ? $CI->session->userdata('supported_locales') : array('en_US');
	$gettext_settings['project_dir'] = $CI->session->userdata('project_dir') ? $CI->session->userdata('project_dir') : APPPATH.'third_party/php-gettext-1.0.11';
	$gettext_settings['locale_dir'] = $CI->session->userdata('locale_dir');
	$gettext_settings['default_locale'] = $CI->session->userdata('defaul_locale') ? $CI->session->userdata('defaul_locale') : 'en_US';
	
	if(isset($CI->smarty) && is_object($CI->smarty)){
		//sets locale as Smarty Global var
		$CI->smarty->assignGlobal('locale', $gettext_settings['locale']);
	}
	
	return $gettext_settings;
}

/**
 * This function is written especially for Hero Framework.
 * It translates the content dynamically retrieved from the database before sending it to smarty->display
 * 
 * @access		public
 * @param		string $content
 * @return		string or false
 * 
 * @author 		Damiano Venturin
 * @license		GPL
 * @link		http://www.venturin.net
 * @since		Dec 8, 2012		
 */
function dynamically_translate_content($content , $add_random = false, $no_cache = false){

	if(!is_string($content) || empty($content)) return false;
	
	$CI =& get_instance();
	
	//TODO if I make this check before calling dynamically_translate_content() I can 
	//make this function more universal and flexible

	//checks if location support is enabled
	$setting = $CI->settings_model->get_setting('enable_localization');
	if(!$setting['value']) return false;

	//$html = $CI->smarty->fetch('string:'.$content);
	$html = $CI->smarty->fetch('eval:'.$content);
	return $html;	
	
/*	
	// set a separate tpl_id for each unique URL
	$tpl_id = md5($_SERVER['REQUEST_URI']);
	if($add_random) $tpl_id .= uniqid('-');
	$tpl_file = $tpl_id.'.thtml';
	$tpl_file_full_path = $CI->config->item('path_templates_localization') . $tpl_file;
		
	$CI->load->helper('file');
	
	$cache_original_status = $CI->smarty->caching;
	
	//$CI->smarty->caching = 1;
	$CI->smarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
	$CI->smarty->setCacheLifetime(300);
	
	
	if(!$CI->smarty->isCached($tpl_file_full_path) || $no_cache){
		//writes the content to a temporary template which will be parsed by smarty->fetch: 
		//if the content contains {t}{/t} blocks Smarty will try to translate the content and will
		//remove the {t}{/t} blocks in any case returning a clean html without {t}{/t} block
		if(write_file($tpl_file_full_path, $content, 'w+')){
	
			$html = $CI->smarty->fetch($tpl_file_full_path);
	
			if(!$cache_original_status) $CI->smarty->setCaching(Smarty::CACHING_OFF);
			
			return $html;
			
		} else {
			
			log_message('debug', 'The temporary template ' . $tpl_id . ' can not be written in '.$tpl_file_full_path . '. Please check filesystem permissions.');
			return false;
		}
	} else {
		$html = $CI->smarty->fetch($tpl_file_full_path);
		if(!$cache_original_status) $CI->smarty->setCaching(Smarty::CACHING_OFF);
		return $html;
	}	
*/
}

/* End of file phpgettext_helper.php */
/* Location: ./application/helpers/phpgettext_helper.php */