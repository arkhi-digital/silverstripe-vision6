<?php

/**
 * Class Vision6Sync
 */
class Vision6Sync extends BuildTask implements Flushable
{
    private static $is_flushing = false;

    /** @var string */
    protected $title = "Synchronize Vision6 Lists/Fields";

    /** @var string */
    protected $description = "Syncs the local database with lists/fields from Vision6 API, will modify if already exists and create if doesn't";

    /**
     * @param $request
     */
    public function run($request)
    {
        static::syncLists();
        static::syncFields();
    }

    /**
     * Sync Lists
     */
    public static function syncLists()
    {
        if (static::$is_flushing && !defined('VISION6_API_KEY')) {
            return;
        }

        $api = new Vision6Api();
        $lists = $api->callMethod("searchLists");

        foreach ($lists as $list) {

            $record = Vision6List::get()->filter(
                array(
                    "ListID" => $list['id']
                )
            )->first();

            if (!$record) {
                $record = Vision6List::create();
            }

            $record->ListID = $list['id'];
            $record->FileFolderID = $list['folder_id'];
            $record->Name = $list['name'];

            $record->write();
        }
    }

    /**
     * Sync Fields For Lists
     */
    public static function syncFields()
    {
        if (static::$is_flushing && !defined('VISION6_API_KEY')) {
            return;
        }

        $api = new Vision6Api();
        $lists = $api->callMethod("searchLists");

        foreach ($lists as $list) {
            $fields = $api->callMethod("searchFields", $list['id']);

            foreach ($fields as $field) {

                $record = \Vision6Field::get()->filter(
                    array(
                        "FieldID" => $field['id']
                    )
                )->first();

                if (!$record) {
                    $record = Vision6Field::create();
                }

                $record->FieldID = $field['id'];
                $record->Name = $field['name'];
                $record->Type = $field['type'];
                $record->IsMandatory = (int)$field['is_mandatory'];
                $record->ShowInForm = (int)$field['show_in_form'];
                $record->Length = $field['length'];
                $record->Size = $field['size'];
                $record->RowSize = $field['row_size'];
                $record->DefaultValue = $field['default_value'];
                $record->ValuesArray = $field['values_array'];
                $record->DefaultsArray = $field['defaults_array'];
                $record->AllowedFileTypes = $field['allowed_file_types'];
                $record->AddressType = $field['address_type'];
                $record->TimeFormat = $field['time_format'];
                $record->DateValidation = $field['date_validation'];
                $record->DisplayOrder = $field['display_order'];
                $record->write();

                /** @var Vision6List $listRecord */
                $listRecord = Vision6List::get()->filter(
                    array(
                        "ListID" => $field['list_id']
                    )
                )->first();

                $listRecord->Fields()->add($record);
            }
        }
    }

    /**
     * This function is triggered early in the request if the "flush" query
     * parameter has been set. Each class that implements Flushable implements
     * this function which looks after it's own specific flushing functionality.
     *
     * @see FlushRequestFilter
     */
    public static function flush()
    {
        static::$is_flushing = true;
        static::syncLists();
        static::syncFields();
    }
}