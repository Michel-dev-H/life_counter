<?php

test('opens the duel setup from the root', function () {
    $this->get('/')->assertRedirect(route('duel.setup'));
});
