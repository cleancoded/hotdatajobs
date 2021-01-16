<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dashboard
 *
 * @author greg
 */
class Wpjb_Module_Ajax_Dashboard 
{
    public function statsAction()
    {
        $request = Daq_Request::getInstance();
        
        $currency = $request->post("currency");
        $stats = $request->post("stats");

        $info = new stdClass();
        $info->orders = 0;
        $info->volume = 0;
        $info->jobs = 0;
        $info->resumes = 0;
        $info->symbol = Wpjb_List_Currency::getCurrencySymbol($currency);
        $info->tick = array();
        
        if($stats == 1) {
            $start = date("Y-m-d 00:00:00", strtotime("today -6 days"));
            $totalTicks = 7;
        } else {
            $start = date("Y-m-d 00:00:00", strtotime("today -29 days"));
            $totalTicks = 30;
        }
        
        for($i=0; $i<$totalTicks; $i++) {
            if($i == 0) {
                $tick = strtotime($start);
            } else {
                $tick = strtotime("$start +$i days");
            }
            $ticks[$tick] = array(
                "tick" => strtolower(date("d-M", $tick)), 
                "orders" => 0,
                "volume" => 0
            );
        }
        
        $query = new Daq_Db_Query;
        $query->select("*");
        $query->from("Wpjb_Model_Payment t");
        $query->where("payment_currency = ?", $currency);
        $query->where("is_valid = 1");
        $query->where("made_at >= ?", $start);
        
        $list = $query->execute();
        
        foreach($list as $payment) {
            list($x) = explode(" ", $payment->made_at);
            $tick = strtotime($x);
            
            $ticks[$tick]["orders"]++;
            $ticks[$tick]["volume"]+= $payment->payment_paid;
            
            $info->orders++;
            $info->volume+= $payment->payment_paid;
            
            if(in_array($payment->object_type, array(1))) {
                $info->jobs++;
            } else {
                $info->resumes++;
            }
        }
        
        $maxVolume = 50;
        $maxOrders = 1;
        
        foreach($ticks as $tick) {
            if($tick["volume"]>$maxVolume) {
                $maxVolume = $tick["volume"];
            }
            if($tick["orders"]>$maxOrders) {
                $maxOrders = $tick["orders"];
            }
        }
        
        $d1 = new stdClass();
        $d1->data = array();
        $d1->color = "#83BC25";
        $d1->label = __("Revenue", "wpjobboard");
        $d1->lines = new stdClass();
        $d1->lines->show = true;
        $d1->lines->fill = false;
        $d1->points = new stdClass();
        $d1->points->show = 1;
        
        $d2 = new stdClass();
        $d2->data = array();
        $d2->color = "#81A4C7";
        $d2->label = __("Orders", "wpjobboard");
        $d2->yaxis = 2;
        $d2->bars = new stdClass();
        $d2->bars->show = true;
        $d2->bars->barWidth = 0.8;
        $d2->bars->align = "center";
        
        $data = array($d2, $d1);
        
        $options = new stdClass();
        $options->xaxis = new stdClass();
        $options->xaxis->ticks = array();
        $options->xaxis->min = 0;
        $options->xaxis->max = 0;
        $options->yaxes = array(new stdClass(), new stdClass());
        $options->yaxes[0]->min = 0;
        $options->yaxes[1]->min = 0;
        $options->yaxes[1]->max = $maxOrders*2;
        $options->yaxes[1]->alignTicksWithAxis = 1;
        $options->yaxes[1]->position = "right";
        
        $options->grid = new stdClass();
        $options->grid->hoverable = true;
        $options->grid->clickable = true;
        $options->grid->borderColor = "#DFDFDF";
        $options->grid->borderWidth = 1;
        $options->grid->color = "#333333";
        
        $options->legend = array("show"=>false);
        
        $i = 0;
        foreach($ticks as $t) {
            $j = $i+0.5;
            extract($t);

            if($totalTicks==30 && $i%5!=0) {
                $options->xaxis->ticks[] = array($j, "");
            } else {
                $options->xaxis->ticks[] = array($j, $tick);
            }
            $options->xaxis->max++;
            
            $data[0]->data[] = array($j, $orders);
            $data[1]->data[] = array($j, $volume);
            
            $info->tick[] = array($j, $tick);;
            
            $i++;
        }
        
        $response = new stdClass;
        $response->data = $data;
        $response->options = $options;
        $response->info = $info;
        
        die(json_encode($response));
    }
}

?>
