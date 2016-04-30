TetraText
=========

**A small text/string formatting composer package for Laravel 5 that formats numeric values, money values, phone numbers, and more. There are also some limited date functions available.**

- [Installation](#installation)
- [Available Functions](#available-functions)

<a name="installation"></a>
## Installation

To install TetraText, make sure "regulus/tetra-text" has been added to Laravel 5's `composer.json` file.

	"require": {
		"regulus/tetra-text": "0.6.*"
	},

Then run `php composer.phar update` from the command line. Composer will install the TetraText package. Now, all you have to do is register the service provider and set up TetraText's alias in `config/app.php`. Add this to the `providers` array:

	Regulus\TetraText\TetraTextServiceProvider::class,

And add this to the `aliases` array:

	'Format' => Regulus\TetraText\Facade::class,

You may use 'TetraText', or another alias, but 'Format' is recommended for the sake of simplicity. TetraText is now ready to go.

<a name="available-functions"></a>
## Available Functions

**Format a string as a number:**

```php
// note: numeric() is an alias for Format::numeric()

// format a string as numeric
echo numeric($value);

// disallow decimals
echo numeric($value, false);

// allow negative numbers
echo numeric($value, true, true);
```

**Format a string into a dollar value:**

```php
// note: money() is an alias for Format::money()

// format a string as money
echo money(343);

// use euro symbol instead of dollar symbol
echo money(343, '€');

// disallow negative values (will output "$0.00")
echo money(-343, '$', false);

// remove thousands separator
echo money(343, '$', true, '');
```

The advantage of the `money()` function over PHP's `number_format()` is that negative dollar values will come out as `-$343.00` instead of `$-343.00` like they would if you simply concatenated a dollar sign to a string formatted with `number_format()`.

**Turn a value into a percent of a specified total (and avoid "division by zero" error):**

```php
// note: percent() is an alias for Format::percent()

// will output "25%"
echo percent(25, 100);

// will output "0%"
echo percent(25, 0);

// use zero decimal places (default is 1)
echo percent(25, 100, 0);

// return as a number rather than a string with the "%" symbol concatenated to the end
echo percent(25, 100, 0, true);
```

**Format a North American phone number:**

```php
// note: phone() is an alias for Format::phone()

// will output "(403) 343-1123"
echo phone(14033431123);

// will output "1 (403) 343-1123"
echo phone(14033431123, ['digits' => 11]);

// will output "(403) 343.1123"
echo phone('1-403-343-1123', ['separator' => '.']);

// will output "403.343.1123"
echo phone('1-403-343-1123', ['separator' => '.', 'areaCodeBrackets' => false]);

// will output "(403) 343-1123 x343"
echo phone('1-403-343-1123 Ext. 343');

// will output "(403) 343-1123 Ext. 343"
echo phone('1-403-343-1123 x343', ['extensionSeparator' => ' Ext. ']);

// will output "(403) 343-1123"
echo phone('1-403-343-1123 Ext. 343', ['stripExtension' => true]);
```

You may pass the `phone()` function a string or an integer. It will automatically strip out non-numeric characters before formatting the variable into a phone number.

**Format a Canadian postal code:**

```php
// note: postal_code() is an alias for Format::postalCode()

// will output "S0N 0H0"
echo postal_code('s0n0h0');

// will output "S0N0H0"
echo postal_code('s0n0h0', false);
```

**Format a boolean as a string:**

```php
// note: bool_to_str() is an alias for Format::boolToStr()

// will output "Yes"
echo bool_to_str(true);

// will output "No"
echo bool_to_str(false);

// will output "Off"
echo bool_to_str(false, 'On/Off');

// will output "Up"
echo bool_to_str(true, ['Up', 'Down']);
```

**Add a suffix to a number:**

```php
// will output '1<sup class="number-suffix">st</sup>'
echo Format::numberSuffix(1);

// will output "2nd"
echo Format::numberSuffix(2, false);

// will output "3<span>rd</span>"
echo Format::numberSuffix(3, 'span', false);
```

**Pluralize an item name based on a specified number:**

```php
// will output "item"
echo Format::pluralize('item', 1);

// will output "items"
echo Format::pluralize('item', 2);

// will output "fungi"
echo Format::pluralize('fungus', 2, 'fungi');
```

**Pluralize a string based on a specified number:**

```php
$users   = User::all();
$message = "Displaying :number :item.";

// may output "Displaying 3 users."
echo Format::pluralizeMessage($message, 'user', count($users));
```

**Get the correct English word prefix for an item name ("a" or "an", based on the sound of the starting syllable):**

```php
// will output "a frog"
echo Format::a('frog');

// will output "an octopus"
echo Format::a('octopus');

// will output "an HTML" (method checks to see if item is all uppercase to denote acronym and then uses letter sound instead)
echo Format::a('HTML');
```

**Convert a string to HTML characters:**

```php
// will output "Penn &amp; Teller"
echo entities('Penn & Teller');
```

**Convert a string to a URI slug:**

```php
// will output "turn-this-title-into-a-slug"
echo Format::slug('Turn This Title Into a Slug!');
```

**Convert a string to a unique URI slug based on the specified table and field name:**

```php
// may output "turn-this-title-into-a-slug-2" if "blog_posts" table already has a row with slug
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts');

// set an ID to ignore/prevent slug conflicts for (ID of table row being edited)
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', ['ignoreId' => 343]);

// set a character limit for the slug
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', ['charLimit' => 64]);

// use a different field than "slug" in DB table
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', ['field' => 'uri_tag']);

// ignore soft deleted records
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', ['softDelete' => true]);

// add additional matching values
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', ['matchingValues' => ['type' => 'Microblog']]);
echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', ['matchingValues' => ['type' => '>= 3']]);
```

**Get the first day of a week:**

```php
// will output "2013-09-22" (using "Sunday" as the first day)
echo Format::firstDayOfWeek('2013-09-27');

// will output "2013-09-23"
echo Format::firstDayOfWeek('2013-09-27', 'Monday');
```

**Get the last day of a week:**

```php
// will output "2013-09-28" (using "Sunday" as the first day)
echo Format::lastDayOfWeek('2013-09-27');

// will output "2013-09-29"
echo Format::lastDayOfWeek('2013-09-27', 'Monday');
```

**Get the first day of a month:**

```php
// will output "September 1"
echo Format::firstDayOfMonth('2013-09-27', 'F j');
```

**Get the last day of a month:**

```php
// will output "2013-09-30"
echo Format::lastDayOfMonth('2013-09-27');
```

**Convert new lines to paragraphs:**

```php
// will output "<p>This is the first paragraph.</p><p>This is the second paragraph.</p>"
echo Format::paragraphs("This is the first paragraph.\nThis is the second paragraph.");
```

**Apply a character limit to a string:**

```php
$string = 'I define <strong>anarchist society</strong> as one where there is no legal possibility for coercive aggression against the person or property of any individual. Anarchists oppose the State because it has its very being in such aggression, namely, the expropriation of private property through taxation, the coercive exclusion of other providers of defense service from its territory, and all of the other depredations and coercions that are built upon these twin foci of invasions of individual rights. <div class="author">-Murray Rothbard</div>';

// will output 'I define <strong>anarchist society</strong> as one where there is no legal possibility for coercive aggression<span class="exceeded-limit">...</span>'
echo char_limit($string, 93);

// will output 'I define <strong>anarchist society</strong> as one where there is no legal possibility for coercive aggression'
echo char_limit($string, 93, ['exceededText' => false]);

// will output 'I define <strong>anarchist society</strong> as one where there is no legal possibility for coercive aggression<a href="https://en.wikiquote.org/wiki/Murray_Rothbard" class="read-more">Read more...</a>'
echo word_limit($string, 14, ['exceededText' => 'Read more...', 'exceededLinkUrl' => 'https://en.wikiquote.org/wiki/Murray_Rothbard']);

// note: char_limit() and word_limit() are aliases for Format::charLimit(), Format::wordLimit()
```

> **Note:** `charLimit()` and `wordLimit()` were designed to maintain HTML tag integrity.

**Apply a character limit to a string:**

```php
// get a translation and make it lowercase (if it does not appear to be an acronym)
echo trans_l($value);

// get a translation choice and make it lowercase (if it does not appear to be an acronym)
echo trans_choice_l($value, 1); //would output "item" from translation variable of "Item|Items"

// get a translation and prepend with "a" or "an" if language is English or exceeds 2 letter language code
echo trans_a($value); //would output "an umbrella" from translation variable of "umbrella"

// get a translation and prepend with "a" or "an" if language is English or exceeds 2 letter language code
echo trans_choice_a($value, 1); //would output "an umbrella" from translation variable of "umbrella|umbrellas"
echo trans_choice_a($value, 2); //would output "2 umbrellas" from translation variable of "umbrella|umbrellas"

// note: trans_l(), trans_choice_l(), trans_a(), and trans_choice_a() are aliases
// for Format::transL(), Format::transChoiceL(), Format::transA(), and Format::transChoiceA()
```

> **Note:** `transA()` and `transChoiceA()` can also make use your resulting string lowercase by setting the second or third argument to `true`. The second argument is the `parameters` array, but if it is a boolean, the `parameters` array will be set to empty and it will be interpreted as the `lower` argument instead.