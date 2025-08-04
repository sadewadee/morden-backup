<?php
namespace MordenBackup\Utils;

class Updater
{
    private $file;
    private $plugin;
    private a $basename;
    private $active;
    private $github_repo;
    private $plugin_data;

    public function __construct($file)
    {
        $this->file = $file;
        $this->plugin_data = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->github_repo = 'sadewadee/morden-backup'; // Format: owner/repo

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'post_install'], 10, 3);
    }

    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();
        if ($release && version_compare($this->plugin_data['Version'], $release->tag_name, '<')) {
            $transient->response[$this->basename] = (object) [
                'slug' => $this->basename,
                'new_version' => $release->tag_name,
                'url' => $this->plugin_data['PluginURI'],
                'package' => $release->zipball_url,
            ];
        }

        return $transient;
    }

    public function plugin_info($res, $action, $args)
    {
        if ($action !== 'plugin_information' || $args->slug !== $this->basename) {
            return $res;
        }

        $release = $this->get_latest_release();
        if (!$release) {
            return $res;
        }

        $res = (object) [
            'name' => $this->plugin_data['Name'],
            'slug' => $this->basename,
            'version' => $release->tag_name,
            'author' => $this->plugin_data['AuthorName'],
            'author_profile' => $this->plugin_data['AuthorURI'],
            'last_updated' => $release->published_at,
            'homepage' => $this->plugin_data['PluginURI'],
            'short_description' => $this->plugin_data['Description'],
            'sections' => [
                'description' => $this->plugin_data['Description'],
                'changelog' => $release->body,
            ],
            'download_link' => $release->zipball_url,
        ];

        return $res;
    }

    public function post_install($true, $hook_extra, $result)
    {
        global $wp_filesystem;
        $proper_destination = WP_PLUGIN_DIR . '/' . dirname($this->basename);
        $wp_filesystem->move($result['destination'], $proper_destination);
        $result['destination'] = $proper_destination;
        activate_plugin($this->basename);
        return $result;
    }

    private function get_latest_release()
    {
        $url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (empty($data) || isset($data->message)) {
            return false;
        }

        return $data;
    }
}
