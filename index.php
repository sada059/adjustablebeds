<?php
define('MOBILE_SITE', 'http://ADJUSTABLEBED.MYNETWORKSOLUTIONS.MOBI');
define('DESKTOP_SITE', '_index.php');
if($_REQUEST['redirect']=='false') {
header('Location: ' . DESKTOP_SITE );
exit;
}
define('DA_USE_COOKIES', true);
define('DA_USE_CACHE', false);
define('DA_CACHE_DIR', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'DeviceAtlasCache' .
DIRECTORY_SEPARATOR);
define('DA_URI', 'http://detect.deviceatlas.com/query');
$da_results = array('_source' => 'none');


if (DA_USE_CACHE && $da_results['_source'] === 'none') {
	$da_cache_file = md5($_SERVER["HTTP_USER_AGENT"]) . '.json';
	if (!file_exists(DA_CACHE_DIR) && !@mkdir(DA_CACHE_DIR)) {
	 	$da_results['_error'] = "Unable to create cache directory: " . DA_CACHE_DIR . "\n";
	} else {
		$da_json = @file_get_contents(DA_CACHE_DIR . $da_cache_file);
		if ($da_json !== false) {
			$da_results = (array)json_decode($da_json, true);
			$da_results['_source'] = 'cache';
			if (DA_USE_COOKIES) {
				setcookie('Mobi_Mtld_DA_Properties', $da_json);
			}
		}
	}
}
if ($da_results['_source'] === 'none') {
	//$da_json = @file_get_contents(DA_URI . "?User-Agent=" . urlencode($_SERVER["HTTP_USER_AGENT"]));

        $curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,DA_URI . "?User-Agent=" . urlencode($_SERVER["HTTP_USER_AGENT"]));
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,100);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
	$content = curl_exec($curl_handle);
	curl_close($curl_handle);

	$da_json = $content;

	if ($da_json !== false) {
		$da_results = array_merge(json_decode($da_json, true), $da_results);
		$da_results['_source'] = 'webservice';
		if (DA_USE_COOKIES) {
			setcookie('Mobi_Mtld_DA_Properties', $da_json);
		}
		if (DA_USE_CACHE) {
			if (@file_put_contents(DA_CACHE_DIR . $da_cache_file, $da_json) === false) {
			     $da_results['_error'] .= "Unable to write cache file " . DA_CACHE_DIR . $da_cache_file . "\n";
			}
		}
	} else {
		$da_results['_error'] .= "Error fetching DeviceAtlas data from webservice.\n";
	}
}

$result=$da_results['mobileDevice'];
$error = $da_results['_error'];


if($da_results['mobileDevice']) header('Location: ' . MOBILE_SITE);
else header('Location: ' . DESKTOP_SITE);

?>
