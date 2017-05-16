<?php

/**
 * Class Vision6Field
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 *
 * @property int FieldID
 * @property string Name
 * @property string Type
 * @property int IsMandatory
 * @property int ShowInForm
 * @property int Length
 * @property int Size
 * @property int RowSize
 * @property string DefaultValue
 * @property string ValuesArray
 * @property string DefaultsArray
 * @property string AllowedFileTypes
 * @property string AddressType
 * @property string TimeFormat
 * @property string DateValidation
 * @property string DisplayOrder
 * @method Vision6List Lists
 */
class Vision6Field extends DataObject
{
    protected static $db = array(
        "FieldID" => "Int", // The ID of the Field.
        "Name" => "Varchar(100)", // The name of the Field in the List.
        "Type" => "Varchar", // The type of the Field. See below for a list of supported Field types.
        "IsMandatory" => "Int(0)", // A value indicating whether the Field is displayed as mandatory in Web Forms by default.
        "ShowInForm" => "Int(1)", // A value indicating whether the Field is displayed in subscription Forms by default.
        "Length" => "Int", // The maximum length for values stored in a text Field.
        "Size" => "Int",
        "RowSize" => "Int",
        "DefaultValue" => "Varchar", // The default value for text, comment, decimal, currency and date Fields. For date Fields can be current or a date in ISO format (e.g. "2001-03-17").
        "ValuesArray" => "Text", // A comma separated list of values for drop-down list, radio and checkbox Fields.
        "DefaultsArray" => "Varchar", // The default value for drop-down list, radio and checkbox Fields.
        "AllowedFileTypes" => "Varchar(255)", // A comma separated list of allowed file types for file Fields. Can be one or more of images, documents, spreadsheets, movies or other.
        "AddressType" => "Varchar(255)",  // The address type for address Fields. Valid values are email, mobile or none.
        "TimeFormat" => "Varchar", // The format of the time part of a date Field. Valid values are off, 12h, or 24h.
        "DateValidation" => "Varchar", // Validation for date Fields. Can be either past, future or a date range separated by a comma in ISO format (e.g. "1980-01-01,1999-12-31").
        "DisplayOrder" => "Varchar" // A value indicating the Field order on the default Webform for the List. A value of -1 indicates the field is hidden.
    );

    protected static $belongs_many_many = array(
        "Lists" => "Vision6List"
    );
}