<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract controller class for automatic rendering of template and content view
 * based on controller and action name.
 *
 * @package    Utilities
 * @author     David Evans
 * @copyright  (c) 2009-2010 David Evans
 * @license    MIT-style
 */
abstract class Kohana_Controller_Layout extends Controller {
	
	/**
	 * @var  string  Page template path (transformed into view object by `before()` method)
	 */
	public $template = 'template';
	
	/**
	 * @var  object  Page content view
	 */
	public $content;
	
	/**
	 * @var  boolean  Auto render template
	 **/
	public $auto_render = TRUE;

	/**
	 * If auto_render is enabled, sets up a template and content view.
	 * 
	 * The template will have a `$content` variable which is bound to the
	 * content view. When the template is rendered it should at some
	 * point render `$content`.
	 * 
	 * The content view will not yet have an associated view file, this
	 * will be assigned when `->render()` is called.
	 * 
	 * @return  void
	 */
	public function before()
	{
		parent::before();
		
		if ($this->auto_render === TRUE)
		{
			$this->content = new View();
			
			if ($this->template)
			{
				$this->template = new View($this->template);
				$this->template->bind('content', $this->content);
			}
		}
	}

	/**
	 * If auto_render is enabled and no response has yet been sent, selects
	 * a content view based on the controller and action name, and then 
	 * returns the template as the request response.
	 * 
	 * @return  void
	 */
	public function after()
	{
		if ($this->auto_render === TRUE AND ! $this->request->response)
		{
			$this->render();
		}
		
		parent::after();
	}
	
	/**
	 * Sets an appropriate view file for the content view and then assigns
	 * the template as the request response.
	 * 
	 * @param   string   view to render (relative to controller path)
	 * @param   array    variables to set in the view
	 * @return  void
	 */
	public function render($view_name = NULL, $data = NULL)
	{
		// Allow caller to pass just the $data argument, omitting
		// the $view_name
		if (func_num_args() == 1 AND is_array($view_name))
		{
			$data = $view_name;
			$view_name = NULL;
		}
		
		// Set the view data
		if ( ! empty($data))
		{
			$this->content->set($data);
		}
		
		// If no view name is supplied, use the default which is:
		// <controller-directory>/<controller-name>/<action>
		if ($view_name === NULL)
		{
			$action = empty($this->request->action) ? Route::$default_action : $this->request->action;
			
			$path = $this->add_controller_path($action);
			
			// Special case for the default action: if the standard view does not exist we
			// use the view at <controller-directory>/<controller-name>
			if ($action === Route::$default_action AND ! Kohana::find_file('views', $path))
			{
				$path = $this->add_controller_path('');
			}
		}
		else
		{
			$path = $this->add_controller_path($view_name);
		}
		
		$this->content->set_filename($path);
		
		$this->request->response = $this->template ? $this->template : $this->content;
	}
	
	/**
	 * Injects a new 'sub-template' into the existing template.
	 * 
	 * Note that any view name you supply will be treated as relative to
	 * the controller path. If you expect your controller to be subclassed
	 * and want to ensure that you always refer to the same view, prefix
	 * the view name with a slash which will stop it being treated as
	 * relative.
	 * 
	 * @param   mixed   view object or view name (relative to controller path)
	 * @param   array   variables to set in the view
	 * @return  object  new 'sub-template' view
	 */
	public function extend_template($view, $data = NULL)
	{
		// If a view name is supplied, transform it into a view object
		if (is_string($view))
		{
			$path = $this->add_controller_path($view);
			$view = new View($path);
		}
		
		// Set the view data
		if ( ! empty($data))
		{
			$view->set($data);
		}
		
		// Find the current innermost template, i.e., the template whose
		// $content variable refers to the $this->content view
		$template = $this->template;
		
		while ($template->content !== $this->content)
		{
			$template = $template->content;
		}
		
		// Inject the new view between the previous innermost template
		// and the ->content view, making it the new innermost
		// template
		$template->bind('content', $view);
		$view->bind('content', $this->content);
		
		return $view;
	}
	
	/**
	 * Adds the current controller directory and controller name to the
	 * supplied path.
	 * 
	 * If the path has a leading slash then the controller path will **not**
	 * be added, but the leading slash will be trimmed.
	 * 
	 * @param   string   Controller-relative path (or path with leading slash)
	 * @return  string   Resource path, for use with `Kohana::find_file`
	 */
	public function add_controller_path($path)
	{
		// If path is already 'absolute' then trim slashes and return it
		if (isset($path[0]) AND $path[0] == '/')
		{
			return trim($path, '/');
		}
		
		// Otherwise, prefix the controller directory and name
		else
		{
			return trim($this->request->directory.'/'.$this->request->controller.'/'.$path, '/');
		}
	}
}
