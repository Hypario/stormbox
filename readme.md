This is the beta of StormBox, there is a LOT of optimisation and
best practices to do

# notes supplémentaires

code trop couplé, tu ne peux juste *pas* le tester.

typiquement, les interactions avec les entités, ou le système de fichiers,
directement dans le contrôleur.

---

la gestion de réponse et d'erreur est trop manuelle, et clairement montrée
faillible sur pas mal de parties du code.

Fais un truc qui s'occupe de générer les payloads, une sorte de query
builder dédié aux réponses

les messages et codes d'erreur doivent être différents pour *tous* les problèmes
différents, un fichier de constantes qui définissent toutes les erreurs n'est
pas à ignorer

tu peux te retrouver avec un truc basique mais propre, comme par ex

```php
<?php
// errors.php
define(ERROR_OK, 0);
define(ERROR_UNKNOWN_FILE_UPLOAD, 10);
define(ERROR_TOO_BIG_CHUNK, 11);

// ApiAction.php::__invoke
if ($chunk > $nbChunk) {
	// 2 approches valides
    throw new KnownException(ERROR_TOO_BIG_CHUNK);
    return Response::error(ERROR_TOO_BIG_CHUNK);
}
//[...]
return Response::ok();

// le truc qui invoque la méthode __invoke
$res = $truc();
return new Response(
	$res['error'] === ERROR_OK ? 200 : 500,
	[], json_encode($res)
);
```

c'est une façon primitive et améliorable, mais 1. prédictable, 2. fonctionnelle,
3. organisée.
