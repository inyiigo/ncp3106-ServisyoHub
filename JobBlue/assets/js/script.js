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
})();


