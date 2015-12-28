<?php

/**
 * <Namespace>_<Module>_AllTests
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
 * @category   <Namespace>
 * @package    <Namespace>_<Module>
 * @copyright  Copyright (c) 2009 <tw@faett.net> Tim Wagner
 * @license    <http://www.gnu.org/licenses/> 
 * 			   GNU General Public License (GPL 3)
 */

// set the include path necessary for running the tests
set_include_path(
	get_include_path() . PATH_SEPARATOR
	. getcwd()
);

require_once 'Faett/PEAR/TestSuite.php';

/**
 * The test suite.
 *
 * @category   	<Namespace>
 * @package    	<Namespace>_<Module>
 * @copyright  	Copyright (c) 2009 <tw@faett.net> Tim Wagner
 * @license    	<http://www.gnu.org/licenses/> 
 * 				GNU General Public License (GPL 3)
 * @author      Tim Wagner <tw@faett.net>
 */
class <Namespace>_<Module>_AllTests
{

    /**
     * Initializes the TestSuite.
     *
     * @return Faett_PEAR_TestSuite The initialized TestSuite
     */
    public static function suite() {
        // initialize the TestSuite
        $suite = new Faett_PEAR_TestSuite(
        	'${ant.project.name}',
        	'',
            '${version}'
        );
        // return the initialized suite
        return $suite;
    }
}