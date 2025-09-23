<?php
  // Simple front controller for the login/signup page (no backend yet)
  $pageTitle = 'Job Blue — Login';
  include __DIR__ . '/includes/header.php';
  include __DIR__ . '/includes/navbar.php';
?>

<main class="auth-container">
  <section class="brand">
    <img src="assets/images/logo.png" alt="Job Blue logo" class="brand__logo" />
    <h1 class="brand__title">Job Blue</h1>
    <p class="brand__subtitle">Find skilled pros or your next job</p>
  </section>

  <section class="card">
    <div class="card__tabs" role="tablist" aria-label="Authentication tabs">
      <button id="login-tab" class="tab is-active" role="tab" aria-selected="true" aria-controls="login-panel">Login</button>
      <button id="signup-tab" class="tab" role="tab" aria-selected="false" aria-controls="signup-panel">Sign Up</button>
    </div>

    <form id="login-panel" class="form is-active" role="tabpanel" aria-labelledby="login-tab" novalidate>
      <div class="form__group">
        <label for="login-username">Username</label>
        <input id="login-username" name="username" type="text" autocomplete="username" required />
        <small class="form__hint" data-for="login-username"></small>
      </div>
      <div class="form__group">
        <label for="login-password">Password</label>
        <input id="login-password" name="password" type="password" autocomplete="current-password" required minlength="6" />
        <small class="form__hint" data-for="login-password"></small>
      </div>
      <div class="form__row">
        <button type="submit" class="btn btn--primary">Login</button>
      </div>
      <p class="alt-action">Don’t have an account? <a href="#" data-switch-to="signup">Sign up</a></p>
    </form>

    <form id="signup-panel" class="form" role="tabpanel" aria-labelledby="signup-tab" hidden novalidate>
      <div class="form__group">
        <label for="signup-username">Username</label>
        <input id="signup-username" name="username" type="text" autocomplete="username" required />
        <small class="form__hint" data-for="signup-username"></small>
      </div>
      <div class="form__group">
        <label for="signup-email">Email</label>
        <input id="signup-email" name="email" type="email" autocomplete="email" required />
        <small class="form__hint" data-for="signup-email"></small>
      </div>
      <div class="form__group">
        <label for="signup-password">Password</label>
        <input id="signup-password" name="password" type="password" autocomplete="new-password" required minlength="6" />
        <small class="form__hint" data-for="signup-password"></small>
      </div>
      <div class="form__group">
        <label for="signup-role">I am a</label>
        <select id="signup-role" name="role" required>
          <option value="">Select role</option>
          <option value="client">Client</option>
          <option value="pro">Blue-collar professional</option>
        </select>
        <small class="form__hint" data-for="signup-role"></small>
      </div>
      <div class="form__row">
        <button type="submit" class="btn btn--primary">Create Account</button>
      </div>
      <p class="alt-action">Already have an account? <a href="#" data-switch-to="login">Login</a></p>
    </form>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


