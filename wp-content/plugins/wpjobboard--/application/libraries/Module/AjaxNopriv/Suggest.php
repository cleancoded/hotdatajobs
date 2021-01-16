<?php

/**
 * Description of ${name}
 *
 * @author ${user}
 * @package 
 */
class Wpjb_Module_AjaxNopriv_Suggest
{
    public static function stateAction()
    {
        $request = Daq_Request::getInstance();
        $pattern = $request->get("q", "");
        $country = $request->get("country", 0);
        
        $arr = Wpjb_List_State::find($pattern, $country);

        if(empty($arr)) {
            // try to get at least some suggestions
            $query = new Daq_Db_Query;
            $query->select("*");
            $query->from("Wpjb_Model_Job t");
            $query->where("job_state LIKE ?", "%$pattern%");
            $query->group("job_state");
            $query->limit(10);
            $result = $query->execute();

            foreach($result as $r) {
                $arr[] = $r->job_state;
            }
        }
        
        echo join("\r\n", $arr);
        exit;
    }
    
    public static function cityAction()
    {
        $request = Daq_Request::getInstance();
        $pattern = $request->get("q");
        $country = $request->get("country", 0);
        $state = $request->get("state", "");
        
        $query = new Daq_Db_Query;
        $query->select("*");
        $query->from("Wpjb_Model_Job t");
        if($country>0) {
            $query->where("job_country = ?", $country);
        }
        if(!empty($state)) {
            $query->where("job_state LIKE ?", "%$state%");
        }
        $query->where("job_city LIKE ?", "%$pattern%");
        $query->group("job_city");
        $query->limit(10);
        
        $result = $query->execute();
        $arr = array();
        
        foreach($result as $r) {
            $arr[] = $r->job_city;
        }
        
        echo join("\r\n", $arr);
        exit;
        
    }
    
    public static function employerAction()
    {
        if(!current_user_can("edit_pages")) {
            echo "-1";
            exit;
        }
        $request = Daq_Request::getInstance();
        $result = Wpjb_Model_Company::search(array(
            "filter" => "all",
            "company_name" => $request->get("q")
        ));
        
        foreach($result->company as $company) {
            echo '<span data-id="'.$company->id.'">'.esc_html($company->company_name).'</span>';
            echo '<!-- suggest delimeter -->';
        }

        echo '<span data-id="0"><em>Anonymous</em></span>';
        echo '<!-- suggest delimeter -->';

        exit;
    }
    
    public static function userAction() 
    {
        if(!current_user_can("edit_pages")) {
            echo "-1";
            exit;
        }
        
        $request = Daq_Request::getInstance();
        $users = new WP_User_Query( array(
            'search'         => '*'.esc_attr( $request->get("q") ).'*',
            'search_columns' => array(
                'user_login',
                'display_name',
                'user_email',
            ),
        ) );

        $users_found = $users->get_results();

        foreach($users_found as $user) {
            echo '<span data-id="'.$user->ID.'">'.esc_html($user->display_name).'</span>';
            echo '<!-- suggest delimeter -->';
        }

        echo '<span data-id="0"><em>- None -</em></span>';
        echo '<!-- suggest delimeter -->';

        exit;
    }
}
