<?php

/**
 * Class Vision6List
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 *
 * @property int ListID
 * @property string Name
 * @property int FileFolderID
 * @method DataList Fields
 */
class Vision6List extends DataObject
{
    protected static $db = array(
        "ListID" => "Int",
        "Name" => "Varchar(200)",
        "FileFolderID" => "Int"
    );

    protected static $many_many = array(
        "Fields" => "Vision6Field"
    );

    /**
     * Builds the FieldList for this Mailing List
     *
     * @return FieldList
     */
    public function getFields()
    {
        $factory = Vision6FieldFactory::create();
        return $factory->setList($this->ListID)->build();
    }
}