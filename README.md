# TavvetDoctrinePrefixBundle

A Symfony bundle that adds a configurable prefix to Doctrine ORM table and column
names, without replacing your naming strategy — it wraps whichever one you already
use (`underscore`, `default`, or a custom one) and prefixes its output. Useful when
several applications share one database schema and need namespaced tables/columns
(e.g. `t_user` / `c_first_name`), or when an existing schema convention requires it.

## Requirements

- PHP ^8.2
- `symfony/framework-bundle` ~7.0
- `doctrine/orm` ~3.2

## Installation

```bash
composer require tavvet/tavvet-doctrine-prefix-bundle
```

If your app doesn't use Symfony Flex to auto-register bundles, add it manually in
`config/bundles.php`:

```php
return [
    // ...
    Tavvet\DoctrinePrefixBundle\TavvetDoctrinePrefixBundle::class => ['all' => true],
];
```

## Configuration

Point Doctrine's `naming_strategy` at the service this bundle registers, then
configure the prefixes:

```yaml
doctrine:
    orm:
        naming_strategy: tavvet_doctrine_prefix.prefix_naming_strategy

tavvet_doctrine_prefix:
    table_prefix: t_ # default ''
    column_prefix: c__ # default ''
    naming_strategy: # base naming strategy this bundle wraps
        type: doctrine.orm.naming_strategy.underscore # default - 'doctrine.orm.naming_strategy.underscore'
        arguments: [] # constructor arguments for the base naming strategy, default = []
```

| Key                        | Type   | Default                                    | Description |
|-----------------------------|--------|---------------------------------------------|-------------|
| `table_prefix`               | string | `''`                                         | Prepended to every table name. |
| `column_prefix`               | string | `''`                                         | Prepended to every column name. |
| `naming_strategy.type`         | string | `doctrine.orm.naming_strategy.underscore`      | Container service id of the base naming strategy to wrap. Must resolve to a real service — `doctrine/doctrine-bundle` registers `doctrine.orm.naming_strategy.default` and `doctrine.orm.naming_strategy.underscore` out of the box; you can also point this at your own service id. Resolved once, at container-compile time — a typo fails fast with a clear `ServiceNotFoundException` instead of breaking silently at runtime. |
| `naming_strategy.arguments`     | array  | `[]`                                         | Constructor arguments used to build a **new** instance of the resolved class (e.g. `[1]` for `UnderscoreNamingStrategy`'s `CASE_UPPER`). This instance is private to the prefixing strategy — it does not reuse or affect the shared `naming_strategy.type` service elsewhere in the container. |

## How it works

`PrefixNamingStrategy` implements Doctrine's `NamingStrategy` and delegates every
call to the wrapped base strategy, prefixing the result:

```
classToTableName('App\Entity\User')   -> table_prefix . underscore('User')       -> 't_user'
propertyToColumnName('firstName', …)  -> column_prefix . underscore('firstName') -> 'c_first_name'
```

Because the wrapped strategy is built from a **class name**, not a live service, a
compiler pass (`ResolveNamingStrategyPass`) resolves `naming_strategy.type` from a
service id to its class at container-compile time — this is what lets
`naming_strategy.arguments` construct a differently-configured instance than
whatever `doctrine-bundle` already wired up for that id.

## Caveat: explicit names bypass the prefix

Doctrine only consults a naming strategy when a name is **not** given explicitly.
So an entity or field with an explicit name is **not** prefixed:

```php
#[ORM\Table(name: 'legacy_users')] // stays 'legacy_users', no table_prefix
#[ORM\Column(name: 'raw_email')]   // stays 'raw_email', no column_prefix
```

If you rely on the prefix being applied across the whole schema (e.g. a shared
database), avoid hard-coding `name:` on tables and columns and let the naming
strategy generate them.

## Development

Everything runs in Docker, no local PHP toolchain needed.

```bash
docker compose up -d
docker compose exec php composer install
docker compose exec php vendor/bin/phpunit          # tests (incl. a functional schema-generation test)
docker compose exec php vendor/bin/phpstan analyse  # static analysis (level 9)
docker compose down -v --rmi local                  # tear down: remove container, image and volume
```

Or, as a single ephemeral run that leaves nothing behind:

```bash
docker compose run --rm php sh -c "composer install && vendor/bin/phpunit && vendor/bin/phpstan analyse"
docker compose down -v --rmi local
```

## License

MIT — see [LICENSE](LICENSE).
