<?php
/**
 * PHP Template Loader Tests
 *
 * Ensures that the PHP Template file loader methods function as designed.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.3
 */
namespace SlaxWeb\View\Tests\Unit;

use SlaxWeb\View\Loader\PHP as Loader;

class PHPLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Temporary test template file
     *
     * @var string
     */
    protected $_tempFile = "";

    /**
     * Temporary test template content
     *
     * @var string
     */
    protected $_tempContent = "";

    /**
     * Prepare tests
     *
     * Sets the temporary file and writes some contents into it.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_tempFile = "temp.template-" . date("Ymd-His") . ".php";
        $this->_tempContent = <<<EOD
Temporary test template.
Do not alter or delete. If you came across this file, a test is currently running!
The file will be automatically removed after use.
EOD;
        $template = <<<EOD
$this->_tempContent
<?= \$var1; ?>
<?= \$var2; ?>
EOD;
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . $this->_tempFile, $template);
    }

    /**
     * Tear down test
     *
     * Removes the temporary template file.
     *
     * @return void
     */
    protected function tearDown()
    {
        unlink(__DIR__ . DIRECTORY_SEPARATOR . $this->_tempFile);
    }

    /**
     * Test Render
     *
     * Test the render method that it properly loads the template file and injects
     * the passed in data array to the template.
     *
     * @return SlaxWeb\View\Loader\PHP_mock
     */
    public function testRender()
    {
        $loader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $loader->setTemplateDir(__DIR__)
            ->setTemplate($this->_tempFile);

        $rendered = $loader->render(["var1" => "foo", "var2" => "bar"], Loader::TPL_RETURN);
        $this->assertEquals($this->_tempContent . "\nfoobar", $rendered);

        return $loader;
    }

    /**
     * Test Variable Caching
     *
     * Ensure that the loader properly caches the variables from one load to another
     * and that it does not cache any variables when instructed not to, by passing
     * bool(false) value as second input parameter to the 'render' method.
     *
     * @param SlaxWeb\View\Loader\PHP_mock $loader PHP Template Loader mock object
     * @return void
     *
     * @depends testRender
     */
    public function testVarCaching($loader)
    {
        $rendered = $loader->render(["var1" => "baz"], Loader::TPL_RETURN);
        $this->assertEquals($this->_tempContent . "\nbazbar", $rendered);
        $rendered = $loader->render(
            ["var1" => "var1", "var2" => "var2"],
            Loader::TPL_RETURN,
            Loader::TPL_NO_CACHE_VARS
        );
        $this->assertEquals($this->_tempContent . "\nvar1var2", $rendered);
        $rendered = $loader->render([], Loader::TPL_RETURN);
        $this->assertEquals($this->_tempContent . "\nbazbar", $rendered);
    }
}
