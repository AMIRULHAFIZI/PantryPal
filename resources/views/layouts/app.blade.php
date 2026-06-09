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

        {{-- ── Broadcast Pop-up & Floating Tab (for authenticated non-admin users) ── --}}
        @auth
            @if(auth()->user()->role !== 'admin')
                @php
                    $activeBroadcast = \App\Models\AdminBroadcast::active()->latest()->first();
                @endphp

                @if($activeBroadcast)
                    {{-- Floating Tab (always present, hidden until pop-up is dismissed) --}}
                    <div id="broadcast-tab" style="display:none;" onclick="openBroadcastPopup()" role="button" aria-label="Open announcement">
                        <span class="tab-dot"></span>
                        <span class="tab-icon">📢</span>
                        <span class="tab-text">Message</span>
                    </div>

                    {{-- Pop-up Modal --}}
                    <div id="broadcast-overlay" role="dialog" aria-modal="true" aria-labelledby="broadcast-title">
                        <div id="broadcast-modal">
                            {{-- Header --}}
                            <div class="modal-header">
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <span style="font-size:1.2rem;">📢</span>
                                    <span id="broadcast-title" style="color:#fb923c;font-weight:700;font-size:.95rem;letter-spacing:.02em;">
                                        {{ $activeBroadcast->title }}
                                    </span>
                                </div>
                                <button id="broadcast-close-btn" onclick="closeBroadcastPopup()"
                                        aria-label="Close announcement">✕</button>
                            </div>

                            {{-- Body --}}
                            <div class="modal-body">
                                @if($activeBroadcast->image_path)
                                    <img src="{{ Storage::url($activeBroadcast->image_path) }}"
                                         alt="{{ $activeBroadcast->title }}"
                                         class="modal-image"
                                         onclick="openImgLightbox(this.src, this.alt)"
                                         title="Click to view full image">
                                @endif

                                {{-- Lightbox (shared, outside modal z-context) --}}
                                <div id="img-lightbox" style="display:none;" onclick="closeImgLightbox()">
                                    <button id="img-lightbox-close" onclick="closeImgLightbox()" aria-label="Close image">✕</button>
                                    <img id="img-lightbox-img" src="" alt="">
                                </div>
                                <p class="msg-text">{{ $activeBroadcast->message }}</p>
                                <div style="margin-top:1rem;padding-top:.75rem;border-top:1px solid rgba(255,255,255,0.07);
                                            display:flex;align-items:center;justify-content:space-between;">
                                    <span style="color:#475569;font-size:.75rem;">
                                        📅 {{ $activeBroadcast->created_at->format('d M Y') }}
                                    </span>
                                    <button onclick="closeBroadcastPopup()"
                                            style="background:rgba(251,146,60,0.15);border:1px solid rgba(251,146,60,0.4);
                                                   color:#fb923c;font-size:.8rem;font-weight:600;padding:.4rem 1rem;
                                                   border-radius:.5rem;cursor:pointer;transition:background .2s;"
                                            onmouseover="this.style.background='rgba(251,146,60,0.25)'"
                                            onmouseout="this.style.background='rgba(251,146,60,0.15)'">
                                        Got it ✓
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        (function () {
                            var BROADCAST_ID = '{{ $activeBroadcast->id }}';
                            var STORAGE_KEY  = 'broadcast_dismissed_' + BROADCAST_ID;

                            var overlay = document.getElementById('broadcast-overlay');
                            var tab     = document.getElementById('broadcast-tab');

                            // Check localStorage — if already dismissed, skip pop-up
                            if (localStorage.getItem(STORAGE_KEY) === '1') {
                                overlay.style.display = 'none';
                                tab.style.display = 'flex';
                            }

                            window.closeBroadcastPopup = function () {
                                overlay.style.display = 'none';
                                tab.style.display = 'flex';
                                localStorage.setItem(STORAGE_KEY, '1');
                            };

                            window.openBroadcastPopup = function () {
                                overlay.style.display = 'flex';
                                tab.style.display = 'none';
                            };

                            // Close on overlay background click
                            overlay.addEventListener('click', function (e) {
                                if (e.target === overlay) closeBroadcastPopup();
                            });

                            // Close on Escape key
                            document.addEventListener('keydown', function (e) {
                                if (e.key === 'Escape') {
                                    var lb = document.getElementById('img-lightbox');
                                    if (lb && lb.style.display !== 'none') {
                                        closeImgLightbox();
                                    } else if (overlay.style.display !== 'none') {
                                        closeBroadcastPopup();
                                    }
                                }
                            });

                            // ── Lightbox functions ──────────────────────────
                            window.openImgLightbox = function (src, alt) {
                                var lb  = document.getElementById('img-lightbox');
                                var img = document.getElementById('img-lightbox-img');
                                img.src = src;
                                img.alt = alt || '';
                                lb.style.display = 'flex';
                            };

                            window.closeImgLightbox = function () {
                                document.getElementById('img-lightbox').style.display = 'none';
                            };
                        })();
                    </script>
                @endif
            @endif
        @endauth
    </body>
</html>

