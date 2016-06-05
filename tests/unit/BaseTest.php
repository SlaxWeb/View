<?php
/**
 * Base View Class Tests
 *
 * Provides test for the Base View Class functionalities and ensures they work as
 * intended.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View\Tests\Unit;

use SlaxWeb\View\Base;
use SlaxWeb\View\AbstractLoader;
use SlaxWeb\Config\Container as Config;
use Symfony\Component\HttpFoundation\Response;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Config
     *
     * @var \SlaxWeb\Config\Container_mock
     */
    protected $_config = null;

    /**
     * Loader
     *
     * @var \SlaxWeb\View\AbstractLoader
     */
    protected $_loader = null;

    /**
     * Response
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $_response = null;

    /**
     * Prepare tests
     *
     * Prepare Base View class Dependency mocks.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(["offsetGet"])
            ->getMock();
        $this->_loader = $this->createMock(AbstractLoader::class);
        $this->_response = $this->createMock(Response::class);
    }

    protected function tearDown()
    {
    }

    /**
     * Test Template File Set
     *
     * Ensure that the Base View indeed does set the template file name if it was
     * not set before hand, and the configuration permits it.
     *
     * @return void
     */
    public function testTemplateFileSet()
    {
        $this->_config->expects($this->exactly(6))
            ->method("offsetGet")
            ->withConsecutive(
                // base view auto-sets template name
                ["view.baseDir"],
                ["view.autoTplName"],
                ["view.classNamespace"],

                // config does not allow automatical setting of template name
                ["view.baseDir"],
                ["view.autoTplName"],

                // template name already set
                ["view.baseDir"]
            )->will(
                $this->onConsecutiveCalls(
                    // base view auto-sets template name
                    "viewDir",
                    true,
                    "",

                    // config does not allow automatical setting of template name
                    "viewDir",
                    false,

                    // template name already set
                    "viewDir"
                )
            );

        $base = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->setMockClassName("BaseViewMock")
            ->getMockForAbstractClass();

        // base view auto-sets template name
        $base->__construct($this->_config, $this->_loader, $this->_response);
        $this->assertEquals("BaseViewMock", $base->template);

        // config does not allow automatical setting of template name
        $base->template = "";
        $base->__construct($this->_config, $this->_loader, $this->_response);
        $this->assertEquals("", $base->template);

        // template name already set
        $base->template = "PreSetTemplateName";
        $base->__construct($this->_config, $this->_loader, $this->_response);
        $this->assertEquals("PreSetTemplateName", $base->template);
    }

    /**
     * Test templates rendering
     *
     * Ensures that the templates are properly rendered, and the subviews and layout
     * are properly rendered and all is properly set in the view data for the main
     * view template rendering.
     *
     * @return void
     */
    public function testRendering()
    {
        $this->_loader->expects($this->exactly(1))
            ->method("setTemplate")
            ->with("PreSetTemplateName");

        $this->_loader->expects($this->exactly(1))
            ->method("render")
            ->with(
                ["foo" => "bar", "subview_testSub" => "Sub view"],
                AbstractLoader::TPL_RETURN,
                AbstractLoader::TPL_CACHE_VARS
            )->willReturn("Main view");

        $this->_config->expects($this->any())
            ->method("offsetGet")
            ->with("view.baseDir")
            ->willReturn("viewDir");

        $this->_response->expects($this->exactly(1))
            ->method("setContent")
            ->with("Previous responseRendered template");

        $this->_response->expects($this->exactly(1))
            ->method("getContent")
            ->willReturn("Previous response");

        $base = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->setMockClassName("BaseViewMock")
            ->getMockForAbstractClass();
        $base->template = "PreSetTemplateName";
        $base->__construct($this->_config, $this->_loader, $this->_response);

        // add a subview
        $subView = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(["render"])
            ->setMockClassName("TestSubView")
            ->getMockForAbstractClass();
        $subView->expects($this->exactly(1))
            ->method("render")
            ->willReturn("Sub view");
        $base->addSubView("testSub", $subView);

        // set layout
        $layoutView = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(["render"])
            ->setMockClassName("TestLayoutView")
            ->getMockForAbstractClass();
        $layoutView->expects($this->exactly(1))
            ->method("render")
            ->with(["foo" => "bar", "subview_testSub" => "Sub view", "mainView" => "Main view"])
            ->willReturn("Rendered template");
        $base->setLayout($layoutView);
        
        $this->assertTrue($base->render(["foo" => "bar"]));
    }

    /**
     * Test template return
     *
     * Ensure that the template in fact is returned when requested so.
     *
     * @return void
     */
    public function testTplReturn()
    {
        $this->_loader->expects($this->exactly(1))
            ->method("render")
            ->with([], AbstractLoader::TPL_RETURN, AbstractLoader::TPL_CACHE_VARS)
            ->willReturn("Main view");

        $this->_config->expects($this->any())
            ->method("offsetGet")
            ->with("view.baseDir")
            ->willReturn("viewDir");

        $base = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->setMockClassName("BaseViewMock")
            ->getMockForAbstractClass();
        $base->template = "PreSetTemplateName";
        $base->__construct($this->_config, $this->_loader, $this->_response);

        $this->assertEquals("Main view", $base->render([], AbstractLoader::TPL_RETURN));
    }
}