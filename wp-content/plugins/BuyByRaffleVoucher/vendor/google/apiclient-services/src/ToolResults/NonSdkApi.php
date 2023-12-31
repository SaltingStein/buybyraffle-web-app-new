<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\ToolResults;

class NonSdkApi extends \Google\Collection
{
  protected $collection_key = 'insights';
  /**
   * @var string
   */
  public $apiSignature;
  /**
   * @var string[]
   */
  public $exampleStackTraces;
  /**
   * @var NonSdkApiInsight[]
   */
  public $insights;
  protected $insightsType = NonSdkApiInsight::class;
  protected $insightsDataType = 'array';
  /**
   * @var int
   */
  public $invocationCount;
  /**
   * @var string
   */
  public $list;

  /**
   * @param string
   */
  public function setApiSignature($apiSignature)
  {
    $this->apiSignature = $apiSignature;
  }
  /**
   * @return string
   */
  public function getApiSignature()
  {
    return $this->apiSignature;
  }
  /**
   * @param string[]
   */
  public function setExampleStackTraces($exampleStackTraces)
  {
    $this->exampleStackTraces = $exampleStackTraces;
  }
  /**
   * @return string[]
   */
  public function getExampleStackTraces()
  {
    return $this->exampleStackTraces;
  }
  /**
   * @param NonSdkApiInsight[]
   */
  public function setInsights($insights)
  {
    $this->insights = $insights;
  }
  /**
   * @return NonSdkApiInsight[]
   */
  public function getInsights()
  {
    return $this->insights;
  }
  /**
   * @param int
   */
  public function setInvocationCount($invocationCount)
  {
    $this->invocationCount = $invocationCount;
  }
  /**
   * @return int
   */
  public function getInvocationCount()
  {
    return $this->invocationCount;
  }
  /**
   * @param string
   */
  public function setList($list)
  {
    $this->list = $list;
  }
  /**
   * @return string
   */
  public function getList()
  {
    return $this->list;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(NonSdkApi::class, 'Google_Service_ToolResults_NonSdkApi');
