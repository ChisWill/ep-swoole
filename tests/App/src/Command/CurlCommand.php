<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep\Annotation\Inject;
use Ep\Console\Command;
use Ep\Console\Request;
use Ep\Tests\App\Service\CurlService;
use Swoole\Process\Manager;
use Symfony\Component\Console\Input\InputArgument;

class CurlCommand extends Command
{
    public function __construct()
    {
        $this->createDefinition('do')->addArgument('id', InputArgument::REQUIRED);
    }

    /**
     * @Inject
     */
    private CurlService $service;

    public function loginAction()
    {
        [$ok, $cookie] = $this->service->getLoginInfo(1);

        if ($ok) {
            return $this->success($cookie);
        } else {
            return $this->error('wrong');
        }
    }

    public function doAction(Request $request)
    {
        $id = (int) $request->getArgument('id');

        [$ok, $cookie] = $this->service->getLoginInfo($id);

        if (!$ok) {
            return $this->error();
        }

        $r = $this->service->getUserId($cookie);

        if ($r === $id) {
            return $this->success('OK');
        } else {
            return $this->error('ERR');
        }
    }

    public function runAction()
    {
        $manager = new Manager();

        for ($i = 1; $i <= 2; $i++) {
            $manager->add(function () use ($i) {
                $r = $this->getService()->call('curl/do', [
                    'id' => $i
                ]);
                if ($r === 0) {
                    echo 0;
                } else {
                    echo 1;
                }
            }, false);
        }

        $manager->start();

        return $this->success();
    }
}
