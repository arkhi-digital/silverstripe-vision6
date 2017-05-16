<?php

/**
 * Class Vision6
 */
class Vision6 extends Object
{

    /**
     * @param Vision6List|int $listOrId
     * @return FieldList|Object
     */
    public function getFieldsForList($listOrId)
    {
        $factory = Vision6FieldFactory::create();
        return $factory->setList($listOrId)->build();
    }

    /**
     * Checks to see if the email is already in the Vision6 List
     * @param $listId
     * @param $emailAddress
     *
     * @return bool
     */
    public function isEmailInList($listId, $emailAddress)
    {
        $api = Vision6Api::create();
        $contacts = $api->callMethod(
            'searchContacts',
            $listId,
            array(
                array(
                    'Email',
                    'exactly',
                    $emailAddress
                )
            )
        );

        return (!empty($contacts));
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
    public function setSession(array $data)
    {
        Session::set('Vision6', $data);
        Session::save();
    }

    /**
     * @param $listId
     * @param TextField|EmailField|string $fieldOrEmail
     * @return bool
     */
    public static function subscribeEmail($listId, $fieldOrEmail)
    {
        $email = null;

        if ($fieldOrEmail instanceof TextField) {
            $email = $fieldOrEmail->Value();
        }

        if (is_string($fieldOrEmail)) {
            $email = $fieldOrEmail;
        }

        if (!$email) {
            user_error(
                'An email address was not provided',
                E_USER_ERROR
            );
        }


        /** @var Vision6List $list */
        $list = Vision6List::get()->filter('ListID', $listId)->first();

        $api = Vision6Api::create();
        $api->callMethod("subscribeContact", (int)$listId, array('Email' => $email));

        if ($api->hasError()) {
            user_error(
                _t(
                    'Vision6.SUBSCRIBE_DEV_ERROR',
                    'An error occurred when attempting to subscribe {email} to {list_title}: {error}',
                    'The message that is logged when an error occurs while attempting to subscribe a user to a mailing list',
                    array(
                        'email' => $email,
                        'list_title' => $list->Name,
                        'error' => $api->getErrorMessage()
                    )
                ),
                E_USER_WARNING
            );
        }

        return (!$api->hasError());
    }
}
