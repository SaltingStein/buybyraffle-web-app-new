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

namespace Google\Service\AdExchangeBuyerII;

class PricePerBuyer extends \Google\Collection
{
  protected $collection_key = 'advertiserIds';
  /**
   * @var string[]
   */
  public $advertiserIds;
  /**
   * @var Buyer
   */
  public $buyer;
  protected $buyerType = Buyer::class;
  protected $buyerDataType = '';
  /**
   * @var Price
   */
  public $price;
  protected $priceType = Price::class;
  protected $priceDataType = '';

  /**
   * @param string[]
   */
  public function setAdvertiserIds($advertiserIds)
  {
    $this->advertiserIds = $advertiserIds;
  }
  /**
   * @return string[]
   */
  public function getAdvertiserIds()
  {
    return $this->advertiserIds;
  }
  /**
   * @param Buyer
   */
  public function setBuyer(Buyer $buyer)
  {
    $this->buyer = $buyer;
  }
  /**
   * @return Buyer
   */
  public function getBuyer()
  {
    return $this->buyer;
  }
  /**
   * @param Price
   */
  public function setPrice(Price $price)
  {
    $this->price = $price;
  }
  /**
   * @return Price
   */
  public function getPrice()
  {
    return $this->price;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PricePerBuyer::class, 'Google_Service_AdExchangeBuyerII_PricePerBuyer');
