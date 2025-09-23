(function(){
  const loginTab = document.getElementById('login-tab');
  const signupTab = document.getElementById('signup-tab');
  const loginPanel = document.getElementById('login-panel');
  const signupPanel = document.getElementById('signup-panel');

  function switchTo(target){
    const isLogin = target === 'login';
    loginTab.classList.toggle('is-active', isLogin);
    signupTab.classList.toggle('is-active', !isLogin);
    loginTab.setAttribute('aria-selected', String(isLogin));
    signupTab.setAttribute('aria-selected', String(!isLogin));

    loginPanel.hidden = !isLogin; signupPanel.hidden = isLogin;
    loginPanel.classList.toggle('is-active', isLogin);
    signupPanel.classList.toggle('is-active', !isLogin);
  }

  loginTab?.addEventListener('click', ()=>switchTo('login'));
  signupTab?.addEventListener('click', ()=>switchTo('signup'));

  document.querySelectorAll('[data-switch-to]')
    .forEach(el=>el.addEventListener('click', (e)=>{
      e.preventDefault();
      const target = el.getAttribute('data-switch-to');
      if(target){ switchTo(target); }
    }));

  // Basic client-side validity hints
  function attachHints(form){
    form?.addEventListener('submit', (e)=>{
      let hasError = false;
      form.querySelectorAll('input, select').forEach((field)=>{
        const hint = form.querySelector(`.form__hint[data-for="${field.id}"]`);
        if(!field.checkValidity()){
          hasError = true;
          if(hint){ hint.textContent = field.validationMessage; hint.classList.add('is-error'); }
        }else{
          if(hint){ hint.textContent = ''; hint.classList.remove('is-error'); }
        }
      });
      if(hasError){ e.preventDefault(); }
    });
  }
  attachHints(loginPanel); attachHints(signupPanel);

  // Temporary: redirect to home after successful validation
  function attachRedirect(form){
    form?.addEventListener('submit', (e)=>{
      if(form.checkValidity()){
        e.preventDefault();
        window.location.href = 'home.php';
      }
    });
  }
  attachRedirect(loginPanel); attachRedirect(signupPanel);

  // Drawer toggle
  const hamburger = document.querySelector('.hamburger');
  const drawer = document.getElementById('drawer');
  const drawerClose = document.querySelector('.drawer__close');
  const overlay = document.querySelector('.drawer-overlay');
  function openDrawer(){
    if(!drawer) return;
    drawer.classList.add('is-open');
    overlay?.removeAttribute('hidden');
    hamburger?.setAttribute('aria-expanded','true');
    drawer?.setAttribute('aria-hidden','false');
    document.body.classList.add('no-scroll');
  }
  function closeDrawer(){
    if(!drawer) return;
    drawer.classList.remove('is-open');
    overlay?.setAttribute('hidden','');
    hamburger?.setAttribute('aria-expanded','false');
    drawer?.setAttribute('aria-hidden','true');
    document.body.classList.remove('no-scroll');
  }
  hamburger?.addEventListener('click', openDrawer);
  drawerClose?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);
  window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeDrawer(); });

  // Ensure correct state on resize (close drawer on desktop->mobile transitions)
  let lastIsMobile = window.innerWidth < 1024;
  window.addEventListener('resize', ()=>{
    const isMobile = window.innerWidth < 1024;
    if(!isMobile && drawer?.classList.contains('is-open')){
      // keep it open on desktop but allow scrolling
      document.body.classList.remove('no-scroll');
    }
    if(isMobile !== lastIsMobile && !isMobile){
      // transition to desktop: hide overlay
      overlay?.setAttribute('hidden','');
    }
    lastIsMobile = isMobile;
  });
})();


