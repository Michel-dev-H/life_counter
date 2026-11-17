<?php

namespace App\NativeComponents;

use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\Reminder;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Native\Mobile\Edge\Concerns\ValidatesInput;
use Native\Mobile\Edge\NativeComponent;

class Reminders extends NativeComponent
{
    use ValidatesInput;

    public Duel $duel;

    public bool $showForm = false;

    public string $name = '';

    public string $description = '';

    public string $triggerTurn = '';

    public string $triggerPhase = '';

    public function navTitle(): string
    {
        return 'Lembretes';
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'triggerTurn' => ['nullable', 'integer'],
            'triggerPhase' => ['nullable', 'string', Rule::in(array_map(
                fn (DuelPhase $phase): string => $phase->value,
                DuelPhase::cases(),
            ))],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do lembrete.',
            'name.max' => 'O nome deve ter no máximo 120 caracteres.',
            'triggerTurn.integer' => 'Informe um número de turno.',
            'triggerPhase.in' => 'Selecione uma fase válida.',
        ];
    }

    public function openForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->triggerTurn = '';
        $this->triggerPhase = '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    /**
     * Pick the trigger phase by its position in the phase list.
     */
    public function setTriggerPhase(int $index): void
    {
        $phase = DuelPhase::cases()[$index] ?? null;

        if ($phase === null) {
            return;
        }

        $this->triggerPhase = $phase->value;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->duel->reminders()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_turn' => $validated['triggerTurn'] !== '' ? (int) $validated['triggerTurn'] : null,
            'trigger_phase' => $validated['triggerPhase'] !== '' ? $validated['triggerPhase'] : null,
            'completed' => false,
        ]);

        $this->closeForm();
    }

    public function toggle(int $reminderId): void
    {
        $reminder = $this->reminder($reminderId);

        $reminder->update(['completed' => ! $reminder->completed]);
    }

    public function delete(int $reminderId): void
    {
        $this->reminder($reminderId)->delete();
    }

    public function render(): View
    {
        $highlighted = $this->duel->highlightedReminderIds();

        return view('native.reminders', [
            'reminders' => $this->duel->reminders()->orderBy('completed')->orderBy('created_at')->get(),
            'highlightedIds' => $highlighted,
            'phases' => DuelPhase::cases(),
        ]);
    }

    private function reminder(int $reminderId): Reminder
    {
        return $this->duel->reminders()->findOrFail($reminderId);
    }
}
