# Ecosystem beta release

## Status: local validation complete; production deployment pending

The hub connects real team/roster management, private player identities, guardian invitations, Momentum, and non-transactional Nutrition/Merch previews. Guardian access requires invitation acceptance by the matching verified adult and confirmation by a different authorized team administrator. Practice games use fictional rosters and are excluded from history and awards.

Momentum launch exchanges a hashed, single-use 90-second code for an API token. Browser workspaces are partitioned by account; the launch fragment is removed before the app loads. Workout uploads use a client session ID to make retries idempotent. Training belongs to PlayerIdentity UUIDs.

## Recorded local verification

- PHP lint passed for every changed PHP file, including Blade templates.
- PHPUnit: 46 tests, 202 assertions; two existing docblock deprecations.
- Vite production build passed (59 modules).
- Momentum JavaScript syntax checks passed; card filter tests (11 assertions), account workspace tests (6 assertions), and parser smoke tests (4 groups) passed.
- Local browser: hub to Momentum connected without another password, catalog loaded, equipment/body-structure filtering worked, and a queued workout reached Log, Review, and authenticated sync. Database inspection confirmed one session and one set at 25 lb / 8 reps.
- Local migrations and seeders passed. Local catalog counts are 116 exercises and 6 aliases. Production read-only inspection found 126 exercises and 61 aliases; legacy rows are preserved.

These checks do not establish live deployment or successful delivery of registration emails.

## Deployment boundaries

- Laravel: DreamHost, current application symlink `/home/dh_hf2733/bulldogstats.com/statbook`, resolving to `statbook-release-d8961e6`. Apache serves its `public` directory.
- Stage the new release outside the public root under `/home/dh_hf2733/releases/`. Preserve the old release and store database/site backups under `/home/dh_hf2733/private-backups/`.
- Copy existing production configuration without printing credentials. Preserve persistent storage and validate production dependencies before activation.
- Do not run blanket migrations: several historical migrations appear pending despite existing tables. Inspect and run only the three new migration files for the equipment index preparation, beta tables, and training client session ID. Review pretend output and back up the database first.
- Obtain approval for the exact recoverable application-path switch after staging is verified. Rollback restores the previous application symlink; additive database changes should remain in place unless a separately reviewed restoration is necessary.
- Momentum: Cloudflare Pages project `momentum-train-app`; pushing `main` automatically publishes to `train.bulldogstats.com`.
- Marketing: the existing root `index.html` currently takes precedence over WordPress. Its Launch App navigation and hero edits are local only. Preserve WordPress and existing content.

## Remaining acceptance checks

1. Stage, back up, inspect targeted migrations, approve activation, and deploy.
2. Verify registration and email verification through the public domains, then one-account hub-to-Momentum launch and retry-safe workout sync.
3. Browser-test team creation, roster linking, guardian accept/confirm/revoke, and a complete guided practice game on desktop and mobile.
4. Verify installed/offline Momentum reopen, service-worker update, account switching, and all five application views on a mobile viewport.
5. Publish marketing navigation; verify the real new-visitor path and capture final screenshots.

## Known beta limits and next priorities

- Guardian invitations currently appear in-app; automated invitation email delivery and reminder/recovery flows remain a follow-up.
- Scoring supports the displayed completed plate-appearance outcomes with deterministic runner advancement. Complex official-scoring cases such as steals and substitutions need dedicated workflows before competitive scorekeeping is advertised.
- Weekly consistency awards require a configured TrainingPhase target; a user-facing phase setup flow remains a follow-up.
- Preserve the production catalog count difference until legacy reconciliation is explicitly approved.
- Add accessible account disconnect/revocation and operational email monitoring before a broader launch.

## Future Nutrition and Merch

Start Nutrition with reviewed educational content and recipes, including ingredient provenance, allergens, and dated editorial review. Keep individualized intake targets and full food tracking out of the preview. NIH's [exercise and athletic performance fact sheet](https://ods.od.nih.gov/factsheets/ExerciseAndAthleticPerformance-Consumer/) is a primary reference for the educational material.

For Merch, choose the operational model before implementation. [WooCommerce](https://woocommerce.com/documentation/woocommerce/) fits the existing WordPress front door if maintaining its catalog, extensions, shipping, and orders is acceptable. [Shopify Buy Button](https://help.shopify.com/en/manual/online-sales-channels/buy-button) supports embedding products into an existing site while managing orders in Shopify. [Stripe Payment Links](https://stripe.com/payments/payment-links) is an option for a narrow initial product drop. Confirm fulfillment, taxes, refunds, size variants, and support ownership before adding checkout. The current preview takes no orders or payment details.
