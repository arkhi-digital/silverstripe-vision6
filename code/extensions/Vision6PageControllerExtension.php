<?php

/**
 * Class Vision6PageControllerExtension
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 */
class Vision6PageControllerExtension extends Extension
{
    /**
     * Template method for generating a Vision6 form
     *
     * @param int $listId The List ID of the List you want to generate the form for
     *
     * @return HTMLText
     */
    public static function Vision6List($listId)
    {
        $factory = Vision6FieldFactory::create();
        $factory->setList($listId);

        return Page_Controller::singleton()->renderWith(
            'Vision6Form',
            array(
                'ListID' => $listId,
                'SessionMessage' => Vision6FieldFactory::singleton()->getSessionMessage($listId),
                'Form' => Vision6::singleton()->createForm('subscribe', $factory->build(), null, $factory->getRequired())
            )
        );
    }


    /**
     * Shortcode Functionality for generating a Vision6 Form
     * @param $arguments
     * @param null $content
     * @param null $parser
     * @param null $tagName
     * @return HTMLText
     */
    public static function ShortCodeVision6List($arguments, $content = null, $parser = null, $tagName = null)
    {
        if (!array_key_exists('list_id', $arguments)) {
            user_error("list_id is missing from short code parameters", E_USER_ERROR);
        }

        return static::Vision6List($arguments['list_id']);
    }

}
