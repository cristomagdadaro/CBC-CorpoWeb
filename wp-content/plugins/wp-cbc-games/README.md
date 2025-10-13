# CBC Games (WordPress Plugin)

Mini-games for the DA-Crop Biotechnology Center, reimplemented in PHP and structured with DDD. Includes:
- Quiz Bee
- Memory Game
- Scramble Game

## Structure (DDD)
- src/Domain: Entities and repository interfaces
- src/Application: Use-case services (QuizService, ScrambleService)
- src/Infrastructure: In-memory repository implementations and REST routes
- src/Presentation: WordPress shortcodes registration
- views/: PHP views for the shortcodes
- assets/: Front-end JS and CSS, plus memory images

## Install
1. Copy the `wp-cbc-games` folder into your WordPress instance at `wp-content/plugins/`.
2. Activate the plugin in WP Admin > Plugins (CBC Games).

## Shortcodes
Add these to any page or post:
- `[cbc_quiz]` – renders the Quiz Bee
- `[cbc_memory]` – renders the Memory Game
- `[cbc_scramble]` – renders the Scramble Game

## Requirements
- WordPress with REST API enabled (default)
- Front-end: TailwindCSS is enqueued from CDN only on pages using the shortcodes

## Notes
- Quiz/Scramble data and Memory images are served from in-memory repositories. You can swap repositories in the Infrastructure layer as needed.
- Scripts and styles are enqueued only when a shortcode renders to keep other pages lean.

## Development
- Namespace: `CBCGames`
- Autoload: Lightweight PSR-4 in `cbc-games.php`

## Accessibility
- Forms include associated labels. Tailwind's `sr-only` utility is used for visually-hidden labels.

## License
Internal use.

