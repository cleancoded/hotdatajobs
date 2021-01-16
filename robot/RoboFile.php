<?php
/**
 * Full-Text RSS console commands for Robo task runner.
 * 
 * Intended to be run on a clean install of Full-Text RSS 
 * running on an Ubuntu instance initialised with our server
 * initialisation script.
 * 
 * Note: Full-Text RSS is a web service, not a command-line tool. 
 * These are simply convenience methods to help work with 
 * Full-Text RSS from the command line. You will require a working
 * Full-Text RSS instance for these to work.
 * 
 * Quick start:
 * 1. Edit robo.yml and change URL to where you installed Full-Text RSS
 *    (keep localhost if you're running on same server)
 * 2. Install Robo (see http://robo.li)
 * 3. Run: "robo init" or "php robo.phar init" (depending on how you installed Robo)
 * 
 * You can safely delete this file if do not intend to use these
 * convenience methods.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function extract($url) {
        $ftr = $this->get_ftr_url().'/extract.php';
        $ftr .= '?'.http_build_query(['url'=>$url]);
        $json = file_get_contents($ftr);
        if ($json && json_decode($json)) {
            echo $json;
        }
    }

    public function title($url) {
        $ftr = $this->get_ftr_url().'/extract.php';
        $ftr .= '?'.http_build_query(['url'=>$url]);
        $json = file_get_contents($ftr);
        if ($json && $json = json_decode($json)) {
            echo $json->title;
        }
    }

    public function html($url) {
        $ftr = $this->get_ftr_url().'/extract.php';
        $ftr .= '?'.http_build_query(['url'=>$url]);
        $json = file_get_contents($ftr);
        if ($json && $json = json_decode($json)) {
            echo $json->content;
        }
    }

    public function text($url) {
        $ftr = $this->get_ftr_url().'/extract.php';
        $ftr .= '?'.http_build_query(['url'=>$url, 'content'=>'text', 'links'=>'remove']);
        $json = file_get_contents($ftr);
        if ($json && $json = json_decode($json)) {
            echo $json->title;
            echo "\n\n";
            echo $json->content;
        }
    }

    public function cleanCache() {
        $cache_cleanup_file = $this->getOrCreateCacheCleanupFile();
        if (!$cache_cleanup_file) exit;
        $url = $this->get_ftr_url().'/'.$cache_cleanup_file;
        $this->say('Requesting '.$url.'...');
        file_get_contents($url);
        $this->say('Done!');
    }

    public function updateSiteConfigFiles() {
        $admin_hash = $this->get_admin_hash();
        if (!$admin_hash) {
            $this->say('Admin credentials not found. Run init.');
            exit;
        }
        // folder permissions
        $this->_chmod('site_config', 0777);
        $this->_chmod('site_config/standard', 0777);
        $url = $this->get_ftr_url().'/admin/update.php?key='.$admin_hash;
        //$this->say($url);
        $html = file_get_contents($url);
        if (strpos($html, 'All done!') !== false) {
            $this->say('Updated!');
        } elseif(strpos($html, 'Your site config files are up to date!') !== false) {
            $this->say('Already up to date');
        } else {
            $this->say('Something went wrong');
        }
    }

    public function init() {
        $admin_hash = $this->get_admin_hash();
        if (!$admin_hash) {
            if (file_exists('custom_config.php')) {
                $this->say('Custom config already exists, so admin credentials needed to initialise. Edit custom_config.php and add password to $options->admin_credentials.');
                exit;
            }
            if (!file_exists('config.php')) {
                $this->say('config.php file not found');
                exit;
            }
            $config_lines = $this->get_config_lines();
            if (!in_array('$options->admin_credentials = array(\'username\'=>\'admin\', \'password\'=>\'\');', $config_lines)) {
                $this-say('Cannot find default credentials in config file. Config file might have been edited.');
                exit;
            }
            $newpasswd = substr(hash('sha512', rand()), 0, 12);
            // TODO: copy to custom_config.php
            $this->taskWriteToFile('custom_config.php')
                ->line('<?php')
                ->line('// Admin password generated from command line ("robo init")')
                ->line('$options->admin_credentials = array(\'username\'=>\'admin\', \'password\'=>\''.$newpasswd.'\');')
                ->run();
            // try again
            $admin_hash = $this->get_admin_hash();
            if (!$admin_hash) {
                $this->say('Failed to add password');
                exit;
            }
            $this->say("New admin credentials:\nusername: admin\npassword: $newpasswd");
        }
        $this->enableCaching();
        $this->enableSiteConfigUpdates();
    }

    public function enableCaching() {
        // check admin permissions
        $this->require_admin();
        $this->require_custom_config();
        // check config
        include 'config.php';
        if ($options->caching) {
            $this->say('Already enabled');
        } else {
            if ($this->config_contains('$options->caching = false;', 'custom_config.php')) {
                $this->taskReplaceInFile('custom_config.php')
                    ->from('$options->caching = false;')
                    ->to('$options->caching = true;')
                    ->run();
            } else {
                $this->taskWriteToFile('custom_config.php')
                    ->append(true)
                    ->line('$options->caching = true;')
                    ->run();
            }
        }
        // folder permissions
        $this->_chmod('cache', 0777);
        $this->_chmod('cache/rss', 0777);
        $this->_chmod('cache/rss-with-key', 0777);
        $this->say('Enabled caching!');

        // set up cron job
        $this->updateCronCacheCleanup();
    }

    public function enableSiteConfigUpdates() {
        $cron_file = '/etc/cron.hourly/ff-ftr-siteconfig-update';
        // check admin permissions
        $this->require_admin();
        // set up cron job
        $admin_hash = $this->get_admin_hash();
        if (!$admin_hash) {
            $this->say('Admin credentials not found. Run init.');
            exit;
        }
        // folder permissions
        $this->_chmod('site_config', 0777);
        $this->_chmod('site_config/standard', 0777);
        $url = $this->get_ftr_url().'/admin/update.php?key='.$admin_hash;
        $this->taskWriteToFile($cron_file)
            ->append(false)
            ->line('#!/bin/sh')
            ->line('wget --quiet -O /dev/null '.$url)
            ->run();
        $this->_chmod($cron_file, 0755);
        $this->say("Site config update cron updated! $url will be requested every hour.");
        $this->say("Cron file: $cron_file");
    }

    public function updateCronCacheCleanup() {
        include 'config.php';
        if (file_exists('custom_config.php')) {
            include 'custom_config.php';
        }
        $cron_file = '/etc/cron.hourly/ff-ftr-cache-cleanup';
        if (!$options->caching) {
            if (file_exists($cron_file)) {
                $this->_remove($cron_file);
            }
            $this->say('Caching is disabled in config. Cache cleanup script removed.');
            exit;
        }
        if ($cache_cleanup_file = $this->getOrCreateCacheCleanupFile()) {
            // all good
        } else {
            exit;
        }
        $url = $this->get_ftr_url().'/'.$cache_cleanup_file;
        $this->taskWriteToFile($cron_file)
            ->append(false)
            ->line('#!/bin/sh')
            ->line('wget --quiet -O /dev/null '.$url)
            ->run();
        $this->_chmod($cron_file, 0755);
        $this->say("Cache cleanup cron updated! $url will be requested every hour.");
        $this->say("Cron file: $cron_file");
    }

    /**********************/
    /*** HELPER METHODS ***/
    /**********************/

    private function get_ftr_url() {
        return \Robo\Robo::Config()->get('RoboFile.full-text-rss-url');
    }

    private function get_admin_hash() {
        include 'config.php';
        if (!isset($options->admin_credentials) || $options->admin_credentials['username'] == '' || $options->admin_credentials['password'] == '') return false;
        return sha1($options->admin_credentials['username'].'+'.$options->admin_credentials['password']);
    }

    private function require_admin() {
        $admin_hash = $this->get_admin_hash();
        if (!$admin_hash) {
            $this->say('Run "robo init" to set up admin password');
            exit;
        }
    }

    private function require_custom_config() {
        if (!file_exists('custom_config.php')) {
            $this->say('No custom_config.php file found. Run "robo init" first or "cp config.php custom_config.php".');
            exit;
        }
    }

    private function get_config_lines() {
        if (!file_exists('config.php')) {
            return false;
        }
        return file('config.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    private function get_custom_config_lines() {
        if (!file_exists('custom_config.php')) {
            return false;
        }
        return file('custom_config.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    private function getOrCreateCacheCleanupFile() {
        $cache_cleanup_file = glob('cleancache_*.php');
        if (count($cache_cleanup_file) > 0) {
            return $cache_cleanup_file[0];
        } elseif (file_exists('cleancache.php')) {
            $cache_cleanup_file = 'cleancache_'.substr(hash('sha512', rand()), 0, 6).'.php';
            $this->_rename('cleancache.php', $cache_cleanup_file);
            return $cache_cleanup_file;
        } else {
            $this->say("Couldn't find cache cleanup file! Rename cleancache.php to cleancache_somethingsecret.php and try again.");
            return false;
        }
    }

    private function config_contains($line, $file='custom_config.php') {
        if ($file=='config.php') {
            $lines = $this->get_config_lines();
        } else {
            $lines = $this->get_custom_config_lines();
        }
        if (!is_array($lines)) {
            $this->say("Couldn't load config file");
            return false;
        }
        return in_array($line, $lines);
    }
}