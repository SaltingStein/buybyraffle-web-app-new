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

namespace Google\Service\Contentwarehouse;

class GeostoreRightsStatusProto extends \Google\Collection
{
  protected $collection_key = 'fieldWithRights';
  /**
   * @var GeostoreFieldWithRightsProto[]
   */
  public $fieldWithRights;
  protected $fieldWithRightsType = GeostoreFieldWithRightsProto::class;
  protected $fieldWithRightsDataType = 'array';

  /**
   * @param GeostoreFieldWithRightsProto[]
   */
  public function setFieldWithRights($fieldWithRights)
  {
    $this->fieldWithRights = $fieldWithRights;
  }
  /**
   * @return GeostoreFieldWithRightsProto[]
   */
  public function getFieldWithRights()
  {
    return $this->fieldWithRights;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreRightsStatusProto::class, 'Google_Service_Contentwarehouse_GeostoreRightsStatusProto');
