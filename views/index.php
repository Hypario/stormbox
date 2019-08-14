<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">

    <title>Test</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<form action="/api/download" method="post">
    <input type="text" name="path">
    <button>Envoyer</button>
</form>

<form action="/api/files" method="post">
    <input type="text" name="path">
    <button>Envoyer</button>
</form>

<div id="drop"></div>

<script src="js/app.js"></script>

</body>
</html>
