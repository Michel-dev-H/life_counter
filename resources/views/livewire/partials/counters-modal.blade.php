<!-- Counters modal (full layout from counters.html) -->
<div id="counters-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
    <button type="button" onclick="closeCountersModal()" class="absolute inset-0 bg-preto-profundo/85 backdrop-blur-sm" aria-label="Fechar"></button>
    <section class="relative z-10 max-h-[92dvh] w-full max-w-2xl overflow-y-auto rounded-xl border border-ciano-holografico/20 bg-grafite p-4 shadow-2xl sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <h2 class="font-headline text-sm font-bold uppercase tracking-wider text-ciano-holografico">Contadores &amp; Campo</h2>
            <button type="button" onclick="closeCountersModal()" class="flex size-11 shrink-0 items-center justify-center rounded text-branco-frio/70 transition hover:bg-azul-tecnologico/20 hover:text-ciano-holografico" aria-label="Fechar">
                <x-icon name="x" class="size-5" />
            </button>
        </div>
        <div class="mt-4 space-y-5">

    <!-- ============================================== -->
    <!-- SECTION 1: HOLOGRAPHIC MINI FIELD MATRIX -->
    <!-- Simplified spatial view of official YGO zones -->
    <!-- ============================================== -->
    <section class="bg-grafite/80 border border-ciano-holografico/25 rounded-xl p-3 shadow-lg">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-1.5">
          <x-icon name="expand" class="size-4 text-ciano-holografico" />
          <h2 class="font-headline text-xs font-bold uppercase tracking-wider text-branco-frio">CAMPO VIRTUAL AUXILIAR</h2>
        </div>
        <span class="text-[10px] text-ciano-holografico font-headline uppercase bg-azul-tecnologico/20 px-1.5 py-0.2 rounded">JOGADOR 1</span>
      </div>

      <!-- Extra Monster Zones Row (Center) -->
      <div class="flex justify-center gap-6 my-2">
        <div class="w-14 h-12 bg-preto-profundo border border-branco-frio/20 rounded flex flex-col items-center justify-center relative">
          <span class="text-[9px] font-headline text-branco-frio/40 uppercase">EMZ 1</span>
          <span class="text-[8px] font-body text-branco-frio/30">Vazia</span>
        </div>
        <div class="w-14 h-12 bg-azul-tecnologico/20 border border-ciano-holografico rounded flex flex-col items-center justify-center relative shadow-[0_0_8px_#35D8FF44]">
          <span class="text-[9px] font-headline text-ciano-holografico uppercase font-bold">EMZ 2</span>
          <span class="text-[8px] font-body text-ciano-holografico">Link-4</span>
        </div>
      </div>

      <!-- Main Monster Zones (5 Zones) -->
      <div class="grid grid-cols-5 gap-1.5 text-center my-2">
        <div class="h-12 bg-preto-profundo border border-branco-frio/20 rounded flex flex-col items-center justify-center">
          <span class="text-[9px] font-headline text-branco-frio/40">MMZ 1</span>
        </div>
        <div class="h-12 bg-dourado-destaque/15 border border-dourado-destaque/50 rounded flex flex-col items-center justify-center relative shadow-[0_0_8px_#D6B56D33]">
          <span class="text-[9px] font-headline text-dourado-destaque font-bold">MMZ 2</span>
          <span class="text-[8px] font-headline text-dourado-destaque">4 SC</span>
        </div>
        <div class="h-12 bg-verde-recuperacao/15 border border-verde-recuperacao/50 rounded flex flex-col items-center justify-center relative">
          <span class="text-[9px] font-headline text-verde-recuperacao font-bold">MMZ 3</span>
          <span class="text-[8px] font-headline text-verde-recuperacao">2 VC</span>
        </div>
        <div class="h-12 bg-preto-profundo border border-branco-frio/20 rounded flex flex-col items-center justify-center">
          <span class="text-[9px] font-headline text-branco-frio/40">MMZ 4</span>
        </div>
        <div class="h-12 bg-preto-profundo border border-branco-frio/20 rounded flex flex-col items-center justify-center">
          <span class="text-[9px] font-headline text-branco-frio/40">MMZ 5</span>
        </div>
      </div>

      <!-- Spell & Trap Zones (5 Zones) + Field Spell & Graveyard -->
      <div class="grid grid-cols-5 gap-1.5 text-center">
        <div class="h-10 bg-preto-profundo border border-branco-frio/20 rounded flex items-center justify-center text-[9px] font-headline text-branco-frio/40">STZ 1</div>
        <div class="h-10 bg-azul-tecnologico/20 border border-ciano-holografico/40 rounded flex items-center justify-center text-[9px] font-headline text-ciano-holografico font-bold">SET</div>
        <div class="h-10 bg-azul-tecnologico/20 border border-ciano-holografico/40 rounded flex items-center justify-center text-[9px] font-headline text-ciano-holografico font-bold">SET</div>
        <div class="h-10 bg-preto-profundo border border-branco-frio/20 rounded flex items-center justify-center text-[9px] font-headline text-branco-frio/40">STZ 4</div>
        <div class="h-10 bg-preto-profundo border border-branco-frio/20 rounded flex items-center justify-center text-[9px] font-headline text-branco-frio/40">STZ 5</div>
      </div>
    </section>

    <!-- ============================================== -->
    <!-- SECTION 2: ACTIVE CARD COUNTERS MODULES -->
    <!-- ============================================== -->
    <section class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="font-headline text-xs font-bold uppercase tracking-wider text-ciano-holografico flex items-center gap-1.5">
          <x-icon name="toll" class="size-4" />
          CONTADORES ATIVOS NO CAMPO
        </h2>
        <span class="text-[11px] text-branco-frio/50 font-body">3 Cartas Rastreadas</span>
      </div>

      <!-- Counter Card 1: Spell Counters (Endymion/Dark Magician) -->
      <article class="bg-grafite border border-dourado-destaque/40 rounded-xl p-3.5 shadow-md">
        <div class="flex items-center justify-between pb-2 border-b border-branco-frio/10">
          <div>
            <div class="flex items-center gap-2">
              <x-icon name="sparkles" class="size-4 text-dourado-destaque" />
              <h3 class="font-headline text-sm font-bold text-branco-frio uppercase">MAGO DO CAOS DAS TREVAS</h3>
            </div>
            <span class="text-[11px] text-branco-frio/60 font-body">Localização: Zona de Monstro 02 • Jogador 1</span>
          </div>
          <span class="text-[10px] font-headline text-dourado-destaque bg-dourado-destaque/10 border border-dourado-destaque/30 px-2 py-0.5 rounded uppercase">
            SPELL COUNTERS
          </span>
        </div>

        <!-- Stepper Component -->
        <div class="flex items-center justify-between my-3 bg-preto-profundo/70 p-2.5 rounded-lg border border-branco-frio/10">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-dourado-destaque shadow-[0_0_8px_#D6B56D]"></span>
            <span class="font-body text-xs text-branco-frio/80">Acúmulo de Magia</span>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="updateCounter(&#39;c1&#39;, -1)" class="w-8 h-8 rounded bg-grafite border border-branco-frio/20 text-branco-frio hover:border-vermelho-dano hover:text-vermelho-dano flex items-center justify-center font-headline text-base active:scale-95">−</button>
            <span id="counter-val-c1" class="font-display text-2xl text-dourado-destaque font-bold tabular-nums w-8 text-center">04</span>
            <button onclick="updateCounter(&#39;c1&#39;, 1)" class="w-8 h-8 rounded bg-grafite border border-branco-frio/20 text-branco-frio hover:border-dourado-destaque hover:text-dourado-destaque flex items-center justify-center font-headline text-base active:scale-95">+</button>
          </div>
        </div>

        <div class="flex items-center justify-between text-[11px] font-body text-branco-frio/50 pt-1">
          <span>Gatilho: Remove 3 para destruir 1 carta em campo</span>
          <button class="text-ciano-holografico hover:underline font-headline text-[10px] uppercase">Editar</button>
        </div>
      </article>

      <!-- Counter Card 2: Venom Counters (Reptile / Venom Swamp) -->
      <article class="bg-grafite border border-verde-recuperacao/40 rounded-xl p-3.5 shadow-md">
        <div class="flex items-center justify-between pb-2 border-b border-branco-frio/10">
          <div>
            <div class="flex items-center gap-2">
              <x-icon name="flame" class="size-4 text-verde-recuperacao" />
              <h3 class="font-headline text-sm font-bold text-branco-frio uppercase">VENOM SWAMP FIELD</h3>
            </div>
            <span class="text-[11px] text-branco-frio/60 font-body">Localização: Zona de Campo • Geral</span>
          </div>
          <span class="text-[10px] font-headline text-verde-recuperacao bg-verde-recuperacao/10 border border-verde-recuperacao/30 px-2 py-0.5 rounded uppercase">
            VENOM COUNTERS
          </span>
        </div>

        <!-- Stepper Component -->
        <div class="flex items-center justify-between my-3 bg-preto-profundo/70 p-2.5 rounded-lg border border-branco-frio/10">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-verde-recuperacao shadow-[0_0_8px_#43D9A3]"></span>
            <span class="font-body text-xs text-branco-frio/80">Contadores de Veneno (−500 ATK/DEF)</span>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="updateCounter(&#39;c2&#39;, -1)" class="w-8 h-8 rounded bg-grafite border border-branco-frio/20 text-branco-frio hover:border-vermelho-dano hover:text-vermelho-dano flex items-center justify-center font-headline text-base active:scale-95">−</button>
            <span id="counter-val-c2" class="font-display text-2xl text-verde-recuperacao font-bold tabular-nums w-8 text-center">02</span>
            <button onclick="updateCounter(&#39;c2&#39;, 1)" class="w-8 h-8 rounded bg-grafite border border-branco-frio/20 text-branco-frio hover:border-verde-recuperacao hover:text-verde-recuperacao flex items-center justify-center font-headline text-base active:scale-95">+</button>
          </div>
        </div>

        <div class="flex items-center justify-between text-[11px] font-body text-branco-frio/50 pt-1">
          <span>Efeito: Monstros com ATK reduzido a 0 são destruídos</span>
          <button class="text-ciano-holografico hover:underline font-headline text-[10px] uppercase">Editar</button>
        </div>
      </article>

      <!-- Counter Card 3: Bushido Counters (Six Samurai) -->
      <article class="bg-grafite border border-vermelho-dano/40 rounded-xl p-3.5 shadow-md">
        <div class="flex items-center justify-between pb-2 border-b border-branco-frio/10">
          <div>
            <div class="flex items-center gap-2">
              <x-icon name="shield" class="size-4 text-vermelho-dano" />
              <h3 class="font-headline text-sm font-bold text-branco-frio uppercase">GATEWAY OF THE SAMURAI</h3>
            </div>
            <span class="text-[11px] text-branco-frio/60 font-body">Localização: Zona de Magia Contínua 03</span>
          </div>
          <span class="text-[10px] font-headline text-vermelho-dano bg-vermelho-dano/10 border border-vermelho-dano/30 px-2 py-0.5 rounded uppercase">
            BUSHIDO
          </span>
        </div>

        <!-- Stepper Component -->
        <div class="flex items-center justify-between my-3 bg-preto-profundo/70 p-2.5 rounded-lg border border-branco-frio/10">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-vermelho-dano shadow-[0_0_8px_#FF4D5A]"></span>
            <span class="font-body text-xs text-branco-frio/80">Contadores de Bushido</span>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="updateCounter(&#39;c3&#39;, -1)" class="w-8 h-8 rounded bg-grafite border border-branco-frio/20 text-branco-frio hover:border-vermelho-dano hover:text-vermelho-dano flex items-center justify-center font-headline text-base active:scale-95">−</button>
            <span id="counter-val-c3" class="font-display text-2xl text-vermelho-dano font-bold tabular-nums w-8 text-center">06</span>
            <button onclick="updateCounter(&#39;c3&#39;, 1)" class="w-8 h-8 rounded bg-grafite border border-branco-frio/20 text-branco-frio hover:border-vermelho-dano hover:text-vermelho-dano flex items-center justify-center font-headline text-base active:scale-95">+</button>
          </div>
        </div>

        <div class="flex items-center justify-between text-[11px] font-body text-branco-frio/50 pt-1">
          <span>Gatilho: Remove 4 para buscar monstro do cemitério</span>
          <button class="text-ciano-holografico hover:underline font-headline text-[10px] uppercase">Editar</button>
        </div>
      </article>
    </section>

    <!-- ============================================== -->
    <!-- SECTION 3: EFFECT REMINDERS / PENDING TRIGGERS -->
    <!-- ============================================== -->
    <section class="space-y-3 pt-2">
      <div class="flex items-center justify-between">
        <h2 class="font-headline text-xs font-bold uppercase tracking-wider text-dourado-destaque flex items-center gap-1.5">
          <x-icon name="timer" class="size-4" />
          LEMBRETES DE EFEITO &amp; GATILHOS PENDENTES
        </h2>
        <span class="text-[10px] font-headline text-dourado-destaque bg-dourado-destaque/20 px-2 py-0.5 rounded">2 ATIVOS</span>
      </div>

      <!-- Reminder 1 -->
      <div class="bg-grafite border-l-4 border-l-dourado-destaque border border-branco-frio/15 rounded-r-xl p-3.5 flex items-start justify-between">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="text-[9px] font-headline uppercase bg-dourado-destaque/20 text-dourado-destaque px-1.5 py-0.2 rounded font-bold">END PHASE</span>
            <span class="text-[11px] font-body text-branco-frio/60">Jogador 1 (Yugi)</span>
          </div>
          <h4 class="font-headline text-sm font-bold text-branco-frio">Scrape &amp; Return: Swords of Revealing Light</h4>
          <p class="font-body text-xs text-branco-frio/70 mt-0.5">Destruir carta de magia no 3º turno e liberar declaração de ataque.</p>
        </div>
        <div class="text-right pl-2">
          <span class="font-headline text-xs text-dourado-destaque font-bold block">1 TURNO</span>
          <span class="text-[10px] text-branco-frio/40 font-body">Restante</span>
        </div>
      </div>

      <!-- Reminder 2 -->
      <div class="bg-grafite border-l-4 border-l-ciano-holografico border border-branco-frio/15 rounded-r-xl p-3.5 flex items-start justify-between">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="text-[9px] font-headline uppercase bg-azul-tecnologico/30 text-ciano-holografico px-1.5 py-0.2 rounded font-bold">STANDBY PHASE</span>
            <span class="text-[11px] font-body text-branco-frio/60">Jogador 2 (Seto)</span>
          </div>
          <h4 class="font-headline text-sm font-bold text-branco-frio">Custo de Manutenção: Imperial Order</h4>
          <p class="font-body text-xs text-branco-frio/70 mt-0.5">Pagar 700 Pontos de Vida ou destruir o card de armadilha contínua.</p>
        </div>
        <div class="text-right pl-2">
          <span class="font-headline text-xs text-ciano-holografico font-bold block">PRÓXIMO</span>
          <span class="text-[10px] text-branco-frio/40 font-body">Turno 4</span>
        </div>
      </div>
    </section>

          </div>
    </section>
</div>
<script>
    function openCountersModal() {
        document.getElementById('counters-modal').classList.remove('hidden');
    }
    function closeCountersModal() {
        document.getElementById('counters-modal').classList.add('hidden');
    }
</script>