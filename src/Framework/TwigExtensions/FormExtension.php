<?php

namespace Framework\TwigExtensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('field', [$this, 'field'], [
                'is_safe' => ['html'],
                'needs_context' => true
            ])
        ];
    }

    /**
     * Génère un champ en HTML pour un formulaire
     * @param array $context
     * @param string $key
     * @param $value
     * @param $label
     * @param array $options
     * @return string
     */
    public function field(array $context, string $key, $value, $label, array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $error = $this->getErrorHTML($context, $key);
        $value = $this->convertValue($value);
        $attributes = [
            'class' => trim('form-control ' . ($options['class'] ?? '')),
            'name' => $key,
            'id' => $key
        ];
        ($error) ? $attributes['class'] .= ' is-invalid' : $attributes['class'] .= '';
        if ($type === 'textarea') {
            $input = $this->textarea($value, $attributes);
        } elseif ($type === 'file') {
            $input = $this->file($attributes);
        } elseif ($type === 'checkbox') {
            $input = $this->checkbox($value, $attributes);
        } elseif (array_key_exists('options', $options)) {
            $input = $this->select($value, $options['options'], $attributes);
        } elseif ($type === 'password') {
            $input = $this->password($value, $attributes);
        } else {
            $input = $this->input($value, $attributes);
        }
        return "
            <div class=\"form-group\">
                <label for=\"{$key}\">{$label}</label>
                {$input}
                {$error}
            </div>";
    }

    /**
     * Récupère l'erreur correspondant à la clef en HTML
     * @param $context
     * @param $key
     * @return string
     */
    private function getErrorHTML($context, $key): string
    {
        $error = $context['errors'][$key] ?? false;
        if ($error) {
            return "<div class=\"invalid-feedback\">{$error}</div>";
        }
        return "";
    }

    /**
     * Génère un champ input
     * @param null|string $value
     * @param array $attributes
     * @return string
     */
    private function input(?string $value, array $attributes): string
    {
        return "<input type=\"text\" " . $this->getAttributes($attributes) . " value=\"{$value}\">";
    }

    /**
     * Génère une checkbox
     * @param null|string $value
     * @param array $attributes
     * @return string
     */
    private function checkbox(?string $value, array $attributes): string
    {
        $html = '<input type="hidden" name="' . $attributes['name'] . '"value="0"/>';
        if ($value) {
            $attributes['checked'] = true;
        }
        return $html . "<input type=\"checkbox\" " . $this->getAttributes($attributes) . " value=\"1\">";
    }

    private function file($attributes)
    {
        return "<input type=\"file\" " . $this->getAttributes($attributes) . ">";
    }

    /**
     * Génère un champ input avec le type password
     * @param null|string $value
     * @param array $attributes
     * @return string
     */
    private function password(?string $value, array $attributes): string
    {
        return "<input type=\"password\" " . $this->getAttributes($attributes) . " value=\"{$value}\">";
    }

    /**
     * Génère un textarea
     * @param null|string $value
     * @param array $attributes
     * @return string
     */
    private function textarea(?string $value, array $attributes): string
    {
        return "<textarea " . $this->getAttributes($attributes) . ">{$value}</textarea>";
    }

    /**
     * Génère un select
     * @param null|string $value
     * @param array $options
     * @param array $attributes
     * @return string
     */
    private function select(?string $value, array $options, array $attributes): string
    {
        $htmlOptions = array_reduce(array_keys($options), function (string $html, string $key) use ($options, $value) {
            $params = ['value' => $key, 'selected' => $key === $value];
            return $html . '<option ' . $this->getAttributes($params) . '>' . $options[$key] . '</option>';
        }, "");
        return "<select {$this->getAttributes($attributes)}>{$htmlOptions}</select>";
    }

    /**
     * Génère la liste des attribues sous formes HTML pour les champs
     * @param array $attributes
     * @return string
     */
    private function getAttributes(array $attributes): string
    {
        $htmlParts = [];
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $htmlParts[] = (string)$key;
            } elseif ($value !== false) {
                $htmlParts[] = "{$key}=\"{$value}\"";
            }
        }
        return implode(' ', $htmlParts);
    }

    private function convertValue($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return (string)$value;
    }
}
