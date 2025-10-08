# 🏃 Parkour

**PARKOUR!** A WP-CLI command to quickly scaffold ACF blocks for Timber themes.

Named after the iconic Office scene where Andy, Michael, and Dwight leap around the office yelling "PARKOUR!" - because creating blocks should be just as quick and fun.

## Features

- 🚀 Interactive block creation with beautiful Laravel Prompts
- 📦 Generates all necessary files (block.json, callback.php, Twig template)
- 🎨 Optional JavaScript and CSS file generation
- ✅ Follows best practices for Timber + ACF blocks
- 🔧 Customizable templates based on your theme patterns

## Requirements

- PHP 8.0+
- WordPress with WP-CLI
- Timber
- ACF Pro

## Installation

### In Your Bedrock Project

Add to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/mccomaschris/parkour"
    }
  ],
  "require-dev": {
    "mccomaschris/parkour": "dev-main"
  }
}
```

Then run:

```bash
composer require mccomaschris/parkour --dev
```

### Configure WP-CLI

Create a `wp-cli.yml` file in your project root:

```yaml
require:
  - vendor/mccomaschris/parkour/src/class-blockcommand.php

path: web/wp
```

This tells WP-CLI to load the Parkour command.

## Usage

### Interactive Mode (Recommended)

Simply run:

```bash
wp parkour create
```

You'll be prompted for:
- Block name
- Title
- Description
- Category
- Icon
- Keywords
- Whether to include JS/CSS files

### Quick Mode

Create a block with defaults:

```bash
wp parkour create hero-section
```

### Specify Theme

```bash
wp parkour create hero-section --theme=herdpress
```

### Skip All Prompts

```bash
wp parkour create hero-section --skip-prompts
```

## What Gets Created

When you create a block named `hero-section`, Parkour generates:

```
your-theme/
├── blocks/
│   └── hero-section/
│       ├── block.json
│       ├── callback.php
│       ├── hero-section.js (optional)
│       └── hero-section.css (optional)
└── views/
    └── blocks/
        └── hero-section.twig
```

## File Structure

### block.json
Standard WordPress block metadata following ACF block conventions.

### callback.php
Render callback with:
- Timber context setup
- Block and field data
- CSS class management
- Background color support
- Anchor support
- Optional JS/CSS enqueuing

### {block-name}.twig
Twig template with:
- Block wrapper with classes
- Anchor ID support
- Preview mode detection
- Field usage examples

### {block-name}.js (optional)
JavaScript with:
- Block initialization
- DOM ready handling
- Scoped to avoid conflicts

### {block-name}.css (optional)
Styles with:
- Block-specific selectors
- Preview mode styles

## Customization

### Modify Templates

Templates are located in `templates/` and use Mustache syntax. You can fork and customize:

- `block.json.mustache`
- `callback.php.mustache`
- `block.twig.mustache`
- `block.js.mustache`
- `block.css.mustache`

### Block Patterns

The generated blocks follow this pattern (based on HerdPress theme):

- Theme prefix for all classes and functions
- Support for background colors via ACF field
- Automatic anchor and className support
- Filemtime-based cache busting for assets
- Preview mode detection in Twig

## Examples

### Creating an Accordion Block

```bash
wp parkour create
```

```
What is the block name? › accordion
Block title › Accordion
Block description › An accessible accordion block for organizing content
Block category › herdpress
Choose an icon › editor-justify
Keywords › accordion, faq, toggle
Include JavaScript file? › Yes
Include CSS file? › Yes
```

### Creating Multiple Blocks

```bash
wp parkour create hero
wp parkour create cta-banner
wp parkour create testimonials
```

## Contributing

This package is maintained by Chris McComas for Marshall University projects. Feel free to fork and customize for your own needs!

## License

MIT

## Why "Parkour"?

Because creating blocks should be fast, fun, and make you want to jump around your office yelling "PARKOUR!" just like Michael Scott.
