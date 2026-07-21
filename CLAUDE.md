# Domain Hunter — release checklist

Version is otherwise entirely git-tag/CHANGELOG-driven (`composer.json` has no
`version` field — it's `"type": "project"`). But `bin/dh` (and its
`bin/domainhunter` symlink) hardcodes the app version string passed to
Symfony Console's `Application` constructor, used for the `--version`/`-V`
CLI flag:

```php
$app = new class('Domain Hunter', '2.0.5') extends Application {
```

**Before pushing/dispatching a new release tag `vX.Y.Z`, always bump this
string to `X.Y.Z` first** (in the same PR as whatever else is shipping, or
its own PR if nothing else changed). It has drifted before (stuck at 2.0.1
through v2.0.2–v2.0.4) because nothing enforces it.

Also add a `## [X.Y.Z] - YYYY-MM-DD` entry to `CHANGELOG.md` before tagging —
`.github/workflows/release.yml` greps the changelog for a heading matching
the bare version number (no `v` prefix) to build the GitHub Release body, so
a missing/mismatched entry means an empty release description.

Release is cut via `.github/workflows/release.yml`, either by pushing a
`vX.Y.Z` tag to `master`, or via `workflow_dispatch` with a `version` input
(useful if the git proxy in this environment 403s on direct tag pushes —
`workflow_dispatch` still creates the tag as part of the release). It
builds `dh.phar`, source archives, creates the GitHub Release, and uploads
to SourceForge.
