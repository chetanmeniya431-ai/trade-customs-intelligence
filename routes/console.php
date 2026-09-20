<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('signals:check')->hourly();
Schedule::command('embeddings:process-shipments')->everyThirtyMinutes();
Schedule::command('documents:check-expiry')->daily();
