# Dot

[![Travis (.com)](https://img.shields.io/travis/com/jonnyynnoj/dot?style=flat-square)](https://travis-ci.com/github/jonnyynnoj/dot)
[![Latest Stable Version](https://poser.pugx.org/noj/dot/v/stable?format=flat-square)](https://packagist.org/packages/noj/dot)
![PHP Version Support](https://img.shields.io/packagist/php-v/noj/dot?style=flat-square)
[![License](https://poser.pugx.org/noj/dot/license?format=flat-square)](https://packagist.org/packages/noj/dot)
[![Total Downloads](https://poser.pugx.org/noj/dot/downloads?format=flat-square)](https://packagist.org/packages/noj/dot)

Dot allows you to get & set array keys or object properties using dot notation.

## Installing

```bash
composer require noj/dot
```

## Usage

First construct a new Dot instance:

```php
$dot = new Dot($data);
$dot = Dot::from($data); // alternative
```

All the examples are using the following data structure unless otherwise specified:

```php
$data = [
    'groups' => [
        (object)[
            'name' => 'group1',
            'items' => [
                [
                    'name' => 'item1',
                    'rare' => false,
                ],
                [
                    'name' => 'item3',
                    'rare' => true,
                ],
            ]
        ],
        (object)[
            'name' => 'group2',
            'items' => []
        ],
        (object)[
            'name' => 'group3',
            'items' => [
                [
                    'name' => 'item2',
                    'rare' => true,
                ],
            ]
        ],
    ]
];
```

### Methods

- [count](#dotcountstring-path-int)
- [find](#dotfindstring-path-mixed-equals-dot)
- [get](#dotgetstring-path-mixed)
- [has](#dothasstring-path-bool)
- [push](#dotpushstring-path-mixed-value-void)
- [set](#dotsetarraystring-paths-mixed-value-void)

All methods are also available as standalone functions ie `\Noj\Dot\get($data, 'groups')`

#### `Dot::count(string $path): int`

Count the number of items at a given path.
```php
$dot->count('groups.0.items'); // 2
$dot->count('groups.*.items'); // 3
```

#### `Dot::find(string $path, mixed $equals): Dot`

Find items matching the given condition.

```php
// find where property === value
$dot->find('groups.*.items.*.rare', true)->get();
/*
[
    ['name' => 'item2', 'rare' => true],
    ['name' => 'item3', 'rare' => true]
]
*/

// pass a callback for custom comparisons 
$dot->find('groups.*.items.*.name', function (string $name) {
    return $name === 'item2' || $name === 'item3';
})->get(); // returns same as above

// leave off the property to receive the whole item
$dot->find('groups.*.items.*', function (array $item) {
    return $item['name'] === 'item3' && $item['rare'];
})->get();
```

#### `Dot::get(string $path): mixed`

Access nested array keys and object properties using dot syntax:

```php
$dot->get('groups.0.items.1.name'); // 'item3'
```

Dot safely returns null if the key or property doesn't exist:

```php
$dot->get('groups.3.items.1.name'); // null
```

You can use a wildcard `*` to pluck values from multiple paths:

```php
$dot->get('groups.*.items.*.name'); // ['item1', 'item3', 'item2']

$dot->get('groups.*.items'); /* [
    ['name' => 'item1', 'rare' => false],
    ['name' => 'item3', 'rare' => true],
    ['name' => 'item2', 'rare' => true],
] */
```

You can call functions using the `@` prefix:

```php
$data = [
    'foo' => new class {
        public function getBar() {
            return ['bar' => 'value'];
        }
    }
];

(new Dot($data))->get('foo.@getBar.bar'); // 'value'
```

If no argument is passed it will return the underlying data:

```php
$dot->get() === $data; // true
```

#### `Dot::has(string $path): bool`

Returns true if path exists, false otherwise:

```php
$dot->has('groups.0.items.1.name'); // true
```

#### `Dot::push(string $path, mixed $value): void`

Push a value onto an existing array:

```php
$dot->push('groups.0.items', ['name' => 'another item']);

// supports wildcards
$dot->push('groups.*.items', ['name' => 'another item']);
```

#### `Dot::set(array|string $paths, mixed $value): void`

You can set nested values using the same syntax:

```php
$dot->set('groups.2.items.0.name', 'a different name');
echo $data['groups'][2]->items[0]['name']; // 'a different name'
```

Set nested keys from multiple paths using a wildcard `*`:

```php
$dot->set('groups.*.items.*.name', 'set all to same name');
```

Keys will be created if they don't already exist:

```php
$dot->set('groups.0.items.2.name', 'a new item');
```

By default, set will initialise missing values as empty arrays. To indicate that something should be an object use the `->` delimiter:
```php
$dot->set('groups.3->items.2.name', 'a new item');
```

You can set multiple values at once by passing an array:

```php
$dot->set([
    'groups.0.items.1.name' => 'something',
    'groups.2.items.0.name' => 'something else',
]):
```

You can call a method:

```php
$data = [
    'foo' => new class {
        public $bars = [];
        public function addBar($bar) {
            $this->bar[] = $bar;
        }
    }
];

$dot = new Dot($data);
$dot->set('foo.@addBar', 'value');
echo $data['foo']->bars; // ['value']
```

Or call a method for each value of an array:

```php
$dot->set('foo.@addBar*', ['value1', 'value2']);
echo $data['foo']->bars; // ['value1', 'value2']
```
