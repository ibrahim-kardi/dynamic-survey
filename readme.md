## What is Dynamic Survey?

Dynamic Survey is a Survey plugin and shows Results of the survey in a visual way.

## Installation

1. Upload the plugin to the `/wp-content/plugins/dynamic-survey` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

1. Add a new survey Tools > Dynamic Survey menu in WordPress
2. Add questions to the survey
3. Display the survey using the shortcode `[dynamic_survey id="X"]` eg: `[dynamic_survey id="1"]`

## Development Setup

Follow the steps

1. [Configure WPCS](#wpcs) to write code that complies with the WordPress Coding Standard.
2. Clone the repository `git clone https://github.com/ibrahim-kardi/dynamic-survey.git`.
3. Checkout to `dev` brunch `git checkout dev`
4. Make your own brunch `git checkout -b your_brunch_name`
5. Run `composer install` for install PHP dependency.

## <a id="wpcs"></a>WPCS configuration

<b>Step 1:</b> Please install these two composer package.

```
1. composer global require squizlabs/php_codesniffer
2. composer global require wp-coding-standards/wpcs
```

<b>Step 2:</b> Set WordPress as default coding standard. `(change your_username)`

```
phpcs --config-set installed_paths /Users/your_username/.composer/vendor/wp-coding-standards/wpcs
phpcs --config-set default_standard WordPress
```

<b>Problem Fix:</b>

If phpcs and phpcbf command not found as command, set it to your path variable.

`export PATH="/Users/your_username/.composer/vendor/bin:$PATH"`
