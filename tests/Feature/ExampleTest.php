<?php

test('the root sends customers to the cake builder', function () {
    $this->get(route('home'))->assertRedirect(route('cake-studio.index'));
});
