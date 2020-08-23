<?php

namespace Core\Template\Render;

trait Rendereable
{
    public $layout = [
        "layout" => null,
        "notFound" => null,
        "error" =>  null
    ];

    public function configure(array $options)
    {
        $this->layout = array_merge($this->layout, $options);
    }

    /**
     * @return LayoutInterface|null
     */
    private function getLayoutByName(string $name)
    {
        $layout = $this->layout[$name];
        if ($layout && Data::isLayout($layout)) {
            $component = $this->app->get($layout);
            return $component;
        }

        return $layout;
    }
    
    public function getLayout()
    {
        return $this->getLayoutByName("layout");
    }

    public function getNotFoundView()
    {
        return $this->getLayoutByName("notFound");
    }

    public function getHttpErrorView()
    {
        return $this->getLayoutByName("error");
    }
}
