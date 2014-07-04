ScrutinizerFormatter
====================

Creates a formatter for PHPSpec to display failed scenarios against files in Scrutinizer

## Usage
In order to run your PHPSpec tests and have Scrutinizer show any failed scenarios against their appropriate spec files, add the following to your build config:

```yaml
before_commands:
    - "composer install"
    
tools:
    custom_commands:
        -
            scope: file
            command: vendor/phpspec/phpspec/bin/phpspec run %pathname% -f scrutinizer --no-code-generation || true
```
