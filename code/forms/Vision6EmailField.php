<?php

class Vision6EmailField extends TextField {

	public function validate($validator)
	{
		$exists = $this->request->postVar('ListID');

		$validator->validationError(
			$this->name, "Not a number. This must be between 2 and 5", "validation"
		);

		return false;
	}

}
