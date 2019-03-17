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

/**
 * HTTP Handler based on cURL
 */
class CurlHttpHandler extends AbstractHttpHandler
{
    public function request($url, $items, $connectTimeout, $timeout, $gzip, $debug)
    {
        $method = strtoupper($items['method']);
        $options = array(
            CURLOPT_HTTP_VERSION => 'CURL_HTTP_VERSION_1_1',
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => "opensearch/php sdk " . OpenSearchClient::SDK_VERSION . "/" . PHP_VERSION,
            CURLOPT_HTTPHEADER => $this->_getHeaders($items),
        );

        if ($items['query_params']) {
            $query = $this->_buildQuery($items['query_params']);
            $url .= preg_match('/\?/i', $url) ? '&' . $query : '?' . $query;
        }
        if ($method != OpenSearchClient::METHOD_GET) {
            if(!empty($items['body_json'])){
                $options[CURLOPT_POSTFIELDS] = $items['body_json'];
            }
        }

        if ($gzip) {
            $options[CURLOPT_ENCODING] = 'gzip';
        }

        if ($debug) {
            $out = fopen('php://temp','rw');
            $options[CURLOPT_VERBOSE] = true;
            $options[CURLOPT_STDERR] = $out;
        }

        $session = curl_init($url);
        curl_setopt_array($session, $options);
        $response = curl_exec($session);
        curl_close($session);

        if ($debug) {
            $this->traceInfo = $this->getDebugInfo($out, $items);
        }

        return $response;
    }

    private function getDebugInfo($handler, $items)
    {
        rewind($handler);
        $trace = new TraceInfo();
        $header = stream_get_contents($handler);
        fclose($handler);

        $trace->tracer = "\n" . $header;
        return $trace;
    }
}
