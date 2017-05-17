<?php

/**
 * Creates an editable field that allows users to sign up to a Vision6 Mailing List
 *
 * @method UserDefinedForm Parent
 * @property int ListID
 * @property string EmailField
 * @property bool GracefulReject
 */
class EditableVision6SubscribeField extends EditableFormField
{
    /** @var array */
    private static $db = array(
        'ListID' => 'Int',
        'EmailField' => 'Varchar(255)',
        'GracefulReject' => 'Boolean'
    );

    /** @var array */
    private static $defaults = array(
        'GracefulReject' => true
    );

    /** @var string */
    private static $singular_name = 'Vision6 Subscriber Field';

    /** @var string */
    private static $plural_name = 'Vision6 Subscriber Fields';

    /** @var string */
    private static $icon = 'vision6/images/editablevision6subscribefield.png';

    /**
     * {@inheritdoc}
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fieldId = $this->ID;
        $parent = $this->Parent();
        $lists = $this->getLists();

        $this->beforeUpdateCMSFields(function (FieldList $fields) use ($fieldId, $parent, $lists) {
            /** @var DataList $otherFields */
            $otherFields = EditableFormField::get()->filter(
                array(
                    'ParentID' => $parent->ID,
                    'ID:not' => $fieldId,
                    'ClassName:not' => array(
                        'EditableFormStep',
                        'EditableFieldGroup',
                        'EditableFieldGroupEnd',
                    )
                )
            )->map('Name', 'Title');

            $fields->addFieldsToTab(
                'Root.Main',
                array(
                    DropdownField::create('ListID', 'Mailing List', $lists),
                    DropdownField::create('EmailField', 'Email Field', $otherFields)->setHasEmptyDefault(true)->setEmptyString('Please select...')->setRightTitle('This is the field that the email address will be extracted from if the user opts to subscribe'),
                    DropdownField::create('GracefulReject', 'Graceful Rejections', array('Disabled', 'Enabled'))->setRightTitle('If disabled, the form will not pass validation and the user will be returned to it if the email is found to already be in the specified mailing list')
                )
            );
        });

        return parent::getCMSFields();
    }

    /**
     * Get a map of all mailing lists
     *
     * @return array
     */
    public function getLists()
    {
        $lists = Vision6List::get()->sort('Name', 'ASC');

        if (!$lists->count()) {
            Vision6Sync::flush();
            $lists = Vision6List::get()->sort('Name', 'ASC');
        }

        $map = array();

        /** @var Vision6List $list */
        foreach ($lists as $list) {
            $map[$list->ListID] = $list->Name;
        }

        return $map;
    }

    /**
     * @return Vision6SubscribeField|false
     */
    public function getFormField()
    {
        if ($this->ListID && $this->EmailField) {
            $subscriberField = Vision6SubscribeField::create($this->Name, $this->Title);
            $subscriberField->setListId($this->ListID);
            $subscriberField->setEmailFieldName($this->EmailField);
            $subscriberField->setGracefulReject($this->GracefulReject);

            return $subscriberField;
        }

        return false;
    }

    /**
     * @param $data
     * @return string
     */
    public function getValueFromData($data)
    {
        $subscribe = isset($data[$this->Name]);

        if (!$subscribe) {
            return 'Unable to subscribe';
        }

        $email = $data[$this->EmailField];

        if (Vision6::singleton()->isEmailInList($this->ListID, $email)) {
            return 'Already Subscribed';
        }

        Vision6::subscribeEmail($this->ListID, $email);

        return 'Subscribed';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getIcon()
    {
        return static::$icon;
    }
}