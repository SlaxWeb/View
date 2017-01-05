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

use SlaxWeb\View\AbstractLoader;

class PHP extends AbstractLoader
{
    /**
     * Load template
     *
     * Load the template by including the PHP template file and extracting data
     * before hand, so it becomes accessible in the view.
     *
     * @param string $template Path to the template file
     * @param array $data View data
     * @return string
     */
    protected function load(string $template, array $data): string
    {
        extract($data);

        $buffer = "";
        ob_start();
        include $this->templateDir . $template;
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}
