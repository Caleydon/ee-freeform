<?php

namespace Solspace\Addons\FreeformNext\Library\DataObjects;

use Solspace\Addons\FreeformNext\Library\Composer\Components\Form;

class FormRenderObject
{
    /** @var string[] */
    private array $outputChunks;

    /**
     * FormRenderEvent constructor.
     */
    public function __construct(private Form $form)
    {
        $this->outputChunks = [];
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return string
     */
    public function getCompiledOutput()
    {
        return implode("\n", $this->outputChunks);
    }

    /**
     * @param string $value
     *
     * @return FormRenderObject
     */
    public function appendToOutput($value)
    {
        $this->outputChunks[] = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return FormRenderObject
     */
    public function appendJsToOutput($value)
    {
        $this->outputChunks[] = "<script>$value</script>";

        return $this;
    }

    /**
     * @param string $value
     *
     * @return FormRenderObject
     */
    public function appendCssToOutput($value)
    {
        $this->outputChunks[] = "<style>$value</style>";

        return $this;
    }
}
