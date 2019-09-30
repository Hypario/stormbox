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
    <label for="path">Le nom du fichier / dossier que vous voulez télécharger</label>
    <input type="text" name="path" id="path">
    <button>Envoyer</button>
</form>

<form action="/api/files" method="post" id="getFile">
    <label for="tree">Le nom du fichier / dossier dont vous voulez voir le contenu</label>
    <input type="text" name="path" id="tree">
    <button>Envoyer</button>
</form>

<p id="percent"></p>

<p>Prenez et déplacer votre fichier / dossier dans la zone pour l'upload</p>
<div id="drop"></div>

<script src="js/app.js"></script>

</body>
</html>
