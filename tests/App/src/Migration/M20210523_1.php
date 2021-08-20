<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Helper\Str;
use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class M20210523_1 implements MigrateInterface
{
    public static function getName(): string
    {
        return 'class student';
    }

    public function up(MigrateBuilder $builder): void
    {
        $builder->createTable('student', [
            'id' => $builder->primaryKey(),
            'class_id' => $builder->integer()->notNull(),
            'name' => $builder->string(50)->notNull(),
            'nickname' => $builder->string(50)->notNull(),
            'password' => $builder->string(100)->notNull(),
            'age' => $builder->smallInteger()->defaultValue(0),
            'birthday' => $builder->dateTime(),
            'sex' => $builder->tinyInteger()->defaultValue(0),
            'desc' => $builder->text()
        ]);

        $builder->createIndex('student_name', 'student', ['class_id', 'name']);

        $builder->createTable('class', [
            'id' => $builder->primaryKey(),
            'school_id' => $builder->integer()->notNull(),
            'name' => $builder->string(50)->notNull()
        ]);
        $builder->createIndex('class_name', 'class', 'name');

        $builder->batchInsert('class', ['school_id', 'name'], [
            ['1', 'I-7'],
            ['1', 'II-1'],
            ['1', 'II-2'],
            ['2', 'One'],
            ['2', 'Two']
        ]);

        $map = $builder->find()->from('class')->map('name', 'id');
        $builder->batchInsert('student', ['class_id', 'name', 'nickname', 'password', 'age', 'birthday', 'sex'], [
            [$map['I-7'], 'Rean Schwarzer', 'Rean', Str::random(), '17', '2003-1-1', 1],
            [$map['I-7'], 'Alisa Reinford', 'Alisa', Str::random(), '16', '2004-2-2', 2],
            [$map['I-7'], 'Fei Claussell', 'Fei', Str::random(), '15', '2005-3-3', 2],
            [$map['I-7'], 'Eliot Craig', 'Eliot', Str::random(), '16', '2004-5-4', 1],
            [$map['I-7'], 'Laura S Arseid', 'Laura', Str::random(), '17', '2003-7-3', 2],
            [$map['I-7'], 'Machias Regnitz', 'Machias', Str::random(), '17', '2003-1-13', 1],
            [$map['I-7'], 'Jusis Albarea', 'Jusis', Str::random(), '17', '2003-9-21', 1],
            [$map['I-7'], 'Emma Millstein', 'Emma', Str::random(), '17', '2003-12-11', 2],
            [$map['I-7'], 'Gaius Worzel', 'Gaius', Str::random(), '17', '2003-8-19', 1],
            [$map['II-1'], 'Crow Armbrust', 'Crow', Str::random(), '19', '2001-4-4', 1],
            [$map['II-2'], 'Angelica Rogner', 'Angelica', Str::random(), '20', '2000-5-5', 2],
            [$map['One'], 'Lloyd Bannings', 'Lloyd', Str::random(), '18', '2002-5-21', 1],
            [$map['One'], 'Randy Orlando', 'Randy', Str::random(), '21', '1999-8-12', 1],
            [$map['Two'], 'Elie MacDowell', 'Elie', Str::random(), '18', '2002-7-5', 2],
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('student');
        $builder->dropTable('class');
    }
}
