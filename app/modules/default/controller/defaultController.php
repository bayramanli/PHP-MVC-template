<?php

class defaultController extends mainController
{
    //index sayfası controller
    public function index()
    {
        $data = [];
        $indexModel = new defaultModel();
        $data["index"] = $indexModel->indexModel();
      
        $this->callLayout("frontend", "main", "default", "index", $data);
    }
    
}
