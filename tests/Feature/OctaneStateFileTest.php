<?php

it('keeps the octane state file out of the shared logs directory', function () {
    expect(config('octane.state_file'))
        ->toBe(storage_path('framework/octane-server-state.json'));
});
