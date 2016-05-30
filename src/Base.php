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
 * @version   0.3
 */
namespace SlaxWeb\View;

use SlaxWeb\Config\Container as Config;
use SlaxWeb\View\AbstractLoader as Loader;

class Base
{
    /**
     * Template name
     *
     * @var string
     */
    public $template = "";

    /**
     * Sub views
     *
     * @var array<\SlaxWeb\View\Base>
     */
    protected $_subViews = [];

    /**
     * Config
     *
     * @var \SlaxWeb\Config\Container
     */
    protected $_config = null;

    /**
     * Template Loader
     *
     * @var \SlaxWeb\View\AbstractLoader
     */
    protected $_loader = null;

    /**
     * Class constructor
     *
     * Instantiate the view, by assigning its dependencies to the class properties.
     * Set the base directory for the template files, and set the template name
     * if none is already set by an override property and config permits it.
     *
     * @param \SlaxWeb\Config\Container $config Configuration container
     * @param \SlaxWeb\View\AbstractLoader $loader Template file loader
     * @return void
     */
    public function __construct(Config $config, Loader $loader)
    {
        $this->_config = $config;
        $this->_loader = $loader;

        $this->_loader->setTemplateDir($config["view.baseDir"]);

        if ($this->template === "" && $config["view.autoTplName"] === true) {
            $class = get_class($this);
            $this->template = substr($class, strrpos($class, "\\") + 1);
        }
    }

    /**
     * Add SubView
     *
     * Adds a SubView to the local container. The '$name' parameter is the name
     * under which the rendered subview is then available in the main view.
     *
     * @param string $name Name of the SubView
     * @param \SlaxWeb\View\Base $subView Sub View object extended from the same Base class
     * @return self
     */
    public function addSubView(string $name, Base $subView): self
    {
        $this->_subViews[$name] = $subView;
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
        $this->_loader->setTemplate($this->template);
        try {
            $buffer = $this->_loader->render($data, $return, $cacheData);
        } catch (Exception\TemplateNotFoundException $e) {
            // @todo: display error message
            return false;
        }
        if ($return === Loader::TPL_RETURN) {
            return $buffer;
        }

        return true;
    }
}
