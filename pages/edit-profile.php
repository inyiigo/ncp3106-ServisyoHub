<?php
session_start();
// Minimal robust DB connection: try config then fallback (exception-safe)
$configPath = __DIR__ . '/../includes/config.php';
$mysqli = null;
$dbAvailable = false;
$errors = $errors ?? []; // ensure $errors exists

if (file_exists($configPath)) { require_once $configPath; }

// build attempts list
$attempts = [];
if (isset($db_host, $db_user, $db_pass, $db_name)) {
    $attempts[] = [$db_host, $db_user, $db_pass, $db_name];
}
$attempts[] = ['localhost', 'root', '', 'servisyohub'];

$lastConnError = '';
foreach ($attempts as $creds) {
    list($h, $u, $p, $n) = $creds;

    // disable mysqli exceptions for the attempt and catch any thrown exceptions
    if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_OFF);
    try {
        // suppress PHP warnings from mysqli::__construct when the target refuses connection
        $tmp = @new mysqli($h, $u, $p, $n);
        if ($tmp instanceof mysqli && !$tmp->connect_error) {
            $mysqli = $tmp;
            $dbAvailable = true;
        } else {
            $lastConnError = $tmp->connect_error ?? 'Connection failed';
            // ensure we don't leave an invalid object
            if ($tmp instanceof mysqli) { @$tmp->close(); }
        }
    } catch (mysqli_sql_exception $ex) {
        $lastConnError = $ex->getMessage();
    } catch (Throwable $ex) {
        $lastConnError = $ex->getMessage();
    } finally {
        // restore usual reporting to avoid surprising behavior in other code
        if (function_exists('mysqli_report')) mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);
    }

    if ($dbAvailable) break;
}

if (!$dbAvailable) {
    $mysqli = null;
    $errors[] = 'Database not available. ' . ($lastConnError ?? '');
}

// helper
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$not_logged_in = empty($_SESSION['user_id']);
$user_id = $not_logged_in ? 0 : intval($_SESSION['user_id']);
$errors = [];
$success = '';
// default user values
$user = ['username'=>'','first_name'=>'','last_name'=>'','email'=>'','phone'=>'','address'=>'','avatar'=>''];

// load user if possible
if (!$not_logged_in && $dbAvailable) {
	$stmt = $mysqli->prepare("SELECT username, first_name, last_name, email, phone, address, COALESCE(avatar,'') FROM users WHERE id = ?");
	if ($stmt) {
		$stmt->bind_param('i',$user_id);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows) {
			$stmt->bind_result($u_username,$u_first,$u_last,$u_email,$u_phone,$u_address,$u_avatar);
			$stmt->fetch();
			$user = [
				'username'=>$u_username,
				'first_name'=>$u_first,
				'last_name'=>$u_last,
				'email'=>$u_email,
				'phone'=>$u_phone,
				'address'=>$u_address,
				'avatar'=>$u_avatar
			];
		} else {
			// no such user -> treat as not logged in for editing
			$not_logged_in = true;
		}
		$stmt->close();
	} else {
		$errors[] = 'Database error: '.$mysqli->error;
		$not_logged_in = true;
	}
}

// handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_logged_in && $dbAvailable) {
	// collect
	$username   = trim($_POST['username'] ?? $user['username']);
	$first_name = trim($_POST['first_name'] ?? $user['first_name']);
	$last_name  = trim($_POST['last_name'] ?? $user['last_name']);
	$email      = trim($_POST['email'] ?? $user['email']);
	$phone      = trim($_POST['phone'] ?? $user['phone']);
	$address    = trim($_POST['address'] ?? $user['address']);
	$password   = $_POST['password'] ?? '';

	// validate
	if ($username === '') $errors[] = 'Username is required.';
	if ($first_name === '') $errors[] = 'First name is required.';
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

	// uniqueness
	if (empty($errors)) {
		$chk = $mysqli->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ?");
		if ($chk) {
			$chk->bind_param('ssi',$username,$email,$user_id);
			$chk->execute();
			$chk->store_result();
			if ($chk->num_rows>0) $errors[] = 'Username or email already in use.';
			$chk->close();
		} else $errors[] = 'Database error: '.$mysqli->error;
	}

	// handle avatar upload
	$newAvatarPath = '';
	if (empty($errors) && !empty($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
		$afile = $_FILES['avatar'];
		if ($afile['error'] === UPLOAD_ERR_OK) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $afile['tmp_name']);
			finfo_close($finfo);
			if (!preg_match('#^image/(png|jpe?g|webp)$#i',$mime)) {
				$errors[] = 'Avatar must be a PNG, JPG or WEBP image.';
			} elseif ($afile['size'] > 3*1024*1024) {
				$errors[] = 'Avatar must be smaller than 3MB.';
			} else {
				$ext = strtolower(pathinfo($afile['name'], PATHINFO_EXTENSION));
				if (!in_array($ext, ['png','jpg','jpeg','webp'])) {
					// prefer extension based on mime if original ext is weird
					$ext = ($mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg'));
				}
				$uploadDir = __DIR__ . '/../assets/uploads/avatars';
				if (!is_dir($uploadDir)) @mkdir($uploadDir,0755,true);
				$filename = 'avatar_u'.$user_id.'_'.time().'.'.$ext;
				$dest = $uploadDir . '/' . $filename;
				if (move_uploaded_file($afile['tmp_name'],$dest)) {
					$newAvatarPath = 'assets/uploads/avatars/'.$filename;
					// optionally remove old avatar file (best-effort)
					if (!empty($user['avatar'])) {
						$old = __DIR__ . '/../' . ltrim($user['avatar'],'/');
						if (is_file($old) && strpos(realpath($old), realpath(__DIR__.'/../assets/uploads/avatars'))===0) {
							@unlink($old);
						}
					}
				} else {
					$errors[] = 'Failed to save uploaded avatar.';
				}
			}
		} else {
			$errors[] = 'Avatar upload error.';
		}
	}

	// perform update
	if (empty($errors)) {
		if ($password !== '') {
			$pwHash = password_hash($password, PASSWORD_DEFAULT);
			// include avatar if uploaded
			if ($newAvatarPath !== '') {
				$upd = $mysqli->prepare("UPDATE users SET username=?, first_name=?, last_name=?, email=?, phone=?, address=?, password=?, avatar=? WHERE id=?");
				$upd->bind_param('ssssssssi',$username,$first_name,$last_name,$email,$phone,$address,$pwHash,$newAvatarPath,$user_id);
			} else {
				$upd = $mysqli->prepare("UPDATE users SET username=?, first_name=?, last_name=?, email=?, phone=?, address=?, password=? WHERE id=?");
				$upd->bind_param('sssssssi',$username,$first_name,$last_name,$email,$phone,$address,$pwHash,$user_id);
			}
		} else {
			if ($newAvatarPath !== '') {
				$upd = $mysqli->prepare("UPDATE users SET username=?, first_name=?, last_name=?, email=?, phone=?, address=?, avatar=? WHERE id=?");
				$upd->bind_param('sssssssi',$username,$first_name,$last_name,$email,$phone,$address,$newAvatarPath,$user_id);
			} else {
				$upd = $mysqli->prepare("UPDATE users SET username=?, first_name=?, last_name=?, email=?, phone=?, address=? WHERE id=?");
				$upd->bind_param('ssssssi',$username,$first_name,$last_name,$email,$phone,$address,$user_id);
			}
		}

		if ($upd) {
			if ($upd->execute()) {
				$success = 'Profile updated successfully.';
				// refresh $user values for UI
				$user['username']=$username;
				$user['first_name']=$first_name;
				$user['last_name']=$last_name;
				$user['email']=$email;
				$user['phone']=$phone;
				$user['address']=$address;
				if ($newAvatarPath !== '') $user['avatar']=$newAvatarPath;
			} else {
				$errors[] = 'Database update failed: '.$mysqli->error;
			}
			$upd->close();
		} else {
			$errors[] = 'Database error: '.$mysqli->error;
		}
	}
} // end POST

// prepare disabled attribute for form controls when not logged in or DB unavailable
$disabledAttr = '';

// small helper to get avatar URL or placeholder
function avatar_url($path){
	if (!$path) return '../assets/images/avatar-placeholder.png';
	return '../'.ltrim($path,'/');
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Edit Profile • Servisyo Hub</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="reg-body theme-profile-bg">
	<main class="form-card glass-card animate-fade">
		<h2>Edit Profile</h2>
		<p class="hint">Update your personal details.</p>

		<?php if ($success): ?>
			<div class="notice success"><?php echo e($success); ?></div>
		<?php endif; ?>
		<?php if (!empty($errors)): ?>
			<div class="notice error"><ul style="margin:0 0 0 18px; padding:0;"><?php foreach($errors as $err) echo '<li>'.e($err).'</li>'; ?></ul></div>
		<?php endif; ?>

		<form id="editForm" method="post" enctype="multipart/form-data" novalidate>
			<div class="profile-top">
				<div class="avatar-preview">
					<img id="avatarImg" src="<?php echo e(avatar_url($user['avatar'])); ?>" alt="avatar">
				</div>
				<div class="flex-1">
					<div class="row">
						<strong class="name-strong"><?php echo e($user['first_name'].' '.$user['last_name']); ?></strong>
						<small class="small-muted"><?php echo e($user['email']); ?></small>
					</div>
					<label class="hint margin-top-8">Change profile picture
						<input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp" <?php echo $disabledAttr; ?>>
					</label>
					<small class="hint">PNG, JPG, WEBP &lt; 3MB.</small>
				</div>
			</div>

			<div class="grid grid-12">
				<div class="field col-6">
					<label for="username">Username</label>
					<input id="username" name="username" type="text" value="<?php echo e($user['username']); ?>" required <?php echo $disabledAttr; ?>>
				</div>
				<div class="field col-6">
					<label for="email">Email</label>
					<input id="email" name="email" type="email" value="<?php echo e($user['email']); ?>" required <?php echo $disabledAttr; ?>>
				</div>
				<div class="field col-6">
					<label for="first_name">First name</label>
					<input id="first_name" name="first_name" type="text" value="<?php echo e($user['first_name']); ?>" required <?php echo $disabledAttr; ?>>
				</div>
				<div class="field col-6">
					<label for="last_name">Last name</label>
					<input id="last_name" name="last_name" type="text" value="<?php echo e($user['last_name']); ?>" <?php echo $disabledAttr; ?>>
				</div>
				<div class="field col-6">
					<label for="phone">Phone</label>
					<input id="phone" name="phone" type="text" value="<?php echo e($user['phone']); ?>" <?php echo $disabledAttr; ?>>
				</div>
				<div class="field col-6">
					<label for="password">New password (leave blank to keep current)</label>
					<input id="password" name="password" type="password" autocomplete="new-password" <?php echo $disabledAttr; ?>>
				</div>
				<div class="field col-12">
					<label for="address">Address</label>
					<textarea id="address" name="address" <?php echo $disabledAttr; ?>><?php echo e($user['address']); ?></textarea>
				</div>
			</div>

			<div class="actions">
				<button type="submit" class="save" <?php echo $disabledAttr; ?>>Save Changes</button>
				<button type="button" id="cancelBtn" class="discard">Cancel</button>
			</div>
		</form>
	</main>
	<script>
// capture initial values for Cancel behavior
var form = document.getElementById('editForm');
var cancelBtn = document.getElementById('cancelBtn');
var initial = {};
Array.prototype.forEach.call(form.elements, function(el){
	if (!el.name) return;
	if (el.type === 'file') return; // file input handled separately
	initial[el.name] = el.type === 'checkbox' || el.type === 'radio' ? el.checked : el.value;
});
// store initial avatar src
var avatarInput = document.getElementById('avatar');
var avatarImg = document.getElementById('avatarImg');
var initialAvatarSrc = avatarImg ? avatarImg.src : '';

// avatar preview on select
if (avatarInput) {
	avatarInput.addEventListener('change', function(){
		var f = this.files && this.files[0];
		if (f) avatarImg.src = URL.createObjectURL(f);
	});
}

// helper: determine if form has unsaved changes
function isDirty(){
	// check inputs (excluding files)
	for (var name in initial){
		var el = form.elements[name];
		if (!el) continue;
		if (el.type === 'checkbox' || el.type === 'radio') {
			if (el.checked !== initial[name]) return true;
		} else {
			if ((el.value ?? '') !== (initial[name] ?? '')) return true;
		}
	}
	// check file input (new avatar selected)
	if (avatarInput && avatarInput.files && avatarInput.files.length > 0) return true;
	// check avatar preview changed (in case image preview was modified)
	if (avatarImg && avatarImg.src !== initialAvatarSrc) return true;
	return false;
}

// Cancel: navigate back to clients-profile.php. Prompt only if there are unsaved changes.
if (cancelBtn) {
	cancelBtn.addEventListener('click', function(){
		if (!isDirty()) {
			window.location.href = 'profile.php';
			return;
		}
		if (confirm('Discard changes? Any unsaved edits will be lost. Proceed back to profile?')) {
			window.location.href = 'profile.php';
		}
	});
}
	</script>
</body>
</html>

