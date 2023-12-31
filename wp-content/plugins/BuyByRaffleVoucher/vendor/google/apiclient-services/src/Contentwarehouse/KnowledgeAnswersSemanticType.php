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

class KnowledgeAnswersSemanticType extends \Google\Collection
{
  protected $collection_key = 'nameRemodelings';
  /**
   * @var bool
   */
  public $allowAll;
  /**
   * @var NlpMeaningComponentSpecificContracts
   */
  public $componentSpecificContracts;
  protected $componentSpecificContractsType = NlpMeaningComponentSpecificContracts::class;
  protected $componentSpecificContractsDataType = '';
  /**
   * @var bool
   */
  public $includesContainingIntent;
  /**
   * @var string[]
   */
  public $name;
  /**
   * @var NlpMeaningSemanticTypeNameComponentSpecificContracts[]
   */
  public $nameContracts;
  protected $nameContractsType = NlpMeaningSemanticTypeNameComponentSpecificContracts::class;
  protected $nameContractsDataType = 'array';
  /**
   * @var NlpMeaningSemanticTypeNameMeaningRemodelings[]
   */
  public $nameRemodelings;
  protected $nameRemodelingsType = NlpMeaningSemanticTypeNameMeaningRemodelings::class;
  protected $nameRemodelingsDataType = 'array';
  /**
   * @var NlpMeaningMeaningRemodelings
   */
  public $remodelings;
  protected $remodelingsType = NlpMeaningMeaningRemodelings::class;
  protected $remodelingsDataType = '';

  /**
   * @param bool
   */
  public function setAllowAll($allowAll)
  {
    $this->allowAll = $allowAll;
  }
  /**
   * @return bool
   */
  public function getAllowAll()
  {
    return $this->allowAll;
  }
  /**
   * @param NlpMeaningComponentSpecificContracts
   */
  public function setComponentSpecificContracts(NlpMeaningComponentSpecificContracts $componentSpecificContracts)
  {
    $this->componentSpecificContracts = $componentSpecificContracts;
  }
  /**
   * @return NlpMeaningComponentSpecificContracts
   */
  public function getComponentSpecificContracts()
  {
    return $this->componentSpecificContracts;
  }
  /**
   * @param bool
   */
  public function setIncludesContainingIntent($includesContainingIntent)
  {
    $this->includesContainingIntent = $includesContainingIntent;
  }
  /**
   * @return bool
   */
  public function getIncludesContainingIntent()
  {
    return $this->includesContainingIntent;
  }
  /**
   * @param string[]
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string[]
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param NlpMeaningSemanticTypeNameComponentSpecificContracts[]
   */
  public function setNameContracts($nameContracts)
  {
    $this->nameContracts = $nameContracts;
  }
  /**
   * @return NlpMeaningSemanticTypeNameComponentSpecificContracts[]
   */
  public function getNameContracts()
  {
    return $this->nameContracts;
  }
  /**
   * @param NlpMeaningSemanticTypeNameMeaningRemodelings[]
   */
  public function setNameRemodelings($nameRemodelings)
  {
    $this->nameRemodelings = $nameRemodelings;
  }
  /**
   * @return NlpMeaningSemanticTypeNameMeaningRemodelings[]
   */
  public function getNameRemodelings()
  {
    return $this->nameRemodelings;
  }
  /**
   * @param NlpMeaningMeaningRemodelings
   */
  public function setRemodelings(NlpMeaningMeaningRemodelings $remodelings)
  {
    $this->remodelings = $remodelings;
  }
  /**
   * @return NlpMeaningMeaningRemodelings
   */
  public function getRemodelings()
  {
    return $this->remodelings;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(KnowledgeAnswersSemanticType::class, 'Google_Service_Contentwarehouse_KnowledgeAnswersSemanticType');
