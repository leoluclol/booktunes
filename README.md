# BookTunes

BookTunes is a small PHP and JavaScript web app that generates reading playlists based on a book title. It supports English and Italian, checks whether the requested book exists, shows a short description of the book, and then returns a playlist that fits its themes and genre.

## What it does

- Lets you enter a book title and generate a playlist for reading.
- Switches the interface between English and Italian.
- Uses the selected language when prompting the AI.
- Verifies whether the book exists before generating songs.
- Shows a short book description before the playlist.
- Saves user feedback into a local SQLite database.

## Project files

- `index.html` - Frontend page with the book input, language switch, playlist display, and feedback form.
- `style.css` - Styling for the page layout, cards, and feedback section.
- `get_playlist.php` - Backend endpoint that checks the book, asks the AI for a description and playlist, and returns JSON.
- `save_feedback.php` - Backend endpoint that stores feedback in SQLite.
- `init_feedback_db.php` - One-time helper to create the feedback database schema.
- `config.php` - Local configuration file used for the OpenAI API key.

## Local setup

1. Make sure PHP is installed with `curl` and `sqlite3` support.
2. Add your OpenAI API key to `config.php`.
3. Initialize the feedback database:

```bash
php init_feedback_db.php
```

4. Start a local PHP server from the project directory:

```bash
php -S localhost:8000
```

5. Open the app in your browser and enter a book title.

## Feedback storage

Feedback submissions are written to `feedback.sqlite` in the project root. The database is created automatically when feedback is first submitted, or you can initialize it with `init_feedback_db.php`.

## Notes

- The AI response is expected to be JSON.
- If a title cannot be verified as an existing book, the app asks for a different title.
- The feedback form is intentionally small and minimal so it stays out of the way of the playlist UI.