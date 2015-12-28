<?php

/**
 * Faett_PEAR_TestSuite
 *
 * NOTICE OF LICENSE
 * 
 * Faett_Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Faett_Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Faett_Manager.  If not, see <http://www.gnu.org/licenses/>.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Faett_Manager to newer
 * versions in the future. If you wish to customize Faett_Manager for your
 * needs please refer to http://www.faett.net for more information.
 *
 * @category   Faett
 * @package    Faett_Manager
 * @copyright  Copyright (c) 2009 <tw@faett.net> Tim Wagner
 * @license    <http://www.gnu.org/licenses/> 
 * 			   GNU General Public License (GPL 3)
 */

require_once 'PHPUnit/Framework.php';
require_once 'Faett/PEAR/xhprof_lib/utils/xhprof_lib.php';
require_once 'Faett/PEAR/xhprof_lib/utils/xhprof_runs.php';

/**
 * This class extends the PHPUnit_Framework_TestSuite and adds profiling
 * functionality provided by XHProf.
 * 
 * @category   	Faett
 * @package    	Faett_Manager
 * @copyright  	Copyright (c) 2009 <tw@faett.net> Tim Wagner
 * @license    	<http://www.gnu.org/licenses/> 
 * 				GNU General Public License (GPL 3)
 * @author      Tim Wagner <tw@faett.net>
 */
class Faett_PEAR_TestSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * The internal run ID to store the profiling result under.
     * @var string
     */
    protected $_runId = 0;

    /**
     * Constructs a new TestSuite:
     *
     *   - PHPUnit_Framework_TestSuite() constructs an empty TestSuite.
     *
     *   - PHPUnit_Framework_TestSuite(ReflectionClass) constructs a
     *     TestSuite from the given class.
     *
     *   - PHPUnit_Framework_TestSuite(ReflectionClass, String)
     *     constructs a TestSuite from the given class with the given
     *     name.
     *
     *   - PHPUnit_Framework_TestSuite(String) either constructs a
     *     TestSuite from the given class (if the passed string is the
     *     name of an existing class) or constructs an empty TestSuite
     *     with the given name.
     *
     * @param $theClass
     * @param $name
     * @param string $runId The run ID used to store the profiling data
     * @return void
     * @see PHPUnit_Framework_TestSuite::__construct($theClass = '', $name = '')
     */
    public function __construct($theClass = '', $name = '', $runId)
    {
        // call the constructor of the parent class
        parent::__construct($theClass, $name);
        // initialize the run ID
        $this->_runId = $runId;
    }

    /**
     * This method extends the method of the parent class to create
     * and store the profiling result file.
     *
     * Runs the tests and collects their result in a TestResult.
     *
     * @param PHPUnit_Framework_TestResult $result
     * @param mixed $filter
     * @param array $groups
     * @param array $excludeGroups
     * @param boolean $processIsolation
     * @return PHPUnit_Framework_TestResult
     * @throws InvalidArgumentException
	 * @see PHPUnit_Framework_TestSuite::run(
	 * 	        PHPUnitFramework_TestResult $result = NULL,
	 * 			$filter = FALSE,
	 * 			array $groups = array(),
	 * 			array $excludeGroups = array(),
	 * 		)
     */
    public function run(
        PHPUnit_Framework_TestResult $result = NULL,
        $filter = FALSE,
        array $groups = array(),
        array $excludeGroups = array()) {
        // check if the XHProf extension is already loaded
        if (!extension_loaded('xhprof')) {
            // if not, run the TestSuite itself and return
            return parent::run($result, $filter, $groups, $excludeGroups);
        }
        // if yes, start profiling
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        // run the TestSuite itself
        $result = parent::run($result, $filter, $groups, $excludeGroups);
        // stop profiling
        $xhprof_data = xhprof_disable();
        // initialize the  runner
        $xhprof_runs = new XHProfRuns_Default();
        // save the profiling data
        $runId = $xhprof_runs->save_run(
            $xhprof_data,
            $nameSpace = $this->getName(),
            $this->_runId
        );
        // return the TestResult
        return $result;
    }
}