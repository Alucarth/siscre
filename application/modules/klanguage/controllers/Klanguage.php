<?php

require_once (APPPATH . "controllers/Secure_area.php");

class Klanguage extends Secure_area {

    function __construct()
    {
        parent::__construct('klanguage');
    }
    
    function test()
    {
        echo "here";
    }
    
    function ajax()
    {
        $type = $this->input->post("type");
        switch($type)
        {
            case 1:
                $this->_handle_load_lang_config();
                break;
            case 2:
                $this->_handle_read_file_content();
                break;
            case 3:
                $this->_handle_update_lang_file();
                break;
            case 4:
                $this->_handle_delete_lang_file();
                break;
            case 5:
                $this->_handle_search_lang_words();
                break;
        }
    }
    
    private function _handle_search_lang_words()
    {
        $keywords = $this->input->post("keywords");
        
        foreach (glob(APPPATH . "language/en/*_lang.php") as $search) {
            $contents = file_get_contents($search);
            if (!stripos($contents, $keywords)) continue;
            $matches[] = $search;
        }
        
        $data = [];
        $data["matches"] = $matches;
        $data["keywords"] = $keywords;
        $this->load->view("klanguage/search_result", $data);
    }
    
    private function _handle_delete_lang_file()
    {
        $lang_name = $this->input->post("lang_name");
        $file = APPPATH . '/language/en/' . str_replace(" ", "_", $lang_name) . "_lang.php";
        
        unlink($file);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_update_lang_file()
    {
        $lang_name = $this->input->post("lang_name");
        $lang = $this->input->post("lang");
        
        $file = APPPATH . '/language/en/' . strtolower(str_replace(" ", "_", $lang_name)) . "_lang.php";
        
        $contents = '<?php ' . "\r\n";
        
        if ( $lang && is_array($lang) )
        {
            foreach ( $lang as $key => $val )
            {
                $contents .= '$lang["'. str_replace(" ", "_", $key) .'"] = "' . $val . '";' . "\r\n";
            }
        }
        else
        {
            $contents .= '$lang["'.$lang_name.'_noinput"] = "noinput";' . "\r\n";
        }
        
        file_put_contents($file, $contents);
        
        $return["status"] = "OK";
        send($return);
    }
    
    private function _handle_read_file_content()
    {
        $lang_name = $this->input->post("lang_name");
        $lang = $this->lang->load($lang_name, "en", true);
        
        $data["lang"] = $lang;
        $this->load->view('klanguage/lang_form', $data);
    }
    
    private function _handle_load_lang_config()
    {
        $this->data["new_add"] = $this->input->post("new_add");
        $this->load->view("klanguage/config", $this->data);
    }

}

?>