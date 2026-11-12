@if ($isTwoPlayerDuel ?? false)
    @include('livewire.arena-two-player')
@else
    @include('livewire.arena-multi-duelists')
@endif
