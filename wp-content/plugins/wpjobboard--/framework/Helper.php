<?php
/**
 * Description of Helper
 *
 * @author greg
 * @package 
 */

class Daq_Helper
{
    public static function registerAll()
    {
        self::registerHtml();
        self::registerDate();
        self::registerDateFormat();
    }

    public static function registerHtml()
    {
        if(!function_exists("daq_escape")) {
            function daq_escape($text)
            {
                return esc_html($text);
            }
        }
    }

    public static function registerDate()
    {
        if(function_exists("daq_distance_of_time_in_words") || function_exists("daq_time_ago_in_words")) {
            return;
        }

        /**
         * Ripped from symfony DateHelper
         * http://trac.symfony-project.org/browser/branches/1.0/lib/helper/DateHelper.php
         *
         * @param <type> $from_time
         * @param <type> $to_time
         * @param <type> $include_seconds
         * @return <type>
         */
        function daq_distance_of_time_in_words($from_time, $to_time = null, $include_seconds = false)
        {
          $to_time = $to_time? $to_time: time();

          $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
          $distance_in_seconds = floor(abs($to_time - $from_time));

          $string = '';
          $parameters = array();

          if ($distance_in_minutes <= 1)
          {
            if (!$include_seconds)
            {
              $string = $distance_in_minutes == 0 ? __('less than a minute', "wpjobboard") : __('1 minute', "wpjobboard");
            }
            else
            {
              if ($distance_in_seconds <= 5)
              {
                $string = __('less than 5 seconds', "wpjobboard");
              }
              else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
              {
                $string = __('less than 10 seconds', "wpjobboard");
              }
              else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
              {
                $string = __('less than 20 seconds', "wpjobboard");
              }
              else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
              {
                $string = __('half a minute', "wpjobboard");
              }
              else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
              {
                $string = __('less than a minute', "wpjobboard");
              }
              else
              {
                $string = __('1 minute', "wpjobboard");
              }
            }
          }
          else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44)
          {
            $string = __('%minutes% minutes', "wpjobboard");
            $parameters['%minutes%'] = $distance_in_minutes;
          }
          else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89)
          {
            $string = __('about 1 hour', "wpjobboard");
          }
          else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439)
          {
            $string = __('about %hours% hours', "wpjobboard");
            $parameters['%hours%'] = round($distance_in_minutes / 60);
          }
          else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879)
          {
            $string = __('1 day', "wpjobboard");
          }
          else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199)
          {
            $string = __('%days% days', "wpjobboard");
            $parameters['%days%'] = round($distance_in_minutes / 1440);
          }
          else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399)
          {
            $string = __('about 1 month', "wpjobboard");
          }
          else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959)
          {
            $string = __('%months% months', "wpjobboard");
            $parameters['%months%'] = round($distance_in_minutes / 43200);
          }
          else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919)
          {
            $string = __('about 1 year', "wpjobboard");
          }
          else
          {
            $string = __('over %years% years', "wpjobboard");
            $parameters['%years%'] = floor($distance_in_minutes / 525960);
          }

          return strtr($string, $parameters);
        }

        // Like distance_of_time_in_words, but where to_time is fixed to time()
        function daq_time_ago_in_words($from_time, $include_seconds = false)
        {
            return daq_distance_of_time_in_words($from_time, time(), $include_seconds);
        }
        
    }
    
    public static function registerDateFormat()
    {
        
        if(function_exists("date_create_from_format")) {
            return;
        }
        
        function date_create_from_format( $dformat, $dvalue )
        {

          $schedule = $dvalue;
          $schedule_format = str_replace(array('Y','m','d', 'H', 'i','a'),array('%Y','%m','%d', '%I', '%M', '%p' ) ,$dformat);
          // %Y, %m and %d correspond to date()'s Y m and d.
          // %I corresponds to H, %M to i and %p to a
          $ugly = strptime($schedule, $schedule_format);
          $ymd = sprintf(
              // This is a format string that takes six total decimal
              // arguments, then left-pads them with zeros to either
              // 4 or 2 characters, as needed
              '%04d-%02d-%02d %02d:%02d:%02d',
              $ugly['tm_year'] + 1900,  // This will be "111", so we need to add 1900.
              $ugly['tm_mon'] + 1,      // This will be the month minus one, so we add one.
              $ugly['tm_mday'], 
              $ugly['tm_hour'], 
              $ugly['tm_min'], 
              $ugly['tm_sec']
          );
          $new_schedule = new DateTime($ymd);

         return $new_schedule;

        }

    }
}

?>