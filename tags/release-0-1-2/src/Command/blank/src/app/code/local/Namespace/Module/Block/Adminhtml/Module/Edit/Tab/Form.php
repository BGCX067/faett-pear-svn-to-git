<?php

/**
 * <Namespace>_<Module>_Block_Adminhtml_<Module>_Edit_Tab_Form
 *
 * NOTICE OF LICENSE
 * 
 * <Namespace>_<Module> is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * <Namespace>_<Module> is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with <Namespace>_<Module>. If not, see <http://www.gnu.org/licenses/>.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade <Namespace>_<Module> to newer
 * versions in the future. If you wish to customize <Namespace>_<Module> for your
 * needs please refer to http://www.faett.net for more information.
 *
 * @category    <Namespace>
 * @package     <Namespace>_<Module>
 * @copyright   Copyright (c) 2009 <info@faett.net> - faett.net
 * @license     <http://www.gnu.org/licenses/> 
 * 			    GNU General Public License (GPL 3)
 * @link	    http://www.faett.net
 * @link	    http://www.techdivision.com
 */

/**
 * @category    <Namespace>
 * @package    	<Namespace>_<Module>
 * @copyright  	Copyright (c) 2009 <info@faett.net> - faett.net
 * @license    	http://opensource.org/licenses/osl-3.0.php
 * 				Open Software License (OSL 3.0)
 * @author      faett.net <info@faett.net>
 */
class <Namespace>_<Module>_Block_Adminhtml_<Module>_Edit_Tab_Form 
	extends Mage_Adminhtml_Block_Widget_Form {
		
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset(
      		'<module>_form', array(
      			'legend' => Mage::helper('<module>')->__('Item information')
      		)
      	);

      	$fieldset->addField('title', 'text', array(
          	'label'     => Mage::helper('<module>')->__('Title'),
          	'class'     => 'required-entry',
          	'required'  => true,
          	'name'      => 'title',
      	));

      	$fieldset->addField('filename', 'file', array(
          	'label'     => Mage::helper('<module>')->__('File'),
          	'required'  => false,
          	'name'      => 'filename',
	  	));

      	$fieldset->addField('status', 'select', array(
          	'label'     => Mage::helper('<module>')->__('Status'),
          	'name'      => 'status',
          	'values'    => array(
            	array(
                  	'value'     => 1,
                  	'label'     => Mage::helper('<module>')->__('Enabled'),
              	),

              	array(
                  	'value'     => 2,
                  	'label'     => Mage::helper('<module>')->__('Disabled'),
              	),
          	),
      	));

      	$fieldset->addField('content', 'editor', array(
          	'name'      => 'content',
          	'label'     => Mage::helper('<module>')->__('Content'),
          	'title'     => Mage::helper('<module>')->__('Content'),
          	'style'     => 'width:700px; height:500px;',
          	'wysiwyg'   => false,
          	'required'  => true,
      	));

      	if ( Mage::getSingleton('adminhtml/session')->get<Module>Data() )
      	{
          	$form->setValues(Mage::getSingleton('adminhtml/session')->get<Module>Data());
          	Mage::getSingleton('adminhtml/session')->set<Module>Data(null);
      	} elseif ( Mage::registry('<module>_data') ) {
          	$form->setValues(Mage::registry('<module>_data')->getData());
      	}
      	
      	return parent::_prepareForm();
  	}
}