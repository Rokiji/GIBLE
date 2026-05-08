# GIBLE — General Intelligent-Based Learning Environment

IT 142-style integrated learning platform: **Laravel**, **MySQL**, Wikipedia + YouTube APIs, Discord webhooks, quiz/flashcard generators, study planner, smart dashboard with **activity-based recommendations**, **progress insights**, and **enriched learning materials** (per proposal).

## Requirements

- PHP **8.2+** and [Composer](https://getcomposer.org/)
- **MySQL** 8 (or compatible)
- Optional: **YouTube Data API v3** key, **Discord** incoming webhook URL

## Setup

1. Install dependencies: `composer install`
2. Copy `.env.example` to `.env`, set `APP_KEY` with `php artisan key:generate`
3. Configure **MySQL** in `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, etc.)
4. Set `YOUTUBE_API_KEY` and `DISCORD_WEBHOOK_URL` if you want live integrations (otherwise summaries/videos/webhooks degrade gracefully with messages).
5. Run migrations: `php artisan migrate`
6. Start the app: `php artisan serve` → http://127.0.0.1:8000

Register, then use **Topic Search**, **Quiz**, **Flashcards**, and **Planner**. The dashboard shows recommended materials backed by Wikipedia/YouTube for the top suggested topics and includes progress analytics.

## Scheduler (study reminders)

Discord reminders use `php artisan gible:send-reminders`, scheduled **every minute** from `routes/console.php`.

Learning tips use `php artisan gible:send-learning-tips`, scheduled daily at **08:00** for active users that have not received a tip yet for the day.

On a server, add a cron entry:

`* * * * * cd /path-to-gible && php artisan schedule:run >> /dev/null 2>&1`

For local testing, run `php artisan schedule:work` in a second terminal (or invoke scheduler commands manually).

## Demonstration checklist (proposal / defense)

1. Confirm env: MySQL running, `.env` has `DISCORD_WEBHOOK_URL` and `YOUTUBE_API_KEY` as needed → `php artisan gible:integration-health`.
2. Open the dashboard while logged in: use **Demo · Discord without waiting for cron** to push a tip or flush due reminders instantly.
3. Show **Topic Search** (Wikipedia + YouTube) and explain that recommendations also pull Wikipedia *related pages* when the API returns data.
4. Optional: leave `schedule:work` running in another terminal so reminders and scheduled tips behave “automatically” like the proposal narrative.

## New reliability and data model additions

- Normalized `learning_topics` catalog with links from searches, quiz results, and flashcard decks.
- `webhook_deliveries` audit table for Discord notifications (attempt count, status, delivered flag, error details).
- Retry support for Discord webhook delivery (`DISCORD_WEBHOOK_MAX_ATTEMPTS`, default `2`).

## Legacy Node build (removed)

An older Express + Prisma prototype was replaced by this Laravel implementation. If a `node_modules` folder remains from before, you may delete it; it is not used.
