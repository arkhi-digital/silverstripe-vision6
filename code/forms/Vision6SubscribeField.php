<?php

class Vision6SubscribeField extends CheckboxField
{
    /** @var int Vision6 List ID */
    protected $listId;

    /** @var string The field name within the parent form that contains the email address */
    protected $emailFieldName;

    /** @var bool Allow "already subscribed" errors to be gracefully ignored */
    protected $gracefulReject = false;

    /**
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->listId) {
            user_error(
                'You must provide a Vision6 List ID',
                E_USER_ERROR
            );
        }

        if (!$this->emailFieldName) {
            user_error(
                'You must provide the fieldName of the email field in this form',
                E_USER_ERROR
            );
        }

        $attributes = parent::getAttributes();

        $attributes = array_merge(
            $attributes,
            array(
                'data-v6-list-id' => $this->listId,
                'data-v6-email-field' => $this->emailFieldName
            )
        );

        return $attributes;
    }

    /**
     * Set the list ID that the email will be subscribed too
     *
     * @param $listId
     * @return $this
     */
    public function setListId($listId) {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Sets the field name that should be holding the email address, this field can be hidden
     * but must exist
     *
     * @param $fieldName
     * @return $this
     */
    public function setEmailFieldName($fieldName) {
        $this->emailFieldName = $fieldName;

        return $this;
    }

    /**
     * If the email address is already subscribed, the user will be returned to the form
     * with an error message, to gracefully allow subscriptions to fail (where could be
     * semantically desired) set this to true
     *
     * @param $bool
     * @return $this
     */
    public function setGracefulReject($bool) {
        $this->gracefulReject = (bool)$bool;

        return $this;
    }

    /**
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {
        $form = $this->getForm();
        $fields = $form->Fields();

        /** @var TextField|EmailField $emailField */
        $emailField = $fields->fieldByName($this->emailFieldName);

        if (!$emailField) {
            user_error(
                sprintf('The field %s was not found in %s', $this->emailFieldName, $form->getName()),
                E_USER_ERROR
            );
        }

        $email = $emailField->Value();

        if (Vision6::singleton()->isEmailInList($this->listId, $email)) {
            if (!$this->gracefulReject) {
                $validator->validationError(
                    $this->name, "That email is already subscribed", "validation"
                );

                return false;
            }
        }

        return true;
    }
}