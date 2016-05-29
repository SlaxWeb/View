<?php
/**
 * Abstract Loader
 *
 * Abstract loader has to be extended by all template loaders, as it provides some
 * base functionality, properties, and constants.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View;

use \Psr\Log\LoggerInterface as Logger;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractLoader
{
    /**
     * Template variables caching
     */
    const TPL_CACHE_VARS = 100;
    const TPL_NO_CACHE_VARS = 101;

    /**
     * Template render output control
     */
    const TPL_RETURN = 200;
    const TPL_OUTPUT = 201;

    /**
     * Response
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $_response = null;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger = null;

    /**
     * Template file
     *
     * @var string
     */
    protected $_template = "";

    /**
     * Template directory
     *
     * @var string
     */
    protected $_templateDir = "";

    /**
     * Cached template data
     *
     * @var array
     */
    protected $_cachedData = [];

    /**
     * Class constructor
     *
     * Assigns the dependant Response object to the class property. The View loader
     * will automatically add template contents to as response body.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response Response object
     * @param \Psr\Log\LoggerInterface $logger PSR4 compatible Logger object
     * @return void
     */
    public function __construct(Response $response, Logger $logger)
    {
        $this->_response = $response;
        $this->_logger = $logger;

        if (method_exists($this, "init")) {
            $this->init();
        }

        $this->_logger->info("PHP Template Loader initialized");
    }

    /**
     * Set the template
     *
     * Sets the template filename.
     *
     * @param string $template Name of the template file
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->_template = $template;
        $this->_logger->debug("Template file set to loader.", ["template" => $this->_template]);
        return $this;
    }

    /**
     * Set the template directory
     *
     * Sets the template directory name.
     *
     * @param string $templateDir Name of the template directory
     * @return self
     */
    public function setTemplateDir(string $templateDir): self
    {
        $this->_templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->_logger->debug("Template directory set to loader." , ["templateDir" => $this->_templateDir]);
        return $this;
    }

    /**
     * Render the template
     *
     * Loads the template file with the retrieved data array, and returns the rendered
     * template. By default the template data is cached in the internal property
     * for all future renders of that same requests. To disable the cached vars
     * and load the template only with the currently passed in data, constant TPL_NO_CACHE_VARS
     * has to be sent as the third parameter.
     *
     * The Render method will automatically add contents of the rendered template
     * file to the Response object as response body. If you wish to retrieve the
     * contents back, pass in constant TPL_RETURN as the second parameter. When
     * the rendered template is only added to the Response object, an empty string
     * is returned.
     *
     * @param array $data Template data to be passed to the template. Default []
     * @param int $return Output or return rendered template. Default self::TPL_OUTPUT
     * @param int $cacheData Cache template data. Default self::TPL_CACHE_VARS
     * @return string
     *
     * @exceptions SlaxWeb\View\Exception\TemplateNotFoundException
     */
    public function render(
        array $data = [],
        int $return = self::TPL_OUTPUT,
        int $cacheData = self::TPL_CACHE_VARS
    ): string {
        $this->_logger->info("Rendering template", ["template" => $this->_template]);

        if ($cacheData === AbstractLoader::TPL_CACHE_VARS) {
            $this->_cachedData = array_merge($this->_cachedData, $data);
            $this->_logger->info("Data combined and cached");
            $data = $this->_cachedData;
        }

        $template = rtrim($this->_templateDir . $this->_template, ".{$this->_fileExt}") . ".{$this->_fileExt}";

        if (file_exists($template) === false) {
            $this->_logger->error(
                "Template does not exist or is not readable",
                ["template" => $template]
            );
            throw new \SlaxWeb\View\Exception\TemplateNotFoundException(
                "Requested template file ({$template}) was not found."
            );
        }

        $this->_load($template, $data);
        $this->_logger->debug(
            "Template loaded and rendered.",
            ["template" => $template, "data" => $data, "rendered" => $buffer]
        );

        if ($return === AbstractLoader::TPL_RETURN) {
            $this->_logger->info("Returning rendered template");
            return $buffer;
        }

        $this->_response->setContent($this->_response->getContent() . $buffer);
        $this->_logger->info("Rendered template appended to Response contents");
        return "";
    }

    /**
     * Load template
     *
     * Load the template file. Defined as abstract, because each loader will load
     * its template files in a different way.
     *
     * @param string $template Path to the template file
     * @param array $data View data
     * @return string
     */
    abstract protected function _load(string $template, array $data): string;
}
