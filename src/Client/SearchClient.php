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

use OpenSearch\Generated\Search\Config;
use OpenSearch\Generated\Search\OpenSearchSearcherServiceIf;
use OpenSearch\Generated\Search\SearchFormat;
use OpenSearch\Generated\Search\SearchParams;
use OpenSearch\Util\UrlParamsBuilder;

/**
 * 应用搜索操作类。
 *
 * 通过制定关键词、过滤条件搜索应用结果。
 *
 */
class SearchClient implements OpenSearchSearcherServiceIf {

    const SEARCH_API_PATH = '/apps/%s/search';

    private $openSearchClient;

    /**
     * 构造方法。
     *
     * @param \OpenSearch\Client\OpenSearchClient $openSearchClient 基础类，负责计算签名，和服务端进行交互和返回结果。
     * @return void
     */
    public function __construct($openSearchClient) {
        $this->openSearchClient = $openSearchClient;
    }

    /**
     * 执行搜索操作。
     *
     * @param \OpenSearch\Generated\Search\SearchParams $searchParams 制定的搜索条件。
     * @return \OpenSearch\Generated\Common\OpenSearchResult OpenSearchResult类
     */
    public function execute(SearchParams $searchParams) {
        $path = self::getPath($searchParams);
        $builder = new UrlParamsBuilder($searchParams);
        return $this->openSearchClient->get($path, $builder->getHttpParams());
    }

    private static function getPath($searchParams) {
        $appNames = isset($searchParams->config->appNames) ? implode(',', $searchParams->config->appNames) : '';
        return sprintf(self::SEARCH_API_PATH, $appNames);
    }
}