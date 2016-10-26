<?php

class Vision6Ext extends Extension
{

    /**
     * Short tag for generating a subscriber form
     *
     * @param int $listID The ListID of the List you want to generate the form for
     *
     * @return Form
     */
    public static function Vision6List($listID)
    {
        $list = \Vision6List::get()->filter(
            array(
                "ListID" => $listID
            )
        )->first();

        if (!$list) {
            throw new \RuntimeException("$listID is not a valid list ID");
        }

        $fields = array();
        array_push($fields, HiddenField::create("ListID")->setAttribute("value", $listID));

        // todo handle errors, create LiteralField
        $session = static::getSession();

        if (array_key_exists('ListID', $session)) {
            // ok the most recently submitted list, is in fact this list we're generating
            // did we have errors submitting this list?
            if (!$session['Success']) {
                // yes there was errors
                return LiteralField::create("blah", "<div class='message error'>There was an error: {$session['Error']['ErrorMessage']}</div>");
            }
            else {
                // no there wasn't, list was submitted succesfully
                return LiteralField::create("blah", "<div class='message success'>You have successfully subscribed to the list: <strong>{$list->Name}</strong></div>");
            }
        }

        foreach ($list->Fields() as $field) {
            switch ($field->Type) {
                case "text": {
                    if ($field->Name == 'Email') {
                        array_push($fields, static::_emailFieldFor($field));
                        break;
                    }
                    array_push($fields, static::_textFieldFor($field));
                }
                break;

                case "comment": {
                    array_push($fields, static::_textareaFieldFor($field));
                }
                break;

                case "checkbox": {
                    if (strstr($field->ValuesArray, ",")) {
                        // has multiple
                        array_push($fields, static::_checkboxSetFor($field));
                        break;
                    }

                    array_push($fields, static::_checkboxFieldFor($field));

                }
                break;
            }
        }

        $actions = FieldList::create(
            FormAction::create('processList', "Continue")
        );

        return Form::create(new Vision6Ext(), "vision6/processList", FieldList::create($fields), $actions);

    }

    public static function ShortCodeVision6List($arguments, $content = NULL, $parser = NULL, $tagName = NULL)
    {
        if (!array_key_exists('list_id', $arguments)) {
            user_error("list_id is missing from short code parameters");
        }

        $form = static::Vision6List($arguments[ 'list_id' ]);

        return $form->forTemplate();
    }

    /**
     * Handles text fields
     *
     * @param \Vision6Field $field
     *
     * @return TextField
     */
    public function _textFieldFor(Vision6Field $field)
    {
        if ($field->Type != "text") {
            throw new \RuntimeException("Field \"{$field->Name}\" is a {$field->Type} field but was provided to _textFieldFor()");
        }

        $ssObj = TextField::create($field->Name, $field->Name, $field->DefaultValue);
        static::_handleAttributes($field, $ssObj);

        return $ssObj;
    }

    /**
     * Handles text fields that are of Email type
     *
     * @param Vision6Field $field
     *
     * @return EmailField
     */
    public function _emailFieldFor(Vision6Field $field)
    {
        if ($field->Type != "text") {
            throw new \RuntimeException("Field \"{$field->Name}\" is a {$field->Type} field but was provided to _emailFieldFor()");
        }

        $ssObj = EmailField::create($field->Name, $field->Name);
        static::_handleAttributes($field, $ssObj);

        return $ssObj;
    }

    /**
     * Handles groups of checkboxes
     *
     * @param Vision6Field $field
     *
     * @return CheckboxSetField
     */
    public function _checkboxSetFor(Vision6Field $field)
    {
        if ($field->Type != "checkbox") {
            throw new \RuntimeException("Field \"{$field->Name}\" is a {$field->Type} field but was provided to _checkboxSetFor()");
        }

        if (!strstr($field->ValuesArray, ",")) {
            throw new \RuntimeException("Field \"{$field->Name}\" is a singular checkbox but was provided to _checkboxSetFor()");
        }

        $values      = explode(",", $field->ValuesArray);
        $valuesArray = array();

        foreach ($values as $value) {
            $valuesArray[ $value ] = $value;
        }

        $ssObj = CheckboxSetField::create($field->Name, $field->Name, $valuesArray);
        static::_handleAttributes($field, $ssObj);

        return $ssObj;
    }

    /**
     * Handles singular checkboxes
     *
     * @param \Vision6Field $field
     *
     * @return CheckboxField
     */
    public function _checkboxFieldFor(Vision6Field $field)
    {
        if ($field->Type != "checkbox") {
            throw new \RuntimeException("Field \"{$field->Name}\" is a {$field->Type} field but was provided to _checkboxFieldFor()");
        }

        if (strstr($field->ValuesArray, ",")) {
            throw new \RuntimeException("Field \"{$field->Name}\" has multiple checkboxes but was provided to _checkboxFieldFor()");
        }

        $ssObj = CheckboxField::create($field->Name, $field->Name);
        static::_handleAttributes($field, $ssObj);

        return $ssObj;
    }

    /**
     * Handles textarea/comment field creation
     *
     * @param Vision6Field $field
     *
     * @return TextareaField
     */
    public function _textareaFieldFor(Vision6Field $field)
    {
        if ($field->Type != "comment") {
            throw new \RuntimeException("Field \"{$field->Name}\" is a {$field->Type} field but was provided to _textareaFieldFor()");
        }

        $ssObj = TextareaField::create($field->Name, $field->Name);
        static::_handleAttributes($field, $ssObj);

        return $ssObj;
    }

    /**
     * Handles dropdown fields
     *
     * @param Vision6Field $field
     *
     * @return DropdownField
     */
    public function _dropdownFieldFor(Vision6Field $field)
    {

    }

    /**
     * Handles attributes required for each field such as mandatory, max length etc
     *
     * @param \Vision6Field                  $field
     * @param TextField|FormField|EmailField $ssObj
     *
     * @return mixed
     */
    public function _handleAttributes(Vision6Field $field, &$ssObj)
    {
        if ((bool)$field->IsMandatory) {
            $ssObj->setAttribute("required", "required");
        }

        if ($field->Length) {
            $ssObj->setAttribute("maxlength", $field->Length);
        }

    }


    /**
     * POST handler
     *
     * todo Complete
     */
    public function handleRequest()
    {
        $returnTo = $_SERVER[ 'HTTP_REFERER' ];

        $api = new Vision6Api();

        $payload = $this->mapParams($_POST);

        $list_id = array_shift($payload);

        $session = $this->getSession();

        // todo add "does contact email exist in vision6 list" check here
        if ($this->isListSecured($list_id)) {
            // visitor has already submitted this list, so lock out the visitor from submitting again
            // realistically the form shouldn't even be submittable is list is secured.
            $session = array(
                'ListID'  => $list_id,
                'Success' => FALSE,
                'Error'   => array(
                    'ErrorCode'    => 1337,
                    'ErrorMessage' => "You have submitted this form too recently, please try again later."
                )
            );

            $this->setv6Session($session);

            header("Location: " . $returnTo);
            die();
        }

        $api->invokeMethod("subscribeContact", (int)$list_id, $payload);

        if ($api->hasError()) {
            $session = $session + array(
                'ListID'  => $list_id,
                'Success' => FALSE,
                'Error'   => array(
                    'ErrorCode'    => $api->getErrorCode(),
                    'ErrorMessage' => $api->getErrorMessage()
                )
            );
        }

        if (!$api->hasError()) {
            // successful
            $this->secureList($list_id);

            $session = $session + array(
                'ListID'  => $list_id,
                'Success' => TRUE
            );
        }

        $this->setv6Session($session);

        header("Location: " . $returnTo);
        die();
    }

    /**
     * Prevents a visitor from subscribing to a list more than once in the timespan of a session.
     *
     * @param $list_id
     */
    public function secureList($list_id)
    {
        $session = $this->getSession();

        if (!array_key_exists('Lists', $session)) {
            $session[ 'Lists' ] = array();
        }

        if (is_array($session[ 'Lists' ]) && !in_array($list_id, $session[ 'Lists' ])) {
            array_push($session[ 'Lists' ], (int)$list_id);
        }

        $this->setv6Session($session);

    }

    /**
     * Gets the session container or creates it if it doesn't exist.
     *
     * @return array|mixed|null|\Session
     */
    public function getSession()
    {
        return \Session::get('Vision6') ?: (\Session::set('Vision6', array())) ?: \Session::get('Vision6');
    }

    /**
     * Sets the session container
     *
     * @param array $data
     *
     * @return void
     */
    public function setv6Session(array $data)
    {
        \Session::set('Vision6', $data);
        \Session::save();
    }

    /**
     * @param $list_id
     *
     * @return bool
     */
    public function isListSecured($list_id)
    {
        $session = $this->getSession();

        var_dump($session);

        if (!array_key_exists('Lists', $session)) {
            return FALSE;
        }

        if (is_array($session[ 'Lists' ]) && in_array($list_id, $session[ 'Lists' ])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Cleans the POST names for what Vision6 expects (SS adds an underscore to names with a space in it, so need to
     * reverse this)
     *
     * @todo array_map/array_walk instead?
     *
     * @param array $params The raw $_POST global variable
     *
     * @return array
     */
    public function mapParams(array $params)
    {
        unset($params[ 'SecurityID' ], $params[ 'action_processList' ]);

        $output = array();

        foreach ($params as $res => $val) {
            if (is_array($val)) {
                $val = implode(",", $val);
            }

            $res            = str_replace("_", " ", $res);
            $output[ $res ] = $val;
        }

        return $output;
    }

    // Functions required from Monkey Patching this extension
    public static function setSession() { }

    public function hasMethod($method) { }

    public function securityTokenEnabled()
    {
        return TRUE;
    }

    public function Link()
    {
        return TRUE;
    }
}
