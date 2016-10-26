<?php

class Vision6SyncLists extends BuildTask {

    /**
     * @var string
     */
    protected $title = "Synchronize Vision6 Lists";

    /**
     * @var string
     */
    protected $description = "Syncs the local database with lists from Vision6 API, will modify if already exists and create if doesn't";

    /**
     * @var bool
     */
    protected $enabled = TRUE;

    /**
     * @param $request
     */
    public function run($request)
    {
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
            $record->FileFolderID = $list['file_folder_id'];
            $record->Name = $list['name'];

            $record->write();
        }
    }

}