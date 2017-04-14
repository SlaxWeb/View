<?php
/**
 * Base View
 *
 * Base view which all View classes should extend from. The Base View handles loading
 * of templates and adding them to the Response object.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View;

use SlaxWeb\Config\Container as Config;
use SlaxWeb\View\AbstractLoader as Loader;
use Symfony\Component\HttpFoundation\Response;

class Base
{
    /**
     * Template name
     *
     * @var string
     */
    public $template = "";

    /**
     * View Data
     *
     * @var array
     */
    public $viewData = [];

    /**
     * Layout
     *
     * @var \SlaxWeb\View\Base
     */
    protected $layout = null;

    /**
     * Sub views
     *
     * @var array<array<\SlaxWeb\View\Base>>
     */
    protected $subViews = [];

    /**
     * Config
     *
     * @var \SlaxWeb\Config\Container
     */
    protected $config = null;

    /**
     * Template Loader
     *
     * @var \SlaxWeb\View\AbstractLoader
     */
    protected $loader = null;

    /**
     * Output
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response = null;

    /**
     * Class constructor
     *
     * Instantiate the view, by assigning its dependencies to the class properties.
     * Set the base directory for the template files, and set the template name
     * if none is already set by an override property and config permits it.
     *
     * @param \SlaxWeb\Config\Container $config Configuration container
     * @param \SlaxWeb\View\AbstractLoader $loader Template file loader
     * @param \Symfony\Component\HttpFoundation\Response $response Response object
     */
    public function __construct(Config $config, Loader $loader, Response $response)
    {
        $this->config = $config;
        $this->loader = $loader;
        $this->response = $response;

        $this->loader->setTemplateDir($config["view.baseDir"]);

        if ($this->template === "" && $config["view.autoTplName"] === true) {
            $class = get_class($this);
            $this->template = str_replace(
                "\\",
                "/",
                str_replace(
                    ltrim($config["view.classNamespace"], "\\"),
                    "",
                    $class
                )
            );
        }
    }

    /**
     * Set Layout
     *
     * Sets the received View class as layout if it is supplied. If no parameter
     * is set, then the layout will not be used.
     *
     * @param \SlaxWeb\View\Base $layout Layout view class
     * @return self
     */
    public function setLayout(Base $layout = null): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Add SubView
     *
     * Adds a SubView to the local container. The '$name' parameter is the name
     * under which the rendered subview is then available in the main view. Example:
     * When '$name' is 'foo', the variable in the template will be 'subview_foo'.
     *
     * @param string $name Name of the SubView
     * @param \SlaxWeb\View\Base $subView Sub View object extended from the same Base class
     * @return self
     */
    public function addSubView(string $name, Base $subView): self
    {
        $this->subViews[$name][] = $subView;
        return $this;
    }

    /**
     * Add SubTemplate
     *
     * Adds a SubTemplate to the local container. The '$name' parameter is the name
     * under which the rendered subview is then available in the main view. Example:
     * When '$name' is 'foo', the variable in the template will be 'subview_foo'.
     * The SubTemplate is the same as a SubView except it does not provide its own
     * View Class, but is simply rendered using the current instance.
     *
     * @param string $name Name of the SubView
     * @param string $subTemplate Sub Template name
     * @return self
     */
    public function addSubTemplate(string $name, string $subTemplate): self
    {
        $this->subViews[$name][] = $subTemplate;
        return $this;
    }

    /**
     * Render view
     *
     * Renders the view by rendering the template with the provided template loader.
     *
     * @param array $data Template data to be passed to the template. Default []
     * @param int $return Output or return rendered template. Default self::TPL_OUTPUT
     * @param int $cacheData Cache template data. Default self::TPL_CACHE_VARS
     * @return mixed
     */
    public function render(
        array $data = [],
        int $return = Loader::TPL_OUTPUT,
        int $cacheData = Loader::TPL_CACHE_VARS
    ) {
        if (method_exists($this, "preRender")) {
            $this->preRender($data);
        }

        // merge pre-existing view data
        $this->viewData = array_merge($this->viewData, $data);

        // render the subviews
        $this->renderSubViews();

        // set the template name to the loader
        $this->loader->setTemplate($this->template);

        // load main view
        try {
            $buffer = $this->loader->render($this->viewData, Loader::TPL_RETURN, $cacheData);
        } catch (Exception\TemplateNotFoundException $e) {
            // @todo: display error message
            return false;
        }

        // load main view into layout
        if ($this->layout !== null) {
            $buffer = $this->layout->render(
                array_merge($this->viewData, ["mainView" => $buffer]),
                Loader::TPL_RETURN,
                $cacheData
            );
        }

        if ($return === Loader::TPL_RETURN) {
            return $buffer;
        }

        // set rendered template to output object
        $this->response->setContent($this->response->getContent() . $buffer);

        return true;
    }

    /**
     * Render SubViews
     *
     * Render the SubViews and add rendered results to the View Data array.
     *
     * @return void
     */
    protected function renderSubViews()
    {
        foreach ($this->subViews as $name => $views) {
            if (isset($this->viewData["subview_{$name}"]) === false) {
                $this->viewData["subview_{$name}"] = "";
            }

            foreach ($views as $view) {
                if (is_string($view)) {
                    $this->loader->setTemplate($view);
                    $this->viewData["subview_{$name}"] .= $this->loader->render(
                        $this->viewData,
                        Loader::TPL_RETURN,
                        Loader::TPL_CACHE_VARS
                    );
                    continue;
                }
                $this->viewData["subview_{$name}"] .= $view->render(
                    $this->viewData,
                    Loader::TPL_RETURN
                );
            }
        }
    }
}
