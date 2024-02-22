<?php

use MADEV\Core\Application;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MADEV framework</title>
    <link rel="stylesheet" href="<?= Application::getResourcesUrl() . 'css/bootstrap.min.css' ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <?= $this->renderView($content, $data); ?>
        </div>
    </div>
    <script src="<?= Application::getResourcesUrl() . 'js/bootstrap.bundle.min.js' ?>"></script>
</body>
</html>
