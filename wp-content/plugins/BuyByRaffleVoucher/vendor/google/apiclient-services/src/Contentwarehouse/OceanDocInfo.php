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

class OceanDocInfo extends \Google\Model
{
  /**
   * @var OceanDocTag
   */
  public $docTag;
  protected $docTagType = OceanDocTag::class;
  protected $docTagDataType = '';

  /**
   * @param OceanDocTag
   */
  public function setDocTag(OceanDocTag $docTag)
  {
    $this->docTag = $docTag;
  }
  /**
   * @return OceanDocTag
   */
  public function getDocTag()
  {
    return $this->docTag;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(OceanDocInfo::class, 'Google_Service_Contentwarehouse_OceanDocInfo');
