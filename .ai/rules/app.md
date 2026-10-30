---
paths:
  - 'app/**/*.php'
---

# App

## YAGNI — só o que um requisito atual exige
Nenhuma abstração, interface, flag, parâmetro ou coluna nasce "para depois".
- Sem requisito concreto e sem teste que o prove, não entra.
- Primeira implementação é concreta: classe concreta, sem interface de uma só implementação.
- Parâmetro só existe quando há chamador que o use.
- Antes de criar, procure o que já existe (componente, scope, enum, trait).
- As telas do Stitch são requisito: implemente o que elas mostram, nada além.

Anti-exemplo: `GameFormat` polimórfico, `array $options = []` preventivo, coluna `replay_url` "por via das dúvidas".

Não se aplica a: assinaturas exigidas pelo framework (`Policy`, `Tool`, `Command`) e requisitos já acordados para esta entrega.

## DRY — uma fonte por regra de negócio
Cada decisão de negócio (cálculo de vida, faixa válida, regra de formato) tem uma única fonte em `app/`.
- Livewire, FormRequest e view chamam essa fonte; não reimplementam a regra.
- Regra de três: duas cópias com o mesmo motivo de mudança podem ficar; na terceira, extraia.
- Texto parecido com motivo de mudança diferente não é duplicação: não una.
- Extraia no menor escopo possível (método → service), não direto para trait ou base class.
- Trait só para comportamento comum entre classes do mesmo contrato.

Anti-exemplo: "vida entre 1 e 99999" validada no componente Livewire e de novo no FormRequest.

Não se aplica a: código gerado pelo framework e a fluxos que vão divergir (log vs. UI).

## TDD — nenhuma produção sem teste falhando antes
Comportamento = branch, validação, cálculo, autorização, persistência ou transição de estado.
- Ciclo: menor teste que falha → ver falhar pelo motivo certo → mínimo para passar → refatorar em verde.
- Nunca refatore em vermelho; nunca escreva produção antes do teste.
- Feature test primeiro (`php artisan make:test --pest`); unit só para lógica sem framework.
- Rode o menor conjunto: `php artisan test --compact --filter=...` ou o arquivo.
- Teste que passou de primeira não provou nada: force a falha e confirme que detecta o defeito.

Sem exceção para mudança de comportamento. Protótipo descartável não entra no repositório.
