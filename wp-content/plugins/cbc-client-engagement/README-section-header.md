Section Header (govph_section_header)

This plugin exposes a reusable section header markup that matches the theme's default header style.

Shortcode
---------
Use in post content or the block editor:

[govph_section_header title="Latest Features"]

Attributes:
- title (required) - The heading text.
- classes (optional) - Additional CSS classes to apply to the heading container.
- tag (optional) - HTML tag to use (default: h2).
- strong (optional) - 1 or 0 (default: 1). Wrap title in <strong> when truthy.
- id (optional) - ID attribute for the element.
- text_alignment (optional) - left|center|right (default: center). Controls the text alignment of the header.

PHP template helper
-------------------
Call from theme templates:

<?php
// Echo the markup
echo CBC_Client_Engagement::section_header_markup('Latest Features', ['classes' => 'my-class', 'tag' => 'h2']);

// Or use the global helper
echo govph_section_header('Latest Features', ['id' => 'features']);
?>

Filters
-------
- govph_section_header_classes (string $classes)
  - Filter just the default classes string.

- govph_section_header_args (array $defaults)
  - Filter the full default args array before merging with caller-provided args.

Examples
--------
// Change the default classes in functions.php of your theme
add_filter('govph_section_header_classes', function($classes) {
    return $classes . ' my-theme-override';
});

// Replace the default args entirely
add_filter('govph_section_header_args', function($defaults) {
    $defaults['classes'] = 'text-2xl text-black bg-none';
    $defaults['tag'] = 'h3';
    return $defaults;
});

Notes
-----
- The default classes use Tailwind-style utility classes. If your theme doesn't use Tailwind, override via the filters or pass your own classes.
- Shortcode output is escaped; use the PHP helper in templates if you need to insert complex markup around it.
