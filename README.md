# Dot

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
                (object)['name' => 'item1'],
                (object)['name' => 'item3'],
            ]
        ],
        (object)[
            'name' => 'group2',
            'items' => []
        ],
        (object)[
            'name' => 'group3',
            'items' => [
                (object)['name' => 'item2'],
            ]
        ],
    ]
];
```

### Methods

- [get](#dotgetstring-path-mixed)
- [has](#dothasstring-path-bool)
- [push](#dotpushstring-path-mixed-value-void)
- [set](#dotsetarraystring-paths-mixed-value-void)

All methods are also available as standalone functions ie `\Noj\Dot\get($data, 'groups')`

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
echo $data['groups'][2]['items'][0]['name']; // 'a different name'
```

Set nested keys from multiple paths using a wildcard `*`:

```php
$dot->set('groups.*.items.*.name', 'set all to same name');
```

Keys will be created if they don't already exist:

```php
$dot->set('groups.0.items.2.name', 'a new item');
```

By default set will initialise missing values as empty arrays. To indicate that something should be an object use the `->` delimiter:
```php
$dot->set('groups.0.items.2->name', 'a new item');
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
