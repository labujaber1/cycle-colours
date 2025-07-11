<?php

/**
 * Create inline CSS for the divs based on their styles and current colours.
 *
 * This function generates CSS rules for each div class and style, applying the current colour.
 * It uses the stored divs array or the default one if not provided.
 *
 * @param array|null $divs The array of divs and their styles. If null, it uses the stored option.
 * @return string $inline_css The generated inline CSS string.
 */
function cycle_colours_create_inline_css($divs = null)
{
    if ($divs === null) {
        $divs = get_option('cycle_colours_div_array', []);
    }
    $inline_css = '';
    foreach ($divs as $div_class => $styles) {
        foreach ($styles as $style => $data) {
            if ($data['interval'] === '0') continue; // Skip if the interval is not set to 0 (disabled)
            $colours = $data['custom_colours_array'] ?? [];
            if (empty($colours) || !is_array($colours)) continue;
            $current_colour = $data['current_colour'] ?? '';
            if (!$current_colour) continue;
            $selector = str_starts_with($div_class, '#') ? esc_attr($div_class) : '.' . esc_attr($div_class);
            $inline_css .= sprintf(
                '%s { %s: %s !important; }' . PHP_EOL,
                $selector,
                esc_attr($style),
                esc_attr($current_colour)
            );
        }
    }
    return $inline_css;
}
