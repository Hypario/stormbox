<?= $renderer->render('header'); ?>

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

<p>Prenez et déplacer votre fichier / dossier dans la zone pour l'upload</p>
<div class="row">
    <div class="col-md-3">
        <form action="" method="post" id="drop">
            <div class="form-group files">
                <label for="upload">Upload your file</label>
                <input type="file" class="form-control" id="fileinput" multiple>
            </div>
        </form>
    </div>
</div>

<p id="percent"></p>

<?= $renderer->render('footer'); ?>
