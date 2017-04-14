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
    protected $config = null;

    /**
     * Loader
     *
     * @var \SlaxWeb\View\AbstractLoader
     */
    protected $loader = null;

    /**
     * Response
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response = null;

    /**
     * Prepare tests
     *
     * Prepare Base View class Dependency mocks.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(["offsetGet"])
            ->getMock();
        $this->loader = $this->createMock(AbstractLoader::class);
        $this->response = $this->createMock(Response::class);
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
        $this->config->expects($this->exactly(6))
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
        $base->__construct($this->config, $this->loader, $this->response);
        $this->assertEquals("BaseViewMock", $base->template);

        // config does not allow automatical setting of template name
        $base->template = "";
        $base->__construct($this->config, $this->loader, $this->response);
        $this->assertEquals("", $base->template);

        // template name already set
        $base->template = "PreSetTemplateName";
        $base->__construct($this->config, $this->loader, $this->response);
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
        $this->loader->expects($this->exactly(1))
            ->method("setTemplate")
            ->with("PreSetTemplateName");

        $this->loader->expects($this->exactly(1))
            ->method("render")
            ->with(
                ["foo" => "bar", "subview_testSub" => "Sub view"],
                AbstractLoader::TPL_RETURN,
                AbstractLoader::TPL_CACHE_VARS
            )->willReturn("Main view");

        $this->config->expects($this->any())
            ->method("offsetGet")
            ->with("view.baseDir")
            ->willReturn("viewDir");

        $this->response->expects($this->exactly(1))
            ->method("setContent")
            ->with("Previous responseRendered template");

        $this->response->expects($this->exactly(1))
            ->method("getContent")
            ->willReturn("Previous response");

        $base = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->setMockClassName("BaseViewMock")
            ->getMockForAbstractClass();
        $base->template = "PreSetTemplateName";
        $base->__construct($this->config, $this->loader, $this->response);

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
        $this->loader->expects($this->exactly(1))
            ->method("render")
            ->with([], AbstractLoader::TPL_RETURN, AbstractLoader::TPL_CACHE_VARS)
            ->willReturn("Main view");

        $this->config->expects($this->any())
            ->method("offsetGet")
            ->with("view.baseDir")
            ->willReturn("viewDir");

        $base = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->setMockClassName("BaseViewMock")
            ->getMockForAbstractClass();
        $base->template = "PreSetTemplateName";
        $base->__construct($this->config, $this->loader, $this->response);

        $this->assertEquals("Main view", $base->render([], AbstractLoader::TPL_RETURN));
    }

    public function testSubViewsAndTemplates()
    {
        $subViews = [
            "subview_view"  =>  "Sub View",
            "subview_tpl"   =>  "Sub Template"
        ];

        $this->loader->expects($this->exactly(2))
            ->method("render")
            ->withConsecutive(
                [
                    ["subview_view" => "Sub View", "subview_tpl" => ""],
                    AbstractLoader::TPL_RETURN,
                    AbstractLoader::TPL_CACHE_VARS
                ],
                [$subViews, AbstractLoader::TPL_RETURN, AbstractLoader::TPL_CACHE_VARS]
            )->will($this->onConsecutiveCalls("Sub Template", "Main View"));

        $this->loader->expects($this->exactly(2))
            ->method("setTemplate")
            ->withConsecutive(
                ["Template"],
                ["PreSetTemplateName"]
            );

        $view = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods(["render"])
            ->getMockForAbstractClass();

        $view->expects($this->once())
            ->method("render")
            ->with(["subview_view" => ""], AbstractLoader::TPL_RETURN)
            ->willReturn("Sub View");

        $this->config->expects($this->any())
            ->method("offsetGet")
            ->with("view.baseDir")
            ->willReturn("viewDir");

        $base = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->setMockClassName("BaseViewMock")
            ->getMockForAbstractClass();
        $base->template = "PreSetTemplateName";
        $base->__construct($this->config, $this->loader, $this->response);

        $base->addSubView("view", $view);
        $base->addSubTemplate("tpl", "Template");
        $base->render([], AbstractLoader::TPL_RETURN);
    }
}
