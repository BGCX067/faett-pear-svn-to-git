<?php

/**
 * PEAR_Command_Mage
 *
 * NOTICE OF LICENSE
 * 
 * Faett_PEAR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Faett_PEAR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Faett_PEAR.  If not, see <http://www.gnu.org/licenses/>.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Faett_PEAR to newer
 * versions in the future. If you wish to customize Faett_PEAR for your
 * needs please refer to http://www.faett.net for more information.
 *
 * @category   Faett
 * @package    Faett_PEAR
 * @copyright  Copyright (c) 2009 <tw@faett.net> Tim Wagner
 * @license    <http://www.gnu.org/licenses/> 
 * 			   GNU General Public License (GPL 3)
 */

require_once 'PEAR/Command/Common.php';
require_once 'PEAR/Command/Mage/ContentsGenerator.php';
require_once 'Archive/Tar.php';

/**
 * @category   Faett
 * @package    Faett_PEAR
 * @copyright  Copyright (c) 2009 <tw@faett.net> Tim Wagner
 * @license    <http://www.gnu.org/licenses/> 
 * 			   GNU General Public License (GPL 3)
 * @author     Tim Wagner <tw@faett.net>
 */
class PEAR_Command_Mage extends PEAR_Command_Common
{

    /**
     * The commands implemented in this class.
     * @var array
     */
    public $commands = array(
        'mage-package' => array(
            'summary' => 'Build Magento Package',
            'function' => 'doPackage',
            'shortcut' => 'mp',
            'options' => array(
                'targetdir' => array(
                    'shortopt' => 'T',
                    'doc' => 'Target directory for package file.',
                    'arg' => 'TARGETDIR',
                ),
            ),
           	'doc' => '[descfile] Creates a Magento specific PEAR package from its description file.'
        ),
        'mage-project' => array(
            'summary' => 'Create basic Magento extension',
            'function' => 'doProject',
            'shortcut' => 'mpr',
            'options' => array(
                'targetdir' => array(
                    'shortopt' => 'T',
                    'doc' => 'Target directory for folder sturcture and configuration files.',
                    'arg' => 'TARGETDIR',
                ),
                'module' => array(
                    'shortopt' => 'M',
                    'doc' => 'Name of Extension to create.',
                    'arg' => 'MODULE',
                ),
                'namespace' => array(
                    'shortopt' => 'N',
                    'doc' => 'Namespace of Extension.',
                    'arg' => 'NAMESPACE',
                ),
                'interface' => array(
                    'shortopt' => 'I',
                    'doc' => 'Name of Interface to use.',
                    'arg' => 'INTERFACE',
                ),
                'theme' => array(
                    'shortopt' => 'Z',
                    'doc' => 'Name of Theme to use.',
                    'arg' => 'THEME',
                )
            ),
            'doc' => '[descfile] Creates the basic folder structur and configuration files for a Magento extension.'
        ),
        'generate-contents' => array(
            'summary' => 'Generates contents node in package2.xml',
            'function' => 'doGenerateContents',
            'shortcut' => 'mgc',
            'options' => array(
                'templatefile' => array(
                    'shortopt' => 'T',
                    'doc' => 'Path to template package2.xml',
                    'arg' => 'TEMPLATEFILE',
                ),
                'srcdir' => array(
                    'shortopt' => 'S',
                    'doc' => 'Path to source code folder.',
                    'arg' => 'SRCDIR',
                ),
                'destinationdir' => array(
                    'shortopt' => 'D',
                    'doc' => 'Path to destination folder where to save the generated package2.xml',
                    'arg' => 'DESTINATIONDIR',
                ),
                'modulename' => array(
                    'shortopt' => 'M',
                    'doc' => 'Name of the Module.',
                    'arg' => 'MODULENAME',
                )
            ),
            'doc' => '[descfile] Generates the contents node in a template package2.xml file.'
        )
    );

    /**
     * True if a package.xml file exists, else FALSE.
     * @var boolean
     */
    private $pkginfofile;

    /**
     * The Magento specific roles.
     * @var array
     */
    private $roles;

    /**
     * The content of the package2.xml file to create the package with.
     * @var SimpleXMLElement
     */
    private $xml;

    /**
     * The options passed from the command line when excecuting the command.
     * @var array
     */
    private $options;

    /**
     * Output written after finishing the command execution.
     * @var string
     */
    private $output;

    /**
     * The files defined in the package2.xml.
     * @var array
     */
    private $files = array();

    private $root = './';

    private $shop = null;

    private $message = null;

    /**
     * The capitzalized namespace.
     * @var string
     */
    private $_capNamespace = '';

    /**
     * The lowercase namespace.
     * @var string
     */
    private $_lowNamespace = '';

    /**
     * The capitalized module name.
     * @var string
     */
    private $_capModule = '';

    /**
     * The lowercase module name.
     * @var string
     */
    private $_lowModule = '';

    /**
     * The constructor to initialize the
     * Command with.
     *
     * @param $ui
     * @param $config
     * @return void
     */
    public function PEAR_Command_Mage(&$ui, &$config)
    {
        parent::PEAR_Command_Common($ui, $config);
    }

    /**
     * The method automatically invoked when running the
     * pear with the 'mage-package' command.
     *
     * @param $command
     * @param $options
     * @param $params
     * @return boolean
     */
    public function doPackage($command, $options, $params)
    {
        $this->output = '';
        $this->options = $options;
        $this->pkginfofile = isset($params[0]) ? $params[0] : 'package.xml';

        $this->xml = simplexml_load_file($this->pkginfofile);

        $result = $this->_collectFiles();

        if ($result instanceof PEAR_Error) {
            return $result;
        }

        $result = $this->_generateTarFile();
        if ($result instanceof PEAR_Error) {
            return $result;
        }

        if ($this->output) {
            $this->ui->outputData($this->output, $command);
        }

        return true;
    }

    /**
     *
     * @param $node
     * @param $path
     * @return unknown_type
     */
    public function _collectFiles($node=null, $path='')
    {
        if (is_null($node)) {
            $node = $this->xml->contents->dir;
        }

        if ($node->getName()=='file') {

            $roles = $this->getRoles();
            $roleDir = $this->config->get(
                $roles[(string) $node['role']]['dir_config']
            );

            $filePath = $roleDir.$path;

            if (!is_file($filePath)) {
                $result = new PEAR_Error('Could not find file: '.$filePath);
                return $result;
            }

            $this->files[$roleDir][] = $filePath;

        } elseif ($children = $node->children()) {
            foreach ($children as $child) {

                $result = $this->_collectFiles(
                    $child,
                    $path . DIRECTORY_SEPARATOR . (string) $child['name']
                );

                if ($result instanceof PEAR_Error) {
                    return $result;
                }
            }
        }

        return true;
    }

    /**
     * Creates and compresses the TAR file if parameter compress
     * is set to TRUE (default).
     *
     * @param boolean $compress If TRUE compress the TAR file
     * @return boolean TRUE if the file was successfully created
     */
    public function _generateTarFile($compress = true)
    {
        $pkgver = (string)$this->xml->name.'-'.(string)$this->xml->version->release;
        $targetdir = !empty($this->options['targetdir']) ? $this->options['targetdir'].DIRECTORY_SEPARATOR : '';
        $tarname = $targetdir.$pkgver.($compress ? '.tgz' : '.tar');

        $tar = new Archive_Tar($tarname, $compress);
        $tar->setErrorHandling(PEAR_ERROR_RETURN);

        $result = $tar->create(array($this->pkginfofile));
        if (PEAR::isError($result)) {
            return $this->raiseError($result);
        }

        foreach ($this->files as $roleDir=>$files) {
            $result = $tar->addModify($files, $pkgver, $roleDir);
        }
        if (PEAR::isError($result)) {
            return $this->raiseError($result);
        }

        $this->output .= 'Successfully created '.$tarname."\n";

        return true;
    }

    /**
     * Initializes and returns the array with the available
     * roles for creating a Magento extension package.
     *
     * @return array The available roles
     */
    public function getRoles()
    {
        // if the roles are not initialized
        if (!$this->roles) {
            // initialize them
            $this->roles = array(
                'magelocal' => array('name'=>'Magento Local module file', 'dir_config'=>'mage_local_dir'),
                'magecommunity' => array('name'=>'Magento Community module file', 'dir_config'=>'mage_community_dir'),
                'magecore' => array('name'=>'Magento Core team module file', 'dir_config'=>'mage_core_dir'),
                'magedesign' => array('name'=>'Magento User Interface (layouts, templates)', 'dir_config'=>'mage_design_dir'),
                'mageetc' => array('name'=>'Magento Global Configuration', 'dir_config'=>'mage_etc_dir'),
                'magelib' => array('name'=>'Magento PHP Library file', 'dir_config'=>'mage_lib_dir'),
                'magelocale' => array('name'=>'Magento Locale language file', 'dir_config'=>'mage_locale_dir'),
                'magemedia' => array('name'=>'Magento Media library', 'dir_config'=>'mage_media_dir'),
                'mageskin' => array('name'=>'Magento Theme Skin (Images, CSS, JS)', 'dir_config'=>'mage_skin_dir'),
                'mageweb' => array('name'=>'Magento Other web accessible file', 'dir_config'=>'mage_web_dir'),
                'magetest' => array('name'=>'Magento PHPUnit test', 'dir_config'=>'mage_test_dir'),
                'mage' => array('name'=>'Magento other', 'dir_config'=>'mage_dir'),
                'php' => array('dir_config'=>'php_dir'),
                'data' => array('dir_config'=>'data_dir'),
                'doc' => array('dir_config'=>'doc_dir'),
                'test' => array('dir_config'=>'test_dir'),
                'temp' => array('dir_config'=>'temp_dir'),
            );
        }
        // return the initialized roles
        return $this->roles;
    }
    
    /**
     * Depending on current source folder structure this method generates
     * automatically the contents node in package2.xml
     * 
     * @param $command
     * @param $options
     * @param $params
     * @return void
     */
    public function doGenerateContents($command, $options, $params)
    {
    	// get instance of contentsGenerator
    	$gen = new PEAR_Command_Mage_ContentsGenerator;
    	// clear output
    	$this->output = '';
    	// init $result var
    	$result = null;
    	// get options
    	$this->options = $options;

    	// check all options
        if (!isset($this->options['templatefile'])) {
        	$result = new PEAR_Error('No templatefile given. Please use option -T');
        }
        if (!isset($this->options['srcdir'])) {
        	$result = new PEAR_Error('No sourcedir given. Please use option -S');
        }
        if (!isset($this->options['destinationdir'])) {
        	$result = new PEAR_Error('No destinationdir given. Please use option -D');
        }
        if (!isset($this->options['modulename'])) {
			$result = new PEAR_Error('No modulename given. Please use option -M');
        }
        // display error if optioncheck failed
        if ($result instanceof PEAR_Error) {
            return $result;
        }
        // set vars by options
    	$templatefile = $this->options['templatefile'];
        $srcdir = $this->options['srcdir'];
        $destinationdir = $this->options['destinationdir'];
        $modulename = $this->options['modulename'];
		// add slashes to our dirs if there are none
        if (substr($srcdir, -1) != "/") $srcdir .= "/";
        if (substr($destinationdir, -1) != "/") $destinationdir .= "/";
        // check if template file exists
    	if (!file_exists($templatefile)) {
    		$this->raiseError('Could not find templatefile: '.$templatefile); 
    	}
    	// check if sourcecode dir is there
    	if (!is_dir($srcdir)) {
    		$this->raiseError('Could not find codesource dir: '.$srcdir);
    	}
    	// check if destination dir is there
    	if (!is_dir($destinationdir)) {
    		$this->raiseError('Could not find destination save dir: '.$destinationdir);
    	}
    	// set source template for package2.xml
		$gen->setTemplateFile($templatefile);
		// set sourcecode dir
		$gen->setSrcDir($srcdir);
		// set saveDir
		$gen->setSaveDir($destinationdir);
		// set modulename
		$gen->setModuleName($modulename);
		// import src directory recursively
		$gen->readRecursivDir($gen->getSrcDir());
		// generate contentsNode and add it to templateDOM	
		$gen->generate($gen->getModuleName());
		// save the modified templateDOM
		$gen->getTemplateDOM()->save($gen->getSaveDir().$gen->getGeneratedFile());
    	$this->output = 'Generated '.$gen->getGeneratedFile().' successfully.';
    	// delegate the messages to the user interface
        if ($this->output) {
            $this->ui->outputData($this->output, $command);
        }
    }

    /**
     * The method automatically invoked when running the
     * pear with the 'mage-project' command.
     *
     * @param $command
     * @param $options
     * @param $params
     * @return void
     */
    public function doProject($command, $options, $params)
    {
        // initialize the output string
        $this->output = '';
        // read the passed parameters
        $this->options = $options;
        // initialize the variables
        $module = $this->options['module'];
        $namespace = $this->options['namespace'];
        $targetdir = $this->options['targetdir'];
        $interface = $this->options['interface'];
        $theme = $this->options['theme'];
        // preset the interface name if not specified
        if ($interface == '') {
            $interface = 'default';
        }
        // preset the theme name if not specified
        if ($theme == '') {
            $theme = 'default';
        }
        // check that the target directory was given
        if ($targetdir == '') {
            $this->raiseError('Invalid target dir: ' . $targetdir);
        }
        // capitalizes und lowercase the namespace and the module name
        $this->_capNamespace = ucfirst($namespace);
        $this->_lowNamespace = strtolower($namespace);
        $this->_capModule = ucfirst($module);
        $this->_lowModule = strtolower($module);
        // create the array with the source file names
        $fromFiles = array(
            'data/Faett_PEAR/Command/blank/buildpath',
            'data/Faett_PEAR/Command/blank/project',
            'data/Faett_PEAR/Command/blank/build.xml',
            'data/Faett_PEAR/Command/blank/build.properties',
            'data/Faett_PEAR/Command/blank/build.default.properties',
            'data/Faett_PEAR/Command/blank/phpunit-to-hudson.xsl',
            'data/Faett_PEAR/Command/blank/pear/package2.xml',
            'data/Faett_PEAR/Command/blank/src/app/etc/modules/Namespace_Module.xml',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Module.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/controllers/IndexController.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/etc/config.xml',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Model/Module.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Model/Mysql4/Module.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Model/Mysql4/Module/Collection.php',
    		'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Model/Status.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/sql/module_setup/mysql4-install-0.1.0.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Adminhtml/Module.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Adminhtml/Module/Edit.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Adminhtml/Module/Grid.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Adminhtml/Module/Edit/Form.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Adminhtml/Module/Edit/Tabs.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Block/Adminhtml/Module/Edit/Tab/Form.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/controllers/Adminhtml/ModuleController.php',
            'data/Faett_PEAR/Command/blank/src/app/code/local/Namespace/Module/Helper/Data.php',
            'data/Faett_PEAR/Command/blank/src/app/design/frontend/interface/theme/layout/module.xml',
            'data/Faett_PEAR/Command/blank/src/app/design/frontend/interface/theme/template/module/module.phtml',
    		'data/Faett_PEAR/Command/blank/src/app/design/adminhtml/interface/theme/layout/module.xml',
        );
        // create the array with the target file names
        $toFiles = array(
            $this->_capNamespace . '_' . $this->_capModule . '/.buildpath',
            $this->_capNamespace . '_' . $this->_capModule . '/.project',
            $this->_capNamespace . '_' . $this->_capModule . '/build.xml',
            $this->_capNamespace . '_' . $this->_capModule . '/build.properties',
            $this->_capNamespace . '_' . $this->_capModule . '/build.default.properties',
            $this->_capNamespace . '_' . $this->_capModule . '/phpunit-to-hudson.xsl',
            $this->_capNamespace . '_' . $this->_capModule . '/pear/package2.xml',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/etc/modules/' . $this->_capNamespace . '_' . $this->_capModule . '.xml',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/' . $this->_capModule . '.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/controllers/IndexController.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/etc/config.xml',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Model/' . $this->_capModule . '.php',
			$this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Model/Mysql4/' . $this->_capModule . '.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Model/Mysql4/' . $this->_capModule . '/Collection.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Model/Status.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/sql/' . $this->_lowModule . '_setup/mysql4-install-0.1.0.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/Adminhtml/' . $this->_capModule . '.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/Adminhtml/' . $this->_capModule . '/Edit.php',
			$this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/Adminhtml/' . $this->_capModule . '/Grid.php',
			$this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/Adminhtml/' . $this->_capModule . '/Edit/Form.php',
			$this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/Adminhtml/' . $this->_capModule . '/Edit/Tabs.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Block/Adminhtml/' . $this->_capModule . '/Edit/Tab/Form.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/controllers/Adminhtml/' . $this->_capModule . 'Controller.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/code/local/' . $this->_capNamespace . '/' . $this->_capModule. '/Helper/Data.php',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/design/frontend/' . $interface . '/'. $theme . '/layout/' . $this->_lowModule . '.xml',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/design/frontend/' . $interface . '/'. $theme . '/template/' . $this->_lowModule .'/' . $this->_lowModule.'.phtml',
            $this->_capNamespace . '_' . $this->_capModule . '/src/app/design/adminhtml/' . $interface . '/'. $theme . '/layout/' . $this->_lowModule . '.xml'
        );
        // check if at least a module name and the namespace are set
    	if (!empty($module) && !empty($namespace)) {
            // if yes, copy the blanko files and replace the placeholders
    		$this->_copyBlankoFiles($fromFiles, $toFiles, $targetdir);
    		$this->_insertCustomVars($toFiles, $targetdir);
            // write a log message that the file has been generated successfully
    		foreach ($toFiles as $file) {
    			$this->output .= "Generate " . $file . "\n";
    		}
            // attach a log message that command was finished successfully
    		$this->output .= "\n" . 'New Project '
    		              . $this->_capNamespace . '_'
    		              . $this->_capModule
    		              . ' successfully generated!';
    	} else {
    	    // write an error message
    		$this->output = 'Please at least namespace and module name.';
    	}
        // delegate the messages to the user interface
        if ($this->output) {
            $this->ui->outputData($this->output, $command);
        }
    }

    /**
     * Copies the template files to the target directory.
     *
     * @param mixed $from Array or string with file names to copy from
     * @param mixed $to Array or string with file names to copy to
     * @param string $targetdir The target directory to create the project in
     * @return boolean TRUE if the files have been copied successfully
     */
    private function _copyBlankoFiles($from, $to, $targetdir = null)
    {
        // check if an array or a string with file names has been passed
        if (!is_array($from)) {
            $from = array($from);
        }
        // check if an array or a string with file names has been passed
        if (!is_array($to)) {
            $to = array($to);
        }
        // check if a target directory has been passed
        if ($targetdir === null) {
            $targetdir = 'new/';
            if (!is_dir($targetdir)) {
                mkdir($targetdir);
            }
        }
        // check if the size of the files to copy from and to copy to is equal
        if (count($from) !== count($to)) {
            $this->raiseError('Count of from -> to files do not match.');
        }
        // create the target folders
        foreach ($to as $file) {
            $newPath = substr($file, 0, strrpos($file, '/'));
            $this->_createFolderPath($newPath, $targetdir);
        }
        // iterate over the files
        for ($i = 0; $i < count($to); $i++) {
            // copy the file
            $copied = file_put_contents(
                $targetdir . $to[$i],
                file_get_contents($from[$i], FILE_USE_INCLUDE_PATH)
            );
            // check if the file has been copied successfully
            if (!$copied) {
                $this->raiseError('Could not copy blanko files.');
            }
        }
        // return TRUE if the files have been copied successfully
        return true;
    }

    /**
     * Creates the target folders.
     *
     * @param mixed $paths Array or string with the folders to create
     * @param string $targetdir The target up from where to create the folders
     * @return boolean Returns TRUE if the folder structure
     * 						   has successfully been created
     */
    private function _createFolderPath($paths, $targetdir = null)
    {
        // check if string or an array has been passed
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        // check if a target directory has been specified
        if ($targetdir === null) {
            $targetdir = getcwd();
        }
        // iterate over the path elements
        foreach ($paths as $path) {
            // split the folder names from the path
            $folders = explode('/', $path);
            $current = '';
            // iterate over the folders and create them
            foreach ($folders as $folder) {
                $fp = $current . DIRECTORY_SEPARATOR . $folder;
                if (!is_dir($targetdir . $fp)) {
                    if (mkdir($targetdir . $fp) === false) {
                        $this->raiseError(
                        	'Could not create new path: ' . $targetdir . $fp
                        );
                    }
                }
                $current = $fp;
            }
        }
        // return TRUE if the folder structure has been created successfully
        return true;
    }

    /**
     * Replaces the placeholders in the passed files.
     *
     * @param mixed $files Array or string with files to
     * 					   replace the placeholders in
     * @param $targetdir The target directory with the files
     * @return void
     */
    private function _insertCustomVars($files, $targetdir = null)
    {
        // check if a string or an array has been passed
        if (!is_array($files)) {
            $files = array($files);
        }
        // check if a target directory has been specified
        if ($targetdir === null) {
            $targetdir = getcwd() . 'new' . DIRECTORY_SEPARATOR;
        }
        // iterate over the files and replace the placeholders
        foreach ($files as $file) {
            $handle = fopen ($targetdir . $file, 'r+');
            $content = '';
            while (!feof($handle)) {
                $content .= fgets($handle);
            }
            fclose($handle);
            $type = strrchr($file, '.');
            switch ($type) {
                case '.xml':
                    $content = $this->_replaceXml($content);
                    break;
                case '.php':
                case '.phtml':
                    $content = $this->_replacePhp($content);
                    break;
                case '.properties':
                    break;
                case '.buildpath':
                    $content = $this->_replacePhp($content);
                    break;
                case '.project':
                    $content = $this->_replaceXml($content);
                    break;
                case '.xsl':
                    break;
                default:
                    $this->raiseError('Unknown file type found: '.$type);
            }
            $handle = fopen($targetdir.$file, 'w');
            fputs($handle, $content);
            fclose($handle);
        }
    }

    /**
     * Replaces placeholders between < and > signs in PHP files.
     *
     * @param string $content The content with the placeholders
     * @return mixed The replaces content
     */
    private function _replacePhp($content)
    {
        // the placeholders to replace
        $search = array(
                        '/<Namespace>/',
                        '/<namespace>/',
                        '/<Module>/',
                        '/<module>/',
       					);
        // the replacements
        $replace = array(
                        $this->_capNamespace,
                        $this->_lowNamespace,
                        $this->_capModule,
                        $this->_lowModule,
                        );
        // replacment itself
        return preg_replace($search, $replace, $content);
    }

    /**
     * Replaces placeholders between < and > signs in XML files.
     *
     * @param string $content The content with the placeholders
     * @return mixed The replaces content
     */
    private function _replaceXml($content)
    {
        // the placeholders to replace
        $search = array(
                        '/\[Namespace\]/',
                        '/\[namespace\]/',
                        '/\[Module\]/',
                        '/\[module\]/',
                        );
        // the replacements
        $replace = array(
                        $this->_capNamespace,
                        $this->_lowNamespace,
                        $this->_capModule,
                        $this->_lowModule,
                        );
        // replacement itself
        return preg_replace($search, $replace, $content);
    }
}