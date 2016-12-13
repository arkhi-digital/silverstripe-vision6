<?php

class Vision6PageController extends Page_Controller
{
	private static $allowed_actions = array(
		'index',
		'subscribe'
	);

	/**
	 * No one has a need to view the index and at no point should anyone be directly linked to it
	 */
	public function index()
	{
		Security::permissionFailure();
	}

	/**
	 * The default action for Vision6::singleton()->createForm()
	 */
	public function subscribe()
	{
		if (!$this->request->isPOST()) {
			user_error('You have reached this page incorrectly, data must be posted.', E_USER_ERROR);
		}

		$api = Vision6Api::create();

		$payload = $this->normalizeFormData($this->request->postVars());
		$listId = array_shift($payload);

		if ((isset($payload['Email']) && strlen($payload['Email'])) && Vision6::singleton()->isEmailInList($listId, $payload['Email'])) {
			Vision6FieldFactory::singleton()->addSessionMessageFor($listId, 'That email is already subscribed');
			$this->redirectBack();
		}

		$api->invokeMethod("subscribeContact", (int)$listId, $payload);

		if ($api->hasError()) {
			// unsuccessful
			if (Director::isDev()) {
				user_error('There was an error: ' . $api->getErrorMessage(), E_USER_ERROR);
			}

			Vision6FieldFactory::singleton()->addSessionMessageFor($listId, 'We have encountered an error and you have not been subscribed.');
			$this->redirectBack();
		}

		if (!$api->hasError()) {
			// successful
			Vision6FieldFactory::singleton()->addSessionMessageFor($listId, 'You have successfully subscribed to this mailing list');
			$this->redirectBack();
		}

		$this->redirectBack();
	}

	/**
	 * Would you like to submit a PR? :))
	 */
	public function redirectBack()
	{
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		die();
	}

	/**
	 * Link overload to return correct URL (it wants to return this classes name instead of our beautiful route rule)
	 *
	 * @param null $action
	 * @return string
	 */
	public function Link($action = null)
	{
		return Director::baseURL() . "vision6" . (($action) ? "/" . $action : "");
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
