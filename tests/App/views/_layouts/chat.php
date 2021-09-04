<?php

/**
 * @var Ep\Web\View $this 
 */

use Ep\Tests\App\Asset\MainAsset;

$this->register([
    MainAsset::class
]);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/ico;base64,aWNv">
    <title><?= $this->context->title ?: 'Basic - EP' ?></title>
    <?php $this->head() ?>
    <style type="text/css">
        header {
            text-align: center;
        }

        footer {
            background-color: cornflowerblue;
            text-align: center;
        }

        footer a {
            color: gold;
            font-size: 30px;
        }
    </style>
</head>

<body>
    <?php $this->beginBody(); ?>
    <script src="https://lib.baomitu.com/jquery/3.5.1/jquery.min.js"></script>
    <header>
        <h1><?= $this->context->title ?></h1>
    </header>

    <?= $content ?>

    <footer>
        <a class="reload-btn" href="javascript:location.reload();">刷新</a>
    </footer>
    <?php $this->endBody(); ?>
</body>

</html>
<?php
$this->endPage();
