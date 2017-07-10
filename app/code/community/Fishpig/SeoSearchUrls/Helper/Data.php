<?php
/**
 * @category    Fishpig
 * @package    Fishpig_SeoSearchUrls
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_SeoSearchUrls_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Retrieve the front name used by the extension
	 *
	 * @return string
	 */
	public function getFrontName()
	{
		return Mage::getStoreConfig('seosearchurls/settings/front_name');
	}
	
	/**
	 * Return the form field name
	 *
	 * @return string
	 */
	public function getFormFieldName()
	{
		return Mage::getStoreConfig('seosearchurls/settings/form_field');
	}
	
	/**
	 * Retrieve the form action
	 *
	 * @return string
	 */
	public function getFormAction()
	{
		return Mage::getStoreConfig('seosearchurls/settings/form_action');
	}
	
	/**
	 * Inject the tag links into the HTML response
	 *
	 * @param Varien_Event_Observer $observer
	 * @return $this
	 */
	public function injectLinksIntoResponseObserver(Varien_Event_Observer $observer)
	{
		// Get HTML from response object
		$html = $observer->getEvent()
			->getFront()
				->getResponse()
					->getBody();
		
		if (strpos($html, 'catalogsearch/result/?') === false) {
			return $this;
		}

		if (preg_match_all('/href="([^"]{1,}\/catalogsearch\/result\/(.*))"/iU', $html, $matches)) {
			$store = Mage::app()->getStore();

			foreach($matches[1] as $i => $originalUrl) {
				parse_str(trim($matches[2][$i], '?'), $query);
				
				if (!isset($query['q'])) {
					continue;
				}

				$newUrl = $this->_getUrl('', array(
					'_direct' => $this->getFrontName() . '/' . urlencode(str_replace(' ', '-', strtolower($query['q']))) . '/',
					'_store' => $store->getId(),
					'_secure' => false,
					'_nosid' 	=> true,
				));
				
				$html = str_replace($originalUrl, $newUrl, $html);
			}
			
			// Set new HTML string to response object
			$observer->getEvent()
				->getFront()
					->getResponse()
						->setBody($html);
		}

		return $this;
	}
}