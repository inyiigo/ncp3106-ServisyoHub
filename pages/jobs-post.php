<?php
session_start();
$display = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : 'there';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Post • Jobs • Servisyo Hub</title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body class="theme-profile-bg">
    <div class="dash-topbar center">
        <div class="dash-brand">
            <img src="../assets/images/bluefont.png" alt="Servisyo Hub" class="dash-brand-logo" />
        </div>
    </div>

    <div class="dash-shell">
        <main class="dash-content">
            <!-- Hero composer (web version of provided design) -->
            <section class="jp-hero">
                <p class="jp-greet">Hi, <?php echo htmlspecialchars($display); ?>.</p>
                <h1 class="jp-title">What do you need done today?</h1>
                <form id="jpForm" aria-label="Create a post">
                    <div class="jp-input-wrap">
                        <input class="jp-input" type="text" id="postContent" placeholder="Live streamer host needed tonight" aria-label="Post content" required />
                    </div>
                    <div class="jp-pills" id="jpPills">
                        <button class="jp-pill" type="button" data-fill="Buy and deliver item">Buy and deliver item</button>
                        <button class="jp-pill" type="button" data-fill="Booth Staff for pop-up">Booth Staff for pop-up</button>
                        <button class="jp-pill" type="button" data-fill="Create a tiktok / reel for my product">Create a tiktok / reel for my product</button>
                        <button class="jp-pill" type="button" data-fill="Help me with moving">Help me with moving</button>
                        <button class="jp-pill" type="button" data-fill="Part-timer for event">Part-timer for event</button>
                    </div>
                    <button class="jp-cta" type="submit">Start posting <span aria-hidden="true">→</span></button>
                </form>
            </section>

            <!-- Trending services -->
            <section class="jp-trending" aria-label="Trending services">
                <div class="jp-trending-header">
                    <div class="jp-trending-title">Trending services</div>
                    <a class="jp-trending-link" href="./home-jobs.php">Browse quests</a>
                </div>
                <div class="jp-list">
                    <a class="jp-item" href="#">
                        <div class="jp-item-left">
                            <div class="jp-item-meta">Part-time · F&amp;B</div>
                            <div class="jp-item-title">Part-timer needed for cafe ☕</div>
                        </div>
                        <svg class="jp-item-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                    <a class="jp-item" href="#">
                        <div class="jp-item-left">
                            <div class="jp-item-meta">Social media · Micro-influencing</div>
                            <div class="jp-item-title">Livestream Host / Assistant 🎤</div>
                        </div>
                        <svg class="jp-item-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                    <a class="jp-item" href="#">
                        <div class="jp-item-left">
                            <div class="jp-item-meta">Errands · Delivery</div>
                            <div class="jp-item-title">Deliver birthday present 🎁</div>
                        </div>
                        <svg class="jp-item-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                    <a class="jp-item" href="#">
                        <div class="jp-item-left">
                            <div class="jp-item-meta">Errands · Overseas errands</div>
                            <div class="jp-item-title">Buy shoes from Japan 🇯🇵</div>
                        </div>
                        <svg class="jp-item-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </section>

            <!-- Live feed -->
            <section class="jobs-results" aria-label="Live posts" id="livePostsSection">
                <div class="results-header">
                    <span class="results-dot" aria-hidden="true"></span>
                    <span>Latest Posts</span>
                </div>
                <div id="livePosts" class="jobs-list"></div>
            </section>
        </main>
    </div>

    <!-- Floating bottom navigation -->
    <nav class="dash-bottom-nav">
        <a href="./home-jobs.php" aria-label="Home">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
            <span>Home</span>
        </a>
        <a href="./my-jobs.php" aria-label="My Jobs">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h10M4 17h7"/></svg>
            <span>My Jobs</span>
        </a>
        <a href="./jobs-post.php" class="active" aria-label="Post">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v8m-4-4h8"/></svg>
            <span>Post</span>
        </a>
        <a href="./jobs-profile.php" aria-label="Profile">
            <svg class="dash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 3-9 6v2h18v-2c0-3-4-6-9-6Z"/></svg>
            <span>Profile</span>
        </a>
    </nav>

    <script>
    // Live posts + hero interactions
    (function(){
        const api = './jobs-posts-api.php';
        const form = document.getElementById('jpForm');
        const content = document.getElementById('postContent');
        const pills = document.getElementById('jpPills');
        const list = document.getElementById('livePosts');
        const liveSection = document.getElementById('livePostsSection');
        let latestId = 0;

        function esc(s){
            return (s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
        }
        function timeSince(ts){
            const d = new Date(ts.replace(' ','T'));
            const diff = Math.max(0, (Date.now()-d.getTime())/1000);
            if (diff < 60) return `${Math.floor(diff)}s ago`;
            if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
            return d.toLocaleString();
        }
        function card(p){
            return `
            <article class=\"job-card\" data-id=\"${p.id}\">\n\
                <h3 class=\"job-title\">${esc(p.content)}</h3>\n\
                <div class=\"job-meta\">\n\
                    <span class=\"job-meta-item\"><svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"7\" r=\"4\"/><path d=\"M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/></svg>Posted ${esc(timeSince(p.created_at))} by ${esc(p.author)}</span>\n\
                </div>\n\
            </article>`;
        }
        async function load(initial=false){
            try{
                const url = latestId>0 && !initial ? `${api}?since_id=${latestId}` : `${api}?limit=20`;
                const res = await fetch(url, {credentials:'same-origin'});
                const data = await res.json();
                if(!data.ok) return;
                const posts = data.posts || [];
                if(initial){
                    list.innerHTML = posts.map(card).join('');
                } else if(posts.length){
                    const frag = document.createElement('div');
                    frag.innerHTML = posts.map(card).join('');
                    while(frag.firstChild){ list.prepend(frag.lastChild); }
                }
                if(posts.length){ latestId = Math.max(latestId, ...posts.map(p=>p.id)); }
            }catch(e){ }
        }

        // Fill input from pill clicks
        pills?.addEventListener('click', (e)=>{
            const t = e.target.closest('button[data-fill]');
            if(!t) return;
            const txt = t.getAttribute('data-fill') || '';
            content.value = txt;
            content.focus();
        });

        form?.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const text = (content.value||'').trim();
            if(!text) { content.focus(); return; }
            const body = { content: text };
            try{
                const res = await fetch(api, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body), credentials:'same-origin'});
                const data = await res.json();
                if(data.ok && data.post){
                    list.insertAdjacentHTML('afterbegin', card(data.post));
                    latestId = Math.max(latestId, data.post.id);
                    content.value = '';
                    // scroll to live posts on successful post
                    liveSection?.scrollIntoView({behavior:'smooth', block:'start'});
                }
            }catch(err){ }
        });
        load(true);
        setInterval(()=>load(false), 5000);
    })();
    </script>
</body>
</html>
