<?php

/**
 * @var Ep\Web\View $this 
 */

use Ep\Tests\App\Asset\JqueryAsset;

$this->register([
    JqueryAsset::class
]);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo - EP</title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody(); ?>

    <header>
        <h3>头部</h3>
        <h2>Controller: <?= $this->context->id ?></h2>
        <h3>Action: <?= $this->context->actionId ?></h3>
    </header>

    <?= $content ?>

    <footer>
        <h3>尾部</h3>
    </footer>

    <?php $this->endBody(); ?>
</body>

</html>
<?php
$this->endPage();
