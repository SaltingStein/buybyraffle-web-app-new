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

namespace Google\Service\HangoutsChat;

class Section extends \Google\Collection
{
  protected $collection_key = 'widgets';
  /**
   * @var string
   */
  public $header;
  /**
   * @var WidgetMarkup[]
   */
  public $widgets;
  protected $widgetsType = WidgetMarkup::class;
  protected $widgetsDataType = 'array';

  /**
   * @param string
   */
  public function setHeader($header)
  {
    $this->header = $header;
  }
  /**
   * @return string
   */
  public function getHeader()
  {
    return $this->header;
  }
  /**
   * @param WidgetMarkup[]
   */
  public function setWidgets($widgets)
  {
    $this->widgets = $widgets;
  }
  /**
   * @return WidgetMarkup[]
   */
  public function getWidgets()
  {
    return $this->widgets;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Section::class, 'Google_Service_HangoutsChat_Section');
