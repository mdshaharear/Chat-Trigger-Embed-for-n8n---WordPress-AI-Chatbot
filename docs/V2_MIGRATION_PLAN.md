# Phase 8 Migration Plan

## Existing Architecture

The 1.5.0 line is a single-purpose n8n embed plugin with profiles, styling, analytics, safe mode, and a runtime lab.

## New Architecture

Phase 8 introduces an internal alpha shell for a dual-engine builder:

* Native UI chatbot path
* OpenAI provider path
* Gemini provider path
* Native n8n path
* Hybrid orchestration path
* Legacy n8n compatibility mode

## Data Migration

* Do not delete 1.5.0 options.
* Map legacy profiles forward non-destructively.
* Preserve the current slug, text domain, table prefixes, and rollback ZIP.
* Keep legacy n8n chatbots on the legacy embed path by default.

## Rollback Behavior

* The 1.5.0 internal ZIP remains the rollback target.
* New 2.0 alpha data should be treated as forward-only.
* Do not claim downgrade safety after native chatbot data is created.

## Alpha Limitations

* No production-ready provider testing in this environment.
* No live OpenAI, Gemini, or n8n verification here.
* Native AI builder surfaces are scaffolded rather than fully complete.

