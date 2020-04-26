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

### `Dot::get(string $path)`

Access nested array keys and object properties using dot syntax:

```php
$dot->get('groups.0.items.1.name'); // 'item3'
```

Dot safely returns null if the key or property doesn't exist:

```php
$dot->get('groups.3.items.1.name'); // null
```

You can pluck values from multidimensional arrays using the `*` syntax:

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

### `Dot::set(array|string $paths, mixed $value)`

You can set nested values using the same syntax:

```php
$dot->set('groups.2.items.0.name', 'a different name');
echo $data['groups'][2]['items'][0]['name']; // 'a different name'
```

Set nested keys of a multidimensional array using the `*` syntax:

```php
$dot->set('groups.*.items.*.name', 'set all to same name');
```

You can set multiple paths at once by passing an array:

```php
$dot->set([
    'groups.0.items.1.name' => 'something',
    'groups.2.items.0.name' => 'something else',
]):
```

#### Invoke Setter

You can call a setter method:

```php
$data = [
    'foo' => new class {
        public $bar;
        public function setBar($bar) {
            $this->bar = $bar;
        }
    }
];

$dot = new Dot($data);
$dot->set('foo.@setBar', 'value');
echo $dot->get('foo.bar'); // 'value'
echo $data['foo']->bar; // 'value'
```

Or call a setter for each value of an array:

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
$dot->set('foo.@addBar*', ['value1', 'value2']);
echo $dot->get('foo.bars'); // ['value1', 'value2']
echo $data['foo']->bars; // ['value1', 'value2']
```
