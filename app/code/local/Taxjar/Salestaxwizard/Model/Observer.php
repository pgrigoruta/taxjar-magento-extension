<?php
class Taxjar_Salestaxwizard_Model_Observer {
  
  public function execute($observer) {
    $this->_setVariables();   
    $this->configuration->setTaxBasis($this->configJson);
    $this->configuration->setShippingTaxability($this->configJson);
    $this->_purgeExisting();
    $this->_createRates($this->regionCode);
    $this->shippingrule->createIfTaxable($this->configJson, $this->newRates);
  }



  private function _setVariables() {
    $this->newRates      = array();
    $this->client        = Mage::getModel('salestaxwizard/client');
    $this->configuration = Mage::getModel('salestaxwizard/configuration');
    $this->shippingrule  = Mage::getModel('salestaxwizard/shippingrule');
    $this->rate          = Mage::getModel('salestaxwizard/rate');
    $regionId            = Mage::getStoreConfig('shipping/origin/region_id');
    $this->regionCode    = Mage::getModel('directory/region')->load($regionId)->getCode();
    $this->configJson    = $this->client->getResource('configuration', $this->regionCode);    
  }

  private function _purgeExisting() {
    $paths = array('tax/calculation', 'tax/calculation_rate');
    foreach($paths as $path){
      $existingRecords = Mage::getModel($path)->getCollection();    
      foreach($existingRecords as $record) {
        $record->delete();
      }
    }
  }

  private function _createRates($regionCode) {
    $ratesJson = $this->client->getResource('rates', $regionCode);
    foreach($ratesJson as $rateJson) {
      $this->newRates[] = $this->rate->create($rateJson);
    }    
  }



}
?>