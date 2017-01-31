<?php

class Vision6PageController extends Page_Controller
{
    private static $allowed_actions = array(
        'index',
        'subscribe',
        'getForm'
    );

    /**
     * No one has a need to view the index and at no point should anyone be directly linked to it
     */
    public function index()
    {
        Security::permissionFailure();
    }

    /**
     * @param $listId
     * @return Form
     */
    public function getForm($listId)
    {
        if ($listId instanceof SS_HTTPRequest) {
            // get last generated form
            if (!isset($_SESSION['LastGeneratedV6Form'])) {
                user_error('Woops', E_USER_ERROR);
            }
            $listId = $_SESSION['LastGeneratedV6Form'];
        }

        $factory = Vision6FieldFactory::create();
        $factory->setList($listId);
        $fields = $factory->build();
        $validator = $factory->getRequired();

        $actions = FieldList::create(
            array(
                FormAction::create('subscribe', 'Subscribe')->setUseButtonTag(true)
            )
        );

        $form = Form::create($this, __FUNCTION__, $fields, $actions, $validator);

        if ($form->hasExtension('FormSpamProtectionExtension')) {
            $form->enableSpamProtection();
        }

        $this->extend('updateForm', $form);
        $_SESSION['LastGeneratedV6Form'] = $listId;
        return $form;
    }

    /**
     * Form processor
     *
     * @param $data
     * @param Form $form
     * @return bool|SS_HTTPResponse
     */
    public function subscribe($data, Form $form)
    {
        if (!$this->request->isPOST()) {
            user_error('You have reached this page incorrectly, data must be posted.', E_USER_ERROR);
        }

        $api = Vision6Api::create();

        $payload = $this->normalizeFormData($form->getData());
        $listId = array_shift($payload);

        $api->invokeMethod("subscribeContact", (int)$listId, $payload);

        if ($api->hasError()) {
            // unsuccessful
            if (Director::isDev()) {
                user_error('There was an error: ' . $api->getErrorMessage(), E_USER_ERROR);
            }

            $form->sessionMessage('We have encountered an error and you have not been subscribed.', 'bad');
            return $this->redirectBack();
        }

        if (!$api->hasError()) {
            // successful
            $form->sessionMessage('You have successfully subscribed to this mailing list', 'good');
            return $this->redirectBack();
        }

        return $this->redirectBack();
    }

    /**
     * Link overload to return correct URL (it wants to return this classes name instead of our beautiful route rule)
     *
     * @param null $action
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::join_links(Director::baseURL(), 'vision6', $action);
    }

    /**
     * Normalizes the POST names for what Vision6 expects (SilverSripe adds an underscore to names with a space in it, so need to
     * reverse this, also need to implode arrays into CSV format)
     *
     * @param array $postVars
     *
     * @return array
     */
    public function normalizeFormData(array $postVars)
    {
        $output = array();

        foreach ($postVars as $key => $val) {
            if (strstr($key, 'action_') || $key == 'SecurityID') {
                continue;
            }

            if (is_array($val)) {
                $val = implode(",", $val);
            }

            $key = str_replace("_", " ", $key);
            $output[$key] = $val;
        }

        return $output;
    }
}
