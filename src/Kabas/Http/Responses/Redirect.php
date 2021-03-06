<?php

namespace Kabas\Http\Responses;

use Kabas\Utils\Url;
use Kabas\Http\Response;

class Redirect extends Response implements ResponseInterface
{
    public function __construct(string $pageID, array $params = [], $lang = null)
    {
        $this->target = $pageID;
        $this->params = $params;
        $this->lang = $lang;
    }

    /**
     * Executes the response. Called automatically.
     * @return void
     */
    public function run()
    {
        $this->setHeaders();
        header('Location: ' . Url::to($this->target, $this->params, $this->lang));
    }
}
