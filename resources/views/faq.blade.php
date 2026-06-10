<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('FAQ & AI Assistant') }}
        </h2>
    </x-slot>

    <style>
        .faq-page { background: #0f172a; min-height: 100vh; padding: 2rem 1rem 6rem; }
        .faq-container { max-width: 860px; margin: 0 auto; }

        /* Hero */
        .faq-hero { text-align: center; margin-bottom: 3rem; }
        .faq-hero h1 { font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: .5rem; }
        .faq-hero h1 span { color: #34d399; }
        .faq-hero p { color: #94a3b8; font-size: 1rem; }

        /* Accordion */
        .accordion-section { margin-bottom: 2.5rem; }
        .accordion-section h2 { color: #34d399; font-size: .75rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase; margin-bottom: 1rem;
            padding-bottom: .5rem; border-bottom: 1px solid rgba(52,211,153,.2); }
        .accordion-item { background: #1e293b; border: 1px solid #334155;
            border-radius: .75rem; margin-bottom: .6rem; overflow: hidden; }
        .accordion-trigger { width: 100%; display: flex; justify-content: space-between;
            align-items: center; padding: 1rem 1.25rem; cursor: pointer;
            background: none; border: none; color: #e2e8f0; font-size: .95rem;
            font-weight: 600; text-align: left; transition: background .2s; }
        .accordion-trigger:hover { background: rgba(52,211,153,.05); }
        .accordion-trigger svg { flex-shrink: 0; transition: transform .3s; color: #64748b; }
        .accordion-trigger.open svg { transform: rotate(180deg); color: #34d399; }
        .accordion-body { display: none; padding: 0 1.25rem 1.1rem; color: #94a3b8;
            font-size: .9rem; line-height: 1.75; }
        .accordion-body.open { display: block; }
        .accordion-body strong { color: #cbd5e1; }
        .badge { display: inline-block; padding: .15rem .55rem; border-radius: 9999px;
            font-size: .7rem; font-weight: 700; margin: 0 .15rem; }
        .badge-red { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
        .badge-orange { background: rgba(249,115,22,.15); color: #fb923c; border: 1px solid rgba(249,115,22,.3); }
        .badge-green { background: rgba(52,211,153,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }

        /* Chat */
        .chat-card { background: #1e293b; border: 1px solid #334155; border-radius: 1.25rem;
            overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .chat-header { background: linear-gradient(135deg,#065f46,#064e3b);
            padding: 1.1rem 1.5rem; display: flex; align-items: center; gap: .85rem; }
        .chat-avatar { width: 2.5rem; height: 2.5rem; background: #34d399;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0; }
        .chat-header-info h3 { color: #fff; font-weight: 700; font-size: 1rem; margin: 0; }
        .chat-header-info p { color: #6ee7b7; font-size: .78rem; margin: 0; }
        .online-dot { width: 8px; height: 8px; background: #34d399; border-radius: 50%;
            display: inline-block; margin-right: 4px; box-shadow: 0 0 6px #34d399;
            animation: pulse-green 2s infinite; }
        @keyframes pulse-green { 0%,100%{opacity:1} 50%{opacity:.4} }

        .chat-messages { height: 420px; overflow-y: auto; padding: 1.25rem;
            display: flex; flex-direction: column; gap: .85rem;
            scroll-behavior: smooth; }
        .chat-messages::-webkit-scrollbar { width: 5px; }
        .chat-messages::-webkit-scrollbar-track { background: #0f172a; }
        .chat-messages::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }

        .msg { display: flex; gap: .6rem; align-items: flex-end; max-width: 80%; }
        .msg.user { align-self: flex-end; flex-direction: row-reverse; }
        .msg.ai { align-self: flex-start; }
        .msg-bubble { padding: .65rem 1rem; border-radius: 1.1rem; font-size: .88rem;
            line-height: 1.65; }
        .msg.user .msg-bubble { background: #059669; color: #fff;
            border-bottom-right-radius: .25rem; }
        .msg.ai .msg-bubble { background: #0f172a; color: #cbd5e1;
            border: 1px solid #334155; border-bottom-left-radius: .25rem; }
        .msg-icon { width: 1.8rem; height: 1.8rem; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: .9rem; }
        .msg.ai .msg-icon { background: #065f46; }
        .msg.user .msg-icon { background: #064e3b; }

        /* Typing indicator */
        .typing-bubble { display: flex; gap: 4px; padding: .65rem 1rem;
            background: #0f172a; border: 1px solid #334155; border-radius: 1.1rem;
            border-bottom-left-radius: .25rem; }
        .typing-bubble span { width: 7px; height: 7px; border-radius: 50%;
            background: #64748b; animation: typing .9s infinite; }
        .typing-bubble span:nth-child(2) { animation-delay: .15s; }
        .typing-bubble span:nth-child(3) { animation-delay: .3s; }
        @keyframes typing { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }

        /* Input */
        .chat-input-row { border-top: 1px solid #1e293b; padding: 1rem 1.25rem;
            display: flex; gap: .75rem; align-items: flex-end; background: #0f172a; }
        .chat-textarea { flex: 1; background: #1e293b; border: 1px solid #334155;
            border-radius: .75rem; color: #e2e8f0; padding: .7rem 1rem; font-size: .9rem;
            resize: none; outline: none; min-height: 44px; max-height: 120px;
            font-family: inherit; transition: border-color .2s; }
        .chat-textarea:focus { border-color: #34d399; }
        .chat-textarea::placeholder { color: #475569; }
        .send-btn { background: #059669; color: #fff; border: none; border-radius: .75rem;
            width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .2s, transform .1s; flex-shrink: 0; }
        .send-btn:hover { background: #047857; }
        .send-btn:active { transform: scale(.95); }
        .send-btn:disabled { background: #334155; cursor: not-allowed; }

        /* Suggestions */
        .suggestions { padding: .85rem 1.25rem; display: flex; flex-wrap: wrap; gap: .5rem;
            border-top: 1px solid #1e293b; background: #0f172a; }
        .suggestion-chip { background: #1e293b; border: 1px solid #334155; color: #94a3b8;
            padding: .35rem .8rem; border-radius: 9999px; font-size: .78rem; cursor: pointer;
            transition: all .2s; white-space: nowrap; }
        .suggestion-chip:hover { background: rgba(52,211,153,.1); border-color: #34d399; color: #34d399; }
    </style>

    <div class="faq-page">
        <div class="faq-container">

            <!-- Hero -->
            <div class="faq-hero" x-data="{show:false}" x-init="setTimeout(()=>show=true,100)"
                 x-show="show" x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0" style="display:none;">
                <div style="font-size:3rem;margin-bottom:.75rem;">🤖</div>
                <h1>PantryPal <span>Help Center</span></h1>
                <p>Browse common questions or ask our AI assistant anything about PantryPal.</p>
            </div>

            <!-- Accordion FAQ -->
            <div class="accordion-section" x-data="accordion()">

                <h2>📦 Pantry & Expiry</h2>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===0}" @click="toggle(0)">
                        How does PantryPal determine when a food item is expired?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===0}">
                        PantryPal compares the <strong>expiry date</strong> you've set for an item against <strong>today's date</strong>.
                        If the expiry date is before today, the item gets a <span class="badge badge-red">Expired</span> badge.
                        If it's within the next 7 days, it gets an <span class="badge badge-orange">Expiring Soon</span> badge.
                        If it's more than 7 days away, the item is considered <span class="badge badge-green">Fresh</span>.
                        Items with no expiry date set are never flagged.
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===1}" @click="toggle(1)">
                        What does "Expiring Soon" mean exactly?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===1}">
                        An item is marked <span class="badge badge-orange">Expiring Soon</span> when its expiry date is <strong>7 days or fewer</strong> from today's date but has not yet passed.
                        For example, if today is June 10 and your milk expires on June 15, it will show as Expiring Soon.
                        These items are prioritised by the Recipe AI so you can use them before they go to waste.
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===2}" @click="toggle(2)">
                        Can I add items without an expiry date?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===2}">
                        Yes! The expiry date field is <strong>completely optional</strong>. Items without a date will never be flagged as expired or expiring soon — they'll simply show a dash in the expiry column.
                        You can always add or update the expiry date later by clicking <strong>Edit</strong> on the item, or by using the <strong>camera scan</strong> feature on the product label.
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===3}" @click="toggle(3)">
                        What does "Out of Stock" mean on the dashboard?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===3}">
                        An item is considered <strong>Out of Stock</strong> when its quantity is set to <strong>0</strong>. The dashboard card shows how many items have reached zero quantity so you know what to restock. You can update quantities by clicking <strong>Edit</strong> on any pantry item.
                    </div>
                </div>

                <h2 style="margin-top:2rem;">📸 Smart Scan</h2>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===4}" @click="toggle(4)">
                        How does receipt scanning work?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===4}">
                        Go to <strong>Smart Scan → Receipt Scan</strong> and upload a photo of your grocery receipt.
                        PantryPal sends the image to <strong>Google Gemini AI</strong> which reads the receipt and extracts each item's name, quantity, unit, and category.
                        All items are automatically added to your pantry. Since receipts don't typically show expiry dates, those are left blank and can be filled in later using the camera scan feature.
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===5}" @click="toggle(5)">
                        How does the expiry date camera scan work?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===5}">
                        On your pantry dashboard, each item has a <strong>📷 camera icon</strong> next to its expiry date.
                        Tap it to take a photo of the product's label or packaging. The AI reads the printed expiry or best-before date and automatically updates the item in your pantry.
                        If the AI cannot clearly read a date, it will ask you to enter it manually via the Edit button instead.
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===6}" @click="toggle(6)">
                        How does ripeness detection work for fruits and vegetables?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===6}">
                        Go to <strong>Smart Scan → Ripeness Check</strong> and upload a photo of a fruit or vegetable.
                        The AI analyzes the image and returns: ripeness level (<em>Unripe / Nearly Ripe / Ripe / Overripe / Spoiled</em>), a 0–100 score, color description, estimated shelf life remaining in days, a recommendation, and a storage tip.
                        If the image is not a fruit or vegetable, the AI will let you know and won't provide a ripeness result.
                    </div>
                </div>

                <h2 style="margin-top:2rem;">🍽️ Recipe AI & Other</h2>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===7}" @click="toggle(7)">
                        How does the Recipe AI suggestion work?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===7}">
                        On your dashboard, tap <strong>"Generate Recipe"</strong>. PantryPal looks at all your items expiring within the next 7 days and sends them to the AI, which suggests a recipe that uses those items first to reduce food waste.
                        Your other pantry items can also be used to support the recipe. Results are cached for 6 hours. If no items are expiring soon, or the expiring items can't reasonably form a meal, the AI will explain why no recipe was generated.
                    </div>
                </div>

                <div class="accordion-item">
                    <button class="accordion-trigger" :class="{open: active===8}" @click="toggle(8)">
                        What is the Admin Broadcast system?
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="accordion-body" :class="{open: active===8}">
                        Admins can send <strong>broadcast announcements</strong> (with title, message, and optional image) to all regular users.
                        When an active broadcast exists, it appears as a pop-up modal when you open any page. You can dismiss it, and a small <strong>📢 floating tab</strong> on the right edge of the screen lets you reopen it anytime.
                    </div>
                </div>

            </div><!-- end accordion -->

            <!-- AI Chatbot -->
            <div class="chat-card" id="chat-section">
                <div class="chat-header">
                    <div class="chat-avatar">🤖</div>
                    <div class="chat-header-info">
                        <h3>PantryPal AI Assistant</h3>
                        <p><span class="online-dot"></span>Online — Ask me anything about PantryPal</p>
                    </div>
                </div>

                <div class="chat-messages" id="chat-messages">
                    <!-- Welcome message -->
                    <div class="msg ai">
                        <div class="msg-icon">🤖</div>
                        <div class="msg-bubble">
                            Hi there! 👋 I'm the PantryPal AI Assistant. I can help with <strong>anything food-related</strong> — storage tips, shelf life, food safety, cooking, nutrition — as well as how <strong>PantryPal</strong> works. What would you like to know?
                        </div>
                    </div>
                </div>

                <!-- Suggestion chips -->
                <div class="suggestions" id="suggestion-chips">
                    <span class="suggestion-chip" onclick="sendSuggestion(this)">How is expiry date determined?</span>
                    <span class="suggestion-chip" onclick="sendSuggestion(this)">How long does chicken last in the fridge?</span>
                    <span class="suggestion-chip" onclick="sendSuggestion(this)">How does Smart Scan work?</span>
                    <span class="suggestion-chip" onclick="sendSuggestion(this)">How to tell if milk has gone bad?</span>
                </div>

                <div class="chat-input-row">
                    <textarea id="chat-input" class="chat-textarea" rows="1"
                        placeholder="Ask about food, storage tips, or how PantryPal works..."
                        onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                    <button class="send-btn" id="send-btn" onclick="sendMessage()" title="Send">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Accordion
        function accordion() {
            return {
                active: null,
                toggle(i) { this.active = this.active === i ? null : i; }
            };
        }

        // ── Local FAQ Fallback (used when AI is rate-limited) ─────────────────
        const LOCAL_FAQ = [
            // ── PantryPal system questions (require system-specific context) ──
            {
                keywords: ['android','ios','iphone','ipad','apple','platform','app store','play store','work on','which device','what device','which phone'],
                answer: "PantryPal works on both Android and iOS! 🎉 It's a web application — just open any modern browser (Chrome, Safari, Firefox) on your phone or tablet. No app store download needed. Camera features (Smart Scan, expiry scan, ripeness detection) work on both Android and iOS."
            },
            {
                keywords: ['pantrypal mark','pantrypal determine','pantrypal detect','how pantrypal','pantrypal expir','expiring soon badge','expired badge','7-day','7 day window','dashboard badge','badge mean'],
                answer: "PantryPal marks items as expired by comparing the expiry date you set against today's date. Before today → red 'Expired' badge. Within 7 days → orange 'Expiring Soon' badge. More than 7 days away → fresh. Items with no date are never flagged."
            },
            {
                keywords: ['receipt scan','scan receipt','upload receipt','receipt photo','scan a receipt'],
                answer: "Go to Smart Scan → Receipt Scan and upload a photo of your grocery receipt. PantryPal uses Google Gemini AI to read the receipt and extract each item's name, quantity, unit, and category — all added to your pantry automatically."
            },
            {
                keywords: ['ripeness detection','ripeness check','ripeness scan','check ripeness','fruit scan','vegetable scan'],
                answer: "Go to Smart Scan → Ripeness Check and upload a photo of a fruit or vegetable. The AI returns the ripeness level (Unripe / Nearly Ripe / Ripe / Overripe / Spoiled), a 0–100 score, estimated shelf life in days, and storage tips."
            },
            {
                keywords: ['generate recipe','chef ai','recipe suggestion','recipe ai','pantrypal recipe'],
                answer: "On your dashboard, tap 'Generate Recipe'. PantryPal looks at items expiring within 7 days and asks the AI to suggest a recipe using those items first — to reduce food waste. Results are cached for 6 hours."
            },
            {
                keywords: ['privacy','private','data','my items','other user'],
                answer: "Your pantry items are completely private — only you can see them. Even admins cannot view individual user pantry contents."
            },
            {
                keywords: ['what is pantrypal','about','overview','system','how does it work','purpose','pantrypal'],
                answer: "PantryPal is a smart food pantry management web app. Key features: expiry tracking dashboard, Smart Scan (receipt scanning, expiry date scanning, ripeness detection), AI recipe suggestions, and admin broadcast announcements. It works on any browser — desktop, Android, and iOS."
            },
            {
                keywords: ['whatsapp share','share via whatsapp','share recipe whatsapp','send recipe whatsapp'],
                answer: "After Chef AI generates a recipe, a 'Share via WhatsApp' button appears. It opens WhatsApp with the full recipe pre-filled so you can share it with anyone."
            },

            // ── General food questions ────────────────────────────────────────
            {
                keywords: ['expire fast','expires fast','expire quickly','expires quickly','go bad fast','go bad quickly','go off fast','spoil fast','spoil quickly','perishable','short shelf life','kind of food that','type of food that','which food expires'],
                answer: "Foods that expire fastest:\n• 🥩 Raw meat & fish — 1–2 days in the fridge\n• 🥛 Fresh milk — 5–7 days after opening\n• 🥗 Leafy greens (spinach, lettuce) — 3–5 days\n• 🍓 Soft berries (strawberries, raspberries) — 2–3 days\n• 🍄 Mushrooms — 3–5 days\n• 🍞 Fresh bread (no preservatives) — 2–4 days\n• 🐟 Cooked seafood — 2–3 days\nAlways refrigerate these quickly and consume them first!"
            },
            {
                keywords: ['store meat','store chicken','store beef','store fish','raw meat storage','meat in fridge','meat storage','how long meat'],
                answer: "Raw meat storage:\n• Chicken & ground meat: use within 1–2 days in fridge\n• Beef, pork, lamb: 3–5 days in fridge\n• Cooked meat: 3–4 days in fridge\n• Frozen raw meat: 3–12 months\nAlways store on the bottom fridge shelf in a sealed container."
            },
            {
                keywords: ['store milk','store dairy','store cheese','store yogurt','dairy storage','how long milk','milk last','milk storage'],
                answer: "Dairy storage:\n• Milk: 5–7 days after opening at 2–4°C\n• Hard cheese: 3–4 weeks (wrap in wax paper)\n• Soft cheese: 1–2 weeks\n• Yogurt: 5–7 days after opening\n• Butter: 1 month in fridge, up to 9 months frozen"
            },
            {
                keywords: ['milk gone bad','milk off','milk spoiled','tell if milk','milk smell','sour milk','is milk bad','check milk'],
                answer: "Signs milk has gone bad:\n• 🤢 Sour or off smell\n• 🟡 Yellow or off-white colour\n• 🧪 Lumpy or curdled texture\n• Sour taste\nWhen in doubt, do a sniff test — your nose knows!"
            },
            {
                keywords: ['store vegetables','store fruit','store produce','vegetable storage','fruit storage','keep vegetables fresh','keep fruit fresh','produce storage','fresh produce'],
                answer: "Produce storage:\n• 🥦 Leafy greens, broccoli, carrots: fridge crisper — 3–7 days\n• 🍅 Tomatoes: room temp until ripe, then fridge — 5–7 days\n• 🍌 Bananas: room temperature — 3–5 days\n• 🍎 Apples: fridge — up to 4–6 weeks\n• 🧅 Onions & garlic: cool, dark, dry — weeks to months\n• 🥔 Potatoes: cool, dark place — up to 3 months"
            },
            {
                keywords: ['how long chicken','chicken last','chicken fridge','raw chicken','cooked chicken','chicken shelf'],
                answer: "Chicken shelf life:\n• Raw chicken in fridge: 1–2 days max\n• Cooked chicken in fridge: 3–4 days\n• Raw chicken frozen: up to 9 months\n• Cooked chicken frozen: 2–6 months\nStore raw chicken on the bottom fridge shelf in a sealed container."
            },
            {
                keywords: ['food gone bad','food spoiled','how to tell if food','is this safe to eat','still good to eat','safe to eat','is food off','can i eat','edible or not'],
                answer: "Signs food has gone bad:\n• 👃 Unusual or sour smell\n• 🎨 Colour changes (brown, grey, yellow)\n• 🦠 Visible mould — discard soft foods entirely\n• 🫠 Slimy or mushy texture\n• 😮 Unexpected bitter or sour taste\nWhen in doubt, throw it out!"
            },
            {
                keywords: ['freeze food','freezing food','can i freeze','freeze leftovers','how to freeze','freezer storage','freezer burn','frozen food last','freezer'],
                answer: "Freezing tips:\n• Cooked food: 2–3 months frozen\n• Raw meat: 3–12 months\n• Bread: up to 3 months\n• Freezer burn is safe to eat but affects taste — cut it off\n• Label containers with the freeze date\n• Cool food before freezing and use airtight containers"
            },
            {
                keywords: ['best before','use by','sell by','expiry date mean','difference between expiry','best before vs use by','what does best before mean'],
                answer: "Understanding food dates:\n• **Use By** — safety date. Don't eat after this, even if it looks fine.\n• **Best Before** — quality date. Safe to eat after, but may be less fresh.\n• **Sell By / Display Until** — for retailers only, not a consumer safety guide.\nAlways check smell, colour, and texture rather than relying only on the date."
            },
            {
                keywords: ['store eggs','eggs last','eggs fridge','egg freshness','float test','how long eggs','egg still good'],
                answer: "Egg storage:\n• Fridge at 4°C: lasts 3–5 weeks\n• **Float test**: Bowl of water — sinks flat = very fresh, stands upright = use soon, floats = discard!\n• Hard-boiled eggs: 1 week in the fridge in their shell"
            },
            {
                keywords: ['leftover','leftovers','reheat','reheating','leftover food','store cooked food','cooked food last'],
                answer: "Leftover tips:\n• Refrigerate within 2 hours of cooking\n• Fridge: 3–4 days | Freezer: 2–3 months\n• Reheat to at least 75°C (165°F) all the way through\n• Don't reheat more than once\n• Use airtight containers"
            },
            {
                keywords: ['food safety','safe temperature','danger zone','bacteria food','cross contamination','food poisoning','food borne illness'],
                answer: "Food safety basics:\n• Danger zone: 4°C–60°C — bacteria grow fast here\n• Keep cold food below 4°C and hot food above 60°C\n• Don't leave food at room temp for over 2 hours\n• Separate boards for raw meat and vegetables\n• Wash hands before and after handling raw meat"
            },
            {
                keywords: ['nutrition','calorie','protein','carbohydrate','fat','vitamin','mineral','healthy eating','diet advice','fibre','fiber','nutrients'],
                answer: "Nutrition basics:\n• 🥩 Protein (meat, eggs, legumes) — builds muscle\n• 🍚 Carbohydrates (rice, bread, fruits) — main energy source\n• 🥑 Healthy fats (avocado, nuts, olive oil) — brain and hormones\n• 🥦 Fibre (vegetables, whole grains) — gut health\nFeel free to ask specific nutrition questions — I'll answer what I can!"
            },
        ];

        function localAnswer(msg) {
            const lower = msg.toLowerCase();
            for (const entry of LOCAL_FAQ) {
                if (entry.keywords.some(kw => lower.includes(kw))) {
                    return entry.answer + '\n\n_(Answered from built-in knowledge — AI is currently at capacity. Try again in a few minutes for a full AI response.)_';
                }
            }
            return "I can help with food and PantryPal questions! Unfortunately the AI is at capacity right now. Try again in a few minutes, or browse the FAQ cards above for quick answers. 📖";
        }

        // ── Chat core ─────────────────────────────────────────────────────────
        const CHAT_URL  = '{{ route("faq.chat") }}';
        const CSRF      = '{{ csrf_token() }}';
        let   isBusy    = false;

        function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        function handleKey(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        }

        function sendSuggestion(chip) {
            const txt = chip.textContent.trim();
            document.getElementById('chat-input').value = txt;
            document.getElementById('suggestion-chips').style.display = 'none';
            sendMessage();
        }

        function appendMessage(role, text) {
            const box  = document.getElementById('chat-messages');
            const wrap = document.createElement('div');
            wrap.className = 'msg ' + role;
            wrap.innerHTML = `
                <div class="msg-icon">${role === 'ai' ? '🤖' : '👤'}</div>
                <div class="msg-bubble">${escapeHtml(text)}</div>`;
            box.appendChild(wrap);
            box.scrollTop = box.scrollHeight;
            return wrap;
        }

        function showTyping() {
            const box  = document.getElementById('chat-messages');
            const wrap = document.createElement('div');
            wrap.className = 'msg ai';
            wrap.id = 'typing-indicator';
            wrap.innerHTML = `
                <div class="msg-icon">🤖</div>
                <div class="typing-bubble"><span></span><span></span><span></span></div>`;
            box.appendChild(wrap);
            box.scrollTop = box.scrollHeight;
        }

        function hideTyping() {
            const el = document.getElementById('typing-indicator');
            if (el) el.remove();
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                .replace(/\n/g,'<br>');
        }

        async function sendMessage() {
            if (isBusy) return;
            const input = document.getElementById('chat-input');
            const msg   = input.value.trim();
            if (!msg) return;

            input.value = '';
            input.style.height = 'auto';
            document.getElementById('suggestion-chips').style.display = 'none';

            isBusy = true;
            document.getElementById('send-btn').disabled = true;

            appendMessage('user', msg);
            showTyping();

            try {
                const res = await fetch(CHAT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: msg }),
                });

                hideTyping();

                const data = await res.json();
                if (data.reply) {
                    appendMessage('ai', data.reply);
                } else if (res.status === 429 || res.status === 503 || (data.error && data.error.toLowerCase().includes('busy'))) {
                    // AI rate-limited — try local fallback
                    const local = localAnswer(msg);
                    appendMessage('ai', local || "The AI assistant is currently at capacity. Please try again in a few minutes, or browse the FAQ cards above for quick answers. 📖");
                } else {
                    appendMessage('ai', data.error || 'Something went wrong. Please try again.');
                }
            } catch (err) {
                hideTyping();
                // Network error — try local fallback
                const local = localAnswer(msg);
                appendMessage('ai', local || 'Connection error. Please check your internet and try again.');
            }

            isBusy = false;
            document.getElementById('send-btn').disabled = false;
            document.getElementById('chat-input').focus();
        }
    </script>

</x-app-layout>
