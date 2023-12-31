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

namespace Google\Service\Docs;

class UpdateSectionStyleRequest extends \Google\Model
{
  /**
   * @var string
   */
  public $fields;
  /**
   * @var Range
   */
  public $range;
  protected $rangeType = Range::class;
  protected $rangeDataType = '';
  /**
   * @var SectionStyle
   */
  public $sectionStyle;
  protected $sectionStyleType = SectionStyle::class;
  protected $sectionStyleDataType = '';

  /**
   * @param string
   */
  public function setFields($fields)
  {
    $this->fields = $fields;
  }
  /**
   * @return string
   */
  public function getFields()
  {
    return $this->fields;
  }
  /**
   * @param Range
   */
  public function setRange(Range $range)
  {
    $this->range = $range;
  }
  /**
   * @return Range
   */
  public function getRange()
  {
    return $this->range;
  }
  /**
   * @param SectionStyle
   */
  public function setSectionStyle(SectionStyle $sectionStyle)
  {
    $this->sectionStyle = $sectionStyle;
  }
  /**
   * @return SectionStyle
   */
  public function getSectionStyle()
  {
    return $this->sectionStyle;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(UpdateSectionStyleRequest::class, 'Google_Service_Docs_UpdateSectionStyleRequest');
