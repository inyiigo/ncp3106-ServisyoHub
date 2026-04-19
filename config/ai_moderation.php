<?php

function ai_env(string $key, string $default = ''): string {
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return (string)$value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string)$_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string)$_SERVER[$key];
    }

    return $default;
}

function ai_table_has_column(mysqli $db, string $table, string $column): bool {
    $table = mysqli_real_escape_string($db, $table);
    $column = mysqli_real_escape_string($db, $column);
    $res = @mysqli_query($db, "SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    if (!$res) {
        return false;
    }

    $exists = @mysqli_num_rows($res) > 0;
    @mysqli_free_result($res);
    return $exists;
}

function ai_ensure_moderation_schema(mysqli $db): void {
    if (!ai_table_has_column($db, 'jobs', 'ai_decision')) {
        @mysqli_query($db, "ALTER TABLE jobs ADD COLUMN ai_decision VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER status");
    }
    if (!ai_table_has_column($db, 'jobs', 'ai_score')) {
        @mysqli_query($db, "ALTER TABLE jobs ADD COLUMN ai_score DECIMAL(5,4) NULL AFTER ai_decision");
    }
    if (!ai_table_has_column($db, 'jobs', 'ai_reason')) {
        @mysqli_query($db, "ALTER TABLE jobs ADD COLUMN ai_reason VARCHAR(255) NULL AFTER ai_score");
    }
    if (!ai_table_has_column($db, 'jobs', 'ai_model')) {
        @mysqli_query($db, "ALTER TABLE jobs ADD COLUMN ai_model VARCHAR(80) NULL AFTER ai_reason");
    }
    if (!ai_table_has_column($db, 'jobs', 'ai_reviewed_at')) {
        @mysqli_query($db, "ALTER TABLE jobs ADD COLUMN ai_reviewed_at DATETIME NULL AFTER ai_model");
    }
    if (!ai_table_has_column($db, 'jobs', 'ai_raw_json')) {
        @mysqli_query($db, "ALTER TABLE jobs ADD COLUMN ai_raw_json LONGTEXT NULL AFTER ai_reviewed_at");
    }

    if (!ai_table_has_column($db, 'offers', 'ai_decision')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN ai_decision VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER citizen_status");
    }
    if (!ai_table_has_column($db, 'offers', 'ai_score')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN ai_score DECIMAL(5,4) NULL AFTER ai_decision");
    }
    if (!ai_table_has_column($db, 'offers', 'ai_reason')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN ai_reason VARCHAR(255) NULL AFTER ai_score");
    }
    if (!ai_table_has_column($db, 'offers', 'ai_model')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN ai_model VARCHAR(80) NULL AFTER ai_reason");
    }
    if (!ai_table_has_column($db, 'offers', 'ai_reviewed_at')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN ai_reviewed_at DATETIME NULL AFTER ai_model");
    }
    if (!ai_table_has_column($db, 'offers', 'ai_raw_json')) {
        @mysqli_query($db, "ALTER TABLE offers ADD COLUMN ai_raw_json LONGTEXT NULL AFTER ai_reviewed_at");
    }

    @mysqli_query($db, "CREATE TABLE IF NOT EXISTS moderation_audit_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        target_type VARCHAR(20) NOT NULL,
        target_id INT NOT NULL,
        decision VARCHAR(20) NOT NULL,
        score DECIMAL(5,4) NULL,
        reason VARCHAR(255) NULL,
        model VARCHAR(80) NULL,
        raw_json LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        overridden_by INT NULL,
        override_note VARCHAR(255) NULL,
        PRIMARY KEY (id),
        KEY idx_target (target_type, target_id),
        KEY idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ai_clamp_score($value): float {
    $score = (float)$value;
    if ($score < 0.0) {
        return 0.0;
    }
    if ($score > 1.0) {
        return 1.0;
    }
    return round($score, 4);
}

function ai_decision_from_score(float $score): string {
    if ($score >= 0.70) {
        return 'reject';
    }
    if ($score >= 0.25) {
        return 'review';
    }
    return 'approve';
}

function ai_rule_based_screen(array $payload): array {
    $parts = [
        (string)($payload['title'] ?? ''),
        (string)($payload['description'] ?? ''),
        (string)($payload['message'] ?? ''),
        (string)($payload['location'] ?? ''),
    ];

    $text = strtolower(trim(implode(' ', $parts)));
    $score = 0.12;
    $reason = 'No high-risk signals detected by rule checks.';

    if ($text === '') {
        return [
            'decision' => 'review',
            'score' => 0.45,
            'reason' => 'Empty content requires manual review.',
            'model' => 'rules-v1',
            'raw' => ['rules' => ['empty-content']],
        ];
    }

    $hardRejectTerms = [
        'sex service',
        'escort',
        'illegal drugs',
        'shabu',
        'gun for sale',
        'stolen',
    ];

    foreach ($hardRejectTerms as $term) {
        if (strpos($text, $term) !== false) {
            return [
                'decision' => 'reject',
                'score' => 0.95,
                'reason' => 'Detected prohibited content pattern.',
                'model' => 'rules-v1',
                'raw' => ['rules' => ['hard-reject-term', $term]],
            ];
        }
    }

    if (preg_match('/https?:\/\//i', $text)) {
        $score = max($score, 0.30);
        $reason = 'Contains external links; queued for review.';
    }

    if (preg_match('/(\+63|09\d{2})\d{7}/', $text)) {
        $score = max($score, 0.35);
        $reason = 'Contains direct contact details; queued for review.';
    }

    if (preg_match('/(.)\1{7,}/', $text)) {
        $score = max($score, 0.60);
        $reason = 'Spam-like repeated characters detected.';
    }

    $decision = ai_decision_from_score($score);

    return [
        'decision' => $decision,
        'score' => ai_clamp_score($score),
        'reason' => $reason,
        'model' => 'rules-v1',
        'raw' => ['rules' => ['rule-score' => $score]],
    ];
}

function ai_extract_json_object(string $text): ?array {
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    $json = substr($text, $start, $end - $start + 1);
    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

function ai_openai_screen(string $type, array $payload): ?array {
    $apiKey = ai_env('OPENAI_API_KEY', '');
    if ($apiKey === '' || !function_exists('curl_init')) {
        return null;
    }

    $model = ai_env('AI_MODERATION_MODEL', ai_env('OPENAI_MODERATION_MODEL', 'gpt-4.1-mini'));
    $endpoint = rtrim(ai_env('OPENAI_API_BASE', 'https://api.openai.com/v1'), '/');

    $system = 'You are a strict marketplace moderation classifier. Return JSON only with keys decision, score, reason. Decision must be one of approve, review, reject. Score must be 0 to 1 where higher is risk.';
    $user = [
        'content_type' => $type,
        'content' => $payload,
    ];

    $body = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
        ],
        'temperature' => 0,
    ];

    $ch = curl_init($endpoint . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return null;
    }

    $content = (string)($decoded['choices'][0]['message']['content'] ?? '');
    $parsed = ai_extract_json_object($content);
    if (!$parsed) {
        return null;
    }

    $decision = strtolower(trim((string)($parsed['decision'] ?? 'review')));
    if (!in_array($decision, ['approve', 'review', 'reject'], true)) {
        $decision = 'review';
    }

    $score = ai_clamp_score($parsed['score'] ?? 0.5);
    $reason = trim((string)($parsed['reason'] ?? 'AI moderation result.'));
    if ($reason === '') {
        $reason = 'AI moderation result.';
    }

    return [
        'decision' => $decision,
        'score' => $score,
        'reason' => $reason,
        'model' => 'openai:' . $model,
        'raw' => [
            'http_code' => $httpCode,
            'response' => $decoded,
            'curl_error' => $curlErr,
        ],
    ];
}

function ai_moderate_content(string $type, array $payload): array {
    $ruleResult = ai_rule_based_screen($payload);

    $aiResult = ai_openai_screen($type, $payload);
    if ($aiResult !== null) {
        return $aiResult;
    }

    return $ruleResult;
}

function ai_log_decision(mysqli $db, string $targetType, int $targetId, array $decision): void {
    $rawJson = json_encode($decision['raw'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $score = ai_clamp_score($decision['score'] ?? 0);
    $verdict = strtolower((string)($decision['decision'] ?? 'review'));
    if (!in_array($verdict, ['approve', 'review', 'reject'], true)) {
        $verdict = 'review';
    }

    if ($stmt = @mysqli_prepare($db, 'INSERT INTO moderation_audit_log (target_type, target_id, decision, score, reason, model, raw_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')) {
        $reason = substr((string)($decision['reason'] ?? ''), 0, 255);
        $model = substr((string)($decision['model'] ?? ''), 0, 80);
        mysqli_stmt_bind_param($stmt, 'sisdsss', $targetType, $targetId, $verdict, $score, $reason, $model, $rawJson);
        @mysqli_stmt_execute($stmt);
        @mysqli_stmt_close($stmt);
    }
}

function ai_apply_job_decision(mysqli $db, int $jobId, array $decision): void {
    $verdict = strtolower((string)($decision['decision'] ?? 'review'));
    if (!in_array($verdict, ['approve', 'review', 'reject'], true)) {
        $verdict = 'review';
    }

    $status = 'pending';
    if ($verdict === 'approve') {
        $status = 'approved';
    } elseif ($verdict === 'reject') {
        $status = 'rejected';
    }

    $score = ai_clamp_score($decision['score'] ?? 0);
    $reason = substr((string)($decision['reason'] ?? ''), 0, 255);
    $model = substr((string)($decision['model'] ?? ''), 0, 80);
    $rawJson = json_encode($decision['raw'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($stmt = @mysqli_prepare($db, "UPDATE jobs SET status = ?, ai_decision = ?, ai_score = ?, ai_reason = ?, ai_model = ?, ai_reviewed_at = NOW(), ai_raw_json = ? WHERE id = ?")) {
        mysqli_stmt_bind_param($stmt, 'ssdsssi', $status, $verdict, $score, $reason, $model, $rawJson, $jobId);
        @mysqli_stmt_execute($stmt);
        @mysqli_stmt_close($stmt);
    }

    ai_log_decision($db, 'job', $jobId, $decision);
}

function ai_apply_offer_decision(mysqli $db, int $offerId, array $decision): void {
    $verdict = strtolower((string)($decision['decision'] ?? 'review'));
    if (!in_array($verdict, ['approve', 'review', 'reject'], true)) {
        $verdict = 'review';
    }

    $adminStatus = 'pending';
    $status = 'pending';
    if ($verdict === 'approve') {
        $adminStatus = 'accepted';
    } elseif ($verdict === 'reject') {
        $adminStatus = 'rejected';
        $status = 'rejected';
    }

    $score = ai_clamp_score($decision['score'] ?? 0);
    $reason = substr((string)($decision['reason'] ?? ''), 0, 255);
    $model = substr((string)($decision['model'] ?? ''), 0, 80);
    $rawJson = json_encode($decision['raw'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($stmt = @mysqli_prepare($db, "UPDATE offers SET admin_status = ?, status = ?, ai_decision = ?, ai_score = ?, ai_reason = ?, ai_model = ?, ai_reviewed_at = NOW(), ai_raw_json = ? WHERE id = ?")) {
        mysqli_stmt_bind_param($stmt, 'sssdsssi', $adminStatus, $status, $verdict, $score, $reason, $model, $rawJson, $offerId);
        @mysqli_stmt_execute($stmt);
        @mysqli_stmt_close($stmt);
    }

    ai_log_decision($db, 'offer', $offerId, $decision);
}
