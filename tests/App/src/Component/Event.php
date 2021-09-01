<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Annotation\Inject;
use Ep\Base\Config;
use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Db\Mysql\Connection as Mysql;
use Yiisoft\Db\Sqlite\Connection as Sqlite;

final class Event
{
    /**
     * @Inject
     */
    private Config $config;

    public function before(BeforeRequest $beforeRequest)
    {
        $request = $beforeRequest->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return;
        }

        /** @var Mysql */
        $mysql = Ep::getDi()->get(Pool::class)->get('mysql');
        $mysql->open();
        $beforeRequest->setRequest($request
            ->withAttribute(
                'sqlite',
                $this->createSqlite()
            )
            ->withAttribute(
                'mysql',
                $mysql
            ));
    }

    public function after(AfterRequest $afterRequest)
    {
        $request = $afterRequest->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return;
        }
        /** @var Mysql */
        $mysql = $request->getAttribute('mysql');
        $mysql->close();
        Ep::getDi()->get(Pool::class)->put('mysql', $mysql);
    }

    private function createSqlite(): Sqlite
    {
        return Ep::getInjector()->make(Sqlite::class, [
            'dsn' => 'sqlite:' . dirname(__FILE__, 3) . '/config/ep.sqlite'
        ]);
    }

    private function createMysql(): Mysql
    {
        $mysql = Ep::getInjector()->make(Mysql::class, [
            'dsn' => $this->config->params['db']['mysql']['dsn']
        ]);
        $mysql->setUsername($this->config->params['db']['mysql']['username']);
        $mysql->setUsername($this->config->params['db']['mysql']['password']);
        return $mysql;
    }
}
