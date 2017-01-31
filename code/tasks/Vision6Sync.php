<?php

class Vision6Sync extends BuildTask {

    /**
     * @var string
     */
    protected $title = "Synchronize Vision6 Lists/Fields";

    /**
     * @var string
     */
    protected $description = "Syncs the local database with lists/fields from Vision6 API, will modify if already exists and create if doesn't";

    /**
     * @var bool
     */
    protected $enabled = TRUE;

    /**
     * @param $request
     */
    public function run($request)
    {
        $this->syncLists();
        $this->syncFields();
    }

    public function syncLists() {
        $api = new Vision6Api();
        $lists = $api->invokeMethod("searchLists");
        //$fields = $api->invokeMethod("searchFields", 368655);

        foreach ($lists as $list) {

            $record = \Vision6List::get()->filter(
                array(
                    "ListID" => $list['id']
                )
            )->first();

            if (!$record) {
                $record = \Vision6List::create();
            }


            $record->ListID = $list['id'];
            $record->FileFolderID = $list['folder_id'];
            $record->Name = $list['name'];

            $record->write();
        }
    }
    public function syncFields() {
        $api = new Vision6Api();
        $lists = $api->invokeMethod("searchLists");

        foreach ($lists as $list) {
            $fields = $api->invokeMethod("searchFields", $list['id']);

            foreach ($fields as $field) {

                $record = \Vision6Field::get()->filter(
                    array(
                        "FieldID" => $field[ 'id' ]
                    )
                )->first();

                if (!$record) {
                    $record = \Vision6Field::create();
                }

                $record->FieldID          = $field[ 'id' ];
                $record->Name             = $field[ 'name' ];
                $record->Type             = $field[ 'type' ];
                $record->IsMandatory      = (int)$field[ 'is_mandatory' ];
                $record->ShowInForm       = (int)$field[ 'show_in_form' ];
                $record->Length           = $field[ 'length' ];
                $record->Size             = $field[ 'size' ];
                $record->RowSize          = $field[ 'row_size' ];
                $record->DefaultValue     = $field[ 'default_value' ];
                $record->ValuesArray      = $field[ 'values_array' ];
                $record->DefaultsArray    = $field[ 'defaults_array' ];
                $record->AllowedFileTypes = $field[ 'allowed_file_types' ];
                $record->AddressType      = $field[ 'address_type' ];
                $record->TimeFormat       = $field[ 'time_format' ];
                $record->DateValidation   = $field[ 'date_validation' ];
                $record->DisplayOrder     = $field[ 'display_order' ];
                $record->write();

                $listRecord = \Vision6List::get()->filter(
                    array(
                        "ListID" => $field['list_id']
                    )
                )->first();

                $listRecord->Fields()->add($record);


            }
        }
    }

}