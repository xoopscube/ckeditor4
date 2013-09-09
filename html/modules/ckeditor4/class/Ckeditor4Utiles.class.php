<?php

if(!defined('XOOPS_ROOT_PATH'))
{
	exit;
}

class Ckeditor4_Utils
{
	const DIRNAME = 'ckeditor4';
	const DHTMLTAREA_DEFAULT_COLS = 50;
	const DHTMLTAREA_DEFAULT_ROWS = 15;
	const DHTMLTAREA_DEFID_PREFIX = 'ckeditor4_form_';
	
	/**
	 * getModuleConfig
	 *
	 * @param   string  $key
	 *
	 * @return  XoopsObjectHandler
	 **/
	public static function getModuleConfig( $key = null )
	{
		static $conf;
		
		if (is_null($conf)) {
			$handler = self::getXoopsHandler('config');
			if (method_exists($handler, 'getConfigsByDirname')) {
				$conf = $handler->getConfigsByDirname(self::DIRNAME);
			} else {
				global $xoopsDB;
				$conf = array();
				$modules_tbl = $xoopsDB->prefix("modules");
				$config_tbl = $xoopsDB->prefix("config");
				$sql = 'SELECT conf_name, conf_value FROM '.$config_tbl.' c, '.$modules_tbl.' m WHERE c.conf_modid=m.mid AND m.dirname=\''.self::DIRNAME.'\'';
				if ($result = $xoopsDB->query($sql)) {
					while($arr = $xoopsDB->fetchRow($result)) {
						$conf[$arr[0]] = $arr[1];
					}
				}
			}
		}
		if ($key) {
			return (isset($conf[$key])) ? $conf[$key] : null;
		} else {
			return $conf;
		}
	}
	
	/**
	 * &getXoopsHandler
	 *
	 * @param   string  $name
	 * @param   bool  $optional
	 *
	 * @return  XoopsObjectHandler
	 **/
	public static function &getXoopsHandler(/*** string ***/ $name,/*** bool ***/ $optional = false)
	{
		// TODO will be emulated xoops_gethandler
		return xoops_gethandler($name, $optional);
	}
	
	public static function getMid() {
		$mHandler =& self::getXoopsHandler('module');
		$xoopsModule = $mHandler->getByDirname(self::DIRNAME);
		return $xoopsModule->getVar('mid');
	}
	
	public static function getJS(&$params)
	{
		static $finder, $isAdmin, $isUser, $inSpecialGroup, $confCss, $confHeadCss, $moduleUrl;
		
		$params['name'] = trim($params['name']);
		$params['class'] = isset($params['class']) ? trim($params['class']) : '';
		$params['cols'] = isset($params['cols']) ? intval($params['cols']) : self::DHTMLTAREA_DEFAULT_COLS;
		$params['rows'] = isset($params['rows']) ? intval($params['rows']) : self::DHTMLTAREA_DEFAULT_ROWS;
		$params['value'] = isset($params['value']) ? $params['value'] : '';
		$params['id'] = isset($params['id']) ? trim($params['id']) : self::DHTMLTAREA_DEFID_PREFIX . $params['name'];
		$params['editor'] = isset($params['editor']) ? trim($params['editor']) : 'bbcode';
		$params['toolbar'] = isset($params['toolbar']) ? trim($params['toolbar']) : null;
		$params['style'] = isset($params['style']) ? trim($params['style']) : '';
		
		if (!empty($params['editor']) && $params['editor'] !== 'none' && (!$params['class'] || !preg_match('/\b'.preg_quote($params['editor']).'\b/', $params['class']))) {
			if (! $params['class']) {
				$params['class'] = $params['editor'];
			} else {
				$params['class'] .= ' ' . $params['editor'];
			}
		}
		
		// rlazy registering & call pre build delegate
		if (defined('XOOPS_CUBE_LEGACY')) {
			$delegate = new XCube_Delegate();
			$delegate->register('Ckeditor4.Utils.PreBuild_ckconfig');
			$delegate->call(new XCube_Ref($params));
		} else {
			self::doFilter('config', 'PreBuild', $params);
		}
		
		$script = '';
		if ($params['editor'] !== 'plain' && $params['editor'] !== 'none') {
			
			$conf = self::getModuleConfig();
			
			if (is_null($finder)) {
		
				// Get X-elFinder module
				$mHandler =& self::getXoopsHandler('module');
				//$xoopsModule = $mHandler->getByDirname(self::DIRNAME);
				$mObj = $mHandler->getByDirname($conf['xelfinder']);
				$finder = is_object($mObj)? $conf['xelfinder'] : '';
		
				if (defined('LEGACY_BASE_VERSION')) {
					$root =& XCube_Root::getSingleton();
					$xoopsUser = $root->mContext->mXoopsUser;
				} else {
					global $xoopsUser;
				}
				
				// Check in a group
				$isAdmin = false;
				$isUser = false;
				$mGroups = array(XOOPS_GROUP_ANONYMOUS);
				if (is_object($xoopsUser)) {
					if ($xoopsUser->isAdmin(self::getMid())) {
						$isAdmin = true;
					}
					$isUser = true;
					$mGroups = $xoopsUser->getGroups();
				}
				$inSpecialGroup = (array_intersect($mGroups, ( !empty($conf['special_groups'])? $conf['special_groups'] : array() )));
				
				// moduleUrl
				$moduleUrl = defined('XOOPS_MODULE_URL')? XOOPS_MODULE_URL : XOOPS_URL . '/modules';
				
				// make CSS data
				$confCss = array();
				$confHeadCss = 'false';
				$conf['contentsCss'] = trim($conf['contentsCss']);
				if ($conf['contentsCss']) {
					foreach(preg_split('/[\r\n]+/', $conf['contentsCss']) as $_css) {
						$_css = trim($_css);
						if ($_css === '<head>') {
							$confHeadCss = 'true';
						} else if ($_css){
							$confCss[] = $_css;
						}
					}
				}
				
				// themes contents.css
				$_themeCss = '/themes/' . $GLOBALS['xoopsConfig']['theme_set'] . '/ckeditor4/contents.css';
				if (is_file(XOOPS_ROOT_PATH . $_themeCss)) {
					$confCss[] = XOOPS_URL . $_themeCss;
				}
				
				// editor_reset.css
				$confCss[] = $moduleUrl . '/ckeditor4/templates/editor_reset.css';
				
				//if (preg_match('#/admin/#', $_SERVER['REQUEST_URI'])) {
				if (defined('_AD_NORIGHT')) { // html/language/[LANG]/admin.php
					$confHeadCss = 'false';
				}
			}
		
			// Make config
			$config = array();
			
			$config['contentsCss'] = array();
			$config['removePlugins'] = '';
			$config['extraPlugins'] = '';
			if (defined('XOOPS_CUBE_LEGACY')) {
				$delegate->register('Ckeditor4.Utils.PreParseBuild_ckconfig');
				$delegate->call(new XCube_Ref($config), $params);
				if ($config['contentsCss'] && ! is_array($config['contentsCss'])) {
					$config['contentsCss'] = array($config['contentsCss']);
				}
			} else {
				self::doFilter('config', 'PreParseBuild', $config, $params);
			}
			
			// Parse params
			if (! is_null($params['toolbar'])) {
				$config['toolbar'] = $params['toolbar'];
			}
			
			$config['xoopscodeXoopsUrl'] = XOOPS_URL . '/';
				
			if ($finder) {
				$config['filebrowserBrowseUrl'] = $moduleUrl . '/' . $finder . '/manager.php?cb=ckeditor';
			}
				
			$config['removePlugins'] = 'save,newpage,forms,preview,print' . ($config['removePlugins']? (',' . trim($config['removePlugins'], ',')) : '');
			if ($params['editor'] !== 'html') {
				$conf['extraPlugins'] = $conf['extraPlugins']? 'xoopscode,' . tirm($conf['extraPlugins']) : 'xoopscode';
				$config['fontSize_sizes'] = 'xx-small;x-small;small;medium;large;x-large;xx-large';
				//$config['removePlugins'] .= ',bidi,flash,iframe,indent,justify,list,pagebreak,pastefromword,preview,resize,table,tabletools,templates';
			}
			$config['extraPlugins'] = trim($conf['extraPlugins']) . ($config['extraPlugins']? (',' . trim($config['extraPlugins'], ',')) : '');
			
			$config['customConfig'] = trim($conf['customConfig']);
			$config['enterMode'] = (int)$conf['enterMode'];
			$config['shiftEnterMode'] = (int)$conf['shiftEnterMode'];
			if ($conf['allowedContent']) $config['allowedContent'] = true;
			$config['autoParagraph'] = (bool)$conf['autoParagraph'];
			
			if (! isset($config['toolbar'])) {
				if ($params['editor'] === 'bbcode') {
					$config['toolbar'] = trim($conf['toolbar_bbcode']);
				} else if ($isAdmin) {
					$config['toolbar'] = trim($conf['toolbar_admin']);
				} else if ($inSpecialGroup) {
					$config['toolbar'] = trim($conf['toolbar_special_group']);
				} else if ($isUser) {
					$config['toolbar'] = trim($conf['toolbar_user']);
				} else {
					$config['toolbar'] = trim($conf['toolbar_guest']);
				}
			}
			if (strtolower($config['toolbar']) === 'full') {
				$config['toolbar'] = null;
			}
			
			$config['contentsCss'] = array_merge($config['contentsCss'], $confCss);
			
			self::setCKConfigSmiley($config);
			
			$modeSource = 0;
			$params['value'] = str_replace('&lt;!--ckeditor4FlgSource--&gt;', '', $params['value'], $modeSource);
			if ($modeSource) $config['startupMode'] = 'source';
			
			// lazy registering & call post build delegate
			if (defined('XOOPS_CUBE_LEGACY')) {
				$delegate->register('Ckeditor4.Utils.PostBuild_ckconfig');
				$delegate->call(new XCube_Ref($config), $params);
			} else {
				self::doFilter('config', 'PostBuild', $config, $params);
			}
			
			// Make config json
			$config_json = array();
			foreach($config as $key => $val) {
				if ($val[0] !== '[') {
					$val = json_encode($val);
				}
				$config_json[] = '"' . $key . '":' . $val;
			}
			$config_json = '{' .join($config_json, ','). '}';
				
			// Make Script
			$id = $params['id'];
			$script = <<<EOD
var ckconfig_{$id} = {$config_json} ;
if (! ckconfig_{$id}.width) {
	ckconfig_{$id}.width = $('#{$id}').parent().width() + 'px';
}
var headCss = $.map($("head link[rel='stylesheet']").filter("[media!='print'][media!='handheld']"), function(o){ return o.href; });
if ({$confHeadCss} && headCss) ckconfig_{$id}.contentsCss = headCss.concat(ckconfig_{$id}.contentsCss);
CKEDITOR.replace( "{$id}", ckconfig_{$id} ) ;
CKEDITOR.instances.{$id}.on("blur",function(e){e.editor.updateElement();});
CKEDITOR.instances.{$id}.on("instanceReady",function(e) {
	// For FormValidater (d3forum etc...)
	if (! $('#{$id}').val()) $('#{$id}').val("&nbsp;");
	// For textarea_inserter
	if (!!$('.{$id}_textarea_inserter')) {
		$('.{$id}_textarea_inserter').hide();
	}
	// For d3forum quote button
	if (!!$('input#quote')) {
		$('input#quote').hide();
	}
});
CKEDITOR.instances.{$id}.on("getData",function(e){
	if (e.editor.mode == 'source') {
		e.data.dataValue += '<!--ckeditor4FlgSource-->';
	}
});
CKEDITOR.instances.{$id}.on("setData",function(e){
	e.data.dataValue = e.data.dataValue.replace('<!--ckeditor4FlgSource-->', '');
});
EOD;
		}
		return $script;
	}
	
	private static function getSmiley()
	{
		static $smiley;
		if (is_null($smiley)) {
			$smiley = array();
			$db =& XoopsDatabaseFactory::getDatabaseConnection();
			if (_CHARSET !== 'UTF-8') self::setDbClientEncoding('utf8');
			if ($res = $db->query('SELECT code, smile_url, emotion FROM '.$db->prefix('smiles'). ' ORDER BY display DESC, id ASC' )) {
				$baseUrl = str_replace(XOOPS_URL . '/', '', XOOPS_UPLOAD_URL) . '/';
				while ($smile = $db->fetchArray($res)) {
					$smiley['smile_url'][] = $baseUrl . $smile['smile_url'];
					$smiley['emotion'][] = $smile['emotion'];
					$smiley['smileyMap'][$smile['emotion']] = ' ' . $smile['code'];
				}
			}
			if (_CHARSET !== 'UTF-8') self::restoreDbClientEncoding();
		}
		return $smiley;
	}
	
	private static function setCKConfigSmiley(&$config) {
		if ($smileys = self::getSmiley()) {
			$config['smiley_path'] = XOOPS_URL . '/';
			$config['smiley_images'] = $smileys['smile_url'];
			$config['smiley_descriptions'] = $smileys['emotion'];
			$config['xoopscode_smileyMap'] = $smileys['smileyMap'];
		}
	}
	
	private static function setDbClientEncoding($enc) {
		self::restoreDbClientEncoding(false);
		$db =& XoopsDatabaseFactory::getDatabaseConnection();
		$link = (is_object($db->conn) && get_class($db->conn) === 'mysqli')? $db->conn : false;
		if ($link && function_exists('mysqli_set_charset')) {
			mysqli_set_charset($link, $enc);
		} else if (function_exists('mysql_set_charset')) {
			mysql_set_charset($enc);
		} else {
			$db->queryF('SET NAMES \''.$enc.'\'');
		}
	}
	
	private static function restoreDbClientEncoding($set = true) {
		static $enc;
		if (is_null($enc)) {
			$db =& XoopsDatabaseFactory::getDatabaseConnection();
			$res = $db->queryF('SHOW VARIABLES LIKE \'character\_set\_client\'');
			list(, $enc) = $db->fetchRow($res);
		}
		if ($set) {
			self::setDbClientEncoding($enc);
		}
	}
	
	private static function doFilter($base, $phase, &$val, $params = null) {
		static $filterPath;
		
		if (! $filterPath) {
			$filterPath = dirname(dirname(__FILE__)) . '/filters/';
		}
		
		if ($filters = @ glob($filterPath . $base . '/' . $phase . '*.filter.php')) {
			foreach($filters as $filter) {
				include($filter);
				$class = 'ckeditor4Filter' . ucfirst($base) . str_replace('.filter.php', '', basename($filter));
				if (class_exists($class)) {
					$cObj = new $class();
					if (method_exists($cObj, 'filter')) {
						$cObj->filter($val, $params);
					}
					$cObj = null;
				}
			}
		}

	}
}

class Ckeditor4_ParentTextArea extends XCube_ActionFilter
{
	/**
	 *	@public
	 */
	public function render(&$html, $params)
	{
		$js = Ckeditor4_Utils::getJS($params);

		$root =& XCube_Root::getSingleton();
		$renderSystem =& $root->getRenderSystem(XOOPSFORM_DEPENDENCE_RENDER_SYSTEM);

		$renderTarget =& $renderSystem->createRenderTarget('main');
		$renderTarget->setAttribute('legacy_module', 'ckeditor4');
		$renderTarget->setTemplateName("ckeditor4_textarea.html");
		$renderTarget->setAttribute("element", $params);

		$renderSystem->render($renderTarget);

		$html = $renderTarget->getResult();
		if (strpos($params['value'], '&lt;!--norich--&gt;') === false) {
			// Add script into HEAD
			$jQuery = $root->mContext->getAttribute('headerScript');
			$jQuery->addScript($js);
			$jQuery->addLibrary('/modules/ckeditor4/ckeditor/ckeditor.js');
		}
		//return XCUBE_DELEGATE_CHAIN_BREAK;
	}
}