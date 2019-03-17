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

use OpenSearch\Client\HttpHandlerInterface;

/**
 * Abstract HTTP Handler
 */
abstract class AbstractHttpHandler implements HttpHandlerInterface
{
    protected $traceInfo = null;

    // Extract from OpenSearchClient
    public function _getHeaders($items, $assoc = false)
    {
        $headers = array();
        $headers['Content-Type'] = $items['content_type'];
        $headers['Date'] = $items['date'];
        $headers['Accept-Language'] = $items['accept_language'];
        $headers['Content-Md5'] = $items['content_md5'];
        $headers['Authorization'] = $items['authorization'];
        if (is_array($items['opensearch_headers'])) {
            $headers = array_merge($headers, $items['opensearch_headers']);
        }

        if ($assoc) {
            return $headers;
        }

        $retval = [];
        foreach ($headers as $k => $v) {
            $retval[] = $k . ': ' . $v;
        }
        return $retval;
    }

    // Extract from OpenSearchClient
    public function _buildQuery($params)
    {
        $query = '';
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $query = !empty($params) ? http_build_query($params, null, '&', PHP_QUERY_RFC3986) : '';
        } else {
            $arg = '';
            foreach ($params as $key => $val) {
                $arg .= rawurlencode($key) . "=" . rawurlencode($val) . "&";
            }
            $query = substr($arg, 0, count($arg) - 2);
        }

        return $query;
    }

    public function getTraceInfo()
    {
        return $this->traceInfo;
    }
}
