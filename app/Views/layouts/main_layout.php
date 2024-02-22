<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MADEV Framework</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
</head>
<body>
    <!-- Exemple de template -->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <?= $this->renderView($content, $data) ?>
        </div>
    </div>
</body>
</html>