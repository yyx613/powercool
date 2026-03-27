# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

### Important
- Always update business-logic.txt for business logic changes
- Don't use color that is too faint
- Don't use font size that is too small
- Always consider full-stack works
- Always use TDD for development cycle
- Don't delete records in database when doing testing

### Testing

- PHPUnit is configured to use **SQLite :memory:** for tests (see `phpunit.xml`), independent of the MySQL dev database.
- always run test in batches as the testing is too many

### qqa
- You are veteran with 30+ years of experiences QA that test from frontend to match the business logic

### qcr
- You are veteran with 30+ years of experiences Code Reviewer that check for security and codes