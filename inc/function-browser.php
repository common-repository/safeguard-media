<?php if (!defined('ABSPATH')) exit;

function WPSM_get_aspscp_browser_info()
{
	$u_agent    = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
	$bname      = 'Unknown';
	$platform   = 'Unknown';
	$version    = "";
	$iospattern = '^(?:(?:(?:Mozilla/\d\.\d\s*\()+|Mobile\s*Safari\s*\d+\.\d+(\.\d+)?\s*)(?:iPhone(?:\s+Simulator)?|iPad|iPod);\s*(?:U;\s*)?(?:[a-z]+(?:-[a-z]+)?;\s*)?CPU\s*(?:iPhone\s*)?(?:OS\s*\d+_\d+(?:_\d+)?\s*)?(?:like|comme)\s*Mac\s*O?S?\s*X(?:;\s*[a-z]+(?:-[a-z]+)?)?\)\s*)?(?:AppleWebKit/\d+(?:\.\d+(?:\.\d+)?|\s*\+)?\s*)?(?:\(KHTML,\s*(?:like|comme)\s*Gecko\s*\)\s*)?(?:Version/\d+\.\d+(?:\.\d+)?\s*)?(?:Mobile/\w+\s*)?(?:Safari/\d+\.\d+(\.\d+)?.*)?$';

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	else if (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}
	else if (preg_match('/android/i',$u_agent)) { 
		$platform = 'android';
	}
	else if (preg_match($iospattern,$u_agent)) { 
		$platform = 'ios';
	}
	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/Firefox/i',$u_agent) && !preg_match('/ArtisReader/i',$u_agent)){
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	elseif(preg_match('/Firefox/i',$u_agent) && preg_match('/ArtisReader/i',$u_agent)){
		$bname = 'ArtisBrowser';
		$ub = "ArtisBrowser";
	}
	else if(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}

	// finally get the correct version number
	$known = array('Version', @$ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,@$ub)){
		$version= $matches['version'][0];
		}
		else {
		$version = $matches['version'][1];
		}
	}
	else {
		$version = $matches['version'][0];
	}

	// check if we have a number
	if( $version == null || $version == "" ){ 
		$version = "?";
	}

	return array(
		'userAgent' => $u_agent,
		'name'      => $ub,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'   => $pattern
	);
}