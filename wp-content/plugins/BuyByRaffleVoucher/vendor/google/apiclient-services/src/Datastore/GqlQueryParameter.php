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

namespace Google\Service\Datastore;

class GqlQueryParameter extends \Google\Model
{
  /**
   * @var string
   */
  public $cursor;
  /**
   * @var Value
   */
  public $value;
  protected $valueType = Value::class;
  protected $valueDataType = '';

  /**
   * @param string
   */
  public function setCursor($cursor)
  {
    $this->cursor = $cursor;
  }
  /**
   * @return string
   */
  public function getCursor()
  {
    return $this->cursor;
  }
  /**
   * @param Value
   */
  public function setValue(Value $value)
  {
    $this->value = $value;
  }
  /**
   * @return Value
   */
  public function getValue()
  {
    return $this->value;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GqlQueryParameter::class, 'Google_Service_Datastore_GqlQueryParameter');
