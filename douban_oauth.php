<?php

class Douban_OAuth 
{
	public function get_auth_request()
	{
		include_once  'OAuth.php';
		include_once  'config.php';
		
		$consumer = new OAuthConsumer($api_key, $api_key_secret);
		$req_req = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $request_token_url);
		
		$req_req->sign_request($sig_method, $consumer, NULL);
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $req_req->to_url());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		
		curl_close($curl);
		parse_str($result, $attr);
		
		$param_str = 'oauth_token=' . $attr['oauth_token'];
		
		session_start();
		$_SESSION['request_token_secret'] = $attr['oauth_token_secret'];
		
		$callback = urlencode("http://localhost/account/douban/register");
		$authorize_request_url = $authorize_url . "?" . $param_str . "&oauth_callback=" . $callback;
		
		
		return $authorize_request_url;
	}
	
	public function get_authorized_user_id()
	{
		include_once  'OAuth.php';
		include_once  'config.php';
		
		// 获取之前的 oauth_token 和 oauth_token_secret 。在上一步授权之后会带着这两个参数跳转到本页，见 request_token.php
		$oauth_token = $_SERVER['QUERY_STRING'];
		parse_str($oauth_token);
		session_start();
		$oauth_token_secret = $_SESSION['request_token_secret'];

		// 创建一个 OAuthConsumer 对象。
		$consumer = new OAuthConsumer($api_key, $api_key_secret);

		// 创建一个 token 对象，参数是上一步获取到的 oauth_token 和 oauth_token_secret
		$request_token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		$acc_req = OAuthRequest::from_consumer_and_token($consumer, $request_token, "GET", $access_token_url);
		$acc_req->sign_request($sig_method, $consumer, $request_token);

		/*
		 * 使用 curl 模拟 HTTP 请求。你也可以打印出 URL 信息：
		 *
		 * var_dump($acc_req->to_url());
		 *
		 * 然后把 URL 复制到浏览器地址栏中打开，也可以看到页面上出现下面的 result 结果。
		 */
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $acc_req->to_url());
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		
		parse_str($result, $params);

		//$token = $params['oauth_token'];
		//$token_secret = $params['oauth_token_secret'];
		//$user_id = $params['douban_user_id'];
		
		if (array_key_exists('douban_user_id', $params)) {
			return $params['douban_user_id'];
		}
		
		return NULL;
	}
}

?>