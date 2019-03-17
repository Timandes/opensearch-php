<?php
/**
 * Copyright 2019 Alibaba Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Timandes White <timands@gmail.com>
*/

namespace OpenSearch\Client\Handler;

use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Generated\Common\TraceInfo;

class SwooleHttpHandler extends AbstractHttpHandler
{
    public function request($url, $items, $connectTimeout, $timeout, $gzip, $debug)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $client = new \Swoole\Coroutine\Http\Client($host);

        $method = strtoupper($items['method']);
        $headers = $this->_getHeaders($items, true);

        // 跳过CURLOPT_HTTP_VERSION（Swoole默认使用HTTP/1.1）
        // 跳过CURLOPT_CONNECTTIMEOUT（注意：暂无法设置连接超时时间）
        // CURLOPT_TIMEOUT
        $client->set(['timeout' => $timeout]);
        // CURLOPT_CUSTOMREQUEST
        $client->setMethod($method);
        // 跳过CURLOPT_HEADER（Swoole默认将响应头、体分离）
        // 跳过CURLOPT_RETURNTRANSFER（Swoole默认返回响应体）
        // CURLOPT_USERAGENT
        $headers['User-Agent'] = "opensearch/php sdk " . OpenSearchClient::SDK_VERSION . "/" . PHP_VERSION;
        // CURLOPT_ENCODING
        if ($gzip) {
            $headers['Accept-Encoding'] = 'gzip';
        }
        // CURLOPT_HTTPHEADER
        $client->setHeaders($headers); // NAME => VALUE

        // 忽略$debug参数（Swoole无法重定向调试信息）

        if ($method == OpenSearchClient::METHOD_GET) {
            $query = $this->_buildQuery($items['query_params']);
            $url .= preg_match('/\?/i', $url) ? '&' . $query : '?' . $query;
        } else {
            if(!empty($items['body_json'])){
                $client->setData($items['body_json']); // Request body
            }
        }

        $result = $client->execute($url); // Boolean

        // 忽略$debug参数（Swoole无法重定向调试信息）
        $this->traceInfo = new TraceInfo(); // Empty trace info

        if (!$result) {
            return false;
        }

        return $client->body;
    }
}
