<?php
namespace KanbanBoard;

use \Michelf\Markdown;

class Application
{
    const QUEUED = 'queued';
    const ACTIVE = 'active';
    const COMPLETED = 'completed';
    const CLOSED = 'closed';
    const WAITING = 'waiting-for-feedback';

    public function __construct(Github $github, array $repositories, array $paused_labels = [])
    {
        $this->github = $github;
        $this->repositories = $repositories;
        $this->paused_labels = $paused_labels;
    }

    public function board() : array
    {
        $milestones = [];
        $output = [];
        foreach ($this->repositories as $repository)
        {
            foreach ($this->github->milestones($repository) as $data)
            {
                $key = $data['title'];
                $milestones[$key] = $data;
                $milestones[$key]['repository'] = $repository;
            }
        }
        ksort($milestones);

        foreach ($milestones as $name => $data)
        {
            $issues = $this->issues($data['repository'], $data['number']);
            $percent = self::percent($data['closed_issues'], $data['open_issues']);

            if (!$percent) {
                continue;
            }

            $output[] = [
                'milestone' => $name,
                'url' => $data['html_url'],
                'progress' => $percent,
                'queued' => $issues['queued'],
                'active' => $issues['active'],
                'completed' => $issues['completed']
            ];
        }

        return $output;
    }

    private function issues(string $repository, int $milestone_id) : array
    {
        $issues = $this->github->issues($repository, $milestone_id);

        foreach ($issues as $issue)
        {
            if (isset($issue['pull_request'])) {
                continue;
            }

            $key = ($issue['state'] === self::CLOSED ? self::COMPLETED : (($issue['assignee']) ? self::ACTIVE : self::QUEUED));

            $issues[$key][] = [
                'id' => $issue['id'],
                'number' => $issue['number'],
                'title' => $issue['title'],
                'body' => Markdown::defaultTransform($issue['body']),
                'url' => $issue['html_url'],
                'assignee' => (is_array($issue) && array_key_exists('assignee', $issue) && !empty($issue['assignee'])) ? $issue['assignee']['avatar_url'] . '?s=16' : null,
                'paused' => self::labelsMatch($issue, $this->paused_labels),
                'progress' => self::percent(
                    substr_count(strtolower($issue['body']), '[x]'),
                    substr_count(strtolower($issue['body']), '[ ]')
                ),
                'closed' => $issue['closed_at']
            ];
        }

        if (array_key_exists(self::ACTIVE, $issues) && is_array($issues[self::ACTIVE])) {
            usort($issues[self::ACTIVE], function ($a, $b) {
                return count($a['paused']) - count($b['paused']) === 0 ? strcmp($a['title'], $b['title']) : count($a['paused']) - count($b['paused']);
            });
        }

        return $issues;
    }

    private static function state(array $issue) : string
    {
        if ($issue['state'] === self::CLOSED) {
            return self::COMPLETED;
        } else if (Utilities::hasValue($issue, 'assignee') && count($issue['assignee'])) {
            return self::ACTIVE;
        }

        return self::QUEUED;
    }

    private static function labelsMatch(array $issue, array $needles) : array
    {
        if (!Utilities::hasValue($issue, 'labels')) {
            return [];
        }

        foreach ($issue['labels'] as $label) {
            if (in_array($label['name'], $needles)) {
                return [$label['name']];
            }
        }

        return [];
    }

    private static function percent(int $complete, int $remaining) : array
    {
        $total = $complete + $remaining;

        if ($total <= 0) {
            return [];
        }

        $percent = ($complete || $remaining) ? round($complete / $total * 100) : 0;

        return [
            'total' => $total,
            'complete' => $complete,
            'remaining' => $remaining,
            'percent' => $percent
        ];

    }
}
