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

namespace Google\Service\Blogger;

class PostReplies extends \Google\Collection
{
  protected $collection_key = 'items';
  /**
   * @var Comment[]
   */
  public $items;
  protected $itemsType = Comment::class;
  protected $itemsDataType = 'array';
  /**
   * @var string
   */
  public $selfLink;
  /**
   * @var string
   */
  public $totalItems;

  /**
   * @param Comment[]
   */
  public function setItems($items)
  {
    $this->items = $items;
  }
  /**
   * @return Comment[]
   */
  public function getItems()
  {
    return $this->items;
  }
  /**
   * @param string
   */
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  /**
   * @return string
   */
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  /**
   * @param string
   */
  public function setTotalItems($totalItems)
  {
    $this->totalItems = $totalItems;
  }
  /**
   * @return string
   */
  public function getTotalItems()
  {
    return $this->totalItems;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PostReplies::class, 'Google_Service_Blogger_PostReplies');
