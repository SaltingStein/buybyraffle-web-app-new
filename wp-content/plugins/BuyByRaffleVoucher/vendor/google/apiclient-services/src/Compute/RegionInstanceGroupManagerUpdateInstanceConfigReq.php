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

namespace Google\Service\Compute;

class RegionInstanceGroupManagerUpdateInstanceConfigReq extends \Google\Collection
{
  protected $collection_key = 'perInstanceConfigs';
  /**
   * @var PerInstanceConfig[]
   */
  public $perInstanceConfigs;
  protected $perInstanceConfigsType = PerInstanceConfig::class;
  protected $perInstanceConfigsDataType = 'array';

  /**
   * @param PerInstanceConfig[]
   */
  public function setPerInstanceConfigs($perInstanceConfigs)
  {
    $this->perInstanceConfigs = $perInstanceConfigs;
  }
  /**
   * @return PerInstanceConfig[]
   */
  public function getPerInstanceConfigs()
  {
    return $this->perInstanceConfigs;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RegionInstanceGroupManagerUpdateInstanceConfigReq::class, 'Google_Service_Compute_RegionInstanceGroupManagerUpdateInstanceConfigReq');
