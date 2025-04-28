<?php

require_once ("Secure_area.php");

class Documents extends CI_Controller {

    function __construct()
    {
        parent::__construct('documents');
    }
    
    function ajax()
    {
        $type = $this->input->post('type');
        switch ($type)
        {
            case 1: // Get customers table
                $this->_delete_document();
                break;
        }
    }
    
    private function _delete_document()
    {
        $id = $this->input->post("id");
        
        $this->db->where("document_id", $id);
        $this->db->delete("documents");
        
        $return["status"] = "OK";
        send($return);
    }
}

?>