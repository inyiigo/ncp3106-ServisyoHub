<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db_connect.php';

$db = $conn ?? $mysqli ?? null;

function j($data, int $status = 200): void {
	http_response_code($status);
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function clean_text(string $value, int $limit = 1000): string {
	$value = trim(str_replace(["\r\n", "\r"], "\n", strip_tags($value)));
	if (function_exists('mb_strlen') && function_exists('mb_substr')) {
		if (mb_strlen($value) > $limit) {
			$value = mb_substr($value, 0, $limit);
		}
	} elseif (strlen($value) > $limit) {
		$value = substr($value, 0, $limit);
	}
	return $value;
}

function display_name(array $row): string {
	$name = trim((string)($row['username'] ?? ''));
	if ($name === '') {
		$name = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
	}
	if ($name === '') {
		$name = trim((string)($row['mobile'] ?? ''));
	}
	return $name !== '' ? $name : 'User';
}

function avatar_url(?string $path): string {
	$path = trim((string)$path);
	if ($path === '') {
		return '';
	}
	if (preg_match('#^https?://#i', $path)) {
		return $path;
	}
	return '../' . ltrim($path, '/');
}

function ensure_chat_table($db): void {
	@mysqli_query($db, "CREATE TABLE IF NOT EXISTS chat_messages (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		job_id INT(11) NOT NULL,
		offer_id INT(11) NOT NULL,
		sender_id INT(10) UNSIGNED NOT NULL,
		recipient_id INT(10) UNSIGNED NOT NULL,
		body TEXT NOT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		read_at DATETIME DEFAULT NULL,
		PRIMARY KEY (id),
		KEY idx_chat_offer (offer_id),
		KEY idx_chat_job (job_id),
		KEY idx_chat_sender (sender_id),
		KEY idx_chat_recipient (recipient_id),
		KEY idx_chat_created (created_at)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

function notifications_has_column($db, string $column): bool {
	$col = mysqli_real_escape_string($db, $column);
	$res = @mysqli_query($db, "SHOW COLUMNS FROM notifications LIKE '{$col}'");
	$ok = ($res && mysqli_num_rows($res) > 0);
	if ($res) {
		@mysqli_free_result($res);
	}
	return $ok;
}

function ensure_notifications_table($db): void {
	@mysqli_query($db, "CREATE TABLE IF NOT EXISTS notifications (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id INT(10) UNSIGNED NOT NULL,
		actor_id INT(10) UNSIGNED NOT NULL,
		job_id INT(11) NOT NULL,
		comment_id INT UNSIGNED DEFAULT NULL,
		offer_id INT(11) DEFAULT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		seen_at DATETIME DEFAULT NULL,
		PRIMARY KEY (id),
		KEY idx_user_seen (user_id, seen_at),
		KEY idx_job (job_id),
		KEY idx_actor (actor_id),
		KEY idx_offer (offer_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

	if (!notifications_has_column($db, 'offer_id')) {
		@mysqli_query($db, "ALTER TABLE notifications ADD COLUMN offer_id INT(11) DEFAULT NULL AFTER comment_id");
		@mysqli_query($db, "ALTER TABLE notifications ADD KEY idx_offer (offer_id)");
	}
	if (!notifications_has_column($db, 'seen_at')) {
		@mysqli_query($db, "ALTER TABLE notifications ADD COLUMN seen_at DATETIME DEFAULT NULL AFTER created_at");
	}
	if (!notifications_has_column($db, 'created_at')) {
		@mysqli_query($db, "ALTER TABLE notifications ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
	}
}

function fetch_participants($db, int $offerId, int $jobId): ?array {
	$sql = "SELECT o.id AS offer_id, o.job_id, o.user_id AS offerer_id, o.amount, o.status AS offer_status,
			   j.user_id AS owner_id, j.title, COALESCE(j.location,'Online') AS location,
			   COALESCE(j.date_needed,'Anytime') AS date_needed,
			   COALESCE(uo.username,'') AS offerer_username, COALESCE(uo.first_name,'') AS offerer_first,
			   COALESCE(uo.last_name,'') AS offerer_last, COALESCE(uo.avatar,'') AS offerer_avatar,
			   COALESCE(up.username,'') AS owner_username, COALESCE(up.first_name,'') AS owner_first,
			   COALESCE(up.last_name,'') AS owner_last, COALESCE(up.avatar,'') AS owner_avatar
		FROM offers o
		JOIN jobs j ON j.id = o.job_id
		LEFT JOIN users uo ON uo.id = o.user_id
		LEFT JOIN users up ON up.id = j.user_id
		WHERE o.id = ? AND o.job_id = ?
		LIMIT 1";
	if (!$stmt = @mysqli_prepare($db, $sql)) {
		return null;
	}
	mysqli_stmt_bind_param($stmt, 'ii', $offerId, $jobId);
	if (!@mysqli_stmt_execute($stmt)) {
		@mysqli_stmt_close($stmt);
		return null;
	}
	$result = @mysqli_stmt_get_result($stmt);
	$row = $result ? @mysqli_fetch_assoc($result) : null;
	@mysqli_stmt_close($stmt);
	return $row ?: null;
}

function conversation_rows($db, int $viewerId, string $tab): array {
	$rows = [];
	$latestOffersSql = "SELECT job_id, user_id, MAX(id) AS offer_id FROM offers GROUP BY job_id, user_id";
	if ($tab === 'citizen') {
		$sql = "SELECT o.id AS offer_id, o.job_id, o.user_id AS offerer_id, o.amount, o.status AS offer_status,
			       o.created_at AS offer_created_at,
			       j.user_id AS owner_id, j.title, COALESCE(j.location,'Online') AS location,
			       COALESCE(j.date_needed,'Anytime') AS date_needed,
			       COALESCE(u.username,'') AS counter_username, COALESCE(u.first_name,'') AS counter_first,
			       COALESCE(u.last_name,'') AS counter_last, COALESCE(u.avatar,'') AS counter_avatar,
			       COALESCE(lm.body,'') AS latest_message_body, COALESCE(lm.sender_id,0) AS latest_message_sender_id,
			       lm.created_at AS latest_message_created_at, COALESCE(lm.id,0) AS latest_message_id,
			       (SELECT COUNT(*) FROM chat_messages cm WHERE cm.offer_id = o.id AND cm.recipient_id = ? AND cm.read_at IS NULL) AS unread_count
			FROM offers o
			JOIN ($latestOffersSql) latest ON latest.offer_id = o.id
			JOIN jobs j ON j.id = o.job_id
			LEFT JOIN users u ON u.id = o.user_id
			LEFT JOIN chat_messages lm ON lm.id = (
				SELECT cm2.id FROM chat_messages cm2 WHERE cm2.offer_id = o.id ORDER BY cm2.id DESC LIMIT 1
			)
			WHERE j.user_id = ?
			  AND o.user_id <> ?
			ORDER BY COALESCE(lm.created_at, o.created_at) DESC, o.id DESC";
		if ($stmt = @mysqli_prepare($db, $sql)) {
			mysqli_stmt_bind_param($stmt, 'iii', $viewerId, $viewerId, $viewerId);
			if (@mysqli_stmt_execute($stmt)) {
				$result = @mysqli_stmt_get_result($stmt);
				while ($row = @mysqli_fetch_assoc($result)) {
					$row['counterparty_id'] = (int)($row['offerer_id'] ?? 0);
					$row['counterparty_name'] = display_name([
						'username' => $row['counter_username'] ?? '',
						'first_name' => $row['counter_first'] ?? '',
						'last_name' => $row['counter_last'] ?? '',
					]);
					$row['counterparty_avatar'] = avatar_url($row['counter_avatar'] ?? '');
					$rows[] = $row;
				}
			}
			@mysqli_stmt_close($stmt);
		}
	} else {
		$sql = "SELECT o.id AS offer_id, o.job_id, o.user_id AS offerer_id, o.amount, o.status AS offer_status,
			       o.created_at AS offer_created_at,
			       j.user_id AS owner_id, j.title, COALESCE(j.location,'Online') AS location,
			       COALESCE(j.date_needed,'Anytime') AS date_needed,
			       COALESCE(u.username,'') AS counter_username, COALESCE(u.first_name,'') AS counter_first,
			       COALESCE(u.last_name,'') AS counter_last, COALESCE(u.avatar,'') AS counter_avatar,
			       COALESCE(lm.body,'') AS latest_message_body, COALESCE(lm.sender_id,0) AS latest_message_sender_id,
			       lm.created_at AS latest_message_created_at, COALESCE(lm.id,0) AS latest_message_id,
			       (SELECT COUNT(*) FROM chat_messages cm WHERE cm.offer_id = o.id AND cm.recipient_id = ? AND cm.read_at IS NULL) AS unread_count
			FROM offers o
			JOIN ($latestOffersSql) latest ON latest.offer_id = o.id
			JOIN jobs j ON j.id = o.job_id
			LEFT JOIN users u ON u.id = j.user_id
			LEFT JOIN chat_messages lm ON lm.id = (
				SELECT cm2.id FROM chat_messages cm2 WHERE cm2.offer_id = o.id ORDER BY cm2.id DESC LIMIT 1
			)
			WHERE o.user_id = ?
			  AND j.user_id <> ?
			ORDER BY COALESCE(lm.created_at, o.created_at) DESC, o.id DESC";
		if ($stmt = @mysqli_prepare($db, $sql)) {
			mysqli_stmt_bind_param($stmt, 'iii', $viewerId, $viewerId, $viewerId);
			if (@mysqli_stmt_execute($stmt)) {
				$result = @mysqli_stmt_get_result($stmt);
				while ($row = @mysqli_fetch_assoc($result)) {
					$row['counterparty_id'] = (int)($row['owner_id'] ?? 0);
					$row['counterparty_name'] = display_name([
						'username' => $row['counter_username'] ?? '',
						'first_name' => $row['counter_first'] ?? '',
						'last_name' => $row['counter_last'] ?? '',
					]);
					$row['counterparty_avatar'] = avatar_url($row['counter_avatar'] ?? '');
					$rows[] = $row;
				}
			}
			@mysqli_stmt_close($stmt);
		}
	}

	return $rows;
}

if (!$db) {
	j(['ok' => false, 'error' => 'Database unavailable'], 500);
	exit;
}

@mysqli_select_db($db, 'login');
ensure_chat_table($db);
ensure_notifications_table($db);

$viewerId = (int)($_SESSION['user_id'] ?? 0);
$tab = (($_REQUEST['tab'] ?? 'kasangga') === 'citizen') ? 'citizen' : 'kasangga';

$payload = $_POST;
if (empty($payload)) {
	$raw = file_get_contents('php://input');
	$decoded = json_decode((string)$raw, true);
	if (is_array($decoded)) {
		$payload = $decoded;
	}
}

$action = (string)($payload['action'] ?? $_REQUEST['action'] ?? 'list');

if ($action === 'list') {
	j([
		'ok' => true,
		'tab' => $tab,
		'conversations' => $viewerId ? conversation_rows($db, $viewerId, $tab) : [],
	]);
	exit;
}

if (!$viewerId) {
	j(['ok' => false, 'error' => 'Login required'], 401);
	exit;
}

$offerId = (int)($payload['offer_id'] ?? $_REQUEST['offer_id'] ?? 0);
$jobId = (int)($payload['job_id'] ?? $_REQUEST['job_id'] ?? 0);

if ($offerId <= 0 || $jobId <= 0) {
	j(['ok' => false, 'error' => 'Conversation not found'], 400);
	exit;
}

$thread = fetch_participants($db, $offerId, $jobId);
if (!$thread) {
	j(['ok' => false, 'error' => 'Conversation not found'], 404);
	exit;
}

$isOwner = $viewerId === (int)($thread['owner_id'] ?? 0);
$isOfferer = $viewerId === (int)($thread['offerer_id'] ?? 0);
if (!$isOwner && !$isOfferer) {
	j(['ok' => false, 'error' => 'Forbidden'], 403);
	exit;
}

$counterpartyId = $isOwner ? (int)$thread['offerer_id'] : (int)$thread['owner_id'];
if ($counterpartyId === $viewerId) {
	j(['ok' => false, 'error' => 'Self conversation is not allowed'], 403);
	exit;
}

$offerStatus = strtolower((string)($thread['offer_status'] ?? 'pending'));

if ($action === 'decision') {
	if (!$isOwner) {
		j(['ok' => false, 'error' => 'Only the post owner can decide on an offer'], 403);
		exit;
	}

	$decision = strtolower(trim((string)($payload['decision'] ?? $_REQUEST['decision'] ?? '')));
	if ($decision !== 'accepted' && $decision !== 'rejected') {
		j(['ok' => false, 'error' => 'Invalid decision'], 400);
		exit;
	}

	if ($set = @mysqli_prepare($db, "UPDATE offers SET status = ? WHERE id = ? AND job_id = ?")) {
		mysqli_stmt_bind_param($set, 'sii', $decision, $offerId, $jobId);
		@mysqli_stmt_execute($set);
		@mysqli_stmt_close($set);

		// Notify offer sender that the owner has made a decision.
		if ($notify = @mysqli_prepare($db, "INSERT INTO notifications (user_id, actor_id, job_id, offer_id, created_at) VALUES (?, ?, ?, ?, NOW())")) {
			$recipientId = (int)($thread['offerer_id'] ?? 0);
			$actorId = (int)$viewerId;
			mysqli_stmt_bind_param($notify, 'iiii', $recipientId, $actorId, $jobId, $offerId);
			@mysqli_stmt_execute($notify);
			@mysqli_stmt_close($notify);
		}

		j([
			'ok' => true,
			'offer_status' => $decision,
		]);
		exit;
	}

	j(['ok' => false, 'error' => 'Could not update offer status'], 500);
	exit;
}

if ($action === 'thread') {
	$sinceId = (int)($payload['since_id'] ?? $_REQUEST['since_id'] ?? 0);
	$messages = [];
	$sql = "SELECT m.id, m.job_id, m.offer_id, m.sender_id, m.recipient_id, m.body, m.created_at, m.read_at,
			   COALESCE(u.username,'') AS sender_username, COALESCE(u.first_name,'') AS sender_first,
			   COALESCE(u.last_name,'') AS sender_last, COALESCE(u.avatar,'') AS sender_avatar
		FROM chat_messages m
		LEFT JOIN users u ON u.id = m.sender_id
		WHERE m.offer_id = ? AND m.job_id = ? AND (m.sender_id = ? OR m.recipient_id = ?)";
	if ($sinceId > 0) {
		$sql .= " AND m.id > ?";
	}
	$sql .= " ORDER BY m.id ASC";
	if ($stmt = @mysqli_prepare($db, $sql)) {
		if ($sinceId > 0) {
			mysqli_stmt_bind_param($stmt, 'iiiii', $offerId, $jobId, $viewerId, $viewerId, $sinceId);
		} else {
			mysqli_stmt_bind_param($stmt, 'iiii', $offerId, $jobId, $viewerId, $viewerId);
		}
		if (@mysqli_stmt_execute($stmt)) {
			$result = @mysqli_stmt_get_result($stmt);
			while ($row = @mysqli_fetch_assoc($result)) {
				$row['sender_name'] = display_name([
					'username' => $row['sender_username'] ?? '',
					'first_name' => $row['sender_first'] ?? '',
					'last_name' => $row['sender_last'] ?? '',
				]);
				$row['sender_avatar'] = avatar_url($row['sender_avatar'] ?? '');
				$row['is_self'] = ((int)$row['sender_id'] === $viewerId);
				$messages[] = $row;
			}
		}
		@mysqli_stmt_close($stmt);
	}

	if ($messages && !$sinceId) {
		if ($mark = @mysqli_prepare($db, "UPDATE chat_messages SET read_at = NOW() WHERE offer_id = ? AND job_id = ? AND recipient_id = ? AND read_at IS NULL")) {
			mysqli_stmt_bind_param($mark, 'iii', $offerId, $jobId, $viewerId);
			@mysqli_stmt_execute($mark);
			@mysqli_stmt_close($mark);
		}
	}

	j([
		'ok' => true,
		'thread' => [
			'offer_id' => (int)$thread['offer_id'],
			'job_id' => (int)$thread['job_id'],
			'title' => (string)($thread['title'] ?? ''),
			'job_title' => (string)($thread['title'] ?? ''),
			'location' => (string)($thread['location'] ?? 'Online'),
			'date_needed' => (string)($thread['date_needed'] ?? 'Anytime'),
			'offer_amount' => isset($thread['amount']) ? (float)$thread['amount'] : null,
			'offer_status' => (string)($thread['offer_status'] ?? 'pending'),
			'viewer_role' => $isOwner ? 'citizen' : 'kasangga',
			'counterparty_id' => $counterpartyId,
			'counterparty_name' => $isOwner
				? display_name([
					'username' => $thread['offerer_username'] ?? '',
					'first_name' => $thread['offerer_first'] ?? '',
					'last_name' => $thread['offerer_last'] ?? '',
				])
				: display_name([
					'username' => $thread['owner_username'] ?? '',
					'first_name' => $thread['owner_first'] ?? '',
					'last_name' => $thread['owner_last'] ?? '',
				]),
			'counterparty_avatar' => $isOwner ? avatar_url($thread['offerer_avatar'] ?? '') : avatar_url($thread['owner_avatar'] ?? ''),
			'messages' => $messages,
		],
	]);
	exit;
}

if ($action === 'send') {
	if ($offerStatus !== 'accepted') {
		j(['ok' => false, 'error' => 'Chat is locked until the offer is accepted'], 403);
		exit;
	}

	$body = clean_text((string)($payload['body'] ?? ''));
	if ($body === '') {
		j(['ok' => false, 'error' => 'Message is required'], 400);
		exit;
	}

	if ($insert = @mysqli_prepare($db, "INSERT INTO chat_messages (job_id, offer_id, sender_id, recipient_id, body, created_at) VALUES (?, ?, ?, ?, ?, NOW())")) {
		mysqli_stmt_bind_param($insert, 'iiiis', $jobId, $offerId, $viewerId, $counterpartyId, $body);
		if (@mysqli_stmt_execute($insert)) {
			$messageId = (int)@mysqli_insert_id($db);
			@mysqli_stmt_close($insert);
			j([
				'ok' => true,
				'message' => [
					'id' => $messageId,
					'job_id' => $jobId,
					'offer_id' => $offerId,
					'sender_id' => $viewerId,
					'recipient_id' => $counterpartyId,
					'body' => $body,
					'created_at' => date('Y-m-d H:i:s'),
				],
			]);
			exit;
		}
		@mysqli_stmt_close($insert);
	}

	j(['ok' => false, 'error' => 'Message could not be sent'], 500);
	exit;
}

j(['ok' => false, 'error' => 'Unsupported action'], 400);
