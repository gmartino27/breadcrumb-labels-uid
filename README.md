# Breadcrumb Labels UID

TYPO3 v14 backend extension that restores the behaviour known from TYPO3
versions before v14: the record uid is appended to the current record's
breadcrumb label (e.g. `My page [42]`).

## Why

Since TYPO3 v14 the backend breadcrumb no longer shows the record uid. This
extension brings it back without patching the core, as a drop-in package that
can be installed via Composer.

## How it works

The core `TYPO3\CMS\Backend\Breadcrumb\RecordBreadcrumbProvider` is `final` and
`@internal`, so it cannot be subclassed. The breadcrumb component instead
selects providers by priority (highest `getPriority()` first) and uses the first
one whose `supports()` returns `true`.

This extension registers
`Gmartino\BreadcrumbLabelsUid\Breadcrumb\RecordUidBreadcrumbProvider` with a
higher priority. It decorates the core provider: breadcrumb generation is fully
delegated to the core provider, and only the label of the resulting
current-record node is post-processed to append ` [uid]` (idempotently — an
existing suffix is never duplicated).

## Installation

```bash
composer require gmartino/breadcrumb-labels-uid
```

In TYPO3 Composer mode the extension is active immediately. Flush the backend
caches afterwards:

```bash
vendor/bin/typo3 cache:flush
```

## Compatibility

- TYPO3 v14
- PHP 8.2+

## License

GPL-2.0-or-later
