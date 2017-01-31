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
}