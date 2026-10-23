# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- The `Contenir\Tracking\Mezzio\Tracking` service, built once from the site's config and shared across requests.
  `head($nonce)` writes Consent Mode and the `contenir:consent` bridge first, then the Google tag, Tag Manager, the
  Meta pixel and LinkedIn Insight, with the page's CSP nonce on every script. `bodyStart()` writes the noscript
  fallbacks. It renders strings, so any template engine can use it.
- The `tracking()` view helper for laminas-view layouts, a plain invokable that returns the service.
- `ConfigProvider`, registering the service under `dependencies` and the helper under `view_helpers`, and named in
  `extra.laminas.config-provider` for laminas-component-installer.
- The service reads the site's settings as Mezzio merges them: the top-level config with `config['settings']`, where
  the CMS writes each environment's settings, laid over it (`SiteSettings`). It writes nothing unless the Tracking
  add-on is enabled, reading the enabled list local over global, as the CMS reads it (`EnabledAddOns`). Scripts are
  gated for a consent banner, and the tags always wait for consent, when the Consent add-on is enabled, or when
  `contenir_tracking.gate` says so. The Consent add-on's advanced Google Consent Mode leaves Google's tags running
  (`ConsentAddOn`).
