<?php
function get_github_activity($username) {
    $url = "https://api.github.com/users/{$username}/events";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MyGoalie');
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) {
        return false;
    }
    
    $events = json_decode($response, true);
    if (!is_array($events)) {
        return false;
    }
    
    $activity = [];
    foreach ($events as $event) {
        if ($event['type'] === 'PushEvent') {
            $activity[] = [
                'type' => 'push',
                'repo' => $event['repo']['name'],
                'commits' => count($event['payload']['commits']),
                'date' => $event['created_at']
            ];
        } elseif ($event['type'] === 'CreateEvent' && $event['payload']['ref_type'] === 'repository') {
            $activity[] = [
                'type' => 'new_repo',
                'repo' => $event['repo']['name'],
                'date' => $event['created_at']
            ];
        }
    }
    
    return $activity;
}

function format_github_activity($activity) {
    if (empty($activity)) {
        return "No recent GitHub activity.";
    }
    
    $output = "<div class='github-activity'>";
    foreach ($activity as $item) {
        $date = date('M j, Y', strtotime($item['date']));
        if ($item['type'] === 'push') {
            $output .= "<div class='activity-item push'>";
            $output .= "<span class='activity-icon'>📤</span>";
            $output .= "<span class='activity-text'>Pushed {$item['commits']} commit(s) to {$item['repo']}</span>";
            $output .= "<span class='activity-date'>{$date}</span>";
            $output .= "</div>";
        } elseif ($item['type'] === 'new_repo') {
            $output .= "<div class='activity-item new-repo'>";
            $output .= "<span class='activity-icon'>📦</span>";
            $output .= "<span class='activity-text'>Created new repository {$item['repo']}</span>";
            $output .= "<span class='activity-date'>{$date}</span>";
            $output .= "</div>";
        }
    }
    $output .= "</div>";
    
    return $output;
}

function get_github_repositories($username) {
    $url = "https://api.github.com/users/{$username}/repos?sort=updated";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MyGoalie');
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) {
        return [];
    }
    
    $repos = json_decode($response, true);
    if (!is_array($repos)) {
        return [];
    }
    
    // Limit to the 20 most recently updated repositories
    $repos = array_slice($repos, 0, 20);
    
    return $repos;
}

function get_repository_branches($repo_full_name) {
    $url = "https://api.github.com/repos/{$repo_full_name}/branches";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MyGoalie');
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) {
        return [];
    }
    
    $branches = json_decode($response, true);
    if (!is_array($branches)) {
        return [];
    }
    
    return $branches;
}

function get_repository_commits($repo_full_name, $branch = 'main') {
    $url = "https://api.github.com/repos/{$repo_full_name}/commits?sha={$branch}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MyGoalie');
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) {
        return [];
    }
    
    $commits = json_decode($response, true);
    if (!is_array($commits)) {
        return [];
    }
    
    // Limit to the 10 most recent commits
    $commits = array_slice($commits, 0, 10);
    
    return $commits;
}
?> 