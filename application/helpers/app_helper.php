<?php

function getDomain()
{
    $CI = & get_instance();
    return str_replace("index.php", "", site_url());
}

function get_last_payment_date($loan_id)
{
    $ci = & get_instance();
    $ci->db->where("loan_id", $loan_id);
    $ci->db->where("date_paid > ", 0);
    $ci->db->order_by("loan_payment_id", "desc");
    $ci->db->limit(1);
    $query = $ci->db->get("loan_payments");

    if ($query->num_rows() > 0)
    {
        return date($ci->config->item('date_format'), $query->row()->date_paid);
    }

    return '';
}

function ifNull($var)
{
    if (!isset($var))
    {
        $var = '';
    }

    return $var;
}

function send($arr)
{
    echo json_encode($arr);
    exit;
}

function process_response($file, $filename, $system_path, $web_path, $session_name = 'session_filename', $session_file = 'session_file')
{
    $ci = &get_instance();

    $return = [
        "data" => [],
        "files" => [
            "files" => [
                1 => [
                    "id" => 1,
                    "filename" => $filename,
                    "filesize" => 1024,
                    "web_path" => $web_path,
                    "system_path" => $system_path
                ]
            ]
        ],
        "upload" => ["id" => $filename]
    ];

    $ci->session->set_userdata($session_name, $filename);
    $ci->session->set_userdata($session_file, $file);

    send($return);
}

function ordinal($a)
{
    // return English ordinal number
    return $a . substr(date('jS', mktime(0, 0, 0, 1, ($a % 10 == 0 ? 9 : ($a % 100 > 20 ? $a % 10 : $a % 100)), 2000)), -2);
}

function iso_to_ukdate($str_date)
{
    $ci = &get_instance();
    return date($ci->config->item('date_format'), strtotime($str_date));
}

function uk_to_isodate($str_date, $include_time = false)
{
    if (!$include_time)
    {
        $tmp = explode("/", $str_date);
        if (!isset($tmp[2]))
        {
            return "";
        }

        return $tmp[2] . "-" . $tmp[1] . "-" . $tmp[0];
    }
}

function us_to_isodate($str_date, $include_time = false)
{
    if (!$include_time)
    {
        $tmp = explode("/", $str_date);
        if (!isset($tmp[2]))
        {
            return "";
        }

        return $tmp[2] . "-" . $tmp[0] . "-" . $tmp[1];
    }
}

function get_alert_notes($person_id = '')
{
    $ci = &get_instance();

    $ci->db->select("CONCAT(p.first_name, ' ', p.last_name) customer_name, n.*");
    $ci->db->from("notes n");
    $ci->db->where("n.alert_flag", 1);
    $ci->db->where("p.person_id", $person_id);
    $ci->db->join("customers c", "n.customer_acc_no = c.account_number", "LEFT");
    $ci->db->join("people p", "p.person_id = c.person_id", "LEFT");

    $query = $ci->db->get();

    if ($query->num_rows() > 0)
    {
        return $query->result();
    }

    return false;
}

function truncate_html($string, $length, $postfix = '&hellip;', $isHtml = true)
{
    $string = trim($string);
    $postfix = (strlen(strip_tags($string)) > $length) ? $postfix : '';
    $i = 0;
    $tags = []; // change to array() if php version < 5.4

    if ($isHtml)
    {
        preg_match_all('/<[^>]+>([^<]*)/', $string, $tagMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($tagMatches as $tagMatch)
        {
            if ($tagMatch[0][1] - $i >= $length)
            {
                break;
            }

            $tag = substr(strtok($tagMatch[0][0], " \t\n\r\0\x0B>"), 1);
            if ($tag[0] != '/')
            {
                $tags[] = $tag;
            }
            elseif (end($tags) == substr($tag, 1))
            {
                array_pop($tags);
            }

            $i += $tagMatch[1][1] - $tagMatch[0][1];
        }
    }

    return substr($string, 0, $length = min(strlen($string), $length + $i)) . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') . $postfix;
}

function time_ago($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v)
    {
        if ($diff->$k)
        {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        }
        else
        {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

if (!function_exists('model_exists'))
{
    function model_exists($name)
    {
        $ci = &get_instance();
        if (file_exists(APPPATH  . 'models/' . $name . '.php'))
        {
            $ci->load->model($name);
            return true;
        }
        
        try
        {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'modules'));
            foreach ($it as $file) 
            {
                if ( $name . '.php' == $file->getFileName() )
                {
                    return true;
                }
            }
        } catch (Exception $ex) {

        }
            
        return false;
    }

}

function is_plugin_active($plugin_name)
{
    $ci = &get_instance();
    
    $sql = "SELECT * FROM c19_plugins WHERE module_name = '$plugin_name' AND status_flag = 'Active'";
    $query = $ci->db->query($sql);
    
    if ( $query && $query->num_rows() > 0 )
    {
        if ( is_dir(APPPATH . 'modules/' . $plugin_name) )
        {
            return true;
        }
    }
    
    return false;
}

function calendar_date_format()
{
    $ci = &get_instance();
    $date_format = $ci->config->item('date_format');
    
    if ( substr_count(strtolower($date_format), 'd') < 2 )
    {
        $date_format = str_ireplace('d', 'dd', $date_format);
    }
    
    if ( substr_count(strtolower($date_format), 'm') < 2 )
    {
        $date_format = str_ireplace('m', 'mm', $date_format);
    }
    
    switch( substr_count(strtolower($date_format), 'y') )
    {
        case 1:
            $date_format = str_ireplace('y', 'yyyy', $date_format);
            break;
        case 2:
            $date_format = str_ireplace('yy', 'yyyy', $date_format);
            break;
        case 3:
            $date_format = str_ireplace('yyy', 'yyyy', $date_format);
            break;
    }
    
    return $date_format;
}

function custom_send_email( $email_data = [] )
{
    $ci = &get_instance();
    
    $ci->load->library('email');
    
    $config['mailtype'] = 'html';
    $ci->email->initialize($config);
    
    $ci->email->from($email_data['from_email'], $email_data['from_name']);
    $ci->email->to($email_data['to_email']);

    $ci->email->subject($email_data['subject']);
    $ci->email->message($email_data['html']);

    $ci->email->send();
    
    return true;
}

function get_documents($foreign_id, $document_type)
{
    $ci = &get_instance();
    
    $ci->db->where("foreign_id", $foreign_id);
    $ci->db->where("document_type", $document_type);
    $query = $ci->db->get("documents");
    
    if ( $query && $query->num_rows() > 0 )
    {
        return $query->result();
    }
    
    return false;
}

function track_action($user_id, $activity_type, $description = '')
{
    $ci = &get_instance();
    
    $insert_data = [];
    $insert_data["user_id"] = $user_id;
    $insert_data["activity_type"] = $activity_type;
    $insert_data["description"] = $description;
    $insert_data["log_date"] = time();
    
    $ci->db->insert("activity_log", $insert_data);
}

function check_access($user_type_id, $module_id, $type = 'add')
{
    if (is_plugin_active('roles'))
    {
        $ci = &get_instance();
        $ci->load->model("roles/role_model");
        
        $role = $ci->role_model->get_info($user_type_id);
        $access_rights = json_decode($role->rights, TRUE);
        $write_access = json_decode($role->write_access, TRUE);
        $edit_access = json_decode($role->edit_access, TRUE);
        $delete_access = json_decode($role->delete_access, TRUE);

        $view_module_ids = is_array($access_rights) ? $access_rights : [];
        $write_module_ids = is_array($write_access) ? $write_access : [];
        $edit_module_ids = is_array($edit_access) ? $edit_access : [];
        $delete_module_ids = is_array($delete_access) ? $delete_access : [];

        switch( $type )
        {
            case "view":
                return in_array($module_id, $view_module_ids);
            case "add":
                return in_array($module_id, $write_module_ids);
            case "edit":
                return in_array($module_id, $edit_module_ids);
            case "delete":
                return in_array($module_id, $delete_module_ids);
        }
    }
    
    return true;
}

function revalidate_transactions($limit = '', $offset = '', $where = '', $no_update = false)
{
    $ci = &get_instance();
    if ( $limit != '' && $offset != '' )
    {
        $sql = "SELECT loan_id, periodic_loan_table, loan_payment_date, loan_applied_date, apply_amount, if(description='','Not specified',description) description FROM c19_loans WHERE delete_flag=0 LIMIT $offset, $limit";
    }
    
    if ( $where != '' )
    {
        $sql = "SELECT loan_id, periodic_loan_table, loan_payment_date, loan_applied_date, apply_amount, if(description='','Not specified',description) description FROM c19_loans WHERE delete_flag=0 $where ORDER BY description";
    }
    
    if ( $limit == '' && $offset == '' && $where == '' )
    {
        $sql = "SELECT loan_id, periodic_loan_table, loan_payment_date, loan_applied_date, apply_amount, if(description='','Not specified',description) description FROM c19_loans WHERE delete_flag=0 ORDER BY description";
    }
    
    $query = $ci->db->query($sql);
    
    $calc = [];
    if ( $query && $query->num_rows() > 0 )
    {
        foreach ( $query->result() as $row )
        {
            $scheds = json_decode($row->periodic_loan_table);
            if ( count($scheds) > 0 )
            {
                $next_payment_date = $row->loan_payment_date;
                $total_interest_paid = 0;
                $total_principal_balance = 0;
                $total_principal_collected = 0;
                foreach ( $scheds as $sched )
                {
                    $payment_date = strtotime($ci->config->item('date_format') == 'd/m/Y' ? uk_to_isodate(strip_tags($sched->payment_date)) : strip_tags($sched->payment_date));
                    if ( $payment_date < $next_payment_date )
                    {
                        $total_interest_paid += $sched->interest;
                        $total_principal_collected += $sched->payment_amount - $sched->interest;
                    }
                    else
                    {
                        $total_principal_balance += $sched->payment_amount - $sched->interest;
                    }
                }
            }
            
            $calc[] = [                
                "loan_id" => $row->loan_id, 
                "description" => trim(strtolower($row->description)),
                "apply_amount" => $row->apply_amount,
                "total_interest_paid" => $total_interest_paid, 
                "total_principal_balance" => $total_principal_balance, 
                "total_principal_collected" => $total_principal_collected, 
                "applied_date" => date("Y-m-d", $row->loan_applied_date)
            ];
        }
    }
    
    if ( $no_update )
    {
        return $calc;
    }
    else
    {
        if ( count($calc) > 0 )
        {
            // Calculate interest paid
            // Calculate total principal balance
            foreach ( $calc as $data )
            {
                $sql = "UPDATE c19_loans 
                        SET total_interest_paid = " . $data["total_interest_paid"] . ", 
                            total_principal_balance = " . $data["total_principal_balance"] . " 
                        WHERE loan_id = " . $data["loan_id"];
                $ci->db->query( $sql );
            }
        }
    }
    
}

function log_transactions($log_data = [])
{
    $ci = &get_instance();
    
    $query = $ci->db->query( "SHOW TABLES LIKE 'c19_transaction_logs'" );
    if ( $query && $query->num_rows() > 0 )
    {
        $log_data['added_date'] = date('Y-m-d H:i:s');    
        $ci->db->insert("transaction_logs", $log_data);
    }
}

function sync_payment_date( $due_paid, $scheds )
{
    $ci = &get_instance();
    
    foreach( $scheds as $sched )
    {
        if ( $ci->config->item('date_format') == 'd/m/Y' )
        {
            $payment_date = strtotime(uk_to_isodate($sched->payment_date));
        }
        else
        {
            $payment_date = strtotime($sched->payment_date);
        }
	
        if ( $due_paid >= $payment_date )
        {

        }
        else
        {
            return $payment_date;
        }
    }
}

function check_file_icon($path)
{
    $words = array("doc", "docx", "odt");
    $xls = array("xls", "xlsx", "csv");
    $tmp = explode(".", basename($path));
    $ext = $tmp[1];

    if (in_array(strtolower($ext), $words))
    {
        return base_url("images/word-filetype.jpg");
    }
    else if (strtolower($ext) === "pdf")
    {
        return base_url("images/pdf-filetype.jpg");
    }
    else if (in_array(strtolower($ext), $xls))
    {
        return base_url("images/xls-filetype.jpg");
    }
    else
    {
        return base_url("images/image-filetype.jpg");
    }

    return '';
}

function allowed_file_types()
{
    return [
        "jpg", "png", "jpeg", "doc", "docx", "xls", "xlsx", "pdf", "zip",
        "JPG", "PNG", "JPEG", "DOC", "DOCX", "XLS", "XLSX", "PDF", "ZIP"
    ];
}

function get_branch_name()
{
    $ci = &get_instance();
    
    $branch_id = $ci->session->userdata('branch_id');
    $sql = "SELECT branch_name FROM c19_branches WHERE id='$branch_id'";
    $query = $ci->db->query( $sql );
    
    if ( $query && $query->num_rows() > 0 )
    {
        return $query->row()->branch_name;
    }
    
    return '';
}

function move_unexcluded_days($payment_date, $excluded_schedules)
{
    $in_day = date("l", $payment_date);
    $in_day = strtolower($in_day);

    if ( isset($excluded_schedules) && is_array($excluded_schedules) )
    {
        if (in_array($in_day, $excluded_schedules))
        {
            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
            $payment_date = move_unexcluded_days($payment_date, $excluded_schedules);
        }
    }
    
    return $payment_date;
}

function get_excluded_days($payment_date, $excluded_schedules)
{
    $ci = &get_instance();
    $ci->load->model("holidays/Holiday_model");
    
    $not_numeric = false;
    if ( !is_numeric($payment_date) )
    {
        $payment_date = strtotime($payment_date);
        $not_numeric = true;
    }
    
    // Get holidays event_date
    $holidays_date = [];
    $holidays = $ci->Holiday_model->get_all();
    foreach ($holidays as $holiday)
    {
        $holidays_date[] = $holiday->event_date;
    }

    $payment_date = move_unexcluded_days($payment_date, $excluded_schedules);
    
    // Calculate holidays here
    foreach($holidays_date as $holiday_date)
    {
        if ($payment_date == strtotime($holiday_date))
        {
            $payment_date = strtotime(date('Y-m-d', $payment_date) . '+1 day');
            $payment_date = move_unexcluded_days($payment_date, $excluded_schedules);
        }
    }
    
    if ( $not_numeric )
    {
        $payment_date = date("Y-m-d", $payment_date);
    }
    
    return $payment_date;
}

function get_view_user_ids( $role_id )
{
    $ci = &get_instance();
    
    $ci->load->model("role");
    $role_info = $ci->role->get_info($role_id);

    $low_level_ids = json_decode($role_info->low_level, 1);

    $user_ids = [];
    if ( count($low_level_ids) > 0 )
    {
        $user_ids = $ci->role->get_staff_user_ids(implode(",", $low_level_ids));
    }

    return $user_ids;
}