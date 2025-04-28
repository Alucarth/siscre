<?php

require_once (APPPATH . "controllers/Secure_area.php");

class Home extends Secure_area {

    function __construct()
    {
        parent::__construct('home');
        $this->load->model("home_model");
    }
    
    function index()
    {
        $period = $this->input->get("p");
        
        $filters = [];
        switch ($period)
        {
            case 'ytd':
                $data["period_label"] = "YTD";
                $filters["date_from"] = date("Y-01-01");
                $filters["date_to"] = date("Y-m-d");
                break;
            case 'mtd':
                $data["period_label"] = "MTD";
                $filters["date_from"] = date("Y-m-01");
                $filters["date_to"] = date("Y-m-d");
                break;
            case 'lm':
                $data["period_label"] = "Last Month";
                $filters["date_from"] = date("Y-m-01", strtotime("-1 months"));
                $filters["date_to"] = date('Y-m-d', strtotime('last day of previous month'));
                break;
            default:
                $data["period_label"] = "YTD";
        }
        
        $data["total_loans"] = $this->home_model->get_total_loans( $filters );
        $data["total_interest"] = $this->home_model->get_total_interest( $filters );
        $data["total_borrowers"] = $this->home_model->get_total_customers( $filters );
        $data["total_payments"] = $this->home_model->get_total_payments( $filters );
        $data["total_num_payments"] = $this->home_model->get_total_num_payments( $filters );
        $data["total_fees"] = $this->home_model->get_total_fees( $filters );
        $data["my_wallet"] = $this->home_model->get_total_wallet( $filters );
        
        $data["top_customers"] = $this->_get_top_customers();
        $data["latest_loans"] = $this->_get_latest_loans();
        $data["overdue_customers"] = $this->_get_overdue_customers();
        $data["overdue_today_customers"] = $this->_get_overdue_today_customers();
        
        $data["sales_chart_data"] = $this->_get_sales_chart_data();
        $data["transaction_chart_data"] = $this->_get_transaction_chart_data();
        $this->load->view("home", $data);
    }
    
    private function _get_overdue_customers()
    {
        $overdue_customers = $this->home_model->get_overdue_customers();
        
        return $overdue_customers;
    }
    
    private function _get_overdue_today_customers()
    {
        $overdue_today_customers = $this->home_model->get_overdue_today_customers();
        
        return $overdue_today_customers;
    }
    
    private function _get_top_customers()
    {
        $top_customers = $this->home_model->get_top_customers();
        
        return $top_customers;
    }
    
    private function _get_latest_loans()
    {
        $latest_loans = $this->home_model->get_latest_loans();
        
        return $latest_loans;
    }
    
    private function _get_sales_chart_data()
    {
        $data = [];
        $loan_sales = $this->home_model->get_loan_sales();        
        
        foreach ( $loan_sales as $sale )
        {
            $data[] = ["label" => $sale->product_name, "value" => $sale->cnt];
        }
        
        return json_encode($data);
    }
    
    private function _get_transaction_chart_data()
    {
        $data = [];
//        $data[] = ["month" => 2008, "value" => 5];
//        $data[] = ["year" => 2009, "value" => 10];
//        $data[] = ["year" => 2010, "value" => 8];
//        $data[] = ["year" => 2011, "value" => 22];
//        $data[] = ["year" => 2012, "value" => 8];
//        $data[] = ["year" => 2014, "value" => 10];
//        $data[] = ["year" => 2015, "value" => 25];
        
        $transactions = $this->home_model->get_transaction_data();
        
        foreach ( $transactions as $transaction )
        {
            $data[] = ["month" => $transaction->applied_date, "value" => $transaction->cnt];
        }
        
        return json_encode($data);
    }

    function logout()
    {
        $this->Employee->logout();
    }

}

?>