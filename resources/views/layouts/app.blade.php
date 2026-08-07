<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])


        <style>
            /* ── Broadcast Pop-up Styles ─────────────────────────────────── */
            #broadcast-overlay {
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.65);
                backdrop-filter: blur(4px);
                z-index: 9998;
                display: flex; align-items: center; justify-content: center;
                padding: 1rem;
                animation: fadeIn .25s ease;
            }
            #broadcast-modal {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                border: 1px solid rgba(251,146,60,0.35);
                border-radius: 1.25rem;
                max-width: 480px; width: 100%;
                box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(251,146,60,0.1);
                animation: slideUp .3s cubic-bezier(.16,1,.3,1);
                overflow: hidden;
                position: relative;
            }
            #broadcast-modal .modal-header {
                background: linear-gradient(90deg, rgba(251,146,60,0.15) 0%, rgba(234,88,12,0.08) 100%);
                border-bottom: 1px solid rgba(251,146,60,0.2);
                padding: 1rem 1.25rem;
                display: flex; align-items: center; justify-content: space-between;
            }
            #broadcast-modal .modal-body { padding: 1.25rem; }
            #broadcast-modal .modal-image {
                width: 100%; max-height: 220px;
                object-fit: cover;
                border-radius: .75rem;
                margin-bottom: 1rem;
                border: 1px solid rgba(255,255,255,0.08);
                cursor: zoom-in;
                transition: transform .2s, box-shadow .2s;
            }
            #broadcast-modal .modal-image:hover {
                transform: scale(1.01);
                box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            }

            /* ── Image Lightbox ─────────────────────────────────────────── */
            #img-lightbox {
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.92);
                backdrop-filter: blur(8px);
                z-index: 99999;
                display: flex; align-items: center; justify-content: center;
                padding: 1rem;
                cursor: zoom-out;
                animation: fadeIn .2s ease;
            }
            #img-lightbox img {
                max-width: 92vw;
                max-height: 88vh;
                border-radius: 1rem;
                box-shadow: 0 30px 80px rgba(0,0,0,0.8);
                object-fit: contain;
                animation: slideUp .25s cubic-bezier(.16,1,.3,1);
            }
            #img-lightbox-close {
                position: fixed; top: 1.25rem; right: 1.25rem;
                width: 2.5rem; height: 2.5rem;
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.2);
                border-radius: 50%;
                color: white; font-size: 1.1rem;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                transition: background .2s;
            }
            #img-lightbox-close:hover { background: rgba(239,68,68,0.4); }
            #broadcast-modal .msg-text {
                color: #cbd5e1;
                font-size: .925rem;
                line-height: 1.65;
                white-space: pre-wrap;
            }
            #broadcast-close-btn {
                width: 2rem; height: 2rem;
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.12);
                border-radius: 50%;
                color: #94a3b8;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                font-size: 1rem; line-height: 1;
                transition: background .2s, color .2s;
                flex-shrink: 0;
            }
            #broadcast-close-btn:hover { background: rgba(239,68,68,0.2); color: #f87171; }

            /* Floating Tab */
            #broadcast-tab {
                position: fixed;
                right: 0; top: 50%;
                transform: translateY(-50%) translateX(calc(100% - 52px));
                background: linear-gradient(135deg, #c2410c, #ea580c, #f97316);
                color: white;
                border-radius: 1rem 0 0 1rem;
                padding: .85rem .65rem .85rem 1.1rem;
                cursor: pointer;
                z-index: 9997;
                box-shadow: -6px 0 28px rgba(249,115,22,0.55),
                            inset 1px 0 0 rgba(255,255,255,0.15);
                display: flex; align-items: center; gap: .6rem;
                font-size: .85rem; font-weight: 800;
                writing-mode: initial;
                transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .2s;
                border: 1.5px solid rgba(255,255,255,0.2);
                border-right: none;
                position: fixed;  /* keep fixed; use isolation for badge stacking */
                isolation: isolate;
            }
            #broadcast-tab:hover {
                transform: translateY(-50%) translateX(0);
                box-shadow: -10px 0 40px rgba(249,115,22,0.65);
            }
            #broadcast-tab .tab-icon {
                font-size: 1.35rem;
                filter: drop-shadow(0 0 6px rgba(255,200,100,0.6));
            }
            #broadcast-tab .tab-text {
                writing-mode: vertical-rl;
                text-orientation: mixed;
                transform: rotate(180deg);
                letter-spacing: .08em;
                font-size: .78rem;
                text-shadow: 0 1px 4px rgba(0,0,0,0.4);
            }
            #broadcast-tab .tab-dot {
                width: 9px; height: 9px;
                background: #fde68a;
                border-radius: 50%;
                box-shadow: 0 0 6px #fde68a;
                animation: pulse-dot 1.4s infinite;
                flex-shrink: 0;
            }

            @keyframes fadeIn   { from { opacity: 0 } to { opacity: 1 } }
            @keyframes slideUp  { from { transform: translateY(30px); opacity:0 } to { transform: translateY(0); opacity:1 } }
            @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.7)} }
        </style>
    </head>
    <body class="font-sans antialiased overflow-x-hidden">
        <div class="min-h-screen bg-gray-100 pb-24 sm:pb-0">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        {{-- ── Broadcast Inbox Pop-up & Floating Tab (for authenticated non-admin users) ── --}}
        @auth
            @if(auth()->user()->role !== 'admin')
                @php
                    $activeBroadcasts = \App\Models\AdminBroadcast::active()->latest()->get();
                    $broadcastsData = $activeBroadcasts->map(fn($b) => [
                        'id'         => $b->id,
                        'title'      => $b->title,
                        'message'    => $b->message,
                        'image_url'  => $b->image_path ? asset('storage/' . $b->image_path) : null,
                        'created_at' => $b->created_at->format('d M Y'),
                        'expires_at' => $b->expires_at ? $b->expires_at->format('d M Y, g:i A') : null,
                    ])->values()->toArray();
                @endphp

                @if($activeBroadcasts->isNotEmpty())

                    {{-- Pass all broadcast data to JS as JSON --}}
                    <script>
                        var BROADCASTS = {!! json_encode($broadcastsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!};
                    </script>

                    {{-- Floating Tab --}}
                    <div id="broadcast-tab" onclick="openBroadcastInbox()" role="button" aria-label="Open announcements"
                         style="display:none;">
                        <span class="tab-dot"></span>
                        <span class="tab-icon">📢</span>
                        <span id="broadcast-tab-badge"
                              style="position:absolute;top:-6px;left:-6px;background:#ef4444;color:#fff;
                                     font-size:.65rem;font-weight:800;border-radius:999px;min-width:18px;height:18px;
                                     display:none;align-items:center;justify-content:center;padding:0 4px;
                                     box-shadow:0 0 0 2px #1e293b;"></span>
                        <span class="tab-text">Messages</span>
                    </div>

                    {{-- Broadcast Inbox Overlay --}}
                    <div id="broadcast-overlay" role="dialog" aria-modal="true" aria-labelledby="bc-title" style="display:none;">
                        <div id="broadcast-modal" style="max-width:520px;">

                            {{-- Header --}}
                            <div class="modal-header">
                                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                                    <span style="font-size:1.2rem;flex-shrink:0;">📢</span>
                                    <div style="min-width:0;">
                                        <div id="bc-title" style="color:#fb923c;font-weight:700;font-size:.92rem;
                                                                   letter-spacing:.02em;white-space:nowrap;
                                                                   overflow:hidden;text-overflow:ellipsis;"></div>
                                        <div id="bc-counter" style="color:#64748b;font-size:.72rem;margin-top:.1rem;"></div>
                                    </div>
                                </div>
                                <button id="broadcast-close-btn" onclick="closeBroadcastInbox()" aria-label="Close">✕</button>
                            </div>

                            {{-- Slides container --}}
                            <div class="modal-body" style="padding-bottom:.75rem;">
                                <div id="bc-image-wrap" style="margin-bottom:1rem;display:none;">
                                    <img id="bc-image" src="" alt="" class="modal-image"
                                         onclick="openImgLightbox(this.src, this.alt)"
                                         title="Click to view full image">
                                </div>

                                {{-- Lightbox --}}
                                <div id="img-lightbox" style="display:none;" onclick="closeImgLightbox()">
                                    <button id="img-lightbox-close" onclick="closeImgLightbox()" aria-label="Close image">✕</button>
                                    <img id="img-lightbox-img" src="" alt="">
                                </div>

                                <p id="bc-message" class="msg-text" style="max-height:180px;overflow-y:auto;"></p>

                                <div style="margin-top:1rem;padding-top:.75rem;border-top:1px solid rgba(255,255,255,0.07);
                                            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                                    <div style="display:flex;flex-direction:column;gap:.2rem;">
                                        <span id="bc-date" style="color:#475569;font-size:.74rem;"></span>
                                        <span id="bc-expires" style="color:#f59e0b;font-size:.71rem;display:none;"></span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:.5rem;">
                                        <button id="bc-mark-btn" onclick="markCurrentRead()"
                                                style="background:rgba(100,116,139,0.15);border:1px solid rgba(100,116,139,0.4);
                                                       color:#94a3b8;font-size:.78rem;font-weight:600;padding:.35rem .85rem;
                                                       border-radius:.5rem;cursor:pointer;transition:all .2s;"
                                                onmouseover="this.style.background='rgba(100,116,139,0.25)'"
                                                onmouseout="this.style.background='rgba(100,116,139,0.15)'">
                                            Mark read ✓
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Navigation Footer --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;
                                        padding:.75rem 1.25rem;border-top:1px solid rgba(255,255,255,0.06);
                                        background:rgba(15,23,42,0.5);">
                                <button onclick="prevBroadcast()"
                                        style="display:flex;align-items:center;gap:.4rem;background:rgba(255,255,255,0.06);
                                               border:1px solid rgba(255,255,255,0.1);color:#94a3b8;font-size:.8rem;
                                               font-weight:600;padding:.4rem .9rem;border-radius:.5rem;cursor:pointer;transition:all .2s;"
                                        onmouseover="this.style.background='rgba(255,255,255,0.12)'"
                                        onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                                    ← Prev
                                </button>

                                {{-- Dot indicators --}}
                                <div id="bc-dots" style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;justify-content:center;max-width:200px;"></div>

                                <button onclick="nextBroadcast()"
                                        style="display:flex;align-items:center;gap:.4rem;background:rgba(251,146,60,0.15);
                                               border:1px solid rgba(251,146,60,0.4);color:#fb923c;font-size:.8rem;
                                               font-weight:600;padding:.4rem .9rem;border-radius:.5rem;cursor:pointer;transition:all .2s;"
                                        onmouseover="this.style.background='rgba(251,146,60,0.25)'"
                                        onmouseout="this.style.background='rgba(251,146,60,0.15)'">
                                    Next →
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Lightbox styles already in <head>, broadcast JS below --}}
                    <script>
                    (function () {
                        var broadcasts  = BROADCASTS;
                        var total       = broadcasts.length;
                        var currentIdx  = 0;

                        var overlay     = document.getElementById('broadcast-overlay');
                        var tab         = document.getElementById('broadcast-tab');
                        var badge       = document.getElementById('broadcast-tab-badge');
                        var dotsWrap    = document.getElementById('bc-dots');

                        /* ── localStorage helpers ── */
                        function isRead(id)    { return localStorage.getItem('bc_read_' + id) === '1'; }
                        function markRead(id)  { localStorage.setItem('bc_read_' + id, '1'); }

                        function unreadCount() {
                            return broadcasts.filter(function(b){ return !isRead(b.id); }).length;
                        }

                        /* ── Badge on tab ── */
                        function refreshBadge() {
                            var n = unreadCount();
                            badge.textContent = n > 9 ? '9+' : n;
                            badge.style.display = n > 0 ? 'flex' : 'none';
                        }

                        /* ── Build dot indicators ── */
                        function buildDots() {
                            dotsWrap.innerHTML = '';
                            broadcasts.forEach(function(b, i) {
                                var d = document.createElement('span');
                                d.dataset.i = i;
                                d.style.cssText = 'width:8px;height:8px;border-radius:50%;cursor:pointer;transition:all .2s;flex-shrink:0;';
                                d.onclick = function() { goTo(parseInt(this.dataset.i)); };
                                dotsWrap.appendChild(d);
                            });
                        }

                        function refreshDots() {
                            var dots = dotsWrap.querySelectorAll('span');
                            dots.forEach(function(d, i) {
                                var read = isRead(broadcasts[i].id);
                                if (i === currentIdx) {
                                    d.style.background = '#fb923c';
                                    d.style.width = '20px';
                                    d.style.borderRadius = '4px';
                                } else if (read) {
                                    d.style.background = '#334155';
                                    d.style.width = '8px';
                                    d.style.borderRadius = '50%';
                                } else {
                                    d.style.background = '#f59e0b';
                                    d.style.width = '8px';
                                    d.style.borderRadius = '50%';
                                }
                            });
                        }

                        /* ── Render current slide ── */
                        function renderSlide() {
                            var b = broadcasts[currentIdx];

                            document.getElementById('bc-title').textContent   = b.title;
                            document.getElementById('bc-counter').textContent = 'Message ' + (currentIdx + 1) + ' of ' + total;
                            document.getElementById('bc-message').textContent = b.message;
                            document.getElementById('bc-date').textContent    = '📅 ' + b.created_at;

                            var expiresEl = document.getElementById('bc-expires');
                            if (b.expires_at) {
                                expiresEl.textContent    = '⏰ Expires: ' + b.expires_at;
                                expiresEl.style.display  = 'block';
                            } else {
                                expiresEl.style.display  = 'none';
                            }

                            var imgWrap = document.getElementById('bc-image-wrap');
                            var imgEl   = document.getElementById('bc-image');
                            if (b.image_url) {
                                imgEl.src              = b.image_url;
                                imgEl.alt              = b.title;
                                imgWrap.style.display  = 'block';
                            } else {
                                imgWrap.style.display  = 'none';
                            }

                            var markBtn = document.getElementById('bc-mark-btn');
                            if (isRead(b.id)) {
                                markBtn.textContent = '✓ Read';
                                markBtn.style.color = '#4ade80';
                                markBtn.style.borderColor = 'rgba(74,222,128,0.4)';
                            } else {
                                markBtn.textContent = 'Mark read ✓';
                                markBtn.style.color = '#94a3b8';
                                markBtn.style.borderColor = 'rgba(100,116,139,0.4)';
                            }

                            refreshDots();
                            refreshBadge();
                        }

                        function goTo(idx) {
                            currentIdx = (idx + total) % total;
                            renderSlide();
                        }

                        window.prevBroadcast = function() { goTo(currentIdx - 1); };
                        window.nextBroadcast = function() { goTo(currentIdx + 1); };

                        window.markCurrentRead = function() {
                            markRead(broadcasts[currentIdx].id);
                            renderSlide();
                        };

                        /* ── Open / Close ── */
                        window.openBroadcastInbox = function() {
                            // Jump to first unread message if any
                            var firstUnread = broadcasts.findIndex(function(b){ return !isRead(b.id); });
                            currentIdx = firstUnread >= 0 ? firstUnread : 0;
                            renderSlide();
                            overlay.style.display = 'flex';
                            tab.style.display     = 'none';
                        };

                        window.closeBroadcastInbox = function() {
                            overlay.style.display = 'none';
                            tab.style.display     = 'flex';
                            refreshBadge();
                        };

                        // Close on overlay background click
                        overlay.addEventListener('click', function(e) {
                            if (e.target === overlay) closeBroadcastInbox();
                        });

                        // Escape key
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape') {
                                var lb = document.getElementById('img-lightbox');
                                if (lb && lb.style.display !== 'none') {
                                    closeImgLightbox();
                                } else if (overlay.style.display !== 'none') {
                                    closeBroadcastInbox();
                                }
                            }
                            if (e.key === 'ArrowLeft'  && overlay.style.display !== 'none') prevBroadcast();
                            if (e.key === 'ArrowRight' && overlay.style.display !== 'none') nextBroadcast();
                        });

                      
                        window.openImgLightbox = function(src, alt) {
                            var lb  = document.getElementById('img-lightbox');
                            var img = document.getElementById('img-lightbox-img');
                            img.src = src; img.alt = alt || '';
                            lb.style.display = 'flex';
                        };
                        window.closeImgLightbox = function() {
                            document.getElementById('img-lightbox').style.display = 'none';
                        };

                       
                        buildDots();
                        tab.style.display = 'flex';
                        refreshBadge();

                     
                        var SESSION_KEY = 'bc_inbox_shown';
                        if (!sessionStorage.getItem(SESSION_KEY)) {
                          
                            sessionStorage.setItem(SESSION_KEY, '1');
                            var firstUnread = broadcasts.findIndex(function(b){ return !isRead(b.id); });
                            currentIdx = firstUnread >= 0 ? firstUnread : 0;
                            renderSlide();
                            overlay.style.display = 'flex';
                            tab.style.display     = 'none';
                        }
                    })();
                    </script>
                @endif
            @endif
        @endauth

        {{-- ── Cross-Device Scan Processing Overlays ─────────────────────────── --}}
        {{-- These overlays appear on ANY page when a scan is triggered from      --}}
        {{-- another device (e.g. phone), detected via 2-second polling.          --}}
        @auth
        @if(!auth()->user()->isAdmin())

        {{-- Receipt Scan Overlay --}}
        <div id="global-receipt-overlay" class="fixed inset-0 z-[9990] flex flex-col items-center justify-center hidden" style="background:rgba(15,23,42,0.93);backdrop-filter:blur(6px);">
            <div class="bg-slate-800 border border-emerald-500/30 rounded-2xl p-10 flex flex-col items-center shadow-2xl max-w-sm w-full mx-4">
                <div class="relative mb-6">
                    <svg class="animate-spin h-16 w-16 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="h-7 w-7 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Analysing Receipt…</h3>
                <p class="text-slate-400 text-sm text-center leading-relaxed">Our AI is scanning your receipt and extracting items. This may take up to 30 seconds — please don't close this page.</p>
                <div class="flex space-x-2 mt-6">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:0ms;"></span>
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:150ms;"></span>
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:300ms;"></span>
                </div>
            </div>
        </div>

        {{-- Ripeness Scan Overlay --}}
        <div id="global-ripeness-overlay" class="fixed inset-0 z-[9990] flex flex-col items-center justify-center hidden" style="background:rgba(15,23,42,0.93);backdrop-filter:blur(6px);">
            <div class="bg-slate-800 border border-emerald-500/30 rounded-2xl p-10 flex flex-col items-center shadow-2xl max-w-sm w-full mx-4">
                <div class="relative mb-6">
                    <svg class="animate-spin h-16 w-16 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="h-7 w-7 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Analysing Ripeness…</h3>
                <p class="text-slate-400 text-sm text-center leading-relaxed">Our AI is examining your fruit or vegetable photo. This may take up to 30 seconds — please don't close this page.</p>
                <div class="flex space-x-2 mt-6">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:0ms;"></span>
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:150ms;"></span>
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-bounce" style="animation-delay:300ms;"></span>
                </div>
            </div>
        </div>

        {{-- Recipe Generation Overlay --}}
        <div id="global-recipe-overlay" class="fixed inset-0 z-[9990] flex flex-col items-center justify-center hidden" style="background:rgba(15,23,42,0.93);backdrop-filter:blur(6px);">
            <div class="bg-slate-800 border border-indigo-500/30 rounded-2xl p-10 flex flex-col items-center shadow-2xl max-w-sm w-full mx-4">
                <div class="relative mb-6">
                    <svg class="animate-spin h-16 w-16 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl">👨‍🍳</span>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Chef AI is Cooking…</h3>
                <p class="text-slate-400 text-sm text-center leading-relaxed">Reviewing your soon-to-expire items and crafting the perfect recipe. This may take a moment!</p>
                <div class="flex space-x-2 mt-6">
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms;"></span>
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms;"></span>
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms;"></span>
                </div>
            </div>
        </div>

        <script>
        // ── Global cross-device scan status polling ───────────────────────────
        // Runs on EVERY page. Polls /scan-status every 2s and shows the matching
        // global overlay so the laptop mirrors whatever the phone is doing.
        (function() {
            var overlays = {
                receipt:  document.getElementById('global-receipt-overlay'),
                ripeness: document.getElementById('global-ripeness-overlay'),
                recipe:   document.getElementById('global-recipe-overlay'),
            };
            var lastStatus = null;

            function showOverlay(type) {
                Object.keys(overlays).forEach(function(k) {
                    var el = overlays[k];
                    if (!el) return;
                    if (k === type) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
                document.body.style.overflow = type ? 'hidden' : '';
            }

            setInterval(async function() {
                try {
                    var res  = await fetch('/scan-status', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'ngrok-skip-browser-warning': 'true'
                        }
                    });
                    var data = await res.json();
                    var status = data.scanning || null;

                    if (status === lastStatus) return; // nothing changed
                    lastStatus = status;
                    showOverlay(status);
                } catch(e) { /* ignore network errors */ }
            }, 2000);
        })();
        </script>

        @endif
        @endauth
    </body>
</html>

