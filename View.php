<?php
namespace SlaxWeb\View;

class View
{
    public $view = "";
    public $viewData = [];
    protected $_loader = null;
    protected $_twig = null;
    protected $_rendered = false;

    public function __construct(array $data = [])
    {
        $this->_loader = new \Twig_Loader_Filesystem(TEMPLATEPATH);
        $this->_twig = new \Twig_Environment($this->_loader, ["cache" => TWIGCACHE]);

        $this->view = $this->_getViewClass($this) . ".html";
        $this->viewData = $data;
    }

    public function __destruct()
    {
        if ($this->_rendered === false) {
            echo $this->_twig->render($this->view, $this->viewData);
        }
    }

    public function render()
    {
        $this->_rendered = true;
        return $this->_twig->render($this->view, $this->viewData);
    }

    protected function _getViewClass($obj)
    {
        $classname = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }
}
