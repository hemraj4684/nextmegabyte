<?php
//----------------------
//Set the settings
$username = 'laravelhemraj@gmail.com'; //Spiceworks username / email
$password = 'nextmegabyte'; //Spiceworks password
$url_root = 'http://localhost:9675/'; //Include a trailing slash
$cookie_file = 'C:\xampp\htdocs\spicecookies.txt'; //cURL must be able to read and write to this file
$debugMode = false; //Set to true to get outputs of all of the HTTP requests
//Array of all the API calls to make. (no leading slash)
//$api_call[] = 'api/alerts.json?filter=recent';
//$api_call[] = 'api/hotfixes.json'; 
$api_call[] = 'api/groups.json';
$fields_string = '';
//We need to initiate a session and get the authenticity_token from the logon page before we can actually login.
$curl = curl_init($url_root .'pro_users/login');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
$loginPage = curl_exec($curl);
curl_close($curl);
if($debugMode) {
	echo "Login form page: (used for getting the authenticity token)\n";
	echo $loginPage;
	echo "\n\n\n";
}
//Using two explode functions to get the authenticity_token from the page:
$authToken = explode('<input name="authenticity_token" type="hidden" value="', $loginPage);
$authToken = explode('"', $authToken['1']);
$authToken = $authToken['0'];
if($debugMode) {
	echo "Authenticity Token: " . $authToken . "\n\n\n";
}
$loginFields = array(
		'authenticity_token' => urlencode($authToken),
		'_pickaxe' => urlencode('⸕'), //This was included in the original login form, so I'm including it here.
		// as of version 7.2.000519 the username and password fields have changed to pro_user 
		'pro_user[email]' => urlencode($username),
		'pro_user[password]' => urlencode($password),
		'btn' => urlencode('login')
	);
//Transform the fields, ready for POST-ing
foreach($loginFields as $key => $val) {
	$fields_string .= $key.'='.$val.'&';
}
$fields_string .= 'btn=login'; //Original form has two btn=login inputs.
if($debugMode) {
	echo "POST String: " . $fields_string . "\n\n\n";
}
//Initiate connection to Login page and send POST data
$curl = curl_init($url_root . 'pro_users/login');
curl_setopt($curl, CURLOPT_POST, count($loginFields) + 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($curl, CURLOPT_REFERER, $url_root . 'login');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file); //These two options ensure the cookies are both read and written.
curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
$loginProcessPage = curl_exec($curl);
curl_close($curl);
if($debugMode) {
	echo "Login process page: (posts all of the login fields and saves the cookies)\n";
	echo $loginProcessPage;
	echo "<br/>";
}
//Stores each API request in an array
foreach($api_call as $key => $api_url) {
	//echo $url_root . $api_url;
	$curl = curl_init($url_root . $api_url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file); //These two options ensure the cookies are both read and written.
	curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
	$api_call_results[] = array(
		'url' => $url_root . $api_url,
		'raw' => curl_exec($curl)
	);
	
	if(false == $api_call_results[$key]['raw']){
		echo curl_error($curl);
		}
	curl_close($curl);
}
//print_r($api_call_results);
//Loops through every stored API request, decodes the JSON and outputs it to the browser.
foreach($api_call_results as $key => $data) {
	$api_call_results[$key]['data'] = json_decode($data['raw'], true);
	echo "<pre>";
	echo "URL : ".$data['url']."<br/>";
	echo $data['raw']. "<br/>";
	print_r($api_call_results[$key]['data']);
	echo "</pre>";
}
?>
Status API Training Shop Blog About
© 2016 GitHub, Inc. Terms Privacy Security Contact Help