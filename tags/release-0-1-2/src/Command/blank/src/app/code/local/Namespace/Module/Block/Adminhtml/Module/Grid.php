<?php

/**
 * <Namespace>_<Module>_Block_Adminhtml_<Module>_Grid
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
 * @category   	<Namespace>
 * @package    	<Namespace>_<Module>
 * @copyright  	Copyright (c) 2009 <info@faett.net> - faett.net
 * @license    	http://opensource.org/licenses/osl-3.0.php
 * 				Open Software License (OSL 3.0)
 * @author      faett.net - Core Team <info@faett.net>
 */
class <Namespace>_<Module>_Block_Adminhtml_<Module>_Grid
    extends Mage_Adminhtml_Block_Widget_Grid {
  
    public function __construct()
  	{
      	parent::__construct();
      	$this->setId('<module>Grid');
      	$this->setDefaultSort('<module>_id');
      	$this->setDefaultDir('ASC');
      	$this->setSaveParametersInSession(true);
  	}

  	protected function _prepareCollection()
  	{
      	$collection = Mage::getModel('<module>/<module>')->getCollection();
      	$this->setCollection($collection);
      	return parent::_prepareCollection();
  	}

  	protected function _prepareColumns()
  	{
      	$this->addColumn('<module>_id', array(
          	'header'    => Mage::helper('<module>')->__('ID'),
          	'align'     =>'right',
          	'width'     => '50px',
          	'index'     => '<module>_id',
      	));

      	$this->addColumn('title', array(
          	'header'    => Mage::helper('<module>')->__('Title'),
          	'align'     =>'left',
          	'index'     => 'title',
      	));

      	$this->addColumn('status', array(
          	'header'    => Mage::helper('<module>')->__('Status'),
          	'align'     => 'left',
          	'width'     => '80px',
          	'index'     => 'status',
          	'type'      => 'options',
          	'options'   => array(
              	1 => 'Enabled',
              	2 => 'Disabled',
          	),
      	));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('<module>')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('<module>')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

		$this->addExportType('*/*/exportCsv', Mage::helper('<module>')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('<module>')->__('XML'));

      	return parent::_prepareColumns();
  	}

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('<module>_id');
        $this->getMassactionBlock()->setFormFieldName('<module>');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('<module>')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('<module>')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('<module>/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('<module>')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('<module>')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  	public function getRowUrl($row)
  	{
      	return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  	}
}