# Twig Relative Path Extension

An extension for [Twig](https://twig.symfony.com/) that allows to include and extends templates using relative paths.

## Instalation

```bash
composer require diasfs/twig-relative-path
```

## Initialization

```php
use Twig\Extension\RelativePathExtension;
use Twig\Environment;

...

$twig = new Environment($loader);
$twig->addExtension(new RelativePathExtension());
```

## Example

```jinja
{# layout.html.twig #}
<!DOCTYPE html>
<html>
    <head>
        ...
    </head>
    <body>
        {% block content '' %}
    </body>
</html>

{# pages/inc/form.html.twig #}
<form>
    ...
</form>


{# pages/page.html.twig #}
{% extends "../layout.html.twig" %}

{% block content %}
    {% include './inc/form.html.twig' %}
{% endblock %}
```

The resulting html will be the following:

```html
<!DOCTYPE html>
<html>
    <head>
        ...
    </head>
    <body>
        <form>
            ...
        </form>
    </body>
</html>
```


## License

The library is released under the MIT License. See the bundled [LICENSE](LICENSE) file for details.