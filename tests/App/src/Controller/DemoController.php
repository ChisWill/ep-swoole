<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use DateInterval;
use Ep;
use Ep\Db\Query;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Model\Student;
use Ep\Tests\App\Model\User;
use Ep\Web\ServerRequest;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Swoole\ConnectionPool;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Connection\LazyConnectionDependencies;
use Yiisoft\Db\Mysql\Connection as MysqlConnection;
use Yiisoft\Db\Redis\Connection;
use Yiisoft\Http\Method;
use Yiisoft\Session\SessionInterface;

class DemoController extends Controller
{
    public string $title = 'Demo';

    private PDO $pdo;
    private ConnectionPool $pool;

    public function __construct()
    {
        $dsn = 'mysql:host=127.0.0.1;dbname=test';
        $this->pdo = new PDO($dsn, 'root', '');
        $this->pdo->exec('set names utf8');

        if (class_exists(ConnectionPool::class)) {
            $this->pool = new ConnectionPool(function () {
                $db = new MysqlConnection('mysql:host=127.0.0.1;dbname=test', Ep::getDi()->get(LazyConnectionDependencies::class));
                $db->setUsername('root');
                $db->setPassword('');
                return $db;
            });
        }
    }

    public function indexAction()
    {
        return $this->string('<h1>hello world</h1>');
    }

    public function jsonAction(ServerRequestInterface $request)
    {
        if ($request->getMethod() === Method::POST) {
            $post = $request->getParsedBody();
            $get = $request->getQueryParams();
            $return = compact('post', 'get');
        } else {
            $body = $request->getBody()->getContents();
            $get = $request->getQueryParams();
            if ($body) {
                $return = [
                    'body' => $body,
                    'get' => $get
                ];
            } elseif ($get) {
                $return = $get;
            } else {
                $return = ['hello' => 'world'];
            }
        }
        return $this->json($return);
    }

    public function requestAction(ServerRequest $request)
    {
        $result = [
            'Method' => $request->getMethod(),
            'All GET' => $request->getQueryParams(),
            'All POST' => $request->getParsedBody(),
            'Raw body' => $request->getBody()->getContents(),
            'Cookies' => $request->getCookieParams(),
            'Host' => $request->getUri()->getHost(),
            'Header' => $request->getHeaders(),
            'Path' => $request->getUri()->getPath()
        ];
        return $this->json($result);
    }

    public function redirectAction(ServerRequestInterface $request)
    {
        $url = $request->getQueryParams()['url'] ?? 'http://www.baidu.com';

        return $this->redirect($url);
    }

    public function loggerAction(LoggerInterface $logger)
    {
        $logger->info('halo');
        return $this->string('over');
    }

    public function cacheAction()
    {
        $cache = Ep::getCache();

        $r = $cache->getOrSet('name', fn () => mt_rand(0, 100), 5);

        return $this->string($r);
    }

    public function saveAction()
    {
        $user = new User;
        $user->username = 'Peter' . mt_rand(0, 1000);
        $user->age = mt_rand(0, 100);
        $r1 = $user->insert();


        $user = User::findModel(1);
        $user->username = 'Mary' . mt_rand(0, 1000);
        $r2 = $user->update();

        return $this->json(compact('r1', 'r2'));
    }

    public function downloadAction(ServerRequest $request, Aliases $aliases)
    {
        // $name = 'eye.png';
        $name = 'face.jpg';
        $file = $aliases->get('@root/static/image/' . $name);

        $newName = null;
        $newName = '0!§ $&()=`´{}  []²³@€µ^°_+\' # - _ . , ; ü ä ö ß 9.jpg';

        return $this->getService()->download($file, $newName);
    }

    public function poolAction(ServerRequest $request)
    {
        $key = ($request->getQueryParams()['key'] ?? 'a');
        $map = [
            'a' => 1,
            'b' => 3,
        ];
        do {
            usleep(10 * 1000);
        } while (date('i') != 23 || date('s') != 45);

        /** @var ConnectionInterface */
        $db = $this->pool->get();

        $count = 0;
        for ($i = 100; $i--;) {
            echo $key;
            $result = Query::find($db)->from('user')->where(['like', 'username', $key])->all();
            if (count($result) != $map[$key]) {
                // echo $key . 'error' . "\n";
                $count++;
            }
        }
        echo $key . ':' . $count . "\n";

        $this->pool->put($db);

        return $this->json($result);
    }

    public function pdoAction(ServerRequest $request)
    {
        $key = ($request->getQueryParams()['key'] ?? 'a');
        $map = [
            'a' => 1,
            'b' => 3,
        ];
        do {
            usleep(10 * 1000);
        } while (date('i') != 19 || date('s') != 25);

        $count = 0;
        for ($i = 100; $i--;) {
            echo $key;
            $st = $this->pdo->prepare('select * from user where username like :id');
            $st->execute([
                ':id' => '%' . $key . '%'
            ]);
            $result = $st->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) != $map[$key]) {
                // echo $key . 'error' . "\n";
                $count++;
            }
        }
        echo $key . ':' . $count . "\n";
        return $this->json($result);
    }

    public function queryAction(ServerRequest $request)
    {
        $key = ($request->getQueryParams()['key'] ?? 'a');

        $result = [];
        $query = Student::find(Ep::getDb('sqlite'))->where(['like', 'name', '%' . $key . '%', false]);
        $result['RawSql'] = $query->getRawSql();
        $user = $query->one();
        $result['user'] = $user;
        $result['Count'] = $query->count();
        $list = $query->asArray()->all();
        $result['All'] = $list;

        return $this->json($result);
    }

    public function eventAction(EventDispatcherInterface $dipatcher)
    {
        $dipatcher->dispatch($this);

        return $this->string();
    }

    public function redisAction(Connection $redis)
    {
        $result = [];
        $r = $redis->set('a', mt_rand(0, 100), 'ex', 5, 'nx');
        $result['set'] = $r;
        $r = $redis->get('a');
        $result['get'] = $r;

        return $this->json($result);
    }

    public function validateAction()
    {
        $user = User::findModel(1);
        $r = $user->validate();
        if ($r) {
            return $this->string('validate ok');
        } else {
            return $this->json($user->getErrors());
        }
    }

    public function formAction(ServerRequestInterface $request)
    {
        $user = User::findModel($request->getQueryParams()['id'] ?? null);
        if ($user->load($request)) {
            if (!$user->validate()) {
                return $this->error($user->getErrors());
            }
            if ($user->save()) {
                return $this->success();
            } else {
                return $this->error($user->getErrors());
            }
        }
        return $this->render('form');
    }

    public function wsAction()
    {
        return $this->render('ws');
    }

    public function chatAction(ServerRequest $request)
    {
        $view = $this->getView()->withLayout('chat');
        $this->title = 'Simple Chat Room';

        $id = $request->getQueryParams()['id'] ?? '';

        return $this->string($view->render('chat', compact('id')));
    }

    public function chatRoomAction(ServerRequest $request)
    {
        $view = $this->getView()->withLayout('chat');
        $this->title = 'Chat Room';

        $id = $request->getQueryParams()['id'] ?? '';

        return $this->string($view->render('chatRoom', compact('id')));
    }

    public function socketioAction()
    {
        return $this->render('socketio');
    }

    public function getCookieAction(ServerRequestInterface $request)
    {
        $cookies = CookieCollection::fromArray($request->getCookieParams());

        return $this->json([
            'cookies' => $request->getCookieParams(),
            'testcookie' => $cookies->getValue('testcookie')
        ]);
    }

    public function setCookieAction()
    {
        $cookie = new Cookie('testcookie', 'testcookie' . mt_rand());
        $cookie = $cookie->withMaxAge(new DateInterval('PT10S'))->withSecure(false);

        $cookie2 = new Cookie('testcookie2', 'testcookie2' . mt_rand());
        $cookie2 = $cookie2->withMaxAge(new DateInterval('PT20S'))->withSecure(false);

        $response = $this->string('ok');
        $response = $response->withAddedHeader('t1', 'v1');
        $response = $response->withAddedHeader('t1', 'v2');
        $response = $response->withAddedHeader('t1', 'v3');

        $response = $response->withHeader('z1', 'v1');
        $response = $response->withHeader('z1', 'v2');
        $response = $response->withHeader('z1', 'v3');

        return $cookie->addToResponse($cookie2->addToResponse($response));
    }

    public function sessionAction(ServerRequest $request, SessionInterface $session)
    {
        $s = $request->getQueryParams()['s'] ?? '';
        if ($s) {
            sleep(10);
        }

        $r = $session->get('a');
        if (!$r) {
            $session->set('a', mt_rand(1, 100));
        }

        return $this->string($r);
    }

    public function testAction()
    {
        return $this->string('over');
    }
}
