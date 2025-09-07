<?php

require_once ("Secure_area.php");

class Home extends Secure_area {

    function __construct()
    {
        parent::__construct('home');
    }

    function index()
    {
        $data["total_loans"] = $this->Loan->get_total_loans();
        $data["total_borrowers"] = $this->Customer->count_all();
        $data["my_wallet"] = $this->My_wallet->get_total();
        $this->load->view("home", $data);
    }
    
    function test_email()
    {
    error_reporting(E_ALL);
ini_set('display_errors', '1');
    	$this->load->library('email');
	
	$email_data['from_email'] = 'norman.marino@gmail.com';
	$email_data['from_name'] = 'Norman';
	$email_data['to_email'] = 'jmendez.marino@gmail.com';
	$email_data['subject'] = 'Test Only';
	$email_data['html'] = 'Hello World!';
    
	    $config['mailtype'] = 'html';
	    $this->email->initialize($config);
    
	    $this->email->from($email_data['from_email'], $email_data['from_name']);
	    $this->email->to($email_data['to_email']);

	    $this->email->subject($email_data['subject']);
	    $this->email->message($email_data['html']);

	    $this->email->send();
    }

    function logout()
    {
        $this->Employee->logout();
    }

}

?>