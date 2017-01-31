<?php

/**
 * Class Vision6EmailField
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 */
class Vision6EmailField extends EmailField
{
    public function validate($validator)
    {
        $listId = $this->getForm()->Fields()->fieldByName('ListID')->Value();

        if (Vision6::singleton()->isEmailInList($listId, $this->value)) {
            $validator->validationError(
                $this->name, "That email is already subscribed", "validation"
            );

            return false;
        }

        return true;
    }
}
