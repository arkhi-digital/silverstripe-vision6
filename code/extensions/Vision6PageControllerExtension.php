<?php

class Vision6PageControllerExtension extends Extension
{

	/**
	 * Template method for generating a Vision6 form
	 *
	 * @param int $listId The List ID of the List you want to generate the form for
	 *
	 * @return Form
	 */
	public static function Vision6List($listId)
	{
		$factory = Vision6FieldFactory::create();
		$factory->setList($listId);
		return Vision6::singleton()->createForm('subscribe', $factory->build(), null, $factory->getRequired());
	}


	/**
	 * Shortcode Functionality for generating a Vision6 Form
	 * @param $arguments
	 * @param null $content
	 * @param null $parser
	 * @param null $tagName
	 * @return Form
	 */
    public static function ShortCodeVision6List($arguments, $content = NULL, $parser = NULL, $tagName = NULL)
    {
        if (!array_key_exists('list_id', $arguments)) {
            user_error("list_id is missing from short code parameters", E_USER_ERROR);
        }

        return static::Vision6List($arguments['list_id']);
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

}
