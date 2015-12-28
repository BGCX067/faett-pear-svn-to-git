<?php

/**
 * <Namespace>_<Module>_IndexController
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
class <Namespace>_<Module>_IndexController
    extends Mage_Core_Controller_Front_Action {
    	
    public function indexAction()
    {
		$this->loadLayout();
		$this->renderLayout();
    }
}