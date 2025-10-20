<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Register • Servisyo Hub</title>
	<link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="reg-body theme-profile-bg">
	<main class="form-card">
		<h2>Register to apply for a job</h2>
		<p class="hint">Fill in your details and upload required files for your application.</p>

		<form action="./home-jobs.php" method="POST" enctype="multipart/form-data">
			<div class="grid">
				<div class="field col-6">
					<label for="first_name">First Name</label>
					<input type="text" id="first_name" name="first_name" placeholder="Juan" required />
				</div>
				<div class="field col-6">
					<label for="last_name">Last Name</label>
					<input type="text" id="last_name" name="last_name" placeholder="Dela Cruz" required />
				</div>
				<div class="field col-6">
					<label for="phone">Phone Number</label>
					<input type="tel" id="phone" name="phone" placeholder="0917 123 4567" inputmode="tel" pattern="[0-9\s+()-]{7,}" required />
				</div>
				<div class="field col-6">
					<label for="email">Email Address</label>
					<input type="email" id="email" name="email" placeholder="you@example.com" required />
				</div>
				<div class="field col-6">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" placeholder="Enter a strong password" required />
				</div>
				<div class="field col-6">
					<label for="gender">Gender</label>
					<select id="gender" name="gender" required>
						<option value="" disabled selected>Select gender</option>
						<option>Male</option>
						<option>Female</option>
						<option>Prefer not to say</option>
					</select>
				</div>
				<div class="field col-6">
					<label for="profession">Profession</label>
					<input type="text" id="profession" name="profession" placeholder="e.g. Plumber, Electrician" required />
				</div>
				<div class="field col-12">
					<label for="address">Address</label>
					<textarea id="address" name="address" rows="3" placeholder="Street, Barangay, City, Province" required></textarea>
				</div>
				
				<div class="field col-12">
					<label for="application_files">Application Files</label>
					<input type="file" id="application_files" name="application_files[]" multiple required />
					<small class="hint">Upload supporting documents (e.g., certificates, IDs). You can select multiple files.</small>
				</div>
			</div>

			<div class="actions">
				<button type="submit" class="btn">Submit Application</button>
				<a href="./user-choice.php" class="btn secondary">Back</a>
			</div>
		</form>
	</main>
		</body>
		</html>

