<?php
namespace MordenBackup\Adapters;

use MordenBackup\Contracts\BackupDestinationInterface;

class BackblazeAdapter implements BackupDestinationInterface
{
    private $config;
    private $auth_token;
    private $api_url;
    private $download_url;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function authorize_account()
    {
        if ($this->auth_token) {
            return true;
        }

        $response = wp_remote_get('https://api.backblazeb2.com/b2api/v2/b2_authorize_account', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->config['key_id'] . ':' . $this->config['application_key']),
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['authorizationToken'])) {
            return false;
        }

        $this->auth_token = $body['authorizationToken'];
        $this->api_url = $body['apiUrl'];
        $this->download_url = $body['downloadUrl'];

        return true;
    }

    public function testConnection(): bool
    {
        return $this->authorize_account();
    }

    public function upload(string $localPath, string $remotePath): bool
    {
        if (!$this->authorize_account()) {
            return false;
        }

        $get_upload_url_response = wp_remote_post($this->api_url . '/b2api/v2/b2_get_upload_url', [
            'headers' => ['Authorization' => $this->auth_token],
            'body' => json_encode(['bucketId' => $this->config['bucket_id']]),
        ]);

        if (is_wp_error($get_upload_url_response)) {
            return false;
        }

        $upload_url_data = json_decode(wp_remote_retrieve_body($get_upload_url_response), true);
        $upload_url = $upload_url_data['uploadUrl'];
        $upload_auth_token = $upload_url_data['authorizationToken'];

        $file_contents = file_get_contents($localPath);
        $sha1 = sha1($file_contents);

        $upload_response = wp_remote_post($upload_url, [
            'headers' => [
                'Authorization' => $upload_auth_token,
                'X-Bz-File-Name' => $remotePath,
                'Content-Type' => 'application/zip',
                'X-Bz-Content-Sha1' => $sha1,
            ],
            'body' => $file_contents,
        ]);

        return !is_wp_error($upload_response);
    }

    public function download(string $remotePath, string $localPath): bool
    {
        if (!$this->authorize_account()) {
            return false;
        }

        $url = $this->download_url . '/file/' . $this->config['bucket_name'] . '/' . $remotePath;
        $response = wp_remote_get($url, ['headers' => ['Authorization' => $this->auth_token]]);

        if (is_wp_error($response)) {
            return false;
        }

        return file_put_contents($localPath, wp_remote_retrieve_body($response)) !== false;
    }

    public function listBackups(): array
    {
        if (!$this->authorize_account()) {
            return [];
        }

        $response = wp_remote_post($this->api_url . '/b2api/v2/b2_list_file_names', [
            'headers' => ['Authorization' => $this->auth_token],
            'body' => json_encode(['bucketId' => $this->config['bucket_id']]),
        ]);

        if (is_wp_error($response)) {
            return [];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return array_map(function($file) { return $file['fileName']; }, $body['files']);
    }

    public function delete(string $remotePath): bool
    {
        if (!$this->authorize_account()) {
            return false;
        }

        // First, get the file ID and version
        $list_response = wp_remote_post($this->api_url . '/b2api/v2/b2_list_file_names', [
            'headers' => ['Authorization' => $this->auth_token],
            'body' => json_encode(['bucketId' => $this->config['bucket_id'], 'startFileName' => $remotePath, 'maxFileCount' => 1]),
        ]);

        if (is_wp_error($list_response)) {
            return false;
        }

        $list_body = json_decode(wp_remote_retrieve_body($list_response), true);
        if (empty($list_body['files'])) {
            return false;
        }

        $file_id = $list_body['files'][0]['fileId'];

        $delete_response = wp_remote_post($this->api_url . '/b2api/v2/b2_delete_file_version', [
            'headers' => ['Authorization' => $this->auth_token],
            'body' => json_encode(['fileName' => $remotePath, 'fileId' => $file_id]),
        ]);

        return !is_wp_error($delete_response);
    }
}
