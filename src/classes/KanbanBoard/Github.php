<?php
namespace KanbanBoard;

use Github\HttpClient\CachedHttpClient;
use Github\Client;

class Github
{
    const CACHE_DIR = "/tmp/github-api-cache";

    private $client;
    private $milestone_api;
    private $account;

    public function __construct(array $token, string $account)
    {
        require '../../vendor/autoload.php';
        $this->account = $account;
        $this->client = new Client(new CachedHttpClient(['cache_dir' => self::CACHE_DIR]));
        $this->client->authenticate($token, Client::AUTH_URL_TOKEN);
        $this->milestone_api = $this->client->api('issues')->milestones();
    }

    public function milestones(string $repository) : array
    {
        return $this->milestone_api->all($this->account, $repository);
    }

    public function issues(string $repository, int $milestone_id) : array
    {
        $issue_parameters = ['milestone' => $milestone_id, 'state' => 'all'];
        return $this->client->api('issue')->all($this->account, $repository, $issue_parameters);
    }
}