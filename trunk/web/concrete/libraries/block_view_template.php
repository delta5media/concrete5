<?
defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @package Blocks
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */

/**
 * An object that represents a block's template, whether it's built-in, or custom.
 *
 * @package Blocks
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @category Concrete
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 *
 */

class BlockViewTemplate {

	private $basePath = '';
	
	private $bFilelname;
	private $btHandle;
	private $obj;
	private $baseURL;
	private $checkHeaderItems = true;
	private $itemsToCheck = array(
		'CSS' => 'view.css', 
		'JAVASCRIPT' => 'view.js'
	);
	
	public function __construct($obj) {
		$this->btHandle = $obj->getBlockTypeHandle();
		$this->obj = $obj;
		if ($obj instanceof Block) {
			$this->bFilename = $obj->getBlockFilename();
		}
		$this->computeView();
	}
	
	private function computeView() {
		$bFilename = $this->bFilename;
		$obj = $this->obj;
		
		// if we've passed in "templates/" as the first part, we strip that off.
		if (strpos($bFilename, 'templates/') === 0) {
			$bFilename = substr($bFilename, 10);
		}

		if ($bFilename) {
			if (is_file(DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename)) {
				$template = DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
				$this->baseURL = DIR_REL . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/';
				$this->checkHeaderItems = false;
			} else if (is_file(DIR_FILES_BLOCK_TYPES_CORE . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename)) {
				$template = DIR_FILES_BLOCK_TYPES_CORE . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
				$this->baseURL = ASSETS_URL . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/';
				$this->checkHeaderItems = false;
			} else if (is_dir(DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename)) {
				$template = DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename . '/' . FILENAME_BLOCK_VIEW;
				$this->baseURL = DIR_REL . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
			} else if (is_dir(DIR_FILES_BLOCK_TYPES_CORE . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename)) {
				$template = DIR_FILES_BLOCK_TYPES_CORE . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename . '/'  . FILENAME_BLOCK_VIEW;
				$this->baseURL = ASSETS_URL . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
			}

			// we check all installed packages
			if (!isset($template)) {
				$pl = PackageList::get();
				$packages = $pl->getPackages();
				foreach($packages as $pkg) {
					$d = (is_dir(DIR_PACKAGES . '/' . $pkg->getPackageHandle())) ? DIR_PACKAGES . '/'. $pkg->getPackageHandle() : DIR_PACKAGES_CORE . '/'. $pkg->getPackageHandle();
					$this->baseURL = (is_dir(DIR_PACKAGES . '/' . $pkg->getPackageHandle())) ? DIR_REL . '/' . DIRNAME_PACKAGES . '/'. $pkg->getPackageHandle() : ASSETS_URL . '/'. DIRNAME_PACKAGES . '/' . $pkg->getPackageHandle();
					if (is_file($d . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename)) {
						$template = $d . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
						$this->baseURL .= '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
						$this->checkHeaderItems = false;
					} else if (is_dir($d . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename)) {
						$template = $d . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename . '/' . FILENAME_BLOCK_VIEW;
						$this->baseURL .= '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES . '/' . $bFilename;
					}
				}
			}
			
		} else if (file_exists(DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '.php')) {
			$template = DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '.php';
			$this->baseURL = DIR_REL . '/' . DIRNAME_BLOCKS . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES;
			$this->checkHeaderItems = false;
		} else if (file_exists(DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '/' . FILENAME_BLOCK_VIEW)) {
			$template = DIR_FILES_BLOCK_TYPES . '/' . $obj->getBlockTypeHandle() . '/' . FILENAME_BLOCK_VIEW;
			$this->baseURL = DIR_REL . '/' . DIRNAME_BLOCKS . '/' . $obj->getBlockTypeHandle() . '/' . DIRNAME_BLOCK_TEMPLATES;
		}
		
		if (!isset($template)) {
			$bv = new BlockView();
			$bv->setBlockObject($obj);
			$template = $bv->getBlockPath(FILENAME_BLOCK_VIEW) . '/' . FILENAME_BLOCK_VIEW;
			$this->baseURL = $bv->getBlockURL();
		}
		
		$this->basePath = dirname($template);
		$this->template = $template;
	}
	
	
	public function getBasePath() {return $this->basePath;}
	public function getBaseURL() {return $this->baseURL;}
	public function setBlockCustomTemplate($bFilename) {
		$this->bFilename = $bFilename;
		$this->computeView();
	}
	
	public function getTemplate() {
		return $this->template;
	}
	
	public function getTemplateHeaderItems() {
		$items = array();
		$h = Loader::helper("html");
		if ($this->checkHeaderItems == false) {
			return $items;
		} else {
			foreach($this->itemsToCheck as $t => $i) {
				if (file_exists($this->basePath . '/' . $i)) {
					switch($t) {
						case 'CSS':
							$items[] = $h->css($this->getBaseURL() . '/' . $i);
							break;
						case 'JAVASCRIPT':
							$items[] = $h->javascript($this->getBaseURL() . '/' . $i);
							break;
					}
				}
			}
			return $items;
		}
	}



}
	