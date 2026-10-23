# contenir/contenir-tracking-mezzio

[![Continuous Integration](https://github.com/contenir/contenir-tracking-mezzio/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/contenir/contenir-tracking-mezzio/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/contenir/contenir-tracking-mezzio/graph/badge.svg)](https://codecov.io/gh/contenir/contenir-tracking-mezzio)

The Mezzio adapter for [contenir/contenir-tracking](https://github.com/contenir/contenir-tracking). It writes a
site's analytics and advertising tags into its layout: Google Consent Mode v2 first, then the Google tag, Tag
Manager, the Meta pixel and LinkedIn Insight, each with the page's CSP nonce.

The tags are set in the CMS, by the [Tracking add-on](https://github.com/contenir-extension/tracking), and read from
the site's merged config, so there is nothing to configure on the site.

It is the counterpart of [contenir/contenir-tracking-laminas-mvc](https://github.com/contenir/contenir-tracking-laminas-mvc),
and behaves the same way; see [How it differs from the laminas-mvc adapter](#how-it-differs-from-the-laminas-mvc-adapter).

## Requirements

- PHP 8.3, 8.4 or 8.5
- A PSR-11 container. The package registers its services the laminas way (`dependencies` and `view_helpers`), as a
  Mezzio application with laminas-servicemanager reads them.
- To call `tracking()` from a layout: laminas-view, as mezzio-laminasviewrenderer brings it. Any other template
  engine can use the `Tracking` service directly.

## Install

```bash
composer require contenir/contenir-tracking-mezzio
```

laminas-component-installer offers to add `Contenir\Tracking\Mezzio\ConfigProvider` to `config/config.php`. Without
it, add the provider to the `ConfigAggregator` by hand.

## Usage

### In a laminas-view layout

Put `head()` as early in `<head>` as possible, since Consent Mode has to be set before any tag loads, and
`bodyStart()` straight after `<body>`:

```php
<head>
    <meta charset="utf-8">
    <?= $this->tracking()->head($nonce) ?>
    …
</head>
<body>
    <?= $this->tracking()->bodyStart() ?>
```

This replaces the gtag and Meta pixel snippets layouts print by hand today.

`head()` puts `$nonce` on every script; without one, the scripts are written without a nonce, so a site without a
Content Security Policy calls `head()`. `bodyStart()` writes only noscript fallbacks, which need no nonce, and only
when the tags don't wait for consent.

`tracking()` (or `Tracking()`) returns the `Contenir\Tracking\Mezzio\Tracking` service itself. The helper is a plain
invokable class, which laminas-view's helper manager accepts as a callable, so the package needs nothing from
laminas-view.

### From anything else

`Contenir\Tracking\Mezzio\Tracking` is registered in the container and renders strings, so any template engine, or a
handler, can use it:

```php
$tracking = $container->get(\Contenir\Tracking\Mezzio\Tracking::class);

$tracking->head($nonce);  // in <head>
$tracking->bodyStart();   // after <body>
```

### Per request

Mezzio services are shared across requests, and the service is built once from the site's config, so it holds
nothing about a request. The nonce is the only per-request value, and it is always passed to `head()`: take it from
wherever the application keeps it, such as the request attribute a CSP middleware sets, and hand it to the template.

Whether an environment tracks doesn't depend on the request either: it is the Tracking add-on's "Track on this
environment" setting, which the CMS keeps in each environment's `settings.local.php`. Neither adapter reads the host.

## What it writes, and when

- **Nothing unless the Tracking add-on is enabled** on the site, and the environment tracks: "Track on this
  environment" in the add-on's settings, kept in the environment's local settings.
- **The enabled add-ons** are read from `config/autoload/extensions.global.php` and `extensions.local.php`, as the
  CMS reads them: a list in the local file replaces the global one entirely. The `ConfigAggregator` would join the
  two lists, so the service reads the files itself.
- **Gating.** When the Consent add-on is enabled, every script that isn't necessary is written inert
  (`type="text/plain" data-category="analytics|marketing"`) for the banner to switch on. Only the Consent Mode bridge
  runs at once.
- **Waiting for consent.** When the Consent add-on is enabled, the tags wait for consent whatever "Wait for consent"
  says, so only the banner's choice grants anything: Google's defaults are denied, the Meta pixel starts revoked and
  LinkedIn waits.
- **Advanced Consent Mode.** When the Consent add-on's settings choose Google's advanced Consent Mode, the Google tag
  and Tag Manager are left running. They see the denied defaults and send Google only cookieless pings until the
  visitor consents. The Meta pixel and LinkedIn stay gated.

The tags, the consent categories and the `contenir:consent` browser event are described in
[contenir/contenir-tracking](https://github.com/contenir/contenir-tracking#consent).

## Settings

A Mezzio site's settings live in two places in its merged config:

- `config/autoload/settings.global.php` returns them at the **top level** of the config (`['google' => […]]`).
- The CMS writes each environment's settings to `settings.local.php` under a **`settings`** key
  (`['settings' => ['extensions' => ['tracking' => […]]]]`).

The service reads the top-level config with `config['settings']` laid over it (`array_replace_recursive`), so a value
under `settings` wins. `Contenir\Tracking\Mezzio\SiteSettings::fromConfig()` does this, for anything else that needs
the same view.

The keys read are contenir/contenir-tracking's `SettingKeys`:

| Setting                                  | What it is                                      |
|------------------------------------------|-------------------------------------------------|
| `google.analytics.key`                   | Google tag ID                                   |
| `google.tag_manager.id`                  | Tag Manager container ID (`GTM-…`)              |
| `facebook.analytics.key`                 | Meta pixel ID                                   |
| `linkedin.insight.partner_id`            | LinkedIn Insight partner ID                     |
| `extensions.tracking.track_environment`  | Whether this environment tracks (default no)    |
| `extensions.tracking.wait_for_consent`   | Whether the tags wait for consent (default yes) |
| `extensions.tracking.consent_regions`    | Regions where the tags wait for consent         |
| `extensions.tracking.ad_signals`         | Google ad signals (default on)                  |
| `extensions.tracking.url_passthrough`    | Google URL passthrough (default off)            |
| `extensions.consent.google_consent_mode` | The Consent add-on's `basic` or `advanced` mode |

## Configuration

Nothing is needed. To change the defaults:

```php
return [
    'contenir_tracking' => [
        'extensions_config' => 'config/autoload', // where extensions.global.php and .local.php are
        'gate'              => true,              // gate scripts whether or not the Consent add-on is enabled
    ],
];
```

`extensions_config` is relative to the working directory, which Mezzio's `public/index.php` sets to the project root.

## How it differs from the laminas-mvc adapter

- **A service, then a helper.** `Contenir\Tracking\Mezzio\Tracking` renders the tags and is registered under
  `dependencies`; the `tracking()` view helper only returns it. In the laminas-mvc adapter the helper is the renderer.
- **Settings** are the top-level config with `config['settings']` over it, where the laminas-mvc adapter reads
  `config['settings']` alone.
- **No `NonceProviderInterface`.** The laminas-mvc adapter can take the nonce from a service when `head()` is given
  none. In Mezzio that service would be shared across requests, so it couldn't hold a page's nonce; `head()` is always
  given it instead.
- **No `Module` class**, which only laminas-mvc uses: the `ConfigProvider` is all Mezzio needs.

## Licence

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
