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

namespace Google\Service\Cloudchannel;

class GoogleCloudChannelV1ListChannelPartnerLinksResponse extends \Google\Collection
{
  protected $collection_key = 'channelPartnerLinks';
  /**
   * @var GoogleCloudChannelV1ChannelPartnerLink[]
   */
  public $channelPartnerLinks;
  protected $channelPartnerLinksType = GoogleCloudChannelV1ChannelPartnerLink::class;
  protected $channelPartnerLinksDataType = 'array';
  /**
   * @var string
   */
  public $nextPageToken;

  /**
   * @param GoogleCloudChannelV1ChannelPartnerLink[]
   */
  public function setChannelPartnerLinks($channelPartnerLinks)
  {
    $this->channelPartnerLinks = $channelPartnerLinks;
  }
  /**
   * @return GoogleCloudChannelV1ChannelPartnerLink[]
   */
  public function getChannelPartnerLinks()
  {
    return $this->channelPartnerLinks;
  }
  /**
   * @param string
   */
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  /**
   * @return string
   */
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudChannelV1ListChannelPartnerLinksResponse::class, 'Google_Service_Cloudchannel_GoogleCloudChannelV1ListChannelPartnerLinksResponse');
