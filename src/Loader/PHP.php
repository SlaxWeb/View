<?php
/**
 * PHP Template Loader
 *
 * The PHP Template Loader loads the PHP Template file, and injects the set data
 * into the template.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View\Loader;

use Symfony\Component\HttpFoundation\Response;

class PHP
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
        return $this;
    }

    /**
     * Class constructor
     *
     * Assigns the dependant Response object to the class property. The View loader
     * will automatically add template contents to as response body.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response Response object
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->_response = $response;
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
        if ($cacheData === self::TPL_CACHE_VARS) {
            $this->_cachedData = array_merge($this->_cachedData, $data);
            $data = $this->_cachedData;
        }

        if (file_exists($this->_templateDir . $this->_template) === false) {
            throw new \SlaxWeb\View\Exception\TemplateNotFoundException(
                "Requested template file ({$this->_templateDir}{$this->_template}) was not found."
            );
        }

        extract($data);

        $buffer = "";
        ob_start();
        include $this->_templateDir . $this->_template;
        $buffer = ob_get_contents();
        ob_end_clean();

        if ($return === self::TPL_RETURN) {
            return $buffer;
        }

        $this->_response->setContent($this->_response->getContent() . $buffer);
        return "";
    }
}
