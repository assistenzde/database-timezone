# Database Timezone

[![Latest Stable Version](https://poser.pugx.org/assistenzde/database-timezone/version)](https://packagist.org/packages/assistenzde/database-timezone)
[![Total Downloads](https://poser.pugx.org/assistenzde/database-timezone/downloads)](https://packagist.org/packages/assistenzde/database-timezone)
[![License](https://poser.pugx.org/assistenzde/database-timezone/license)](https://packagist.org/packages/assistenzde/database-timezone)
[![PHP ≥v7.4](https://img.shields.io/badge/PHP-%E2%89%A57%2E4-0044aa.svg)](https://www.php.net/manual/en/migration74.new-features.php)
[![Symfony ≥5](https://img.shields.io/badge/Symfony-%E2%89%A55-0044aa.svg)](https://symfony.com/)

The **Database Timezone** bundle contains method to save dates and datetimes values/objects always in the same (custom) timezone in database.
This results in an easier datetime/date handling when accessing database values directly or via PHP.

Quick example usage:

Always save dates/times in **UTC** timezone in database. Change the configuration of `/config/packages/database_timezone.yaml` to:
```yaml
database_timezone:
  database: UTC
```

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Symfony Configuration](#symfony-configuration)
- [Usage](#usage)

## Requirements

The usage of [**PHP ≥ v7.4**](https://www.php.net/manual/en/migration74.php)
and [**Symfony ≥ 5**](https://symfony.com/doc/5.0/setup.html) is recommended.

## Installation

Please install via [composer](https://getcomposer.org/).

```bash
composer require assistenzde/database-timezone
```

The bundle will be automatically added to your `bundles.yaml` configuration.

## Symfony Configuration

Please add a `database_timezone.yaml` file in your configuration directory (i.e. */config/pakcages/*) and spcify the timezone to save all datatime values in the database.
```yaml
database_timezone:
  database: UTC
```

## Usage

All database values will be saved in the configured timezone.

