<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep;
use Ep\Tests\App\Model\Student;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

final class IdentityRepository implements IdentityWithTokenRepositoryInterface
{
    public function findIdentityByToken(string $token, ?string $type = null): ?IdentityInterface
    {
        return Student::find(Ep::getDb('sqlite'))
            ->where(['id' => $token])
            ->one();
    }
}
