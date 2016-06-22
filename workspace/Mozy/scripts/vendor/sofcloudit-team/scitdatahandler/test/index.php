<?php

include_once "../autoload.php";

use SCITDataHandler\SCITDataHandler;
use SCITDataHandler\Model\SCITDataModel as Model;
use SCITDataHandler\Handlers\SCITAbstractHandler;

/**
 * Class Note
 */
class Note extends Model
{
    public $name;
    public $priority;
    public $body;
}

/**
 * Class ToScreen
 * Example of a custom data handler
 */
class ToScreen extends SCITAbstractHandler
{
    public function run(Model $model)
    {
        var_dump($model);
    }
}

/**
 * Here we set the configurations and
 * instantiate the DataHandler
 */
$configuration = array(
    'File' => array(
        "path" => "notes",
        "file" => "{name}.txt",
        "template" => "[{datetime}] [{priority}] {body}"
    ),
    'ToScreen' => array(
        'namespace' => 'ToScreen'
    )
);
$DHandler = new SCITDataHandler($configuration);

/**
 * Now we fill the model and dispatch it
 * with the DataHandler
 */
$note = new Note();
$note->name = "Redmine";
$note->priority = "high";
$note->body = "Impute hours in Redmine";
$DHandler->dispatch($note);