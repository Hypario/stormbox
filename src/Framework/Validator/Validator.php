<?php

namespace Framework\Validator;


use App\Framework\Validator\ValidationError;
use Framework\Database\Table;
use PDO;

class Validator
{

    /**
     * @var array
     */
    private array $params;

    /**
     * @var ValidationError[]
     */
    private array $errors = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function required(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value)) {
                $this->addError($key, 'required');
            }
        }
        return $this;
    }

    /**
     * Vérifie que le champ n'est pas vide
     * @param string ...$keys
     * @return Validator
     */
    public function notEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, 'empty');
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param int|null $min
     * @param int|null $max
     * @return Validator
     */
    public function length(string $key, ?int $min, ?int $max = null): self
    {
        $value = $this->getValue($key);
        $length = mb_strlen($value);
        if (!is_null($min) &&
            !is_null($max) &&
            ($length < $min || $length > $max)
        ) {
            $this->addError($key, 'betweenLength', [$min, $max]);
        }
        if (!is_null($min) &&
            $length < $min
        ) {
            $this->addError($key, 'minLength', [$min]);
        }
        if (!is_null($max) &&
            $length > $max
        ) {
            $this->addError($key, 'maxLength', [$max]);
        }
        return $this;
    }

    /**
     * Vérifie que l'élément est un slug
     * @param string[] $keys
     * @return Validator
     */
    public function slug(string ...$keys): self
    {
        $pattern = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (!is_null($value) && !preg_match($pattern, $value)) {
                $this->addError($key, 'slug');
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $format
     * @return Validator
     */
    public function dateTime(string $key, string $format = 'Y-m-d H:i:s'): self
    {
        $value = $this->getValue($key);
        $date = \DateTime::createFromFormat($format, $value);
        $errors = \DateTime::getLastErrors();
        if ($errors['error_count'] > 0 || $errors['warning_count'] > 0 || $date === false) {
            $this->addError($key, 'dateTime', [$format]);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $table
     * @param PDO $pdo
     * @return Validator
     */
    public function exists(string $key, string $table, PDO $pdo): self
    {
        $value = $this->getValue($key);
        $statement = $pdo->prepare("SELECT id FROM {$table} WHERE id = ?");
        $statement->execute([$value]);
        if ($statement->fetchColumn() === false) {
            $this->addError($key, 'exists', [$table]);
        }
        return $this;
    }

    /**
     * Vérifie que la clef est unique
     * @param string $key
     * @param string|Table $table
     * @param PDO $pdo
     * @param int|null $exclude
     * @return Validator
     */
    public function unique(string $key, $table, ?PDO $pdo = null, ?int $exclude = null): self
    {
        if ($table instanceof Table) {
            $pdo = $table->getPdo();
            $table = $table->getTable();
        }
        $value = $this->getValue($key);
        $query = "SELECT id FROM {$table} WHERE $key = ?";
        $params = [$value];
        if ($exclude !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude;
        }
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        if ($statement->fetchColumn() !== false) {
            $this->addError($key, 'unique', [$value]);
        }
        return $this;
    }

    /**
     * Vérifie si le fichier a bien été uploadé
     * @param string $key
     * @return Validator
     */
    public function uploaded(string $key): self
    {
        $file = $this->getValue($key);
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            $this->addError($key, 'uploaded');
        }
        return $this;
    }

    public function email(string $key): self
    {
        $value = $this->getValue($key);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($key, 'email');
        }
        return $this;
    }

    public function confirm(string $key): self
    {
        $value = $this->getValue($key);
        $valueConfirm = $this->getValue($key . '_confirm');
        if ($value !== $valueConfirm) {
            $this->addError($key, 'confirm');
        }
        return $this;
    }

    /**
     * Vérifie le type du fichier
     * @param string $key
     * @param array $extensions
     * @return Validator
     */
    public function extension(string $key, array $extensions): self
    {
        /** @var UploadedFileInterface $file */
        $file = $this->getValue($key);

        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;
            if (!in_array($extension, $extensions) || $expectedType !== $type) {
                $this->addError($key, 'filetype', [join(',', $extensions)]);
            }
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Récupère les erreurs
     * @return ValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }

    private function addError(string $key, string $rule, array $attributes = []): void
    {
        $this->errors[$key] = new ValidationError($key, $rule, $attributes);
    }

}
