<?php
namespace Vulgar\Repo\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Vulgar\Repo\Models\Repository;

class RepoService
{
    protected Client $client;
    protected string $username;
    protected string $token;

    public function __construct()
    {
        $this->client = new Client();
        $this->username = config('repo.github_username');
        $this->token = config('repo.github_token');
    }

    public function fetchRepositories(): array
    {
        if (Cache::has('github_repos')) {
            return Cache::get('github_repos');
        }

        $url = "https://api.github.com/users/{$this->username}/repos";

        $options = [
            'headers' => array_filter([
                'Authorization' => $this->token ? 'token ' . $this->token : null
            ])
        ];

        $response = $this->client->get($url, $options);
        $repos = json_decode($response->getBody()->getContents(), true);
        
        foreach ($repos as $key => &$repo) {
            try{
                $readme = $this->fetchReadme($repo['full_name']);
            }catch(\Exception $e){
                unset($repos[$key]);
                continue;
            }

            $repo['readme'] = $readme;
            
            // Save or update repository in the database
            Repository::updateOrCreate(
                ['full_name' => $repo['full_name']],
                [
                    'name' => $repo['name'],
                    'html_url' => $repo['html_url'],
                    'description' => $repo['description'],
                    'readme' => $readme
                ]
            );
        }

        // reset keys in case of unset
        $repos = array_values($repos);

        Cache::put('github_repos', $repos, config('repo.cache_duration'));
        
        return $repos;
    }

    public function fetchReadme(string $repoFullName): string
    {
        $url = "https://api.github.com/repos/{$repoFullName}/readme";
        $options = [
            'headers' => array_filter([
                'Authorization' => $this->token ? 'token ' . $this->token : null,
                'Accept' => 'application/vnd.github.v3.raw'
            ])
        ];

        $response = $this->client->get($url, $options);

        return $response->getBody()->getContents();
    }
}
