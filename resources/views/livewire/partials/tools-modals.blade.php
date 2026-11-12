<!-- Coin Flip Modal (Plain JS + fetch) -->
    <div id="coin-flip-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <button type="button" onclick="closeCoinFlipModal()" class="absolute inset-0 bg-surface/85 backdrop-blur-sm" aria-label="Fechar"></button>
        <section class="relative z-10 max-h-[92dvh] w-full max-w-md overflow-y-auto rounded-xl border border-white/10 bg-surface-container-lowest p-4 shadow-2xl sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-sans text-headline-sm font-semibold text-on-surface">Lançar moeda</h2>
                <button type="button" onclick="closeCoinFlipModal()" class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition hover:bg-surface-container-high hover:text-on-surface" aria-label="Fechar">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>
            <div class="mt-4 flex flex-col items-center gap-3">
                <button type="button" onclick="flipCoin()" class="flex h-14 w-full items-center justify-center rounded bg-gradient-to-br from-primary-container to-[#4facfe] font-mono text-headline-sm font-bold uppercase tracking-wide text-on-primary transition active:scale-[0.99]">Lançar</button>
                <p id="coin-result" class="font-mono text-headline-sm font-bold text-primary hidden"></p>
                <div class="flex gap-4 font-mono text-label-sm text-on-surface-variant">
                    <span id="coin-heads">Cara: 0</span>
                    <span id="coin-tails">Coroa: 0</span>
                    <span id="coin-total">Total: 0</span>
                </div>
            </div>
            <div id="coin-history" class="mt-4 hidden">
                <h3 class="font-mono text-label-sm uppercase tracking-wider text-on-surface">Histórico</h3>
                <ul id="coin-history-list" class="mt-2 flex flex-col gap-1"></ul>
            </div>
        </section>
    </div>
    <script>
        function openCoinFlipModal() {
            document.getElementById('coin-flip-modal').classList.remove('hidden');
            loadCoinFlipHistory();
        }
        function closeCoinFlipModal() {
            document.getElementById('coin-flip-modal').classList.add('hidden');
        }
        async function loadCoinFlipHistory() {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const res = await fetch('/duel/' + duelId + '/coin-flips');
                const data = await res.json();
                document.getElementById('coin-heads').textContent = 'Cara: ' + data.heads;
                document.getElementById('coin-tails').textContent = 'Coroa: ' + data.tails;
                document.getElementById('coin-total').textContent = 'Total: ' + (data.heads + data.tails);
                const list = document.getElementById('coin-history-list');
                list.innerHTML = '';
                if (data.history.length > 0) {
                    document.getElementById('coin-history').classList.remove('hidden');
                    data.history.forEach(function(flip) {
                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between rounded bg-surface-container-high px-2 py-1 font-mono text-label-sm text-on-surface-variant';
                        li.innerHTML = '<span>' + (flip.result === 'heads' ? 'Cara' : 'Coroa') + '</span><span class="text-xs opacity-60">' + flip.time + '</span>';
                        list.appendChild(li);
                    });
                } else {
                    document.getElementById('coin-history').classList.add('hidden');
                }
            } catch (e) { console.error(e); }
        }
        async function flipCoin() {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const res = await fetch('/duel/' + duelId + '/coin-flip', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                const data = await res.json();
                const resultEl = document.getElementById('coin-result');
                resultEl.textContent = data.result === 'heads' ? 'Cara' : 'Coroa';
                resultEl.classList.remove('hidden');
                document.getElementById('coin-heads').textContent = 'Cara: ' + data.heads;
                document.getElementById('coin-tails').textContent = 'Coroa: ' + data.tails;
                document.getElementById('coin-total').textContent = 'Total: ' + (data.heads + data.tails);
                const list = document.getElementById('coin-history-list');
                list.innerHTML = '';
                if (data.history.length > 0) {
                    document.getElementById('coin-history').classList.remove('hidden');
                    data.history.forEach(function(flip) {
                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between rounded bg-surface-container-high px-2 py-1 font-mono text-label-sm text-on-surface-variant';
                        li.innerHTML = '<span>' + (flip.result === 'heads' ? 'Cara' : 'Coroa') + '</span><span class="text-xs opacity-60">' + flip.time + '</span>';
                        list.appendChild(li);
                    });
                }
            } catch (e) { console.error(e); }
        }
    </script>

    <!-- Dice Modal (Plain JS + fetch) -->
    <div id="dice-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <button type="button" onclick="closeDiceModal()" class="absolute inset-0 bg-surface/85 backdrop-blur-sm" aria-label="Fechar"></button>
        <section class="relative z-10 max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-xl border border-white/10 bg-surface-container-lowest p-4 shadow-2xl sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-sans text-headline-sm font-semibold text-on-surface">Lançar dados</h2>
                <button type="button" onclick="closeDiceModal()" class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition hover:bg-surface-container-high hover:text-on-surface" aria-label="Fechar">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>
            <div class="mt-4 flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <label class="font-mono text-label-sm text-on-surface-variant">Quantidade:</label>
                    <input type="number" id="dice-count" min="1" max="10" value="1" class="h-9 w-16 rounded border border-white/10 bg-surface-container-lowest px-2 text-on-surface focus:border-primary-container focus:outline-none" />
                    <button type="button" onclick="rollDice()" class="flex h-9 items-center justify-center rounded bg-gradient-to-br from-primary-container to-[#4facfe] px-4 font-mono text-label-sm font-bold uppercase text-on-primary transition active:scale-[0.99]">Lançar</button>
                </div>
                <div id="dice-result" class="hidden">
                    <p class="font-mono text-headline-sm font-bold text-primary" id="dice-total"></p>
                    <p class="font-mono text-label-sm text-on-surface-variant" id="dice-values"></p>
                </div>
                <div id="dice-history" class="hidden mt-4">
                    <h3 class="font-mono text-label-sm uppercase tracking-wider text-on-surface">Histórico</h3>
                    <ul id="dice-history-list" class="mt-2 flex flex-col gap-1"></ul>
                </div>
            </div>
        </section>
    </div>
    <script>
        function openDiceModal() {
            document.getElementById('dice-modal').classList.remove('hidden');
            loadDiceHistory();
        }
        function closeDiceModal() {
            document.getElementById('dice-modal').classList.add('hidden');
        }
        async function loadDiceHistory() {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const res = await fetch('/duel/' + duelId + '/dice-rolls');
                const data = await res.json();
                const list = document.getElementById('dice-history-list');
                list.innerHTML = '';
                if (data.history.length > 0) {
                    document.getElementById('dice-history').classList.remove('hidden');
                    data.history.forEach(function(r) {
                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between rounded bg-surface-container-high px-2 py-1 font-mono text-label-sm text-on-surface-variant';
                        li.innerHTML = '<span>' + r.count + ' dado(s): ' + r.results.join(', ') + ' = ' + r.total + '</span><span class="text-xs opacity-60">' + r.time + '</span>';
                        list.appendChild(li);
                    });
                }
            } catch (e) { console.error(e); }
        }
        async function rollDice() {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const count = document.getElementById('dice-count').value;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const res = await fetch('/duel/' + duelId + '/dice-roll', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ count: parseInt(count) }),
                });
                const data = await res.json();
                document.getElementById('dice-result').classList.remove('hidden');
                document.getElementById('dice-total').textContent = 'Soma: ' + data.total;
                document.getElementById('dice-values').textContent = data.results.join(', ');
                loadDiceHistory();
            } catch (e) { console.error(e); }
        }
    </script>

    <!-- Reminders Modal (Plain JS + fetch) -->
    <div id="reminders-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <button type="button" onclick="closeRemindersModal()" class="absolute inset-0 bg-surface/85 backdrop-blur-sm" aria-label="Fechar"></button>
        <section class="relative z-10 max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-xl border border-white/10 bg-surface-container-lowest p-4 shadow-2xl sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-sans text-headline-sm font-semibold text-on-surface">Lembretes</h2>
                <button type="button" onclick="closeRemindersModal()" class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition hover:bg-surface-container-high hover:text-on-surface" aria-label="Fechar">
                    <x-icon name="x" class="size-5" />
                </button>
            </div>
            <form onsubmit="saveReminder(event)" class="mt-4 flex flex-col gap-2">
                <input type="text" id="reminder-name" placeholder="Nome do lembrete" class="h-10 rounded border border-white/10 bg-surface-container-lowest px-3 font-sans text-body-sm text-on-surface placeholder:text-on-surface-variant/60 focus:border-primary-container focus:outline-none" aria-label="Nome" />
                <textarea id="reminder-desc" placeholder="Descrição (opcional)" rows="2" class="rounded border border-white/10 bg-surface-container-lowest px-3 py-2 font-sans text-body-sm text-on-surface placeholder:text-on-surface-variant/60 focus:border-primary-container focus:outline-none" aria-label="Descrição"></textarea>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex flex-col gap-1 font-mono text-label-xs text-on-surface-variant">Turno gatilho
                        <input type="number" min="1" id="reminder-turn" class="h-9 rounded border border-white/10 bg-surface-container-lowest px-2 text-on-surface focus:border-primary-container focus:outline-none" />
                    </label>
                    <label class="flex flex-col gap-1 font-mono text-label-xs text-on-surface-variant">Fase gatilho
                        <select id="reminder-phase" class="h-9 rounded border border-white/10 bg-surface-container-lowest px-2 text-on-surface focus:border-primary-container focus:outline-none">
                            <option value="">—</option>
                            <option value="DP">Draw</option>
                            <option value="SP">Standby</option>
                            <option value="M1">Main 1</option>
                            <option value="BP">Battle</option>
                            <option value="M2">Main 2</option>
                            <option value="EP">End</option>
                        </select>
                    </label>
                </div>
                <button type="submit" class="flex h-10 items-center justify-center rounded bg-gradient-to-br from-primary-container to-[#4facfe] font-mono text-label-sm font-bold uppercase text-on-primary transition active:scale-[0.99]">Adicionar lembrete</button>
            </form>
            <div id="reminders-list" class="mt-4"></div>
        </section>
    </div>
    <script>
        function openRemindersModal() {
            document.getElementById('reminders-modal').classList.remove('hidden');
            loadReminders();
        }
        function closeRemindersModal() {
            document.getElementById('reminders-modal').classList.add('hidden');
        }
        async function loadReminders() {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const res = await fetch('/duel/' + duelId + '/reminders');
                const data = await res.json();
                const container = document.getElementById('reminders-list');
                container.innerHTML = '';
                if (data.reminders.length === 0) {
                    container.innerHTML = '<p class="font-mono text-label-sm text-on-surface-variant">Nenhum lembrete registrado.</p>';
                    return;
                }
                const ul = document.createElement('ul');
                ul.className = 'flex flex-col gap-2';
                data.reminders.forEach(function(r) {
                    const li = document.createElement('li');
                    li.className = 'rounded border px-3 py-2 ' + (r.completed ? 'opacity-50 ' : '') + (r.highlighted ? 'border-primary-container bg-primary-container/10' : 'border-white/10 bg-surface-container-low');
                    li.innerHTML = '<div class="flex items-start justify-between gap-2"><div class="min-w-0"><p class="font-mono text-label-sm font-bold text-on-surface">' + r.name + '</p>' + (r.description ? '<p class="font-sans text-body-sm text-on-surface-variant">' + r.description + '</p>' : '') + '</div><div class="flex shrink-0 gap-1"><button type="button" onclick="toggleReminder(' + r.id + ')" class="flex size-8 items-center justify-center rounded text-on-surface-variant transition hover:text-primary" aria-label="Concluir">' + (r.completed ? '&#8635;' : '&#10003;') + '</button><button type="button" onclick="deleteReminder(' + r.id + ')" class="flex size-8 items-center justify-center rounded text-on-surface-variant transition hover:text-damage" aria-label="Remover">&#215;</button></div></div>';
                    ul.appendChild(li);
                });
                container.appendChild(ul);
            } catch (e) { console.error(e); }
        }
        async function saveReminder(e) {
            e.preventDefault();
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const name = document.getElementById('reminder-name').value;
                const description = document.getElementById('reminder-desc').value;
                const triggerTurn = document.getElementById('reminder-turn').value;
                const triggerPhase = document.getElementById('reminder-phase').value;
                const res = await fetch('/duel/' + duelId + '/reminders', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name, description: description, trigger_turn: triggerTurn ? parseInt(triggerTurn) : null, trigger_phase: triggerPhase || null }),
                });
                const data = await res.json();
                document.getElementById('reminder-name').value = '';
                document.getElementById('reminder-desc').value = '';
                document.getElementById('reminder-turn').value = '';
                document.getElementById('reminder-phase').value = '';
                loadReminders();
            } catch (e) { console.error(e); }
        }
        async function toggleReminder(id) {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                await fetch('/duel/' + duelId + '/reminders/' + id + '/toggle', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                loadReminders();
            } catch (e) { console.error(e); }
        }
        async function deleteReminder(id) {
            try {
                const duelId = (location.pathname.match(/\/duel\/(\d+)/) || [])[1];
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                await fetch('/duel/' + duelId + '/reminders/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                loadReminders();
            } catch (e) { console.error(e); }
        }
    </script>