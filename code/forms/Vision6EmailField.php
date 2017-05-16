<?php

/**
 * Class Vision6EmailField
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 */
class Vision6EmailField extends EmailField
{
    /**
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {
        /** @var HiddenField $listIdField */
        $listIdField = $this->getForm()->Fields()->fieldByName('ListID');
        $listId = $listIdField->Value();

        if (Vision6::singleton()->isEmailInList($listId, $this->value)) {
            $validator->validationError(
                $this->name, "That email is already subscribed", "validation"
            );

            return false;
        }

        return true;
    }
}
