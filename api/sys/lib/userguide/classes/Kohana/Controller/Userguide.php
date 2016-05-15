<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User guide based on Kohana guide module.
 *
 * @package    Kohana/Userguide
 * @category   Controllers
 * @author     Kohana Team
 */
abstract class Kohana_Controller_Userguide extends Controller_Template {

	public $template = 'userguide/template';

	// Routes
	protected $media; 
	protected $guide;

	public function before()
	{
		parent::before();


		if ($this->request->action() === 'media')
		{
			// Do not template media files
			$this->auto_render = FALSE;
		}
		else
		{
			// Grab the necessary routes
			$this->media = Route::get('media');
			$this->guide = Route::get('docs/guide');

			// Set the base URL for links and images
			Kodoc_Markdown::$base_url  = URL::site($this->guide->uri()).'/';
			Kodoc_Markdown::$image_url = URL::site($this->media->uri(array('file' => 'vendor/')));
		}

		// Default show_comments to config value
		$this->template->show_comments = Kohana::$config->load('userguide.show_comments');
	}
	
	// List all modules that have userguides
	public function index()
	{
		$this->template->title = "Userguide";
		$this->template->breadcrumb = array('User Guide');
		$this->template->content = View::factory('userguide/index', array('modules' => $this->_modules()));
		$this->template->menu = View::factory('userguide/menu', array('modules' => $this->_modules()));
		
		// Don't show disqus on the index page
		$this->template->show_comments = FALSE;
	}
	
	// Display an error if a page isn't found
	public function error($message)
	{
		$this->response->status(404);
		$this->template->title = "Userguide - Error";
		$this->template->content = View::factory('userguide/error',array('message' => $message));
		
		// Don't show disqus on error pages
		$this->template->show_comments = FALSE;

		// If we are in a module and that module has a menu, show that
		if ($module = $this->request->param('module') AND $menu = $this->file($module.'/menu') AND Kohana::$config->load('userguide.apidoc.'.$module.'.enabled'))
		{
			// Namespace the markdown parser
			Kodoc_Markdown::$base_url  = URL::site($this->guide->uri()).'/'.$module.'/';
			Kodoc_Markdown::$image_url = URL::site($this->media->uri()).'/'.$module.'/';

			$this->template->menu = Kodoc_Markdown::markdown($this->_get_all_menu_markdown());
			$this->template->breadcrumb = array(
				$this->guide->uri() => 'User Guide',
				$this->guide->uri(array('module' => $module)) => Kohana::$config->load('userguide.apidoc.'.$module.'.name'),
				'Error'
			);
		}
		// Otherwise, show the userguide module menu on the side
		else
		{
			$this->template->menu = View::factory('userguide/menu',array('modules' => $this->_modules()));
			$this->template->breadcrumb = array($this->request->route()->uri() => 'User Guide','Error');
		}
	}

	public function action_docs()
	{
		$module = $this->request->param('module');
		$page = $this->request->param('page');

		// Trim trailing slash
		$page = rtrim($page, '/');

		// If no module provided in the url, show the user guide index page, which lists the modules.
		if ( ! $module)
		{
			return $this->index();
		}
		
		// If this module's userguide pages are disabled, show the error page
		if ( ! Kohana::$config->load('userguide.apidoc.'.$module.'.enabled'))
		{
			return $this->error('That module doesn\'t exist, or has userguide pages disabled.');
		}
		
		// Prevent "guide/module" and "guide/module/index" from having duplicate content
		if ( $page == 'index')
		{
			return $this->error('Userguide page not found');
		}
		
		// If a module is set, but no page was provided in the url, show the index page
		if ( ! $page )
		{
			$page = 'index';
		}

		// Find the markdown file for this page
		$file = $this->file($module.'/'.$page);

		// If it's not found, show the error page
		if ( ! $file)
		{
			return $this->error('Userguide page not found');
		}
		
		// Namespace the markdown parser
		Kodoc_Markdown::$base_url  = URL::site($this->guide->uri()).'/'.$module.'/';
		Kodoc_Markdown::$image_url = URL::site($this->media->uri()).'/'.$module.'/';

		// Set the page title
		$this->template->title = $page == 'index' ? Kohana::$config->load('userguide.apidoc.'.$module.'.name') : $this->title($page);

		// Parse the page contents into the template
		Kodoc_Markdown::$show_toc = true;
		$this->template->content = Kodoc_Markdown::markdown(file_get_contents($file));
		Kodoc_Markdown::$show_toc = false;

		// Attach this module's menu to the template
		$this->template->menu = Kodoc_Markdown::markdown($this->_get_all_menu_markdown());

		// Bind the breadcrumb
		$this->template->bind('breadcrumb', $breadcrumb);
		
		// Bind the copyright
		$this->template->copyright = Kohana::$config->load('userguide.apidoc.'.$module.'.copyright');

		// Add the breadcrumb trail
		$breadcrumb = array();
		$breadcrumb[$this->guide->uri()] = 'User Guide';
		$breadcrumb[$this->guide->uri(array('module' => $module))] = Kohana::$config->load('userguide.apidoc.'.$module.'.name');
		
		// TODO try and get parent category names (from menu).  Regex magic or javascript dom stuff perhaps?
		
		// Only add the current page title to breadcrumbs if it isn't the index, otherwise we get repeats.
		if ($page != 'index')
		{
			$breadcrumb[] = $this->template->title;
		}
	}


	public function after()
	{
		if ($this->auto_render)
		{
			// Get the media route
			$media = Route::get('media');

			// Add styles
			$this->template->styles = array(
				$media->uri(array('file' => '/vendor/guide/css/print.css'))  => 'print',
				$media->uri(array('file' => '/vendor/guide/css/screen.css')) => 'screen',
				$media->uri(array('file' => '/vendor/guide/css/kodoc.css'))  => 'screen',
				$media->uri(array('file' => '/vendor/guide/css/shCore.css')) => 'screen',
				$media->uri(array('file' => '/vendor/guide/css/shThemeKodoc.css')) => 'screen',
			);

			// Add scripts
			$this->template->scripts = array(
				$media->uri(array('file' => '/vendor/guide/js/jquery.min.js')),
				$media->uri(array('file' => '/vendor/guide/js/jquery.cookie.js')),
				$media->uri(array('file' => '/vendor/guide/js/kodoc.js')),
				// Syntax Highlighter
				$media->uri(array('file' => '/vendor/guide/js/shCore.js')),
				$media->uri(array('file' => '/vendor/guide/js/shBrushPhp.js')),
			);

			// Add languages
			$this->template->translations = Kohana::message('userguide', 'translations');
		}

		return parent::after();
	}

	/**
	 * Locates the appropriate markdown file for a given guide page. Page URLS
	 * can be specified in one of three forms:
	 *
	 *  * userguide/adding
	 *  * userguide/adding.md
	 *  * userguide/adding.markdown
	 *
	 * In every case, the userguide will search the cascading file system paths
	 * for the file guide/userguide/adding.md.
	 *
	 * @param string $page The relative URL of the guide page
	 * @return string
	 */
	public function file($page)
	{

		// Strip optional .md or .markdown suffix from the passed filename
		$info = pathinfo($page);
		if (isset($info['extension'])
			AND (($info['extension'] === 'md') OR ($info['extension'] === 'markdown')))
		{
			$page = $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'];
		}
		return Kohana::find_file('guide', $page, 'md');
	}

	public function section($page)
	{
		$markdown = $this->_get_all_menu_markdown();
		
		if (preg_match('~\*{2}(.+?)\*{2}[^*]+\[[^\]]+\]\('.preg_quote($page).'\)~mu', $markdown, $matches))
		{
			return $matches[1];
		}
		
		return $page;
	}

	public function title($page)
	{
		$markdown = $this->_get_all_menu_markdown();
		
		if (preg_match('~\[([^\]]+)\]\('.preg_quote($page).'\)~mu', $markdown, $matches))
		{
			// Found a title for this link
			return $matches[1];
		}
		
		return $page;
	}
	
	protected function _get_all_menu_markdown()
	{
		// Only do this once per request...
		static $markdown = '';
		
		if (empty($markdown))
		{
			// Get menu items
			$file = $this->file($this->request->param('module').'/menu');
	
			if ($file AND $text = file_get_contents($file))
			{
				// Add spans around non-link categories. This is a terrible hack.
				//echo Debug::vars($text);
				
				//$text = preg_replace('/(\s*[\-\*\+]\s*)(.*)/','$1<span>$2</span>',$text);
				$text = preg_replace('/^(\s*[\-\*\+]\s*)([^\[\]]+)$/m','$1<span>$2</span>',$text);
				//echo Debug::vars($text);
				$markdown .= $text;
			}
			
		}
		
		return $markdown;
	}
	
	// Get the list of modules from the config, and reverses it so it displays in the order the modules are added, but move Kohana to the top.
	protected function _modules()
	{

		$modules = array_reverse(Kohana::$config->load('userguide.modules'));
		
		if (isset($modules['kohana']))
		{
			$kohana = $modules['kohana'];
			unset($modules['kohana']);
			$modules = array_merge(array('kohana' => $kohana), $modules);
		}
		
		// Remove modules that have been disabled via config
		foreach ($modules as $key => $value)
		{
			if ( ! Kohana::$config->load('userguide.apidoc.'.$key.'.enabled'))
			{
				unset($modules[$key]);
			}
		}
		
		return $modules;
	}

} // End Userguide
