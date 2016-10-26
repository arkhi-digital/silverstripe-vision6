<?php

class Vision6List extends DataObject {
    protected static $db = array(
        "ListID" => "Int",
        "Name" => "Varchar(200)",
        "FileFolderID" => "Int"
    );

    protected static $many_many = array(
        "Fields" => "Vision6Field"
    );
}