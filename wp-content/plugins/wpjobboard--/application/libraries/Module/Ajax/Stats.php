<?php
/**
 * Description of Stats
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Ajax_Stats
{
    public function indexAction()
    {
        $request = Daq_Request::getInstance();
        $chart = $request->post("type", 1);
        $sDate = $request->post("start");
        $eDate = $request->post("end");

        $response = new stdClass();
        $response->isError = false;

        $d = new Daq_Validate_Date();
        if(!$d->isValid($sDate)) {
            $response->isError = true;
            $response->error = "Invalid date format for start date.";
            die(json_encode($response));
        }
        if(!$d->isValid($eDate)) {
            $response->isError = true;
            $response->error = "Invalid date format for end date.";
            die(json_encode($response));
        }
        if(strtotime($eDate)<strtotime($sDate)) {
            $response->isError = true;
            $response->error = "End date cannot be greater then start date.";
            die(json_encode($response));
        }
        if(!in_array($chart, array(1,2))) {
            $response->isError = true;
            $response->error = "Invalid chart type.";
            die(json_encode($response));
        }

        $query = new Daq_Db_Query();
        $result = $query->select("SUM(payment_sum) AS sum, t.payment_currency AS curr, DATE(t.job_created_at) AS created_at")
            ->from("Wpjb_Model_Job t")
            ->where("job_created_at >= ?", $sDate)
            ->where("job_created_at <= ?", $eDate." 23:59:59")
            ->group("DATE(job_created_at), payment_currency")
            ->fetchAll();

        $min = null;
        $max = null;
        $curr = array();
        $dt = array();
        $response->chart = new stdClass;
        $response->chart->meta = array("Date");
        $response->chart->data = array();

        foreach($result as $r) {
            $time = strtotime($r->created_at);
            if($min === null || $time<$min) {
                $min = $time;
            }
            if($time > $max) {
                $max = $time;
            }
            if(!in_array($r->curr, $curr)) {
                $response->chart->meta[] = $r->curr;
                $curr[$r->curr] = 0;
            }
        }
        $keyList = array_keys($curr);
        for($i=$min; $i<=$max; $i=strtotime(date("Y-m-d", $i)." +1 DAY")) {
            $dDay = new stdClass;
            $dDay->date = date("Y-m-d", $i);
            $dDay->data = $curr;
            foreach($result as $r) {
                if($r->created_at == $dDay->date) {
                    $dDay->data[(int)$r->curr] = $r->sum;
                }
            }
            $response->chart->data[] = $dDay;

        }

        echo json_encode($response);
        die;
    }
}

?>