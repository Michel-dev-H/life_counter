---
paths:
  - 'tests/**/*.php'
---

# Tests

## TDD — o teste nasce antes do código
- Todo teste novo é escrito antes da implementação e visto falhando.
- Use `RefreshDatabase` no teste que toca o banco.
- Estrutura, nomes e dados seguem a skill `testing-best-practices` (factories, não arrays soltos).
- Não apague nem enfraqueça teste para ficar verde: conserte o código.
- Teste que não distingue nenhum defeito não entra.

Não se aplica a: nada.
