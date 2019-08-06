<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <!-- Accessibilité: jamais de user-scalable=no, jamais de scale forcée -->
    <!-- meta name=viewport content="width=device-width" et c'est tout -->
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- x-ua-compatible déprécié -->
    <title>Test</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<form action="/api/download" method="post">
    <input type="text" name="path">
    <button>Envoyer</button>
</form>

<div id="drop"></div>

<script src="js/app.js"></script>

</body>
</html>
