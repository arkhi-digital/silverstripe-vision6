# silverstripe-vision6

[![Code Climate](https://codeclimate.com/github/steadlane/silverstripe-vision6/badges/gpa.svg)](https://codeclimate.com/github/steadlane/silverstripe-vision6)
[![Latest Stable Version](https://poser.pugx.org/steadlane/vision6/version)](https://packagist.org/packages/steadlane/vision6)
[![License](https://poser.pugx.org/steadlane/vision6/license)](https://packagist.org/packages/steadlane/vision6)

## Installation

This module only supports installation via composer:

```
composer require steadlane/vision6
```

Run `/dev/build` afterwards for SilverStripe to become aware of this extension

Visit `/dev/tasks` and run the `Synchronize Vision6 Lists` and `Synchronize Vision6 Fields` tasks in that order (won't be required in future, will intelligently sync when required)


## Short Code
To include a subscriber form on any given page from within the CMS you will need to use the shortcode below:

**Code:**
```
[vision6_list, list_id=<<YOUR LIST ID>>]
```

**Note**: Change `<<<YOUR LIST ID>>>` to the ID of the form you want to display. See [Finding my List ID](#finding-my-list-id)

## Template Syntax
Within an .SS template you can invoke a list in a similar way you would for the shortcode

**Code:**
```
$Vision6List(<<<YOUR LIST ID>>>);
```

**Note**: Change `<<<YOUR LIST ID>>>` to the ID of the form you want to display. See [Finding my List ID](#finding-my-list-id)

## Caveat
Due to the way forms are being generated dynamically, and in order to maintain the functionality provided by `Form` you may only have one list per page. Having more will lead to validation issues and data handling issues. If you feel you can eliminate this caveat then we implore you to submit a PR

## Finding my List ID

1. Login to Vision6 dashboard and select "Lists and Forms" from the side menu
2. Find the list you want the ID for and click the "Edit" button
3. You can find the list ID in the address bar, eg for `http://www.vision6.com.au/list/form/designer?id=377499` your list ID is 377499

## Contributing

If you feel you can improve this module in any way, shape or form please do not hesitate to submit a PR for review.

## Bugs / Issues

To report a bug or an issue please use our [issue tracker](https://github.com/steadlane/silverstripe-vision6/issues).

## License

This module is distributed under the [BSD-3 Clause](https://github.com/steadlane/silverstripe-vision6/blob/master/LICENSE) license.
