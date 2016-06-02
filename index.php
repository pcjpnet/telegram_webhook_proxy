<?php
//////////////////////////////////////////////////
//	Telegram Bot Webhook Proxy Script	//
//						//
//		by @pcjpnet (http://pc-jp.net/)	//
//////////////////////////////////////////////////
//
// 1. put this file to the web server.
//    (require php, https access)
// 2. edit settings in this file.
// 3. access the browser to this file.
//    (EX: https://example.com/tg_script/index.php)
// 4. set webhook
//
// ========== SETTINGS ==========
$post_url = array(
""
);

$save_log = "false";	//true or false
$log_mode = "single";	//single or size or all
$log_file_name = "./log.txt";
$log_max_size = 1000;	//bytes (size mode)
$display_log = "true";	//display log in browser

$web_whitelist_mode = "false";	// IP Whitelist (Web)
$tg_whitelist_mode = "true";	// IP Whitelist (Webhook)

//IP Whitelist 
$web_whitelist = array(
"192.168.0.",
"192.168.1."
);

// Telegram Messenger LLP IP Address Block Whitelist
// https://ipinfo.io/AS62041
$tg_whitelist = array(
"149.154.16",
"149.154.170.",
"149.154.171.",
"149.154.172.",
"149.154.173.",
"149.154.174.",
"149.154.175.",
"91.108.4.",
"91.108.5.",
"91.108.6.",
"91.108.7.",
"91.108.56.",
"91.108.57.",
"91.108.58.",
"91.108.59."
);

// ========== END ==========

$post = file_get_contents("php://input");
$ip = $_SERVER["REMOTE_ADDR"];

if ($post) {
	if ($tg_whitelist_mode == "true" && match_ip($ip, $tg_whitelist) === false) {
		exit;
	}
	foreach($post_url as $url) {
		if ($url) { json_post($url, $post); }
	}

	if ($save_log == "true") {
		if ($log_mode == "single") {
			file_put_contents($log_file_name, $post);
		} elseif ($log_mode == "size") {
			if (filesize($log_file_name) > $log_max_size) {
				file_put_contents($log_file_name, $post);
			} else {
				file_put_contents($log_file_name, $post, FILE_APPEND);
			}
		} else {
			file_put_contents($log_file_name, $post, FILE_APPEND);
		}
	}

} else {
	if ($web_whitelist_mode == "true" && match_ip($ip, $web_whitelist) === false) {
		exit;
	}
	if ($_SERVER["HTTPS"]) {
		print("set Webhook: (access to this url by browser)<br />\n");
		print("https://api.telegram.org/bot[token]/setWebhook?url=https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
		print("<br />\n<br />\n");
		if ($save_log == "true" && $display_log == "true") {
			print(file_get_contents($log_file_name));
		} else {
			print("save_log or display_log = false");
		}
	} else {
		print("Require HTTPS");
	}
}


// POST FUNCTION
function json_post($url, $data) {
	$header = array(
		"Content-Type: application/json",
		"Content-Length: ".strlen($data));
	$context = array(
		"http" => array(
		"method"  => "POST",
		"header"  => implode("\r\n", $header),
		"content" => $data));
	return file_get_contents($url, false, stream_context_create($context));
}

// IP WHITELIST
function match_ip($ip, $lists) {
	foreach ($lists as $list) {
		if (strpos($ip, $list) === 0) {
			return true;
		}
	}
	return false;
}

?>

