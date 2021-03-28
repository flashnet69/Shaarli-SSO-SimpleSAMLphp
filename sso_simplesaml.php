<?php

/**
 * SSO Plugin.
 *
 * Login via SSO with SimpleSAMLPhp
 */

use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;
use Shaarli\Render\TemplatePage;

use Katzgrau\KLogger\Logger;
use Psr\Log\LogLevel;

use Shaarli\Security\SessionManager;


const EXT_TRANSLATION_DOMAIN = 'sso_simplesaml';

/*
 * This is not necessary, but it's easier if you don't want Poedit to mix up your translations.
 */
function sso_simplesaml_t($text, $nText = '', $nb = 1)
{
    return t($text, $nText, $nb, EXT_TRANSLATION_DOMAIN);
}

/**
 * Initialization function.
 * It will be called when the plugin is loaded.
 * This function can be used to return a list of initialization errors.
 *
 * @param $conf ConfigManager instance.
 *
 * @return array List of errors (optional).
 */
function sso_simplesaml_init($conf)
{
	// Custom translation with the domain 'ssosimplesaml'
	if (! $conf->exists('translation.extensions.sso_simplesaml')) {
        $conf->set('translation.extensions.sso_simplesaml', 'plugins/sso_simplesaml/languages/');
        $conf->write(true);
    }

	$errors = [];
   
	// If the user is connected with SimpleSAML, we check if he can be connected to this instance of Shaarli
	if(!empty($_SESSION['sso_uid'])){
	   // The user can only connect to the instance that contains his name with uid
	   if(strpos($_SERVER['SCRIPT_NAME'], $_SESSION['sso_uid']) === false){
		   if (isset($_SESSION['ip']) && isset($_SESSION['username'])) {
				unset($_SESSION['ip']);
				unset($_SESSION['expires_on']);
				unset($_SESSION['username']);
				unset($_SESSION['visibility']);
				
				// JS redirection to home directory
				echo "<script>
					window.location = '//".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."';
				</script>";
			}
			echo '<style>#login-button{display:none;}</style>';
		}
	} 

  
	// On Login page
	if(strpos( $_SERVER['SCRIPT_URL'], 'login') !== false){
		
		// Check inclusion of library simpleSAMLPhp and initialize the instance
		if(file_exists($conf->get('plugins.SSO_SIMPLESAML_PATH'))){	
			
			require_once($conf->get('plugins.SSO_SIMPLESAML_PATH'));
			$as = new \SimpleSAML\Auth\Simple('default-sp');
			$attrs = $as->getAttributes();
		   
			$session = \SimpleSAML\Session::getSessionFromRequest();
			$session->cleanup();
			   
			// Authenticiation with SimpleSAML
			$as->requireAuth();
			
			$session = \SimpleSAML\Session::getSessionFromRequest();
			$session->cleanup();
			
			$attrs = $as->getAttributes();
			$_SESSION['sso_uid'] = $attrs['uid'][0];

			// The user can only connect to the instance that contains his name with uid
			if(strpos($_SERVER['SCRIPT_NAME'], $attrs['uid'][0]) === false){
				
				$sessionManager = new SessionManager($_SESSION, $conf, session_save_path());
				$sessionManager->setSessionParameter(
				SessionManager::KEY_ERROR_MESSAGES,
					[t('Wrong login/password.')]
				);
			
			} else {
				// If authentification is OK, auto connection to Shaarli
				$client_ip_id = client_ip_id($_SERVER);
				$sessionManager = new SessionManager($_SESSION, $conf, session_save_path());
				$sessionManager->initialize();
				$sessionManager->storeLoginInfo($client_ip_id);
				
				// JS redirection to home directory
				echo "<script>
					window.location = '//".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."';
				</script>";
			}
		}else {
			$errors[] = sso_simplesaml_t('SimpleSAMLphp path is not defined or does not exist on the server');
		}
	} 
	
    return $errors;
	
}


/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function sso_simplesaml_translation()
{
    // meta
    t('SimpleSAMLphp path is not defined or does not exist on the server');
    t('Connection via SSO using SimpleSAMLphp');
    t('Path of files to include SimpleSAMLphp');
}
