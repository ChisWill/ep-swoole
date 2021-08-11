<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Validator\Rule\{
    Required,
    Number,
    HasLength,
};

/**
 * @property int $id
 * @property int $class_id
 * @property string $name
 * @property string $password
 * @property int $age
 * @property string $birthday
 * @property int $sex
 * @property string $desc
 */
class Student extends ActiveRecord implements IdentityInterface
{
    public const PK = 'id';

    public function tableName(): string
    {
        return '{{%student}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'class_id' => [
                Required::rule(),
                Number::rule()->integer(),
            ],
            'name' => [
                Required::rule(),
                HasLength::rule()->max(50),
            ],
            'password' => [
                Required::rule(),
                HasLength::rule()->max(100),
            ],
            'age' => [
                Number::rule()->integer()->skipOnEmpty(true),
            ],
            'sex' => [
                Number::rule()->integer()->skipOnEmpty(true),
            ],
        ];
    }

    protected function userRules(): array
    {
        return [];
    }

    public function getId(): ?string
    {
        return (string) $this->id ?: null;
    }
}
