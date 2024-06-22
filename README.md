# TavvetDoctrinePrefixBundle

### Configuration example

You can configure prefixes and base naming strategy in app/config/config.yml

```yaml
doctrine:
    # ...
    orm:
        # ...
        naming_strategy: tavvet_doctrine_prefix.prefix_naming_strategy

tavvet_doctrine_prefix:
    table_prefix: t_ # default ''
    column_prefix: c__ # default ''
    naming_strategy: # base naming strategy
        type: doctrine.orm.naming_strategy.underscore # default - 'doctrine.orm.naming_strategy.underscore'
        arguments: [] # Constructor arguments for base naming strategy, default = []
```
