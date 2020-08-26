<?php

namespace Core\Template\Render;

use function Core\Template\Lib\{is_layout};

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
        $layout = isset($this->layout[$name]) ? $this->layout[$name] : null;
        if ($layout && is_layout($layout)) {
            return  $this->app->get($layout);
        }

        return $layout;
    }

    /**
     * @return LayoutInterface|null
     */
    public function getLayout()
    {
        return $this->getLayoutByName("layout");
    }

    /**
     * @return LayoutInterface|null
     */
    public function getNotFoundView()
    {
        return $this->getLayoutByName("notFound");
    }

    /**
     * @return LayoutInterface|null
     */
    public function getHttpErrorView()
    {
        return $this->getLayoutByName("error");
    }
}
