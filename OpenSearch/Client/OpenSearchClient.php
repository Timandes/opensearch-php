<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

namespace OpenSearch\Client;

use OpenSearch\Generated\OpenSearch\OpenSearch;
use OpenSearch\Generated\OpenSearch\Constant;
use OpenSearch\Generated\Common\OpenSearchResult;
use OpenSearch\Generated\Common\TraceInfo;
use OpenSearch\Client\Handler\CurlHttpHandler;

class OpenSearchClient extends OpenSearch {

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    const API_VERSION = '3';
    const API_TYPE = 'openapi';

    const SDK_VERSION = '3.3.0';
    const SDK_TYPE    = 'opensearch_sdk';

    private $debug = false;

    public $timeout = 10;
    public $connectTimeout = 1;

    /** @var HttpHandlerInterface */
    private $httpHandler = null;

    /**
     * 构造方法。
     *
     * @param string $accessKey 指定您的accessKeyId，在 https://ak-console.aliyun.com/#/accesskey 中可以创建。
     * @param string $secret 指定您的secret。
     * @param string $host 指定您要访问的区域的endPoint，在控制台应用详情页中有指定。
     * @param array @options 指定一些可选参数，debug：true/false，是否开启debug模式（默认不开启），gzip:true/false 是否开启gzip压缩（默认不开启），timeout：超时时间，seconds（默认10秒）,connectTimeout: 连接超时时间，seconds(默认1秒)
     * @return void
     */
    public function __construct($accessKey, $secret, $host, $options = array()) {
        $args = array(
            'accessKey' => trim($accessKey),
            'secret' => trim($secret),
            'host' => trim($host),
            'options' => $options
        );

        if (isset($options['gzip'])) {
            $args['gzip'] = $options['gzip'];
        }

        if (isset($options['timeout'])) {
            $args['timeout'] = $options['timeout'];
        }

        if (isset($options['connectTimeout'])) {
            $args['connectTimeout'] = $options['connectTimeout'];
        }

        if (isset($options['debug'])) {
            $this->debug = (boolean) $options['debug'];
        }

        $this->httpHandler = new CurlHttpHandler();

        parent::__construct($args);
    }

    public function setHttpHandler(HttpHandlerInterface $httpHandler)
    {
        $this->httpHandler = $httpHandler;
    }

    /**
     * 发送一个GET请求。
     *
     * @param string $uri 发起GET请求的uri。
     * @param array $params 发起GET请求的参数，以param_key => param_value的方式体现。
     * @return \OpenSearch\Generated\Common\OpenSearchResult
     */
    public function get($uri, $params = array()) {
        return $this->call($uri, $params, '', self::METHOD_GET);
    }

    /**
     * 发送一个PUT请求。
     *
     * @param string $uri 发起PUT请求的uri。
     * @param string $body 发起PUT请求的body体，为一个原始的json格式的string。
     * @return \OpenSearch\Generated\Common\OpenSearchResult
     */
    public function put($uri, $body = '') {
        return $this->call($uri, array(), $body, self::METHOD_PUT);
    }

    /**
     * 发送一个POST请求。
     *
     * @param string $uri 发起POST请求的uri。
     * @param string $body 发起POST请求的body体，为一个原始的json格式的string。
     * @return \OpenSearch\Generated\Common\OpenSearchResult
     */
    public function post($uri, $body = '') {
        return $this->call($uri, array(), $body, self::METHOD_POST);
    }

    /**
     * 发送一个DELETE请求。
     *
     * @param string $uri 发起DELETE请求的uri。
     * @param string $body 发起DELETE请求的body体，为一个原始的json格式的string。
     * @return \OpenSearch\Generated\Common\OpenSearchResult
     */
    public function delete($uri, $body = '') {
        return $this->call($uri, array(), $body, self::METHOD_DELETE);
    }

    /**
     * 发送一个PATCH请求。
     *
     * @param string $uri 发起PATCH请求的uri。
     * @param string $body 发起PATCH请求的body体，为一个原始的json格式的string。
     * @return \OpenSearch\Generated\Common\OpenSearchResult
     */
    public function patch($uri, $body = '') {
        return $this->call($uri, array(), $body, self::METHOD_PATCH);
    }

    /**
     * 发送一个请求。
     *
     * @param string $uri 发起请求的uri。
     * @param array $params 指定的url中的query string 列表。
     * @param string $body 发起请求的body体，为一个原始的json格式的string。
     * @param string $method 发起请求的方法，有GET/POST/DELETE/PUT/PATCH等
     * @return \OpenSearch\Generated\Common\OpenSearchResult
     */
    public function call($uri, array $params, $body, $method) {
        $path = "/v" . self::API_VERSION . "/" . self::API_TYPE . "{$uri}";
        return $this->request($path, $params, $body, $method);
    }

    public function request(string $requestUri, array $params, string $body, string $method): OpenSearchResult
    {
        $url = $this->host . $requestUri;

        $items = array();
        $items['method'] = $method;
        $items['request_path'] = $requestUri;
        $items['content_type'] = "application/json";
        $items['accept_language'] = "zh-cn";
        $items['date'] = gmdate('Y-m-d\TH:i:s\Z');
        $items['opensearch_headers'] = array();
        $items['content_md5'] = "";
        $items['opensearch_headers']['X-Opensearch-Nonce'] = $this->_nonce();

        if ($method != self::METHOD_GET) {
            if (!empty($body)) {
                $items['content_md5'] = md5($body);
                $items['body_json'] = $body;
            }
        }
        $items['query_params'] = $params;

        $signature = $this->_signature($this->secret, $items);
        $items['authorization'] = "OPENSEARCH {$this->accessKey}:{$signature}";

        return $this->_curl($url, $items);
    }

    private function _nonce() {
        return intval(microtime(true) * 1000) . mt_rand(10000, 99999);
    }

    private function _signature($secret, $items) {
        $params = isset($items['query_params']) ? $items['query_params'] : "";

        $signature = '';
        $string = '';
        $string .= strtoupper($items['method']) . "\n";
        $string .= $items['content_md5'] . "\n";
        $string .= $items['content_type'] . "\n";
        $string .= $items['date'] . "\n";

        $headers = self::_filter($items['opensearch_headers']);
        foreach($headers as $key => $value){
          $string .= strtolower($key) . ":" . $value."\n";
        }

        $resource = str_replace('%2F', '/', rawurlencode($items['request_path']));
        $sortParams = self::_filter($params);

        $queryString = $this->_buildQuery($sortParams);
        $canonicalizedResource = $resource;

        if(!empty($queryString)){
          $canonicalizedResource .= '?'.$queryString;
        }

        $string .= $canonicalizedResource;

        $signature = base64_encode(hash_hmac('sha1', $string, $secret, true));
        return $signature;
    }

    private function _buildQuery($params) {
        return $this->httpHandler->_buildQuery($params);
    }

    private function _filter($parameters = array()){
        $params = array();
        if(!empty($parameters)){
            foreach ($parameters as $key => $val) {
                if ($key == "Signature" ||$val === "" || $val === NULL){
                    continue;
                } else {
                    $params[$key] = $parameters[$key];
                }
            }

            uksort($params,'strnatcasecmp');
            reset($params);
        }
        return $params;
    }

    private function _curl($url, $items) {
        $response = $this->httpHandler->request($url, $items
                , $this->connectTimeout, $this->timeout, $this->gzip, $this->debug);

        $openSearchResult = new OpenSearchResult();
        $openSearchResult->result = $response;

        if ($this->debug) {
            $openSearchResult->traceInfo = $this->httpHandler->getTraceInfo();
        }

        return $openSearchResult;
    }

}
