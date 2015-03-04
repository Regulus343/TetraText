TetraText
=========

**A small text/string formatting composer package for Laravel 5 that formats numeric values, money values, phone numbers, and more. There are also some limited date functions available.**

> **Note:** For Laravel 4, you may use <a href="https://github.com/Regulus343/TetraText/tree/v0.4.4">version 0.4.4</a>.

- [Installation](#installation)
- [Available Functions](#available-functions)

<a name="installation"></a>
## Installation

To install TetraText, make sure "regulus/tetra-text" has been added to Laravel 5's `composer.json` file.

	"require": {
		"regulus/tetra-text": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the TetraText package. Now, all you have to do is register the service provider and set up TetraText's alias in `config/app.php`. Add this to the `providers` array:

	'Regulus\TetraText\TetraTextServiceProvider',

And add this to the `aliases` array:

	'Format' => 'Regulus\TetraText\Facade',

You may use 'TetraText', or another alias, but 'Format' is recommended for the sake of simplicity. TetraText is now ready to go.

<a name="available-functions"></a>
## Available Functions

**Format a string as a number:**

	//note: numeric() is an alias for Format::numeric()

	//format a string as numeric
	echo numeric($value);

	//disallow decimals
	echo numeric($value, false);

	//allow negative numbers
	echo numeric($value, true, true);

**Format a string into a dollar value:**

	//note: money() is an alias for Format::money()

	//format a string as money
	echo money(100);

	//use euro symbol instead of dollar symbol
	echo money(100, '€');

	//disallow negative numbers
	echo money(100, '$', false);

	//remove thousands separator
	echo money(100, '$', true, '');

The advantage of the `money()` function over PHP's `number_format()` is that negative dollar values will come out as `-$343.00` instead of `$-343.00` like they would if you simply concatenated a dollar sign to a string formatted with `number_format()`.

**Turn a value into a percent of a specified total:**

	//note: percent() is an alias for Format::percent()

	//will output "25%"
	echo percent(25, 100);

	//use zero decimal places (default is 1)
	echo percent(25, 100, 0);

	//will return as a number rather than a string with the "%" symbol concatenated to the end
	echo percent(25, 100, 0, true);

The advantage of using the `percent()` function is that it will automatically avoid the "division by zero" error and return `0%`.

**Format a North American phone number:**

	//note: phone() is an alias for Format::phone()

	//will output "(403) 343-5555"
	echo phone(14033435555);

	//will output "1 (403) 343-5555"
	echo phone(14033435555, 11);

	//will output "(403) 343.5555"
	echo phone('1-403-343-5555', 10, '.');

	//will output "403.343.5555"
	echo phone('1-403-343-5555', 10, '.', false);

You may pass the `phone()` function a string or an integer. It will automatically strip out non-numeric characters before formatting into a phone number.

**Format a Canadian postal code:**

	//note: postal_code() is an alias for Format::postalCode()

	//will output "S0N 0H0"
	echo postal_code('s0n0h0');

	//will output "S0N0H0"
	echo postal_code('s0n0h0', false);

**Format a boolean as a string:**

	//note: bool_to_str() is an alias for Format::boolToStr()

	//will output "Yes"
	echo bool_to_str(true);

	//will output "No"
	echo bool_to_str(false);

	//will output "Off"
	echo bool_to_str(false, 'On/Off');

	//will output "Up"
	echo bool_to_str(true, array('Up', 'Down'));

**Format an array of items as a string:**

	//will output "Tiger, Rhino, Dinosaur"
	echo Format::listToStr(array('Tiger', 'Rhino', 'Dinosaur'));

	//will output "Tiger<br />Rhino<br />Dinosaur"
	echo Format::listToStr(array('Tiger', 'Rhino', 'Dinosaur'), '<br />');

	//will output "Tiger, Dinosaur"
	echo Format::listToStr(array('Tiger' => true, 'Rhino' => false, 'Dinosaur' => true));

**Format a collection of objects as a string list of items using a specific attribute or mutator from the object:**

	$users = User::orderBy('id')->get();

	may output "Joe, Sue, Jim"
	echo Format::objListToStr($items, 'first_name');

	may output "Joe Smith, Sue Johnson, Jim Tucker" (assuming the User model has a getName() method)
	echo Format::objListToStr($items, 'getName()');

**Pluralize an item name based on a specified number:**

	//will output "item"
	echo Format::pluralize('item', 1);

	//will output "items"
	echo Format::pluralize('item', 2);

	//will output "pagez"
	echo Format::pluralize('page', 2, 'pagez');

**Pluralize a string based on a specified number:**

	$users = User::all();
	$message = "Displaying :number :item.";

	//may output "Displaying 3 users."
	echo Format::pluralizeMessage($message, 'user', count($users));

**Get the correct English word prefix for an item name ("a" or "an", based on the sound of the starting syllable):**

	//will output "a"
	echo Format::a('frog');

	//will output "an"
	echo Format::a('octopus');

	//will output "an" (method checks the first two letters for capitals to denote acronym and then uses letter sound)
	echo Format::a('HTML');

**Convert a string to HTML characters:**

	//will output "PHP &amp; Laravel 4"
	echo Format::entities('PHP & Laravel 4');

**Convert a string to a URI slug:**

	//will output "turn-this-title-into-a-slug"
	echo Format::slug('Turn This Title Into a Slug!');

**Convert a string to a unique URI slug based on the specified table and field name:**

	//may output "turn-this-title-into-a-slug-2" if "blog_posts" table already has a row with slug
	echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts');

	//set an ID to ignore/prevent slug conflicts for (ID of table row being edited)
	echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', 3);

	//set a character limit for the slug
	echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', 3, 52);

	//use a different field than "slug" in DB table
	echo Format::uniqueSlug('Turn This Title Into a Slug!', 'blog_posts', 3, false, 'uri_tag');

**Get the first day of a week:**

	//will output "2013-09-22" (using "Sunday" as the first day)
	echo Format::firstDayOfWeek('2013-09-27');

	//will output "2013-09-23"
	echo Format::firstDayOfWeek('2013-09-27', 'Monday');

**Get the last day of a week:**

	//will output "2013-09-28" (using "Sunday" as the first day)
	echo Format::lastDayOfWeek('2013-09-27');

	//will output "2013-09-29"
	echo Format::lastDayOfWeek('2013-09-27', 'Monday');

**Get the first day of a month:**

	//will output "2013-09-01"
	echo Format::firstDayOfMonth('2013-09-27');

**Get the last day of a month:**

	//will output "2013-09-30"
	echo Format::lastDayOfMonth('2013-09-27');

**Convert new lines to paragraphs:**

	//will output "<p>This is the first paragraph.</p><p>This is the second paragraph.</p>"
	echo Format::paragraphs("This is the first paragraph.\nThis is the second paragraph.");

**Apply a character limit to a string:**

	$string = "This is a not-very-long string, but long enough to test charLimit().";

	//will output "This is a not-very-long stri..."
	echo Format::charLimit($string, 24);

	//will output "This is a not-very-long stri"
	echo Format::charLimit($string, 24, false);

	//will output 'This is a not-very-long stri<a href="http://website.com/articles/test-article" class="read-more">read more</a>'
	echo Format::charLimit($string, 24, 'read more', 'http://website.com/articles/test-article');

	//may output 'This is a not-very-long stri<a href="http://website.com/articles/test-article" class="read-more">read more</a>'
	echo Format::charLimit($string, 24, 'read more', 'article/test-article');