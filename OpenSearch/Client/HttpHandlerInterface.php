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

namespace OpenSearch\Client;

/**
 * HTTP Handler
 */
interface HttpHandlerInterface
{
    /**
     * Performs a HTTP request and returns response body
     *
     * @return string|false
     */
    public function request($url, $items, $connectTimeout, $timeout, $gzip, $debug);

    /**
     * Get trace info object
     *
     * @return TraceInfo
     */
    public function getTraceInfo();
}
