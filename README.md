# Dot

Dot allows you to get & set array keys or object properties using dot notation.

## Installing

```bash
composer require noj/dot
```

## Usage

### Get

Access nested array keys and object properties using dot syntax:

```php
$data = [
    'foo' => (object)[
        'bar' => [
            'baz' => 'value'
        ]
    ]
];

(new Dot($data))->get('foo.bar.baz'); // 'value'
// or alternatively
Dot::from($data)->get('foo.bar.baz'); // 'value'
```

You can access numeric array indexes in the same way:

```php
$data = ['values' => ['foo', 'bar']];
(new Dot($data))->get('values.1'); // bar
```

Dot safely returns null if the key or property doesn't exist:

```php
$data = ['foo' => ['bar' => 1]];
(new Dot($data))->get('foo.baz'); // null
```

You can pluck values from multiple paths:

```php
$data = [
    'nested' => [
        'data' => [
            ['foo' => ['bar' => 'value1']],
            ['foo' => ['bar' => 'value2']],
            ['foo' => ['bar' => 'value2']],
        ]
    ]
];

$dot = new Dot($data);
$dot->get('nested.data.*.foo') // [['bar' => 'value1'], ['bar' => 'value2'], ['bar' => 'value3']]
$dot->get('nested.data.*.foo.bar') // ['value1', 'value2', 'value3']
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

### Set

You can set nested values using the same syntax:

```php
$data = [
    'foo' => (object)[
        'bar' => [
            'baz' => 'value'
        ]
    ]
];

$dot = new Dot($data);
$dot->set('foo.bar.baz', 'something');
echo $dot->get('foo.bar.baz'); // 'something'
echo $data['foo']['bar']['baz']; // 'something'
```

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

Or call a setter multiple times for each value of an array:

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
