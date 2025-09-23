<?php
  $pageTitle = 'Job Blue — Home';
  include __DIR__ . '/includes/header.php';
  include __DIR__ . '/includes/navbar.php';
?>

<main class="home">
  <header class="home__top">
    <div class="home__location">
      <span class="home__loc-label">My Location</span>
      <button class="home__loc-value" aria-label="Change location">Sobriedad St.</button>
    </div>
    <button class="home__bell" aria-label="Notifications">🔔</button>
  </header>

  <section class="home__search">
    <div class="searchbar">
      <span class="searchbar__icon">🔍</span>
      <input placeholder="Search services here.." aria-label="Search services" />
    </div>
  </section>

  <section class="home__cats">
    <div class="section-head">
      <h2>Categories</h2>
      <a href="#" class="see-all">See All</a>
    </div>
    <div class="cats">
      <button class="cat"><span class="cat__icon">⚡</span><span class="cat__label">Electricity</span></button>
      <button class="cat"><span class="cat__icon">🔨</span><span class="cat__label">HandCraft</span></button>
      <button class="cat"><span class="cat__icon">💧</span><span class="cat__label">Plumber</span></button>
      <button class="cat"><span class="cat__icon">🧽</span><span class="cat__label">Cleaning</span></button>
    </div>
  </section>

  <section class="home__near">
    <div class="section-head">
      <h2>Nears on you</h2>
      <a href="#" class="see-all">View All</a>
    </div>
    <div class="cards">
      <article class="cardjob">
        <div class="cardjob__media"></div>
        <div class="cardjob__body">
          <h3>Ping Lacson</h3>
          <p class="role">Plumber</p>
          <div class="meta"><span>★ 4.9</span><span>3km</span></div>
        </div>
      </article>
      <article class="cardjob">
        <div class="cardjob__media"></div>
        <div class="cardjob__body">
          <h3>Keane Cregen</h3>
          <p class="role">Electrician</p>
          <div class="meta"><span>★ 4.7</span><span>2km</span></div>
        </div>
      </article>
    </div>
  </section>
</main>

<nav class="tabbar" aria-label="Primary">
  <a class="tabbar__item is-active" href="#">Home</a>
  <a class="tabbar__item" href="#">My Jobs</a>
  <a class="tabbar__item" href="#">Chat</a>
  <a class="tabbar__item" href="#">Account</a>
</nav>

<?php include __DIR__ . '/includes/footer.php'; ?>


