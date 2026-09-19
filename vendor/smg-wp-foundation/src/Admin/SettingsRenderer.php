<?php
namespace SMG\WPFoundation\Admin;

final class SettingsRenderer {
    public function render(array $schema, array $values): void {
        foreach ($schema as $field) {
            if (!is_array($field) || empty($field['key']) || empty($field['type'])) {
                continue;
            }

            $key = sanitize_key((string)$field['key']);
            $type = (string)$field['type'];
            $label = (string)($field['label'] ?? $key);
            $description = (string)($field['description'] ?? '');
            $value = $values[$key] ?? ($field['default'] ?? '');

            echo '<div class="smg-foundation-field">';

            if ($type === 'checkbox') {
                printf(
                    '<label><input type="checkbox" name="settings[%1$s]" value="1" %2$s> <strong>%3$s</strong></label>',
                    esc_attr($key),
                    checked((bool)$value, true, false),
                    esc_html($label)
                );
            } else {
                printf(
                    '<label for="smg-field-%1$s"><strong>%2$s</strong></label>',
                    esc_attr($key),
                    esc_html($label)
                );
                $this->control($type, $key, $value, $field);
            }

            if ($description !== '') {
                echo '<p class="description">'.esc_html($description).'</p>';
            }

            echo '</div>';
        }
    }

    private function control(string $type, string $key, mixed $value, array $field): void {
        $name = 'settings['.$key.']';
        $id = 'smg-field-'.$key;

        switch ($type) {
            case 'textarea':
                printf(
                    '<textarea class="large-text" rows="5" id="%1$s" name="%2$s">%3$s</textarea>',
                    esc_attr($id),
                    esc_attr($name),
                    esc_textarea((string)$value)
                );
                break;

            case 'number':
                printf(
                    '<input class="regular-text" type="number" id="%1$s" name="%2$s" value="%3$s">',
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr((string)$value)
                );
                break;

            case 'select':
                $this->select($id, $name, $value, (array)($field['options'] ?? []));
                break;

            default:
                $htmlType = in_array($type, ['url', 'email', 'password'], true)
                    ? $type
                    : 'text';

                printf(
                    '<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s">',
                    esc_attr($htmlType),
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr((string)$value)
                );
        }
    }

    private function select(string $id, string $name, mixed $value, array $options): void {
        printf(
            '<select id="%1$s" name="%2$s">',
            esc_attr($id),
            esc_attr($name)
        );

        foreach ($options as $optionValue => $optionLabel) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr((string)$optionValue),
                selected((string)$value, (string)$optionValue, false),
                esc_html((string)$optionLabel)
            );
        }

        echo '</select>';
    }
}
