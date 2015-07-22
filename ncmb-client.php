<?php
/*
        NIFTY Cloud mobile backend Client v0.0.1
*/
/*
        Copyright 2015 nd.yuya

        Licensed under the Apache License, Version 2.0 (the "License");
        you may not use this file except in compliance with the License.
        You may obtain a copy of the License at

                http://www.apache.org/licenses/LICENSE-2.0

        Unless required by applicable law or agreed to in writing, software
        distributed under the License is distributed on an "AS IS" BASIS,
        WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
        See the License for the specific language governing permissions and
        limitations under the License.
*/

class NCMBClient {
	// 定数
	const API_ENDPOINT = 'mb.api.cloud.nifty.com';
	const API_VERSION  = '2013-09-01';

	// 変数  
	private $application_key = null;
	private $client_key = null;
	private $session_token = null;

	// コンストラクタ
	public function __construct($application_key, $client_key) {
		$this->init($application_key, $client_key);
	}

	// 初期化  
	public function init($application_key, $client_key) {
		$this->application_key = $application_key;
		$this->client_key = $client_key;    
	}

	// 会員セッショントークンの設定  
	public function set_session_token($session_token) {
		$this->session_token = $session_token;
	}

	// 会員セッショントークンの解除  
	public function release_session_token() {
		$this->session_token = null;
	}

	// GETメソッド用
	public function get($path) {
		return $this->rest_api('GET', $path);
	}

	// POSTメソッド用  
	public function post($path, $body = null) {
		return $this->rest_api('POST', $path, $body);
	}

	// PUTメソッド用  
	public function put($path, $body = null) {
		return $this->rest_api('PUT', $path, $body);
	}

	// DELETEメソッド用
	public function delete($path) {
		return $this->rest_api('DELETE', $path);
	}

	//===========================
	// Private Methods
	//===========================

	// URLの構築メソッド
	private function gen_url($path) {
		return 'https://' . self::API_ENDPOINT . '/' . self::API_VERSION . (strpos($path, '/') === 0 ? $path : '/' . $path);
	}

	// タイムスタンプの生成メソッド  
	private function gen_timestamp() {
		$now = microtime(true);
		$msec = sprintf("%03d", ($now - floor($now)) * 1000);
		return gmdate('Y-m-d\TH:i:s.', floor($now)) . $msec . 'Z';
	}

	// シグネチャの計算メソッド
	private function gen_signature($method, $url, $timestamp) {
		// URLからホスト名、パス、クエリストリングを取得する
		$fqdn = parse_url($url, PHP_URL_HOST);
		$path = parse_url($url, PHP_URL_PATH);
		$query = parse_url($url, PHP_URL_QUERY);

		// シグネチャ生成用のパラメータの素を作る
		$signature_parameters = array(
			'SignatureMethod' => 'HmacSHA256',
			'SignatureVersion' => '2',
 			'X-NCMB-Application-Key' => $this->application_key,
			'X-NCMB-Timestamp' => $timestamp
		);

		// クエリストリングを"&"で分割し、シグネチャ生成用パラメータに追加する
		parse_str($query, $queries);    
		if ($queries !== null)
			$signature_parameters = array_merge($signature_parameters, $queries);

		// シグネチャ生成用パラメータを、キーの自然昇順に並び替える    
		ksort($signature_parameters);

		// 並び替えたシグネチャ生成用パラメータを"&"で連結する
		// 　パラメータのうちクエリストリングの物はURLエンコードしてから連結する、それ以外はそのまま使う
		$signature_parameter_string = '';
		foreach($signature_parameters as $key => $value) {
			if ($signature_parameter_string !== '')
				$signature_parameter_string .= '&';
			$signature_parameter_string .= $key . '=' . (in_array($key, array_keys($queries)) ? urlencode($value) : $value);
		}

		// 署名用文字列を生成する   
		$signature_seed = implode("\n", array(
			$method,
			$fqdn,
			$path,
			$signature_parameter_string
		));

		// 署名用文字列をクライアントキーを秘密鍵としてsha256でハッシュ値を求め、base64エンコードする
		return base64_encode(hash_hmac('sha256', $signature_seed, $this->client_key, true));
	}

	// REST APIに対するリクエストを行う実体  
	private function rest_api($method, $path, $body = null) {
		if (strlen($this->application_key) < 1 && strlen($this->client_key))
			return;
 
		// HTTPリクエストヘッダ（必須項目）を作る
		$url = $this->gen_url($path);
		$timestamp = $this->gen_timestamp();
		$signature = $this->gen_signature($method, $url, $timestamp);
		$header = array(
			'X-NCMB-Application-Key: '. $this->application_key,
			'X-NCMB-Signature: ' . $signature,
			'X-NCMB-Timestamp: ' . $timestamp
		);

		// セッショントークンがある場合だけ、ヘッダに追加する    
		if ($this->session_token !== null)
			array_push($header, 'X-NCMB-Apps-Session-Token: ' . $this->session_token);

		// HTTPリクエストの詳細な設定をする
		$options = array('http' => array(
			'ignore_errors' => true,	// APIリクエストの結果がエラーでもレスポンスボディを取得する
			'max_redirects' => 0,		// リダイレクトはしない
			'method' => $method		// リクエストメソッドを設定する
		));

		// HTTPリクエストボディが必要な場合のみ設定する    
		if ($body !== null) {
			array_push($header, 'Content-Type: application/json');
			array_push($header, 'Content-Length: ' . strlen($body));
			$options['http']['content'] = $body;
		}

		// 追加分も含むHTTPリクエストヘッダを、HTTPリクエストの詳細設定に追加する
		$options['http']['header'] = implode("\r\n", $header);

		// 設定に従い、HTTPリクエストを行った結果を返す
		return file_get_contents($url, false, stream_context_create($options));
	}
}
?>
