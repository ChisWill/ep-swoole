<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\MatchRegularExpression;
use Yiisoft\Validator\Rule\Number;

/**
 * @property int $id
 * @property string $username
 * @property int $age
 */
class User extends ActiveRecord
{
    public function tableName(): string
    {
        return 'user';
    }

    public function rules(): array
    {
        return [
            'age' => [(new Number)->integer()->max(99)->tooBigMessage('最多99岁')],
            'username' => [(new HasLength)->max(8)->min(2)->tooLongMessage('用户名最多8个字')->tooShortMessage('最少2个字')],
            'password' => [(new HasLength)->max(6)->tooLongMessage('最多6个'), (new MatchRegularExpression('/^[a-z\d]{4,6}$/i'))->message('4-8个字符')]
        ];
    }
}
