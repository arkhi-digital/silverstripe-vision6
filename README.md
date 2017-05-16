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

## Configuration

Your Vision6 API key must be defined as the constant `VISION6_API_KEY`. In your `mysite/_config.php` you'll be required to add:

```php
define('VISION6_API_KEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
```

Once configured you can then `?flush=1` to syncronise your Vision6 Mailing Lists with SilverStripe. These lists will be refreshed upon every flush

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

## Independent Form Field
This module comes with a `SubscribeField` which is based off `CheckboxField` the only difference is that the validator on this will actually check to see if an email is subscribed to a specific list.

A complete example for you to implement this yourself:

```php
class TestV6Form extends Form
{
    public function __construct(Controller $controller, $name)
    {
        /** @var Vision6SubscribeField $subscriberField */
        $subscriberField = Vision6SubscribeField::create('Subscribe', 'Subscribe');
        $subscriberField->setEmailFieldName('Email');
        $subscriberField->setListId(375305);
        $subscriberField->setValue(1);

        $fields = FieldList::create(
            array(
                EmailField::create('Email'),
                $subscriberField
            )
        );

        $actions = FieldList::create(
            FormAction::create('process', 'Subscribe')
        );

        $validator = RequiredFields::create(
            array(
                'Email'
            )
        );

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }
}
```

Then in your form handling function, which in the above example would be `process` simply
 
```php
Vision6::subscribeEmail(375305, 'me@example.com');
```

## Contributing
If you feel you can improve this module in any way, shape or form please do not hesitate to submit a PR for review.

## Bugs / Issues
To report a bug or an issue please use our [issue tracker](https://github.com/steadlane/silverstripe-vision6/issues).

## License
This module is distributed under the [BSD-3 Clause](https://github.com/steadlane/silverstripe-vision6/blob/master/LICENSE) license.
