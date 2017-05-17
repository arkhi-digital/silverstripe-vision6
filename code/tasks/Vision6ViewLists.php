<?php

/**
 * Class Vision6ViewLists
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 */
class Vision6ViewLists extends BuildTask
{
    /** @var string */
    protected $title = "Vision6: View All Lists";

    /** @var string */
    protected $description = "This task will display all synced lists and their IDs";

    /**
     * @param $request
     */
    public function run($request)
    {
        $lists = Vision6List::get()->sort('ListID', 'ASC');
        $format = "<li>[<strong>%s</strong>] %s | %s fields</li>";
        $output = array();

        /** @var Vision6List $list */
        foreach ($lists as $list) {
            $output[] = sprintf($format, $list->ListID, $list->Name, $list->Fields()->count());
        }

        echo "<p>This task exists purely to make tracking down the right list ID immensely easier. It does not actually accomplish anything beyond displaying your currently synced lists</p><p>To resync your lists, simply <a href='?flush=1'>flush</a></a></p>";
        echo "<ul>" . implode(PHP_EOL, $output) . "</ul>";
    }
}