<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Tests\App\Component\Controller;
use Yiisoft\Http\Status;

class IndexController extends Controller
{
    public string $title = '首页';

    public function indexAction()
    {
        $message = 'Default Page';

        return $this->render('index', compact('message'));
    }

    public function missAction()
    {
        return $this->string('迷路了', Status::NOT_FOUND);
    }
}
