<?php

namespace App\Http\Controllers;

use App\Models\CoinFlip;
use App\Models\DiceRoll;
use App\Models\Duel;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;

class DuelToolsController extends Controller
{
    public function flipCoin(int $duel): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        $result = random_int(0, 1) === 0 ? 'heads' : 'tails';

        CoinFlip::create([
            'duel_id' => $duel->id,
            'result' => $result,
        ]);

        $heads = $duel->coinFlips()->where('result', 'heads')->count();
        $tails = $duel->coinFlips()->where('result', 'tails')->count();
        $history = $duel->coinFlips()->latest()->limit(20)->get()->map(fn ($f) => [
            'result' => $f->result,
            'time' => $f->created_at->format('H:i:s'),
        ])->toArray();

        return response()->json([
            'result' => $result,
            'heads' => $heads,
            'tails' => $tails,
            'history' => $history,
        ]);
    }

    public function coinFlipHistory(int $duel): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        $heads = $duel->coinFlips()->where('result', 'heads')->count();
        $tails = $duel->coinFlips()->where('result', 'tails')->count();
        $history = $duel->coinFlips()->latest()->limit(20)->get()->map(fn ($f) => [
            'result' => $f->result,
            'time' => $f->created_at->format('H:i:s'),
        ])->toArray();

        return response()->json([
            'heads' => $heads,
            'tails' => $tails,
            'history' => $history,
        ]);
    }

    public function rollDice(int $duel): JsonResponse
    {
        $duel = Duel::findOrFail($duel);
        $count = (int) request()->input('count', 1);
        $count = max(1, min(10, $count));

        $results = [];
        $total = 0;
        for ($i = 0; $i < $count; $i++) {
            $roll = random_int(1, 6);
            $results[] = $roll;
            $total += $roll;
        }

        $roll = DiceRoll::create([
            'duel_id' => $duel->id,
            'dice_count' => $count,
            'results' => $results,
            'total' => $total,
        ]);

        $history = $duel->diceRolls()->latest()->limit(10)->get()->map(fn ($r) => [
            'count' => $r->dice_count,
            'results' => $r->results,
            'total' => $r->total,
            'time' => $r->created_at->format('H:i:s'),
        ])->toArray();

        return response()->json([
            'count' => $count,
            'results' => $results,
            'total' => $total,
            'history' => $history,
        ]);
    }

    public function diceHistory(int $duel): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        $history = $duel->diceRolls()->latest()->limit(10)->get()->map(fn ($r) => [
            'count' => $r->dice_count,
            'results' => $r->results,
            'total' => $r->total,
            'time' => $r->created_at->format('H:i:s'),
        ])->toArray();

        return response()->json(['history' => $history]);
    }

    // Reminders
    public function listReminders(int $duel): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        $highlighted = $duel->reminders()
            ->where('completed', false)
            ->get()
            ->filter(fn (Reminder $reminder): bool => ($reminder->trigger_turn !== null || $reminder->trigger_phase !== null)
                && ($reminder->trigger_turn === null || $reminder->trigger_turn === $duel->turn)
                && ($reminder->trigger_phase === null || $reminder->trigger_phase === $duel->phase->value))
            ->pluck('id')
            ->all();

        $reminders = $duel->reminders()->orderBy('completed')->orderBy('created_at')->get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'description' => $r->description,
            'completed' => $r->completed,
            'highlighted' => in_array($r->id, $highlighted, true),
            'trigger_turn' => $r->trigger_turn,
            'trigger_phase' => $r->trigger_phase,
        ])->toArray();

        return response()->json(['reminders' => $reminders]);
    }

    public function saveReminder(int $duel): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        $validated = request()->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'trigger_turn' => 'nullable|integer',
            'trigger_phase' => 'nullable|string',
        ]);

        Reminder::create([
            'duel_id' => $duel->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_turn' => $validated['trigger_turn'] ?? null,
            'trigger_phase' => $validated['trigger_phase'] ?? null,
            'completed' => false,
        ]);

        return $this->listReminders($duel->id);
    }

    public function toggleReminder(int $duel, int $reminder): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        $reminderModel = Reminder::where('duel_id', $duel->id)->findOrFail($reminder);
        $reminderModel->update(['completed' => ! $reminderModel->completed]);

        return $this->listReminders($duel->id);
    }

    public function deleteReminder(int $duel, int $reminder): JsonResponse
    {
        $duel = Duel::findOrFail($duel);

        Reminder::where('duel_id', $duel->id)->where('id', $reminder)->delete();

        return $this->listReminders($duel->id);
    }
}
