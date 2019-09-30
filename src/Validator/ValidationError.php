<?php

namespace Hypario\Validator;

class ValidationError
{

    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $rule;

    private $messages = [
        'required' => 'Le champ %s est requis',
        'empty' => 'Le champ %s ne peut être vide',
        'slug' => 'Le champ %s n\'est pas un slug valide',
        'minLength' => 'Le champ %s doit contenir plus de %d caractères',
        'maxLength' => 'Le champ %s doit contenir moins de %d caractères',
        'betweenLength' => 'Le champ %s doit contenir entre %d et %d caractères',
        'dateTime' => 'Le champ %s doit être une date valide (%s)',
        'exists' => 'Le champ %s n\'existe pas dans %s',
        'unique' => 'Le champ %s doit être unique',
        'filetype' => 'Le champ %s n\'est pas au format valide (%s)',
        'uploaded' => 'Vous devez uploader un fichier',
        'email' => "L'adresse email ne semble pas valide",
        'confirm' => "Vous n'avez pas confirmé le champ %s"
    ];
    /**
     * @var array
     */
    private $attributes;

    public function __construct(string $key, string $rule, array $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $params = array_merge([$this->messages[$this->rule], $this->key], $this->attributes);
        return (string)call_user_func_array('sprintf', $params);
    }
}
